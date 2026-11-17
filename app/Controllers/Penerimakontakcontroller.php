<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PenerimaKontakModel;

/**
 * PenerimaKontakController  — menu "Laporan Penerima & Kontak"
 *   GET laporan/penerima-kontak                  -> halaman laporan (tab: kategori | kontak | penerima)
 *   GET laporan/penerima-kontak/export/{jenis}   -> unduh CSV (jenis: kontak | penerima | kategori)
 */
class PenerimaKontakController extends BaseController
{
    protected $penerimaKontakModel;

    /** Pemisah kolom CSV. Excel versi Indonesia memakai ";" — ubah ke "," bila Excel Anda memakai koma. */
    private const CSV_DELIM = ';';

    /** Batas baris tab "Penerima Kampanye" di layar (ekspor CSV tidak dibatasi). */
    private const LIMIT_PENERIMA = 5000;

    public function __construct()
    {
        $this->penerimaKontakModel = new PenerimaKontakModel();
    }

    /* ==============================================================
     *  HALAMAN & EKSPOR
     * ============================================================== */

    public function penerimaKontak()
    {
        $f = $this->filterPenerimaKontak();

        $kontak = $this->olahKontak($this->penerimaKontakModel->daftarKontak($f));

        $penerima      = $this->olahPenerima($this->penerimaKontakModel->daftarPenerima($f, self::LIMIT_PENERIMA));
        $totalPenerima = $this->penerimaKontakModel->hitungPenerima($f);

        // PERBAIKAN: Path view disesuaikan dengan folder laporan
        return view('laporan/penerimadankontak', [
            'filter'        => $f,
            'kategoriList'  => $this->penerimaKontakModel->semuaKategori(),
            'kampanyeList'  => $this->penerimaKontakModel->opsiKampanye(),
            'kartu'         => $this->penerimaKontakModel->kartuKontak($f),
            'perKategori'   => $this->penerimaKontakModel->perKategori($f),
            'kontak'        => $kontak,
            'penerima'      => $penerima,
            'totalPenerima' => $totalPenerima,
            'limitPenerima' => self::LIMIT_PENERIMA,
            'exportBase'    => base_url('laporan/penerima-kontak/export'),
            'queryString'   => $this->queryString($f),
        ]);
    }

    /** Ekspor CSV: /laporan/penerima-kontak/export/{kontak|penerima|kategori} */
    public function exportPenerimaKontak(string $jenis = 'kontak')
    {
        $f = $this->filterPenerimaKontak();
        $stamp = date('Ymd-His');

        if ($jenis === 'kategori') {
            $header = ['Kategori', 'Jumlah Kontak', 'Pernah Dikirimi', 'Belum Pernah Dikirimi', 'Bermasalah',
                       'Email Berhasil', 'Email Gagal', 'Tingkat Keberhasilan (%)'];
            $baris = [];
            foreach ($this->penerimaKontakModel->perKategori($f) as $r) {
                $d = $r['email_berhasil'] + $r['email_gagal'];
                $baris[] = [$r['nama'], $r['jumlah_kontak'], $r['pernah_dikirim'], $r['belum_dikirim'], $r['bermasalah'],
                            $r['email_berhasil'], $r['email_gagal'],
                            $d > 0 ? str_replace('.', ',', (string) round($r['email_berhasil'] / $d * 100, 1)) : '-'];
            }
            return $this->kirimCsv("laporan-kontak-per-kategori-$stamp.csv", $header, $baris);
        }

        if ($jenis === 'penerima') {
            $header = ['Waktu', 'Kampanye', 'Nama Penerima', 'Email Penerima', 'Kategori Kontak', 'Status',
                       'Jumlah Percobaan', 'Keterangan Gagal'];
            $baris = [];
            foreach ($this->olahPenerima($this->penerimaKontakModel->daftarPenerima($f, null)) as $r) {
                $baris[] = [$r['waktu'] ?? '-', $r['nama_kampanye'], $r['nama_penerima'] ?? '-', $r['alamat_email_penerima'],
                            $r['kategori_nama'], $r['status_label'], $r['jumlah_percobaan'], $r['error_ringkas']];
            }
            return $this->kirimCsv("laporan-penerima-kampanye-$stamp.csv", $header, $baris);
        }

        // default: kontak
        $header = ['Nama', 'Perusahaan', 'Email', 'No HP', 'Kota/Kabupaten', 'Provinsi', 'Kategori',
                   'Total Dikirim', 'Berhasil', 'Gagal', 'Jumlah Kampanye', 'Terakhir Terkirim',
                   'Jumlah Tidak Terkirim', 'Status'];
        $baris = [];
        foreach ($this->olahKontak($this->penerimaKontakModel->daftarKontak($f)) as $r) {
            $baris[] = [$r['nama'] ?? '-', $r['nama_perusahaan'] ?? '-', $r['email'] ?? '-', $r['no_hp'] ?? '-',
                        $r['kota_kabupaten'] ?? '-', $r['provinsi'] ?? '-', $r['kategori_nama'],
                        $r['total_kirim'], $r['berhasil'], $r['gagal'], $r['jml_kampanye'],
                        $r['terakhir_terkirim'] ?? '-', $r['jumlah_tidak_terkirim'], $r['status_label']];
        }
        return $this->kirimCsv("laporan-kontak-$stamp.csv", $header, $baris);
    }

    private function filterPenerimaKontak(): array
    {
        $g = $this->request->getGet();

        $dari   = $this->tanggal($g['dari'] ?? null);
        $sampai = $this->tanggal($g['sampai'] ?? null);
        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $kategori = (string) ($g['kategori'] ?? '');
        if ($kategori !== 'none' && !ctype_digit($kategori)) {
            $kategori = '';
        }

        $statusKontak = (string) ($g['status_kontak'] ?? '');
        if (!in_array($statusKontak, ['belum', 'pernah', 'bermasalah', 'tanpa_email'], true)) {
            $statusKontak = '';
        }

        $statusKirim = (string) ($g['status_kirim'] ?? '');
        if (!in_array($statusKirim, ['terkirim', 'gagal', 'menunggu'], true)) {
            $statusKirim = '';
        }

        $tab = (string) ($g['tab'] ?? 'kategori');
        if (!in_array($tab, ['kategori', 'kontak', 'penerima'], true)) {
            $tab = 'kategori';
        }

        return [
            'dari'          => $dari,
            'sampai'        => $sampai,
            'kategori'      => $kategori,
            'kampanye'      => ((int) ($g['kampanye'] ?? 0)) ?: null,
            'status_kontak' => $statusKontak,
            'status_kirim'  => $statusKirim,
            'tab'           => $tab,
        ];
    }

    /** Tambahkan status & label pada tiap kontak. */
    private function olahKontak(array $rows): array
    {
        foreach ($rows as &$r) {
            foreach (['total_kirim', 'berhasil', 'gagal', 'jml_kampanye', 'jumlah_tidak_terkirim'] as $k) {
                $r[$k] = (int) ($r[$k] ?? 0);
            }

            $tanpaEmail = trim((string) ($r['email'] ?? '')) === '';
            $bermasalah = ($r['gagal'] > 0 && $r['berhasil'] === 0) || $r['jumlah_tidak_terkirim'] > 0;

            if ($tanpaEmail) {
                [$r['status_kode'], $r['status_label'], $r['status_class']] = ['tanpa_email', 'Tanpa email', 'label-default'];
            } elseif ($bermasalah) {
                [$r['status_kode'], $r['status_label'], $r['status_class']] = ['bermasalah', 'Bermasalah', 'label-danger'];
            } elseif ($r['total_kirim'] === 0) {
                [$r['status_kode'], $r['status_label'], $r['status_class']] = ['belum', 'Belum pernah dikirimi', 'label-warning'];
            } else {
                [$r['status_kode'], $r['status_label'], $r['status_class']] = ['pernah', 'Pernah dikirimi', 'label-success'];
            }
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
            $r['error_ringkas'] = $r['status_pengiriman'] === 'gagal' ? $this->ringkasError($r['pesan_kesalahan'] ?? '') : '';
        }
        unset($r);

        return $rows;
    }

    /**
     * Ringkas log SMTP yang panjang menjadi satu baris alasan gagal.
     */
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
                continue; // lewati balasan sukses (2xx/3xx)
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