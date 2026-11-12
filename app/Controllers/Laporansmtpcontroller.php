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
        $db        = db_connect();
        $smtpModel = new SmtpSettingModel();

        $smtpList = $smtpModel->orderBy('is_active', 'DESC')->orderBy('nama', 'ASC')->findAll();
        $aktif    = $smtpModel->getActive();
        $aktifId  = (int) ($aktif['id'] ?? 0);

        $kuotaDefault = (int) env('EMAIL_DAILY_LIMIT', 450);
        $punyaKolom   = $db->fieldExists('kuota_harian', 'smtp_settings');

        $harian = $this->hitungKirim(date('Y-m-d H:i:s', time() - 86400), $aktifId);
        $total  = $this->hitungKirim(null, $aktifId);

        $baris = [];
        $sum   = ['kuota' => 0, 'terpakai' => 0, 'sisa' => 0];

        foreach ($smtpList as $s) {
            $id       = (int) $s['id'];
            $kuota    = $this->kuotaSmtp($s, $kuotaDefault, $punyaKolom);
            $terpakai = (int) ($harian[$id]['terkirim'] ?? 0);
            $sisa     = max(0, $kuota - $terpakai);

            $baris[] = [
                'id'             => $id,
                'nama'           => $s['nama'] ?? '-',
                'from_email'     => $s['from_email'] ?? '-',
                'aktif'          => (int) ($s['is_active'] ?? 0) === 1,
                'kuota'          => $kuota,
                'terpakai'       => $terpakai,
                'sisa'           => $sisa,
                'persen'         => $kuota > 0 ? min(100, (int) round($terpakai / $kuota * 100)) : 0,
                'gagal_24j'      => (int) ($harian[$id]['gagal'] ?? 0),
                'terkirim_total' => (int) ($total[$id]['terkirim'] ?? 0),
                'gagal_total'    => (int) ($total[$id]['gagal'] ?? 0),
            ];

            $sum['kuota']    += $kuota;
            $sum['terpakai'] += $terpakai;
            $sum['sisa']     += $sisa;
        }

        return view('laporan/smtp', [
            'baris'        => $baris,
            'ringkasan'    => $sum + [
                'jumlah_smtp'  => count($smtpList),
                'jumlah_aktif' => count(array_filter($baris, static fn ($b) => $b['aktif'])),
            ],
            'kuotaPerSmtp' => true,
            'kuotaDefault' => $kuotaDefault,
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
                'gagal'    => (int) $r['gagal'],
            ];
        }

        return $hasil;
    }
}