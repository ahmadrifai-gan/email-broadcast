<?php

namespace App\Controllers;

use App\Libraries\EmailSender;
use App\Models\JadwalKampanyeModel;
use App\Models\KampanyeEmailModel;
use App\Models\KategoriTemplateEmailModel;
use App\Models\KontakModel;
use App\Models\PenerimaKampanyeModel;
use App\Models\RiwayatPengirimanEmailModel;
use App\Models\SmtpSettingModel;
use App\Models\TemplateEmailModel;

/**
 * 4 mode pengiriman:
 *   sekarang           : kirim langsung ke semua penerima (1 pengiriman)
 *   jadwal             : terjadwal, 1 pengiriman ke semua penerima
 *   sekarang_berulang  : mulai sekarang, penerima DIBAGI ke beberapa pengiriman
 *   jadwal_berulang    : mulai pada waktu tertentu, penerima DIBAGI ke beberapa pengiriman
 *
 * Mode "berulang" TIDAK mengulang pesan ke orang yang sama: setiap akun hanya menerima 1 kali,
 * pada pengiriman yang ditentukan rencananya (jumlah pengiriman, akun tiap pengiriman, dan jarak
 * waktunya bisa diatur sendiri atau diacak -- rencana dibuat di browser, server hanya memvalidasi).
 *
 * Setiap pengiriman = 1 baris di tabel jadwal_kampanye. Pengiriman berjalan berurutan.
 *
 * SMTP pengirim: kampanye bisa memilih SMTP tertentu (kolom smtp_setting_id).
 * Prioritas resolve: kampanye.smtp_setting_id → smtp_settings.is_active=1 → .env.
 */
class KirimEmailController extends BaseController
{
    protected const MODE            = ['sekarang', 'sekarang_berulang', 'jadwal', 'jadwal_berulang'];
    protected const MAKS_PENGIRIMAN = 200;
    protected const MAKS_KECEPATAN  = 200;

    protected $kampanyeModel;
    protected $jadwalModel;
    protected $penerimaModel;
    protected $riwayatModel;
    protected $templateModel;
    protected $kategoriModel;
    protected $kontakModel;
    protected $smtpModel;         // ▼▼ SMTP ▼▼
    protected $emailSender;

    protected bool $batasTercapai = false;
    protected int  $tungguDetik   = 0;

    public function __construct()
    {
        $this->kampanyeModel = new KampanyeEmailModel();
        $this->jadwalModel   = new JadwalKampanyeModel();
        $this->penerimaModel = new PenerimaKampanyeModel();
        $this->riwayatModel  = new RiwayatPengirimanEmailModel();
        $this->templateModel = new TemplateEmailModel();
        $this->kategoriModel = new KategoriTemplateEmailModel();
        $this->kontakModel   = new KontakModel();
        $this->smtpModel     = new SmtpSettingModel();   // ▼▼ SMTP ▼▼
        $this->emailSender   = new EmailSender();
    }

    protected function minGapMenit(): int
    {
        return max(1, (int) env('EMAIL_MIN_INTERVAL_MENIT', 1));
    }

    protected function isBerulang(string $mode): bool
    {
        return in_array($mode, ['sekarang_berulang', 'jadwal_berulang'], true);
    }

    // =========================================================
    //  LIST
    // =========================================================
    public function index()
    {
        return view('kirim_email/index', [
            'kampanye' => $this->kampanyeModel->getWithTemplate(),
        ]);
    }

    // =========================================================
    //  FORM BUAT KAMPANYE
    // =========================================================
    public function buat()
    {
        $kategori = $this->kategoriModel
            ->where('status', 'aktif')
            ->orderBy('nama_kategori', 'ASC')
            ->findAll();

        $templates  = $this->templateModel->where('status !=', 'arsip')
            ->orderBy('nama_template', 'ASC')->findAll();
        $templateJs = array_map(static fn($t) => [
            'id'          => (int) $t['id'],
            'kategori_id' => (int) $t['kategori_template_id'],
            'nama'        => $t['nama_template'],
            'subjek'      => $t['subjek_email'],
            'html'        => $t['isi_html'],
            'teks'        => $t['isi_teks'] ?? '',
        ], $templates);

        $kontak = $this->kontakModel
            ->where('email IS NOT NULL', null, false)
            ->where('email !=', '')
            ->orderBy('nama_perusahaan', 'ASC')
            ->findAll();

        // ▼▼ SMTP ▼▼ daftar SMTP untuk dropdown (aktif dulu, lalu urut nama)
        $smtpList = $this->smtpModel
            ->orderBy('is_active', 'DESC')
            ->orderBy('nama', 'ASC')
            ->findAll();

        return view('kirim_email/buat', [
            'kategori'       => $kategori,
            'templateJs'     => $templateJs,
            'kontak'         => $kontak,
            'smtpList'       => $smtpList,               // ▼▼ SMTP ▼▼
            'minGap'         => $this->minGapMenit(),
            'maksPengiriman' => self::MAKS_PENGIRIMAN,
            'ambangBanyak'   => max(1, (int) env('EMAIL_THROTTLE_MIN_PENERIMA', 50)),
            'batasHarian'    => (int) env('EMAIL_DAILY_LIMIT', 450),
            'maksKecepatan'  => self::MAKS_KECEPATAN,
        ]);
    }

    // =========================================================
    //  SIMPAN KAMPANYE
    // =========================================================
    public function simpan()
    {
        if (! $this->validate([
            'template_email_id' => 'required|is_natural_no_zero',
            'nama_kampanye'     => 'required|max_length[150]',
            'subjek_email'      => 'required|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $penerimaIds = $this->request->getPost('penerima');
        if (empty($penerimaIds) || ! is_array($penerimaIds)) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal 1 penerima.');
        }
        $penerimaIds = array_values(array_unique(array_map('intval', $penerimaIds)));

        $templateId = (int) $this->request->getPost('template_email_id');
        $template   = $this->templateModel->find($templateId);
        if (! $template || $template['status'] === 'arsip') {
            return redirect()->back()->withInput()->with('error', 'Template tidak ditemukan.');
        }

        // ▼▼ SMTP ▼▼ Validasi SMTP pilihan (kosong = pakai default aktif)
        $smtpId = (int) ($this->request->getPost('smtp_setting_id') ?: 0);
        if ($smtpId > 0 && ! $this->smtpModel->find($smtpId)) {
            return redirect()->back()->withInput()
                ->with('error', 'SMTP yang dipilih tidak ditemukan. Silakan pilih ulang.');
        }
        $smtpId = $smtpId > 0 ? $smtpId : null;

        $kontakList = $this->kontakModel
            ->whereIn('id', $penerimaIds)
            ->where('email IS NOT NULL', null, false)
            ->where('email !=', '')
            ->findAll();
        if (empty($kontakList)) {
            return redirect()->back()->withInput()->with('error', 'Kontak tidak valid.');
        }
        $kontakById = [];
        foreach ($kontakList as $k) {
            $kontakById[(int) $k['id']] = $k;
        }

        $j = $this->bangunJadwal(array_keys($kontakById));
        if (isset($j['error'])) {
            return redirect()->back()->withInput()->with('error', $j['error']);
        }
        $mode  = $j['mode'];
        $waktu = $j['waktu'];

        $assign = [];
        if ($j['akun'] === null) {
            foreach ($kontakById as $id => $_) {
                $assign[$id] = 1;
            }
        } else {
            foreach ($j['akun'] as $i => $ids) {
                foreach ($ids as $id) {
                    $assign[$id] = $i + 1;
                }
            }
        }
        $kontakList = array_values(array_filter($kontakList, static fn($k) => isset($assign[(int) $k['id']])));

        $kecMin  = null;
        $kecMaks = null;
        if (! $this->isBerulang($mode)) {
            $kecMode = (string) $this->request->getPost('kecepatan_mode');
            if ($kecMode === 'bertahap') {
                $kecMin = (int) $this->request->getPost('kecepatan_per_menit');
                if ($kecMin < 1 || $kecMin > self::MAKS_KECEPATAN) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Kecepatan kirim harus antara 1 dan ' . self::MAKS_KECEPATAN . ' email per menit.');
                }
            } elseif ($kecMode === 'acak') {
                $kecMin  = (int) $this->request->getPost('kecepatan_min');
                $kecMaks = (int) $this->request->getPost('kecepatan_maks');
                if ($kecMin < 1 || $kecMaks < $kecMin || $kecMaks > self::MAKS_KECEPATAN) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Rentang acak harus 1 - ' . self::MAKS_KECEPATAN . ' email per menit dan nilai maksimal >= minimal.');
                }
                if ($kecMaks === $kecMin) {
                    $kecMaks = null;
                }
            }
        }

        $tipeKolom = ['sekarang' => 'sekarang', 'sekarang_berulang' => 'sekarang_berulang',
                      'jadwal' => 'terjadwal', 'jadwal_berulang' => 'terjadwal_berulang'][$mode];

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $db->transStart();

        $kampanyeId = $this->kampanyeModel->insert([
            'template_email_id'   => $templateId,
            'smtp_setting_id'     => $smtpId,                // ▼▼ SMTP ▼▼
            'nama_kampanye'       => $this->request->getPost('nama_kampanye'),
            'subjek_email'        => $this->request->getPost('subjek_email'),
            'status'              => 'terjadwal',
            'tipe_pengiriman'     => $tipeKolom,
            'jadwal_pengiriman'   => date('Y-m-d H:i:s', $waktu[0]),
            'total_putaran'       => count($waktu),
            'jumlah_putaran'      => 0,
            'kecepatan_per_menit' => $kecMin,
            'kecepatan_maks'      => $kecMaks,
            'jumlah_penerima'     => count($kontakList),
            'jumlah_berhasil'     => 0,
            'jumlah_gagal'        => 0,
        ]);

        if ($kampanyeId) {
            $hitung = array_count_values($assign);

            $rowsJadwal = [];
            foreach ($waktu as $i => $ts) {
                $rowsJadwal[] = [
                    'kampanye_email_id' => $kampanyeId,
                    'putaran_ke'        => $i + 1,
                    'waktu_kirim'       => date('Y-m-d H:i:s', $ts),
                    'status'            => 'menunggu',
                    'jumlah_penerima'   => $hitung[$i + 1] ?? 0,
                    'dibuat_pada'       => $now,
                    'diperbarui_pada'   => $now,
                ];
            }
            $this->jadwalModel->insertBatch($rowsJadwal);

            $idPutaran = [];
            foreach ($this->jadwalModel->getByKampanye((int) $kampanyeId) as $jd) {
                $idPutaran[(int) $jd['putaran_ke']] = (int) $jd['id'];
            }

            $rows = [];
            foreach ($kontakList as $k) {
                $rows[] = [
                    'kampanye_email_id'     => $kampanyeId,
                    'kontak_email_id'       => $k['id'],
                    'jadwal_kampanye_id'    => $idPutaran[$assign[(int) $k['id']]] ?? null,
                    'alamat_email_penerima' => $k['email'],
                    'nama_penerima'         => $k['nama_perusahaan'],
                    'status_pengiriman'     => 'menunggu',
                    'jumlah_percobaan'      => 0,
                ];
            }
            $this->penerimaModel->insertBatch($rows);
        }

        $db->transComplete();

        if (! $kampanyeId || $db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan kampanye.');
        }

        $total = count($waktu);
        if (in_array($mode, ['sekarang', 'sekarang_berulang'], true)) {
            $pesan = 'Kampanye dibuat, pengiriman dimulai...'
                   . ($total > 1 ? " ({$total} pengiriman, " . count($kontakList) . ' akun dibagi bertahap)' : '');
            return redirect()->to('kirim-email/detail/' . $kampanyeId . '?auto=1')->with('success', $pesan);
        }

        $pesan = $total > 1
            ? "Kampanye dijadwalkan: {$total} pengiriman untuk " . count($kontakList) . ' akun, dimulai ' . date('Y-m-d H:i', $waktu[0]) . '.'
            : 'Kampanye dijadwalkan pada ' . date('Y-m-d H:i', $waktu[0]) . '. Akan dikirim otomatis.';

        return redirect()->to('kirim-email/detail/' . $kampanyeId)->with('success', $pesan);
    }

    protected function bangunJadwal(array $idTersedia): array
    {
        $now  = time();
        $mode = (string) $this->request->getPost('mode_kirim');
        if (! in_array($mode, self::MODE, true)) {
            $mode = 'sekarang';
        }
        $berulang   = $this->isBerulang($mode);
        $butuhWaktu = in_array($mode, ['jadwal', 'jadwal_berulang'], true);
        $minGap     = $this->minGapMenit();

        if ($butuhWaktu) {
            $raw = trim((string) $this->request->getPost('jadwal_pengiriman'));
            $t1  = $raw !== '' ? strtotime($raw) : false;
            if (! $t1) {
                return ['error' => 'Tanggal & jam pengiriman wajib diisi.'];
            }
            if ($t1 <= $now) {
                return ['error' => 'Waktu pengiriman harus di masa depan.'];
            }
        } else {
            $t1 = $now;
        }

        if (! $berulang) {
            return ['mode' => $mode, 'waktu' => [$t1], 'akun' => null];
        }

        $total  = (int) $this->request->getPost('total_putaran');
        $offset = $this->request->getPost('plan_offset');
        $akun   = $this->request->getPost('plan_akun');

        if ($total < 2 || $total > self::MAKS_PENGIRIMAN
            || ! is_array($offset) || ! is_array($akun)
            || count($offset) !== $total || count($akun) !== $total) {
            return ['error' => 'Rencana pengiriman belum lengkap. Buka pengaturan jadwal lalu buat rencana (minimal 2 pengiriman).'];
        }
        $offset = array_values($offset);
        $akun   = array_values($akun);

        $waktu = [];
        $plan  = [];
        $dipakai = [];
        $prevOffset = 0;

        for ($i = 0; $i < $total; $i++) {
            $ke = $i + 1;
            $o  = filter_var($offset[$i], FILTER_VALIDATE_INT);
            if ($o === false || $o < 0 || $o > 525600) {
                return ['error' => "Pengiriman ke-{$ke}: waktu tidak valid."];
            }
            if ($i === 0 && $o !== 0) {
                return ['error' => 'Pengiriman pertama harus berada di titik awal (offset 0).'];
            }
            if ($i > 0 && ($o - $prevOffset) < $minGap) {
                return ['error' => "Pengiriman ke-{$ke} harus minimal {$minGap} menit setelah pengiriman ke-{$i}."];
            }

            $ids = array_values(array_unique(array_filter(
                array_map('intval', explode(',', (string) $akun[$i])),
                static fn($v) => $v > 0
            )));
            if (empty($ids)) {
                return ['error' => "Pengiriman ke-{$ke} belum punya akun."];
            }
            foreach ($ids as $id) {
                if (! in_array($id, $idTersedia, true)) {
                    return ['error' => "Pengiriman ke-{$ke}: ada akun yang tidak valid / tidak punya email."];
                }
                if (isset($dipakai[$id])) {
                    return ['error' => "Pengiriman ke-{$ke}: satu akun hanya boleh ada di satu pengiriman."];
                }
                $dipakai[$id] = true;
            }

            $waktu[]    = $t1 + $o * 60;
            $plan[]     = $ids;
            $prevOffset = $o;
        }

        return ['mode' => $mode, 'waktu' => $waktu, 'akun' => $plan];
    }

    // =========================================================
    //  DETAIL
    // =========================================================
    public function detail($id)
    {
        $kampanye = $this->kampanyeModel->find($id);
        if (! $kampanye) {
            return redirect()->to('kirim-email')->with('error', 'Data tidak ditemukan.');
        }

        // ▼▼ SMTP ▼▼ info SMTP yang dipakai kampanye ini
        $smtpInfo = null;
        if (! empty($kampanye['smtp_setting_id'])) {
            $smtpInfo = $this->smtpModel->find((int) $kampanye['smtp_setting_id']);
        }
        if (! $smtpInfo) {
            $smtpInfo = $this->smtpModel->getActive(); // default aktif (kalau ada)
        }

        return view('kirim_email/detail', [
            'kampanye' => $kampanye,
            'template' => $this->templateModel->withDeleted()->find($kampanye['template_email_id']),
            'penerima' => $this->penerimaModel->getByKampanye((int) $id),
            'jadwal'   => $this->jadwalModel->getByKampanye((int) $id),
            'sisa'     => $this->penerimaModel->getPendingCount((int) $id),
            'smtpInfo' => $smtpInfo,            // ▼▼ SMTP ▼▼
            'auto'     => (bool) $this->request->getGet('auto'),
        ]);
    }

    // =========================================================
    //  PROSES 1 BATCH (AJAX)
    // =========================================================
    public function proses($id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $kampanye = $this->kampanyeModel->find($id);
        if (! $kampanye) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kampanye tidak ditemukan.', 'csrf' => csrf_hash()]);
        }

        if (in_array($kampanye['status'], ['dijeda', 'dibatalkan'], true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kampanye sedang ' . $kampanye['status'] . '.',
                'csrf'    => csrf_hash(),
            ]);
        }

        $mulai = (bool) $this->request->getGet('mulai');
        if (in_array($kampanye['status'], ['draf', 'terjadwal'], true)) {
            if (! $mulai) {
                return $this->response->setJSON(array_merge(
                    ['success' => true, 'batch' => [], 'batas_harian' => false, 'tunggu_detik' => 0, 'csrf' => csrf_hash()],
                    $this->ringkasStatus((int) $id),
                    ['selesai' => true]
                ));
            }
            $this->mulaiPutaran((int) $id);
        }

        $hasil = $this->prosesBatchInternal((int) $id, 5);
        $stat  = $this->hitungUlang((int) $id);

        return $this->response->setJSON(array_merge($stat, [
            'success'      => true,
            'batch'        => $hasil,
            'batas_harian' => $this->batasTercapai,
            'tunggu_detik' => $this->tungguDetik,
            'csrf'         => csrf_hash(),
        ]));
    }

    // =========================================================
    //  POLLING BROWSER
    // =========================================================
    public function prosesTerjadwal()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $ids = $this->klaimKampanyeSiap(120);

        return $this->response->setJSON([
            'success' => true,
            'ids'     => $ids,
            'message' => count($ids) . ' kampanye siap diproses.',
            'csrf'    => csrf_hash(),
        ]);
    }

    // =========================================================
    //  CRON URL
    // =========================================================
    public function cron($token = null)
    {
        $expected = env('EMAIL_CRON_TOKEN', '');
        if (empty($expected) || ! hash_equals((string) $expected, (string) $token)) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Token tidak valid.']);
        }

        @set_time_limit(120);
        $perRun = max(1, (int) env('EMAIL_BATCH_PER_RUN', 10));
        $ids    = $this->klaimKampanyeSiap(15);
        $mulai  = time();
        $kirim  = 0;

        foreach ($ids as $id) {
            $k = $this->kampanyeModel->find($id);
            if ($this->isBerulang($this->modeDariTipe((string) ($k['tipe_pengiriman'] ?? '')))) {
                $cap = 500;
            } else {
                $kec = $this->kuotaMenitIni($k);
                $cap = $kec !== null ? $kec['rate'] : $perRun;
            }

            $kirimK = 0;
            while ($kirimK < $cap && ! $this->batasTercapai && (time() - $mulai) < 50) {
                $batch   = $this->prosesBatchInternal($id, min(10, $cap - $kirimK));
                $kirimK += count($batch);
                $stat    = $this->hitungUlang($id);
                if ($stat['selesai'] || empty($batch)) {
                    break;
                }
            }
            $kirim += $kirimK;

            if ($this->batasTercapai || (time() - $mulai) >= 50) {
                break;
            }
        }

        return $this->response->setJSON([
            'success'      => true,
            'processed'    => count($ids),
            'dikirim'      => $kirim,
            'per_run'      => $perRun,
            'batas_harian' => $this->batasTercapai,
        ]);
    }

    protected function modeDariTipe(string $tipe): string
    {
        return ['terjadwal' => 'jadwal', 'terjadwal_berulang' => 'jadwal_berulang'][$tipe] ?? $tipe;
    }

    // =========================================================
    //  KIRIM ULANG
    // =========================================================
    public function kirimUlang($penerimaId)
    {
        $p = $this->penerimaModel->find($penerimaId);
        if (! $p) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $k = $this->kampanyeModel->find($p['kampanye_email_id']);
        if (! $k || $k['status'] !== 'selesai') {
            return redirect()->back()->with('error', 'Kirim ulang hanya tersedia untuk kampanye yang sudah selesai.');
        }

        $this->penerimaModel->update($penerimaId, [
            'status_pengiriman' => 'menunggu',
            'jumlah_percobaan'  => 0,
            'pesan_kesalahan'   => null,
        ]);
        $this->kampanyeModel->update($k['id'], [
            'status'               => 'sedang_dikirim',
            'selesai_dikirim_pada' => null,
        ]);

        return redirect()->to('kirim-email/detail/' . $p['kampanye_email_id'] . '?auto=1')
            ->with('success', 'Penerima dikirim ulang...');
    }

    // =========================================================
    //  JEDA / LANJUTKAN / BATALKAN
    // =========================================================
    public function jeda($id)
    {
        $db = \Config\Database::connect();
        $db->table('kampanye_email')->where('id', $id)
            ->whereIn('status', ['terjadwal', 'sedang_dikirim'])
            ->update(['status' => 'dijeda', 'diperbarui_pada' => date('Y-m-d H:i:s')]);

        if ($db->affectedRows() < 1) {
            return redirect()->to('kirim-email/detail/' . $id)->with('error', 'Kampanye tidak bisa dijeda pada status ini.');
        }
        return redirect()->to('kirim-email/detail/' . $id)->with('success', 'Kampanye dijeda. Tidak ada email yang dikirim sampai dilanjutkan.');
    }

    public function lanjut($id)
    {
        $k = $this->kampanyeModel->find($id);
        if (! $k || $k['status'] !== 'dijeda') {
            return redirect()->to('kirim-email/detail/' . $id)->with('error', 'Kampanye tidak sedang dijeda.');
        }

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        if ($this->jadwalModel->getBerjalan((int) $id)) {
            $this->kampanyeModel->update($id, ['status' => 'sedang_dikirim']);
            return redirect()->to('kirim-email/detail/' . $id . '?auto=1')->with('success', 'Pengiriman dilanjutkan.');
        }

        $next = $this->jadwalModel->getBerikutnya((int) $id);
        if (! $next) {
            $this->kampanyeModel->update($id, ['status' => 'selesai']);
            return redirect()->to('kirim-email/detail/' . $id)->with('success', 'Tidak ada jadwal tersisa; kampanye ditandai selesai.');
        }

        $delta = time() - strtotime($next['waktu_kirim']);
        if ($delta > 0) {
            $delta += 60;
            $db->query(
                "UPDATE jadwal_kampanye SET waktu_kirim = DATE_ADD(waktu_kirim, INTERVAL ? SECOND), diperbarui_pada = ?
                 WHERE kampanye_email_id = ? AND status = 'menunggu'",
                [$delta, $now, (int) $id]
            );
            $next = $this->jadwalModel->getBerikutnya((int) $id);
        }

        $this->kampanyeModel->update($id, ['status' => 'terjadwal', 'jadwal_pengiriman' => $next['waktu_kirim']]);

        return redirect()->to('kirim-email/detail/' . $id)
            ->with('success', 'Kampanye dilanjutkan. Pengiriman berikutnya: ' . $next['waktu_kirim']);
    }

    public function batal($id)
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->table('kampanye_email')->where('id', $id)
            ->whereIn('status', ['terjadwal', 'sedang_dikirim', 'dijeda'])
            ->update(['status' => 'dibatalkan', 'selesai_dikirim_pada' => $now, 'diperbarui_pada' => $now]);

        if ($db->affectedRows() < 1) {
            return redirect()->to('kirim-email/detail/' . $id)->with('error', 'Kampanye tidak bisa dibatalkan pada status ini.');
        }

        $db->table('jadwal_kampanye')->where('kampanye_email_id', $id)
            ->whereIn('status', ['menunggu', 'berjalan'])
            ->update(['status' => 'dilewati', 'diperbarui_pada' => $now]);

        return redirect()->to('kirim-email/detail/' . $id)->with('success', 'Kampanye dibatalkan. Sisa jadwal tidak akan dikirim.');
    }

    // =========================================================
    //  HAPUS KAMPANYE
    // =========================================================
    public function hapus($id)
    {
        $this->kampanyeModel->delete($id);
        return redirect()->to('kirim-email')->with('success', 'Kampanye dihapus.');
    }

    // =========================================================
    //  HELPER: SIKLUS PENGIRIMAN
    // =========================================================
    protected function mulaiPutaran(int $id): bool
    {
        $k = $this->kampanyeModel->find($id);
        if (! $k) {
            return false;
        }

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->table('kampanye_email')
            ->where('id', $id)
            ->whereIn('status', ['draf', 'terjadwal'])
            ->update([
                'status'               => 'sedang_dikirim',
                'mulai_dikirim_pada'   => $k['mulai_dikirim_pada'] ?: $now,
                'selesai_dikirim_pada' => null,
                'diperbarui_pada'      => $now,
            ]);

        if ($db->affectedRows() < 1) {
            return false;
        }

        $next = $this->jadwalModel->getBerikutnya($id);
        if ($next) {
            $db->table('jadwal_kampanye')->where('id', $next['id'])->where('status', 'menunggu')
                ->update(['status' => 'berjalan', 'dimulai_pada' => $now, 'diperbarui_pada' => $now]);
        }

        return true;
    }

    protected function klaimKampanyeSiap(int $macetDetik = 120): array
    {
        $db    = \Config\Database::connect();
        $now   = date('Y-m-d H:i:s');
        $macet = date('Y-m-d H:i:s', time() - $macetDetik);

        $kandidat = $db->table('kampanye_email')
            ->select('id, status')
            ->groupStart()
                ->where('status', 'terjadwal')
                ->where('jadwal_pengiriman <=', $now)
            ->groupEnd()
            ->orGroupStart()
                ->where('status', 'sedang_dikirim')
                ->where('diperbarui_pada <', $macet)
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $ids = [];
        foreach ($kandidat as $k) {
            if ($k['status'] === 'terjadwal') {
                if ($this->mulaiPutaran((int) $k['id'])) {
                    $ids[] = (int) $k['id'];
                }
            } else {
                $db->table('kampanye_email')
                    ->where('id', $k['id'])
                    ->where('status', 'sedang_dikirim')
                    ->where('diperbarui_pada <', $macet)
                    ->update(['diperbarui_pada' => $now]);
                if ($db->affectedRows() > 0) {
                    $ids[] = (int) $k['id'];
                }
            }
        }

        return $ids;
    }

    protected function ringkasStatus(int $id): array
    {
        $berhasil = $this->penerimaModel->where('kampanye_email_id', $id)
            ->where('status_pengiriman', 'terkirim')->countAllResults();
        $gagal = $this->penerimaModel->where('kampanye_email_id', $id)
            ->where('status_pengiriman', 'gagal')->countAllResults();
        $sisa = $this->penerimaModel->getPendingCount($id);
        $k    = $this->kampanyeModel->find($id) ?: [];

        return [
            'berhasil'      => $berhasil,
            'gagal'         => $gagal,
            'sisa'          => $sisa,
            'status'        => $k['status'] ?? '',
            'berikutnya'    => (($k['status'] ?? '') === 'terjadwal') ? ($k['jadwal_pengiriman'] ?? null) : null,
            'putaran'       => (int) ($k['jumlah_putaran'] ?? 0),
            'total_putaran' => (int) ($k['total_putaran'] ?? 1),
        ];
    }

    protected function hitungUlang(int $id): array
    {
        $r = $this->ringkasStatus($id);
        $this->kampanyeModel->update($id, ['jumlah_berhasil' => $r['berhasil'], 'jumlah_gagal' => $r['gagal']]);

        $berjalan = $this->jadwalModel->getBerjalan($id);
        $sisaCek = $this->penerimaModel->getPendingCount($id, $berjalan ? (int) $berjalan['id'] : null);

        if ($sisaCek === 0) {
            $this->akhiriPutaran($id);
            $r = $this->ringkasStatus($id);
        }
        $r['selesai'] = ($sisaCek === 0);

        return $r;
    }

    protected function akhiriPutaran(int $id): void
    {
        $k = $this->kampanyeModel->find($id);
        if (! $k || $k['status'] !== 'sedang_dikirim') {
            return;
        }

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $berjalan = $this->jadwalModel->getBerjalan($id);
        if ($berjalan) {
            $jid = (int) $berjalan['id'];
            $ok  = $this->penerimaModel->where('kampanye_email_id', $id)->where('jadwal_kampanye_id', $jid)
                ->where('status_pengiriman', 'terkirim')->countAllResults();
            $gl  = $this->penerimaModel->where('kampanye_email_id', $id)->where('jadwal_kampanye_id', $jid)
                ->where('status_pengiriman', 'gagal')->countAllResults();

            $db->table('jadwal_kampanye')->where('id', $jid)->where('status', 'berjalan')
                ->update([
                    'status'          => 'selesai',
                    'selesai_pada'    => $now,
                    'jumlah_berhasil' => $ok,
                    'jumlah_gagal'    => $gl,
                    'diperbarui_pada' => $now,
                ]);
            if ($db->affectedRows() < 1) {
                return;
            }
        }

        $data = [
            'selesai_dikirim_pada' => $now,
            'diperbarui_pada'      => $now,
            'jumlah_putaran'       => $this->jadwalModel->hitungSelesai($id),
        ];

        $next = $this->jadwalModel->getBerikutnya($id);
        if ($next) {
            $data['status']            = 'terjadwal';
            $data['jadwal_pengiriman'] = $next['waktu_kirim'];
        } else {
            $data['status'] = 'selesai';
        }

        $db->table('kampanye_email')->where('id', $id)->where('status', 'sedang_dikirim')->update($data);
    }

    protected function sisaJatahHarian(): ?int
    {
        $limit = (int) env('EMAIL_DAILY_LIMIT', 450);
        if ($limit <= 0) {
            return null;
        }
        $dipakai = $this->riwayatModel
            ->where('jenis_aktivitas', 'terkirim')
            ->where('terjadi_pada >=', date('Y-m-d H:i:s', time() - 86400))
            ->countAllResults();

        return max(0, $limit - $dipakai);
    }

    protected function kuotaMenitIni(array $kampanye): ?array
    {
        $min = (int) ($kampanye['kecepatan_per_menit'] ?? 0);
        if ($min <= 0) {
            return null;
        }
        $maks = (int) ($kampanye['kecepatan_maks'] ?? 0);
        $rate = $min;
        if ($maks > $min) {
            $rate = $min + (crc32($kampanye['id'] . '|' . date('YmdHi')) % ($maks - $min + 1));
        }

        $terpakai = \Config\Database::connect()->table('riwayat_pengiriman_email r')
            ->join('penerima_kampanye p', 'p.id = r.penerima_kampanye_id')
            ->where('p.kampanye_email_id', $kampanye['id'])
            ->where('r.terjadi_pada >=', date('Y-m-d H:i:00'))
            ->countAllResults();

        return ['rate' => $rate, 'sisa' => max(0, $rate - $terpakai)];
    }

    /**
     * Kirim 1 batch email untuk pengiriman yang sedang berjalan.
     * ▼▼ SMTP ▼▼: config SMTP di-resolve sekali per batch:
     *    kampanye.smtp_setting_id → SMTP aktif global → .env
     */
    protected function prosesBatchInternal(int $kampanyeId, int $limit = 5): array
    {
        $this->tungguDetik = 0;
        $kampanye = $this->kampanyeModel->find($kampanyeId);
        if (! $kampanye || $kampanye['status'] !== 'sedang_dikirim') {
            return [];
        }

        $template = $this->templateModel->withDeleted()->find($kampanye['template_email_id']);
        if (! $template) {
            return [];
        }
        $template['subjek_email'] = $kampanye['subjek_email'];

        $jatah = $this->sisaJatahHarian();
        if ($jatah !== null) {
            if ($jatah <= 0) {
                $this->batasTercapai = true;
                return [];
            }
            $limit = min($limit, $jatah);
        }

        $kuota = $this->kuotaMenitIni($kampanye);
        if ($kuota !== null) {
            if ($kuota['sisa'] <= 0) {
                $this->tungguDetik = max(1, 60 - (int) date('s'));
                return [];
            }
            $limit = min($limit, $kuota['sisa']);
        }

        $berjalan = $this->jadwalModel->getBerjalan($kampanyeId);
        $batch    = $this->penerimaModel->getPendingBatch($kampanyeId, $limit, $berjalan ? (int) $berjalan['id'] : null);
        if (empty($batch)) {
            return [];
        }

        $kontakIds = array_filter(array_column($batch, 'kontak_email_id'));
        $kontakMap = [];
        if (! empty($kontakIds)) {
            foreach ($this->kontakModel->whereIn('id', $kontakIds)->findAll() as $k) {
                $kontakMap[$k['id']] = $k;
            }
        }

        // ▼▼ SMTP ▼▼ resolve config SMTP sekali untuk seluruh batch
        $smtpConfig = (new \App\Libraries\SmtpConfigurator())
            ->forKampanye($kampanye['smtp_setting_id'] ?? null);

        $hasil = [];
        foreach ($batch as $p) {
            if (! $this->penerimaModel->klaim((int) $p['id'])) {
                continue;
            }

            $kontak       = $kontakMap[$p['kontak_email_id']] ?? [];
            $penerimaData = array_merge($p, [
                'nama_perusahaan' => $kontak['nama_perusahaan'] ?? ($p['nama_penerima'] ?? ''),
                'domisili'        => $kontak['domisili'] ?? '',
            ]);

            // ▼▼ SMTP ▼▼ kirim dengan config SMTP yang sudah di-resolve
            $res = $this->emailSender->kirimSatu($penerimaData, $template, $smtpConfig);

            $this->penerimaModel->update($p['id'], [
                'jumlah_percobaan'  => ((int) $p['jumlah_percobaan']) + 1,
                'status_pengiriman' => $res['sukses'] ? 'terkirim' : 'gagal',
                'id_pesan_penyedia' => $res['id_pesan'],
                'terkirim_pada'     => $res['sukses'] ? date('Y-m-d H:i:s') : null,
                'pesan_kesalahan'   => $res['sukses'] ? null : mb_substr($res['pesan'], 0, 500),
            ]);

            $this->riwayatModel->insert([
                'penerima_kampanye_id' => $p['id'],
                'jenis_aktivitas'      => $res['sukses'] ? 'terkirim' : 'gagal',
                'penyedia_email'       => 'smtp',
                'id_pesan_penyedia'    => $res['id_pesan'],
                'detail_aktivitas'     => mb_substr($res['pesan'], 0, 1000),
                'terjadi_pada'         => date('Y-m-d H:i:s'),
            ]);

            $hasil[] = [
                'id'     => $p['id'],
                'email'  => $p['alamat_email_penerima'],
                'sukses' => $res['sukses'],
                'pesan'  => mb_substr($res['pesan'], 0, 200),
            ];
        }

        return $hasil;
    }
}