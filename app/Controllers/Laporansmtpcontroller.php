<?php

namespace App\Controllers;

use App\Models\SmtpSettingModel;

/**
 * Simpan di: app/Controllers/LaporanSmtpController.php
 *
 * Kuota per SMTP, urutan prioritas:
 *   1) kolom smtp_settings.kuota_harian   (jika kolomnya ada dan terisi)
 *   2) .env  EMAIL_KUOTA_SMTP_<id>        (mis. EMAIL_KUOTA_SMTP_1 = 500)
 *   3) .env  EMAIL_DAILY_LIMIT            (default 450)
 *
 * Tanpa mengubah database pun halaman ini langsung jalan (memakai poin 2 dan 3).
 * Jendela kuota = 24 jam terakhir, sama dengan sisaJatahHarian() di KirimEmailController.
 */
class LaporanSmtpController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $smtpModel = new SmtpSettingModel();

        $smtpList = $smtpModel->orderBy('is_active', 'DESC')->orderBy('nama', 'ASC')->findAll();
        $aktif = $smtpModel->getActive();
        $aktifId = (int) ($aktif['id'] ?? 0);

        $kuotaDefault = (int) env('EMAIL_DAILY_LIMIT', 500);
        $punyaKolom = $db->fieldExists('kuota_harian', 'smtp_settings');

        $harian = $this->hitungKirim(date('Y-m-d H:i:s', time() - 86400), $aktifId);
        $total = $this->hitungKirim(null, $aktifId);
        // SMTP yang dipilih untuk grafik: tanpa parameter = SMTP aktif, 0 = semua SMTP
        $param = $this->request->getGet('smtp_id');
        $pilihId = ($param === null || $param === '') ? $aktifId : (int) $param;

        $idValid = array_map('intval', array_column($smtpList, 'id'));
        if ($pilihId !== 0 && !in_array($pilihId, $idValid, true)) {
            $pilihId = $aktifId;
        }

        $namaPilihan = 'Semua SMTP';
        foreach ($smtpList as $s) {
            if ((int) $s['id'] === $pilihId) {
                $namaPilihan = $s['nama'] ?? 'SMTP';
            }
        }

        $grafikHarian = $this->hitungTren('harian', $pilihId, $aktifId);
        $grafikBulanan = $this->hitungTren('bulanan', $pilihId, $aktifId);

        $baris = [];
        $sum = ['kuota' => 0, 'terpakai' => 0, 'sisa' => 0];

        foreach ($smtpList as $s) {
            $id = (int) $s['id'];
            $kuota = $this->kuotaSmtp($s, $kuotaDefault, $punyaKolom);
            $terpakai = (int) ($harian[$id]['terkirim'] ?? 0);
            $sisa = max(0, $kuota - $terpakai);

            $baris[] = [
                'id' => $id,
                'nama' => $s['nama'] ?? '-',
                'from_email' => $s['from_email'] ?? '-',
                'aktif' => (int) ($s['is_active'] ?? 0) === 1,
                'kuota' => $kuota,
                'terpakai' => $terpakai,
                'sisa' => $sisa,
                'persen' => $kuota > 0 ? min(100, (int) round($terpakai / $kuota * 100)) : 0,
                'gagal_24j' => (int) ($harian[$id]['gagal'] ?? 0),
                'terkirim_total' => (int) ($total[$id]['terkirim'] ?? 0),
                'gagal_total' => (int) ($total[$id]['gagal'] ?? 0),
            ];

            if ($pilihId === 0 || $pilihId === $id) {
                $sum['kuota'] += $kuota;
                $sum['terpakai'] += $terpakai;
                $sum['sisa'] += $sisa;
            }
        }

        return view('laporan/smtp', [
            'baris' => $baris,
            'ringkasan' => $sum + [
                'jumlah_smtp' => count($smtpList),
                'jumlah_aktif' => count(array_filter($baris, static fn($b) => $b['aktif'])),
            ],
            'kuotaPerSmtp' => true,
            'kuotaDefault' => $kuotaDefault,
            'grafikHarian' => $grafikHarian,
            'grafikBulanan' => $grafikBulanan,
            'daftarSmtp' => $baris,         // <-- baru
            'pilihId' => $pilihId,       // <-- baru
            'namaPilihan' => $namaPilihan,
        ]);
    }

    protected function kuotaSmtp(array $smtp, int $default, bool $punyaKolom): int
    {
        if ($punyaKolom && (int) ($smtp['kuota_harian'] ?? 0) > 0) {
            return (int) $smtp['kuota_harian'];
        }

        $env = (int) env('EMAIL_KUOTA_SMTP_' . (int) $smtp['id'], 0);

        return $env > 0 ? $env : $default;
    }

    /**
     * Hitung terkirim & gagal per SMTP dari riwayat_pengiriman_email.
     * Kampanye tanpa smtp_setting_id dihitung ke SMTP aktif.
     * Kegagalan "Tidak dikirim: ..." (ditolak validasi sebelum kirim) tidak dihitung.
     *
     * @return array<int, array{terkirim:int, gagal:int}>
     */
    protected function hitungKirim(?string $sejak, int $aktifId): array
    {
        $b = db_connect()->table('riwayat_pengiriman_email r')
            ->select('COALESCE(k.smtp_setting_id, ' . $aktifId . ') AS smtp_id', false)
            ->select("SUM(r.jenis_aktivitas = 'terkirim') AS terkirim", false)
            ->select(
                "SUM(r.jenis_aktivitas = 'gagal' AND (r.detail_aktivitas IS NULL OR r.detail_aktivitas NOT LIKE 'Tidak dikirim:%')) AS gagal",
                false
            )
            ->join('penerima_kampanye p', 'p.id = r.penerima_kampanye_id')
            ->join('kampanye_email k', 'k.id = p.kampanye_email_id');

        if ($sejak !== null) {
            $b->where('r.terjadi_pada >=', $sejak);
        }

        $hasil = [];
        foreach ($b->groupBy('smtp_id')->get()->getResultArray() as $r) {
            $hasil[(int) $r['smtp_id']] = [
                'terkirim' => (int) $r['terkirim'],
                'gagal' => (int) $r['gagal'],
            ];
        }

        return $hasil;
    }

    /**
     * Ambil tren email terkirim dan gagal untuk grafik laporan.
     *
     * @return array{labels:array<int,string>,terkirim:array<int,int>,gagal:array<int,int>}
     */
    protected function hitungTren(string $periode, int $pilihId = 0, int $aktifId = 0): array
    {
        $db = db_connect();
        $sekarang = new \DateTimeImmutable('today');

        if ($periode === 'harian') {
            $jumlahPeriode = 7;
            $mulai = $sekarang->modify('monday this week');
            $akhir = $mulai->modify('+7 days');
            $format = '%Y-%m-%d';
            $namaPeriode = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        } else {
            $jumlahPeriode = 12;
            $mulai = $sekarang->setDate((int) $sekarang->format('Y'), 1, 1);
            $akhir = $mulai->modify('+1 year');
            $format = '%Y-%m';
            $namaPeriode = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ];
        }

        $tanggalMulai = $mulai->format('Y-m-d 00:00:00');
        $tanggalBerikutnya = $akhir->format('Y-m-d 00:00:00');

        $b = $db->table('riwayat_pengiriman_email r')
            ->select("DATE_FORMAT(r.terjadi_pada, '{$format}') AS periode", false)
            ->select("SUM(r.jenis_aktivitas = 'terkirim') AS terkirim", false)
            ->select(
                "SUM(r.jenis_aktivitas = 'gagal' AND (r.detail_aktivitas IS NULL OR r.detail_aktivitas NOT LIKE 'Tidak dikirim:%')) AS gagal",
                false
            )
            ->join('penerima_kampanye p', 'p.id = r.penerima_kampanye_id', 'left')
            ->join('kampanye_email k', 'k.id = p.kampanye_email_id', 'left')
            ->where('r.terjadi_pada >=', $tanggalMulai)
            ->where('r.terjadi_pada <', $tanggalBerikutnya);

        // Filter SMTP (kampanye tanpa pilihan SMTP dihitung ke SMTP aktif)
        if ($pilihId > 0) {
            $b->where('COALESCE(k.smtp_setting_id, ' . $aktifId . ') = ' . $pilihId, null, false);
        }

        $hasil = $b->groupBy('periode')->get()->getResultArray();

        $berdasarkanPeriode = [];
        foreach ($hasil as $baris) {
            $berdasarkanPeriode[$baris['periode']] = [
                'terkirim' => (int) $baris['terkirim'],
                'gagal' => (int) $baris['gagal'],
            ];
        }

        $labels = [];
        $terkirim = [];
        $gagal = [];
        $tanggal = $mulai;

        for ($i = 0; $i < $jumlahPeriode; $i++) {
            $kunci = $periode === 'harian' ? $tanggal->format('Y-m-d') : $tanggal->format('Y-m');
            $data = $berdasarkanPeriode[$kunci] ?? ['terkirim' => 0, 'gagal' => 0];

            $labels[] = $namaPeriode[$i];
            $terkirim[] = $data['terkirim'];
            $gagal[] = $data['gagal'];
            $tanggal = $periode === 'harian' ? $tanggal->modify('+1 day') : $tanggal->modify('+1 month');
        }

        return [
            'labels' => $labels,
            'terkirim' => $terkirim,
            'gagal' => $gagal,
        ];
    }
}