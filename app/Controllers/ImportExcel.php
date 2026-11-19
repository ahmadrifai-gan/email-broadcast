<?php

namespace App\Controllers;

use App\Libraries\ExcelService;
use App\Models\PerusahaanModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Simpan di: app/Controllers/ImportExcel.php
 *
 * Import data analisis perusahaan dari Excel ke tabel kontak
 * (kolom nomor_excel, main_categories, sub_categories, company_profile,
 *  website, link_catalog, produk_yang_dijual_auto, status_analisis).
 *
 * Alur: pilih berkas -> pratinjau -> konfirmasi import.
 */
class ImportExcel extends BaseController
{
    // Nama berkas sementara hanya boleh berbentuk ini (mencegah path traversal)
    protected const POLA_BERKAS = '/^preview_[a-f0-9]{16}\.(xlsx|xls|csv)$/';

    protected function dirUpload(): string
    {
        $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'analyzer' . DIRECTORY_SEPARATOR;
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    /** Semua respons JSON membawa token CSRF terbaru untuk permintaan berikutnya. */
    protected function json(array $data)
    {
        $data['csrf_token'] = csrf_token();
        $data['csrf_hash']  = csrf_hash();

        return $this->response->setJSON($data);
    }

    // =========================================================
    //  HALAMAN
    // =========================================================
    public function index()
    {
        return view('import_excel/index', [
            'jumlahSekarang' => (new PerusahaanModel())->countAllResults(),
        ]);
    }

    // =========================================================
    //  UNDUH TEMPLATE
    // =========================================================
    public function template()
    {
        $ss    = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Perusahaan');

        $sheet->fromArray([
            'NOMOR', 'NAMA PERUSAHAAN', 'MAIN CATEGORIES', 'SUB CATEGORIES',
            'COMPANY PROFILE', 'WEBSITE', 'LINK CATALOG', 'PRODUK YANG DIJUAL (AUTO)',
        ], null, 'A1');

        $sheet->fromArray([
            '1', 'PT Contoh Makmur', 'Makanan', 'Tepung',
            'Produsen tepung untuk industri roti.', 'https://contoh.co.id',
            'https://contoh.co.id/katalog.pdf', "1. Tepung terigu\n2. Tepung tapioka",
        ], null, 'A2');

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        foreach (range('A', 'H') as $kol) {
            $sheet->getColumnDimension($kol)->setAutoSize(true);
        }

        ob_start();
        (new Xlsx($ss))->save('php://output');
        $isi = ob_get_clean();

        return $this->response->download('template-import-perusahaan.xlsx', $isi);
    }

    // =========================================================
    //  LANGKAH 1: PRATINJAU (AJAX)
    // =========================================================
    public function preview()
    {
        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $this->json(['success' => false, 'message' => 'Berkas belum dipilih atau gagal diunggah.']);
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return $this->json(['success' => false, 'message' => 'Format berkas harus .xlsx .xls atau .csv.']);
        }
        if ($file->getSizeByUnit('mb') > 20) {
            return $this->json(['success' => false, 'message' => 'Ukuran berkas maksimal 20 MB.']);
        }

        $nama = 'preview_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $file->move($this->dirUpload(), $nama);
        $path = $this->dirUpload() . $nama;

        $hasil = (new ExcelService())->previewExcel($path, 10);
        if (! $hasil['success']) {
            @unlink($path);

            return $this->json(['success' => false, 'message' => $hasil['error']]);
        }

        return $this->json($hasil + ['temp_file' => $nama]);
    }

    // =========================================================
    //  LANGKAH 2: IMPORT (AJAX)
    // =========================================================
    public function process()
    {
        // basename() + pola ketat: nama berkas tidak bisa diarahkan ke folder lain
        $nama = basename((string) $this->request->getPost('temp_file'));
        $mode = $this->request->getPost('mode') === 'update' ? 'update' : 'append';

        if ($nama === '' || $nama === '.') {
            return $this->json(['success' => false, 'message' => 'Berkas belum dipratinjau. Pilih berkas lalu klik Pratinjau.']);
        }
        if (! preg_match(self::POLA_BERKAS, $nama)) {
            return $this->json(['success' => false, 'message' => 'Nama berkas tidak valid.']);
        }

        $path = $this->dirUpload() . $nama;
        if (! is_file($path)) {
            return $this->json(['success' => false, 'message' => 'Berkas tidak ditemukan di server. Silakan pilih berkas kembali.']);
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $hasil = (new ExcelService())->importExcel($path, $mode);
        @unlink($path);

        return $this->json($hasil);
    }
}