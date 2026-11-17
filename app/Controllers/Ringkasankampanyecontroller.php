<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RingkasanKampanyeModel;

/**
 * RingkasanKampanyeController  — menu "Ringkasan Kampanye"
 *   GET laporan/ringkasan-kampanye          -> halaman laporan
 *   GET laporan/ringkasan-kampanye/export   -> unduh CSV
 */
class RingkasanKampanyeController extends BaseController
{
    protected $ringkasanModel;

    /** Pemisah kolom CSV. Excel versi Indonesia memakai ";" — ubah ke "," bila Excel Anda memakai koma. */
    private const CSV_DELIM = ';';

    private const STATUS_KAMPANYE = [
        'draf'           => ['Draf',           'label-default'],
        'terjadwal'      => ['Terjadwal',      'label-info'],
        'sedang_dikirim' => ['Sedang dikirim', 'label-warning'],
        'selesai'        => ['Selesai',        'label-success'],
        'dijeda'         => ['Dijeda',         'label-primary'],
        'dibatalkan'     => ['Dibatalkan',     'label-danger'],
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
        $this->ringkasanModel = new RingkasanKampanyeModel();
    }

    /* ==============================================================
     *  HALAMAN & EKSPOR
     * ============================================================== */

    public function ringkasanKampanye()
    {
        $f = $this->filterRingkasan();

        $semua = $this->olahKampanye($this->ringkasanModel->daftarKampanye($f['dari'], $f['sampai']));

        // Hitung jumlah kampanye per status (sebelum filter status)
        $statusCount = [];
        foreach ($semua as $r) {
            $statusCount[$r['status']] = ($statusCount[$r['status']] ?? 0) + 1;
        }

        $kampanye = $f['status'] === ''
            ? $semua
            : array_values(array_filter($semua, fn($r) => $r['status'] === $f['status']));

        $ringkas = $this->hitungRingkas($kampanye);
        $harian  = $this->ringkasanModel->aktivitasHarian(array_column($kampanye, 'id'));

        // PERBAIKAN: Path view disesuaikan dengan struktur folder (app/Views/laporan/ringkasanKampanye.php)
        return view('laporan/ringkasanKampanye', [
            'filter'      => $f,
            'kampanye'    => $kampanye,
            'ringkas'     => $ringkas,
            'statusCount' => $statusCount,
            'harian'      => $harian,
            'statusMap'   => self::STATUS_KAMPANYE,
            'exportUrl'   => base_url('laporan/ringkasan-kampanye/export') . $this->queryString($f),
        ]);
    }

    public function exportRingkasanKampanye()
    {
        $f = $this->filterRingkasan();
        $rows = $this->olahKampanye($this->ringkasanModel->daftarKampanye($f['dari'], $f['sampai']));
        if ($f['status'] !== '') {
            $rows = array_values(array_filter($rows, fn($r) => $r['status'] === $f['status']));
        }

        $header = ['No', 'Nama Kampanye', 'Subjek', 'Template', 'SMTP', 'Status', 'Tipe Pengiriman',
                   'Penerima', 'Berhasil', 'Gagal', 'Menunggu', 'Tingkat Keberhasilan (%)',
                   'Jadwal', 'Mulai Dikirim', 'Selesai Dikirim', 'Durasi'];
        $baris = [];
        foreach ($rows as $i => $r) {
            $baris[] = [
                $i + 1, $r['nama_kampanye'], $r['subjek_email'], $r['nama_template'] ?? '-',
                $r['smtp_nama'] ?? 'Default aktif', $r['status_label'], $r['tipe_label'],
                $r['total_penerima'], $r['berhasil'], $r['gagal'], $r['menunggu'],
                $r['rate'] === null ? '-' : str_replace('.', ',', (string) $r['rate']),
                $r['jadwal_pengiriman'] ?? '-', $r['mulai_dikirim_pada'] ?? '-',
                $r['selesai_dikirim_pada'] ?? '-', $r['durasi_teks'],
            ];
        }

        return $this->kirimCsv('ringkasan-kampanye-' . date('Ymd-His') . '.csv', $header, $baris);
    }

    private function filterRingkasan(): array
    {
        $g = $this->request->getGet();

        // Kunjungan pertama (tanpa parameter) -> default 30 hari terakhir.
        // Jika pengguna mengosongkan tanggal lalu menerapkan, artinya "semua waktu".
        if ($g === []) {
            $dari   = date('Y-m-d', strtotime('-29 days'));
            $sampai = date('Y-m-d');
        } else {
            $dari   = $this->tanggal($g['dari'] ?? null);
            $sampai = $this->tanggal($g['sampai'] ?? null);
        }
        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $status = (string) ($g['status'] ?? '');
        if (!isset(self::STATUS_KAMPANYE[$status])) {
            $status = '';
        }

        return ['dari' => $dari, 'sampai' => $sampai, 'status' => $status];
    }

    /** Tambahkan label, tingkat keberhasilan, dan durasi pada tiap baris kampanye. */
    private function olahKampanye(array $rows): array
    {
        foreach ($rows as &$r) {
            foreach (['total_penerima', 'berhasil', 'gagal', 'menunggu', 'total_putaran', 'jumlah_putaran'] as $k) {
                $r[$k] = (int) ($r[$k] ?? 0);
            }
            $diproses  = $r['berhasil'] + $r['gagal'];
            $r['rate'] = $diproses > 0 ? round($r['berhasil'] / $diproses * 100, 1) : null;

            [$lbl, $cls] = self::STATUS_KAMPANYE[$r['status']] ?? [ucfirst(str_replace('_', ' ', (string) $r['status'])), 'label-default'];
            $r['status_label'] = $lbl;
            $r['status_class'] = $cls;
            $r['tipe_label']   = self::TIPE_KAMPANYE[$r['tipe_pengiriman'] ?? 'sekarang'] ?? 'Langsung';

            $r['durasi_detik'] = $r['durasi_detik'] === null ? null : (int) $r['durasi_detik'];
            $r['durasi_teks']  = $this->formatDurasi($r['durasi_detik']);
        }
        unset($r);

        return $rows;
    }

    private function hitungRingkas(array $rows): array
    {
        $r = ['kampanye' => count($rows), 'penerima' => 0, 'berhasil' => 0, 'gagal' => 0, 'menunggu' => 0,
              'rate' => null, 'rata_durasi' => null];
        $durasi = [];
        foreach ($rows as $x) {
            $r['penerima'] += $x['total_penerima'];
            $r['berhasil'] += $x['berhasil'];
            $r['gagal']    += $x['gagal'];
            $r['menunggu'] += $x['menunggu'];
            if ($x['durasi_detik'] !== null) $durasi[] = $x['durasi_detik'];
        }
        $diproses = $r['berhasil'] + $r['gagal'];
        $r['rate'] = $diproses > 0 ? round($r['berhasil'] / $diproses * 100, 1) : null;
        $r['rata_durasi'] = $durasi ? $this->formatDurasi((int) round(array_sum($durasi) / count($durasi))) : '-';
        return $r;
    }

    /* ==============================================================
     *  UTILITAS
     * ============================================================== */

    private function tanggal(?string $v): ?string
    {
        if ($v === null || $v === '') return null;
        $d = \DateTime::createFromFormat('Y-m-d', $v);
        return ($d && $d->format('Y-m-d') === $v) ? $v : null;
    }

    /** Query string (diawali "?") dari filter, untuk link ekspor. */
    private function queryString(array $f): string
    {
        unset($f['tab']);
        $f = array_filter($f, fn($v) => $v !== null && $v !== '');
        return $f ? '?' . http_build_query($f) : '';
    }

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
        // Cegah CSV/Excel formula injection (nilai yang diawali = + - @)
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
            ->setBody("\xEF\xBB\xBF" . $csv);   // BOM agar Excel membaca UTF-8
    }
}