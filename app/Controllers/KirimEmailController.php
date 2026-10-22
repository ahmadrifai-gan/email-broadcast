<?php

namespace App\Controllers;

use App\Libraries\EmailSender;
use App\Models\KampanyeEmailModel;
use App\Models\KategoriTemplateEmailModel;
use App\Models\KontakModel;
use App\Models\PenerimaKampanyeModel;
use App\Models\RiwayatPengirimanEmailModel;
use App\Models\TemplateEmailModel;

class KirimEmailController extends BaseController
{
    protected $kampanyeModel;
    protected $penerimaModel;
    protected $riwayatModel;
    protected $templateModel;
    protected $kategoriModel;
    protected $kontakModel;
    protected $emailSender;

    public function __construct()
    {
        $this->kampanyeModel = new KampanyeEmailModel();
        $this->penerimaModel = new PenerimaKampanyeModel();
        $this->riwayatModel  = new RiwayatPengirimanEmailModel();
        $this->templateModel = new TemplateEmailModel();
        $this->kategoriModel = new KategoriTemplateEmailModel();
        $this->kontakModel   = new KontakModel();
        $this->emailSender   = new EmailSender();
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
    //  FORM BUAT KAMPANYE (kategori -> template -> preview -> jadwal)
    // =========================================================
    public function buat()
    {
        // Kategori aktif dari database
        $kategori = $this->kategoriModel
            ->where('status', 'aktif')
            ->orderBy('nama_kategori', 'ASC')
            ->findAll();

        // Template (selain arsip) dari database -> dikirim ke JS untuk filter & preview
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

        // Kontak dari database (hanya yang punya email)
        $kontak = $this->kontakModel
            ->where('email IS NOT NULL', null, false)
            ->where('email !=', '')
            ->orderBy('nama_perusahaan', 'ASC')
            ->findAll();

        return view('kirim_email/buat', [
            'kategori'   => $kategori,
            'templateJs' => $templateJs,
            'kontak'     => $kontak,
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

        $kontakList = $this->kontakModel
            ->whereIn('id', $penerimaIds)
            ->where('email IS NOT NULL', null, false)
            ->where('email !=', '')
            ->findAll();
        if (empty($kontakList)) {
            return redirect()->back()->withInput()->with('error', 'Kontak tidak valid.');
        }

        // Mode kirim: langsung / dijadwalkan
        $mode   = $this->request->getPost('mode_kirim') === 'jadwal' ? 'jadwal' : 'sekarang';
        $jadwal = null;
        $status = 'draf';

        if ($mode === 'jadwal') {
            $raw = trim((string) $this->request->getPost('jadwal_pengiriman'));
            $ts  = $raw !== '' ? strtotime($raw) : false;
            if (! $ts) {
                return redirect()->back()->withInput()->with('error', 'Tanggal & jam pengiriman wajib diisi.');
            }
            if ($ts <= time()) {
                return redirect()->back()->withInput()->with('error', 'Jadwal pengiriman harus di masa depan.');
            }
            $jadwal = date('Y-m-d H:i:s', $ts);
            $status = 'terjadwal';
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $kampanyeId = $this->kampanyeModel->insert([
            'template_email_id' => $templateId,
            'nama_kampanye'     => $this->request->getPost('nama_kampanye'),
            'subjek_email'      => $this->request->getPost('subjek_email'),
            'status'            => $status,
            'jadwal_pengiriman' => $jadwal,
            'jumlah_penerima'   => count($kontakList),
            'jumlah_berhasil'   => 0,
            'jumlah_gagal'      => 0,
        ]);

        if ($kampanyeId) {
            $rows = [];
            foreach ($kontakList as $k) {
                $rows[] = [
                    'kampanye_email_id'     => $kampanyeId,
                    'kontak_email_id'       => $k['id'],
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

        if ($mode === 'sekarang') {
            // auto=1 -> halaman detail langsung memulai pengiriman
            return redirect()->to('kirim-email/detail/' . $kampanyeId . '?auto=1')
                ->with('success', 'Kampanye dibuat, pengiriman dimulai...');
        }

        return redirect()->to('kirim-email/detail/' . $kampanyeId)
            ->with('success', 'Kampanye dijadwalkan pada ' . $jadwal . '. Akan dikirim otomatis.');
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

        return view('kirim_email/detail', [
            'kampanye' => $kampanye,
            'template' => $this->templateModel->withDeleted()->find($kampanye['template_email_id']),
            'penerima' => $this->penerimaModel->getByKampanye((int) $id),
            'sisa'     => $this->penerimaModel->getPendingCount((int) $id),
            'auto'     => (bool) $this->request->getGet('auto'),
        ]);
    }

    // =========================================================
    //  PROSES 1 BATCH (AJAX) - dipanggil berulang oleh JS
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

        if (in_array($kampanye['status'], ['draf', 'terjadwal'], true)) {
            $this->kampanyeModel->update($id, [
                'status'             => 'sedang_dikirim',
                'mulai_dikirim_pada' => $kampanye['mulai_dikirim_pada'] ?: date('Y-m-d H:i:s'),
            ]);
        }

        $hasil = $this->prosesBatchInternal((int) $id, 5);
        $stat  = $this->hitungUlang((int) $id);

        return $this->response->setJSON([
            'success'  => true,
            'selesai'  => $stat['selesai'],
            'sisa'     => $stat['sisa'],
            'batch'    => $hasil,
            'berhasil' => $stat['berhasil'],
            'gagal'    => $stat['gagal'],
            'csrf'     => csrf_hash(), // token baru untuk request berikutnya
        ]);
    }

    // =========================================================
    //  POLLING DARI BROWSER: ambil kampanye terjadwal yang sudah jatuh tempo
    // =========================================================
    public function prosesTerjadwal()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $ids = $this->klaimKampanyeSiap();

        return $this->response->setJSON([
            'success' => true,
            'ids'     => $ids,
            'message' => count($ids) . ' kampanye siap diproses.',
            'csrf'    => csrf_hash(),
        ]);
    }

    // =========================================================
    //  CRON URL EKSTERNAL:  GET /kirim-email/cron/{EMAIL_CRON_TOKEN}
    //  Panggil tiap 1 menit (cron-job.org / Task Scheduler / crontab)
    // =========================================================
    public function cron($token = null)
    {
        $expected = env('EMAIL_CRON_TOKEN', '');
        if (empty($expected) || ! hash_equals((string) $expected, (string) $token)) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Token tidak valid.']);
        }

        @set_time_limit(120);
        $mulai = time();
        $ids   = $this->klaimKampanyeSiap();
        $kirim = 0;

        foreach ($ids as $id) {
            // Maks ~50 detik per request; sisa dilanjutkan pada panggilan cron berikutnya
            while (time() - $mulai < 50) {
                $batch = $this->prosesBatchInternal($id, 10);
                $kirim += count($batch);
                $stat   = $this->hitungUlang($id);
                if ($stat['selesai']) {
                    break;
                }
                if (empty($batch)) {
                    sleep(2);
                }
            }
        }

        return $this->response->setJSON([
            'success'   => true,
            'processed' => count($ids),
            'dikirim'   => $kirim,
        ]);
    }

    // =========================================================
    //  KIRIM ULANG 1 PENERIMA GAGAL
    // =========================================================
    public function kirimUlang($penerimaId)
    {
        $p = $this->penerimaModel->find($penerimaId);
        if (! $p) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $this->penerimaModel->update($penerimaId, [
            'status_pengiriman' => 'menunggu',
            'jumlah_percobaan'  => 0,
            'pesan_kesalahan'   => null,
        ]);

        // Buka kembali kampanye yang sudah selesai agar bisa diproses lagi
        $k = $this->kampanyeModel->find($p['kampanye_email_id']);
        if ($k && $k['status'] === 'selesai') {
            $this->kampanyeModel->update($k['id'], [
                'status'               => 'draf',
                'selesai_dikirim_pada' => null,
            ]);
        }

        return redirect()->to('kirim-email/detail/' . $p['kampanye_email_id'] . '?auto=1')
            ->with('success', 'Penerima dikirim ulang...');
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
    //  HELPER
    // =========================================================

    /**
     * Ambil (dan "kunci") kampanye yang siap diproses:
     *  - terjadwal & jadwalnya sudah lewat
     *  - sedang_dikirim tapi macet > 2 menit (mis. browser ditutup)
     * Atomik: hanya satu proses yang berhasil mengklaim tiap kampanye.
     */
    protected function klaimKampanyeSiap(): array
    {
        $db    = \Config\Database::connect();
        $now   = date('Y-m-d H:i:s');
        $macet = date('Y-m-d H:i:s', time() - 120);

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
            $b = $db->table('kampanye_email')->where('id', $k['id']);

            if ($k['status'] === 'terjadwal') {
                $b->where('status', 'terjadwal')->update([
                    'status'             => 'sedang_dikirim',
                    'mulai_dikirim_pada' => $now,
                    'diperbarui_pada'    => $now,
                ]);
            } else {
                $b->where('status', 'sedang_dikirim')
                    ->where('diperbarui_pada <', $macet)
                    ->update(['diperbarui_pada' => $now]);
            }

            if ($db->affectedRows() > 0) {
                $ids[] = (int) $k['id'];
            }
        }

        return $ids;
    }

    /** Hitung ulang statistik kampanye & tandai selesai bila tidak ada sisa. */
    protected function hitungUlang(int $id): array
    {
        $berhasil = $this->penerimaModel->where('kampanye_email_id', $id)
            ->where('status_pengiriman', 'terkirim')->countAllResults();
        $gagal = $this->penerimaModel->where('kampanye_email_id', $id)
            ->where('status_pengiriman', 'gagal')->countAllResults();
        $sisa = $this->penerimaModel->getPendingCount($id);

        $data = ['jumlah_berhasil' => $berhasil, 'jumlah_gagal' => $gagal];
        if ($sisa === 0) {
            $data['status']               = 'selesai';
            $data['selesai_dikirim_pada'] = date('Y-m-d H:i:s');
        }
        $this->kampanyeModel->update($id, $data);

        return [
            'berhasil' => $berhasil,
            'gagal'    => $gagal,
            'sisa'     => $sisa,
            'selesai'  => $sisa === 0,
        ];
    }

    /** Kirim 1 batch email. Dipakai oleh proses() dan cron(). */
    protected function prosesBatchInternal(int $kampanyeId, int $limit = 5): array
    {
        $kampanye = $this->kampanyeModel->find($kampanyeId);
        if (! $kampanye) {
            return [];
        }

        $template = $this->templateModel->withDeleted()->find($kampanye['template_email_id']);
        if (! $template) {
            return [];
        }
        // Subjek yang dipakai = subjek kampanye (yang bisa diedit di form)
        $template['subjek_email'] = $kampanye['subjek_email'];

        $batch = $this->penerimaModel->getPendingBatch($kampanyeId, $limit);
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

        $hasil = [];
        foreach ($batch as $p) {
            // Kunci dulu supaya tidak terkirim ganda oleh proses lain
            if (! $this->penerimaModel->klaim((int) $p['id'])) {
                continue;
            }

            $kontak       = $kontakMap[$p['kontak_email_id']] ?? [];
            $penerimaData = array_merge($p, [
                'nama_perusahaan' => $kontak['nama_perusahaan'] ?? ($p['nama_penerima'] ?? ''),
                'domisili'        => $kontak['domisili'] ?? '',
            ]);

            $res = $this->emailSender->kirimSatu($penerimaData, $template);

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