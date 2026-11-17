<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PerKampanyeModel;

/**
 * PerKampanyeController — menu "Laporan Per Kampanye"
 * View: app/Views/laporan/perkampanye.php
 *
 *   GET laporan/per-kampanye?id={kampanye}                    -> halaman laporan
 *   GET laporan/per-kampanye/export/{jenis}?id={kampanye}     -> unduh CSV (penerima | gagal | riwayat)
 */
class PerKampanyeController extends BaseController
{
    protected $perKampanyeModel;

    private const CSV_DELIM = ';';
    private const LIMIT_RIWAYAT = 3000;

    private const STATUS_KAMPANYE = [
        'draf'           => ['Draf',           'label-default'],
        'terjadwal'      => ['Terjadwal',      'label-info'],
        'sedang_dikirim' => ['Sedang dikirim', 'label-warning'],
        'selesai'        => ['Selesai',        'label-success'],
        'dijeda'         => ['Dijeda',         'label-primary'],
        'dibatalkan'     => ['Dibatalkan',     'label-danger'],
    ];

    private const STATUS_PUTARAN = [
        'menunggu'   => ['Menunggu',   'label-info'],
        'berjalan'   => ['Berjalan',   'label-warning'],
        'selesai'    => ['Selesai',    'label-success'],
        'dijeda'     => ['Dijeda',     'label-primary'],
        'gagal'      => ['Gagal',      'label-danger'],
        'dibatalkan' => ['Dibatalkan', 'label-danger'],
    ];

    private const TIPE_KAMPANYE = [
        'sekarang'           => 'Langsung',
        'terjadwal'          => 'Terjadwal',
        'sekarang_berulang'  => 'Langsung + berulang',
        'terjadwal_berulang' => 'Terjadwal + berulang',
        'berulang'           => 'Terjadwal + berulang',
    ];

    public function __construct()
    {
        $this->perKampanyeModel = new PerKampanyeModel();
    }

    /* ==============================================================
     *  HALAMAN
     * ============================================================== */

    public function index()
    {
        $daftar = $this->perKampanyeModel->opsiKampanye();

        $id = (int) $this->request->getGet('id');
        if ($id === 0 && $daftar) {
            $id = (int) $daftar[0]['id'];
        }

        // Belum ada kampanye sama sekali
        if ($id === 0) {
            return view('laporan/perkampanye', [
                'daftar'     => [],
                'kampanye'   => null,
                'tab'        => 'penerima',
                'exportBase' => base_url('laporan/per-kampanye/export'),
            ]);
        }

        $kampanye = $this->perKampanyeModel->kampanye($id);
        if (!$kampanye) {
            return redirect()->to('laporan/per-kampanye')->with('error', 'Kampanye tidak ditemukan.');
        }

        $tab = (string) $this->request->getGet('tab');
        if (!in_array($tab, ['penerima', 'putaran', 'kategori', 'riwayat'], true)) {
            $tab = 'penerima';
        }

        $ringkas  = $this->hitungRingkas($id);
        $riw      = $this->perKampanyeModel->ringkasRiwayat($id);
        $linimasa = $this->perKampanyeModel->linimasa($id, $riw['awal'], $riw['akhir']);
        $putaran  = $this->olahPutaran($this->perKampanyeModel->perPutaran($id));
        $kategori = $this->perKampanyeModel->perKategori($id);
        $penerima = $this->olahPenerima($this->perKampanyeModel->daftarPenerima($id));
        $totalRiw = $this->perKampanyeModel->hitungRiwayat($id);
        $riwayat  = $this->perKampanyeModel->riwayat($id, self::LIMIT_RIWAYAT);

        return view('laporan/perkampanye', [
            'daftar'       => $daftar,
            'kampanye'     => $this->olahKampanye($kampanye),
            'ringkas'      => $ringkas,
            'riw'          => $riw,
            'linimasa'     => $linimasa,
            'putaran'      => $putaran,
            'kategori'     => $kategori,
            'penerima'     => $penerima,
            'riwayat'      => $this->olahRiwayat($riwayat),
            'totalRiwayat' => $totalRiw,
            'limitRiwayat' => self::LIMIT_RIWAYAT,
            'ganda'        => $this->perKampanyeModel->kirimGanda($id),
            'tab'          => $tab,
            'exportBase'   => base_url('laporan/per-kampanye/export'),
        ]);
    }

    /* ==============================================================
     *  EKSPOR CSV
     * ============================================================== */

    public function export(string $jenis = 'penerima')
    {
        $id = (int) $this->request->getGet('id');
        $kampanye = $id ? $this->perKampanyeModel->kampanye($id) : null;
        if (!$kampanye) {
            return redirect()->to('laporan/per-kampanye')->with('error', 'Kampanye tidak ditemukan.');
        }

        $slug  = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($kampanye['nama_kampanye'])), '-') ?: 'kampanye';
        $stamp = date('Ymd-His');

        if ($jenis === 'riwayat') {
            $header = ['Waktu', 'Email Penerima', 'Jenis Aktivitas', 'Penyedia', 'ID Pesan', 'Keterangan Gagal'];
            $baris  = [];
            foreach ($this->olahRiwayat($this->perKampanyeModel->riwayat($id, null)) as $r) {
                $baris[] = [
                    $r['terjadi_pada'], $r['alamat_email_penerima'], $r['jenis_aktivitas'],
                    $r['penyedia_email'] ?? '-', $r['id_pesan_penyedia'] ?? '-', $r['error_ringkas'] ?? '',
                ];
            }
            return $this->kirimCsv("riwayat-$slug-$stamp.csv", $header, $baris);
        }

        $hanyaGagal = ($jenis === 'gagal');
        $rows = $this->olahPenerima($this->perKampanyeModel->daftarPenerima($id, $hanyaGagal ? 'gagal' : ''));

        $header = ['No', 'Putaran', 'Nama Penerima', 'Email Penerima', 'Kategori Kontak', 'Status',
                   'Jumlah Percobaan', 'Waktu Terkirim', 'ID Pesan', 'Keterangan Gagal'];
        $baris = [];
        foreach ($rows as $i => $r) {
            $baris[] = [
                $i + 1, $r['putaran_ke'] ?? '-', $r['nama_penerima'] ?? '-', $r['alamat_email_penerima'],
                $r['kategori_nama'], $r['status_label'], $r['jumlah_percobaan'],
                $r['terkirim_pada'] ?? '-', $r['id_pesan_penyedia'] ?? '-', $r['error_ringkas'] ?? '',
            ];
        }
        return $this->kirimCsv(($hanyaGagal ? 'penerima-gagal' : 'penerima') . "-$slug-$stamp.csv", $header, $baris);
    }

    /* ==============================================================
     *  OLAH DATA
     * ============================================================== */

    private function olahKampanye(array $k): array
    {
        [$k['status_label'], $k['status_class']] = self::STATUS_KAMPANYE[$k['status']]
            ?? [ucfirst(str_replace('_', ' ', (string) $k['status'])), 'label-default'];
        $k['tipe_label'] = self::TIPE_KAMPANYE[$k['tipe_pengiriman'] ?? 'sekarang'] ?? 'Langsung';

        $durasi = (!empty($k['mulai_dikirim_pada']) && !empty($k['selesai_dikirim_pada']))
            ? max(0, strtotime($k['selesai_dikirim_pada']) - strtotime($k['mulai_dikirim_pada']))
            : null;
        $k['durasi_teks'] = $this->formatDurasi($durasi);

        $min = (int) ($k['kecepatan_per_menit'] ?? 0);
        $maks = (int) ($k['kecepatan_maks'] ?? 0);
        $k['kecepatan_teks'] = $min <= 0 ? 'Tanpa pembatasan'
            : ($maks > $min ? "$min–$maks email/menit" : "$min email/menit");

        $k['total_putaran']  = (int) ($k['total_putaran'] ?? 1);
        $k['jumlah_putaran'] = (int) ($k['jumlah_putaran'] ?? 0);
        return $k;
    }

    private function hitungRingkas(int $id): array
    {
        $r = $this->perKampanyeModel->ringkasPenerima($id);
        $diproses  = $r['berhasil'] + $r['gagal'];
        $r['rate'] = $diproses > 0 ? round($r['berhasil'] / $diproses * 100, 1) : null;

        $t = max(1, $r['total']);
        $r['pct_berhasil'] = round($r['berhasil'] / $t * 100, 2);
        $r['pct_gagal']    = round($r['gagal']    / $t * 100, 2);
        $r['pct_menunggu'] = max(0, round(100 - $r['pct_berhasil'] - $r['pct_gagal'], 2));
        return $r;
    }

    private function olahPutaran(array $rows): array
    {
        foreach ($rows as &$r) {
            foreach (['total', 'berhasil', 'gagal', 'menunggu'] as $k) {
                $r[$k] = (int) $r[$k];
            }
            [$r['status_label'], $r['status_class']] = self::STATUS_PUTARAN[$r['status']]
                ?? [ucfirst((string) $r['status']), 'label-default'];

            $r['terlambat_detik'] = $r['terlambat_detik'] === null ? null : (int) $r['terlambat_detik'];
            $r['durasi_detik']    = $r['durasi_detik'] === null ? null : (int) $r['durasi_detik'];
            $r['durasi_teks']     = $this->formatDurasi($r['durasi_detik']);
            $r['terlambat_teks']  = $r['terlambat_detik'] === null ? '-'
                : ($r['terlambat_detik'] <= 60 ? 'Tepat waktu' : '+' . $this->formatDurasi($r['terlambat_detik']));
            $d = $r['berhasil'] + $r['gagal'];
            $r['rate'] = $d > 0 ? round($r['berhasil'] / $d * 100, 1) : null;
        }
        unset($r);
        return $rows;
    }

    private function olahPenerima(array $rows): array
    {
        foreach ($rows as &$r) {
            $r['jumlah_percobaan'] = (int) $r['jumlah_percobaan'];
            switch ($r['status_pengiriman']) {
                case 'terkirim': [$r['status_label'], $r['status_class']] = ['Terkirim', 'label-success']; break;
                case 'gagal':    [$r['status_label'], $r['status_class']] = ['Gagal',    'label-danger'];  break;
                default:         [$r['status_label'], $r['status_class']] = ['Menunggu', 'label-warning']; break;
            }
            $r['error_ringkas'] = $r['status_pengiriman'] === 'gagal'
                ? $this->ringkasError($r['pesan_kesalahan'] ?? '') : '';
        }
        unset($r);
        return $rows;
    }

    private function olahRiwayat(array $rows): array
    {
        foreach ($rows as &$r) {
            $r['error_ringkas'] = $r['jenis_aktivitas'] === 'gagal'
                ? $this->ringkasError($r['detail_gagal'] ?? '') : '';
        }
        unset($r);
        return $rows;
    }

    private function ringkasError(?string $pesan): string
    {
        $pesan = trim((string) $pesan);
        if ($pesan === '') {
            return 'Alasan tidak tercatat';
        }

        $baris = preg_split('/\r\n|\r|\n/', $pesan);
        for ($i = count($baris) - 1; $i >= 0; $i--) {
            $b = trim($baris[$i]);
            if ($b === '' || preg_match('/^(hello:|starttls:|auth[a-z ]*:)?\s*[23]\d\d[ -]/i', $b)) {
                continue;
            }
            if (preg_match('/^[45]\d\d[ -]/', $b)
                || preg_match('/(error|failed|denied|not accepted|unable|could not|timed out|timeout|invalid|authenticat)/i', $b)) {
                return mb_strimwidth($b, 0, 160, '…');
            }
        }

        return 'Gagal pada proses SMTP (klik Detail untuk melihat log)';
    }

    /* ==============================================================
     *  UTILITAS
     * ============================================================== */

    private function formatDurasi(?int $detik): string
    {
        if ($detik === null) return '-';
        if ($detik < 60) return $detik . ' dtk';
        $j = intdiv($detik, 3600);
        $m = intdiv($detik % 3600, 60);
        $s = $detik % 60;
        if ($j > 0) return $j . ' j ' . $m . ' mnt';
        return $m . ' mnt ' . $s . ' dtk';
    }

    private function csvAman($v)
    {
        if (is_string($v) && preg_match('/^[=+\-@\t\r]/', $v)) {
            return "'" . $v;
        }
        return $v;
    }

    private function kirimCsv(string $namaFile, array $header, array $baris)
    {
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, $header, self::CSV_DELIM, '"', '\\');
        foreach ($baris as $r) {
            fputcsv($fh, array_map([$this, 'csvAman'], $r), self::CSV_DELIM, '"', '\\');
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $namaFile . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }
}