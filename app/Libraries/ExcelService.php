<?php

namespace App\Libraries;

use App\Models\PerusahaanModel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelService
{
    protected string $storageDir;
    protected string $templatePath;

    public function __construct()
    {
        $this->storageDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'analyzer';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }
        $this->templatePath = $this->storageDir . DIRECTORY_SEPARATOR . 'last_uploaded.xlsx';
    }

    /**
     * Memeriksa apakah file template asli tersimpan di storage
     */
    public function hasTemplateFile(): bool
    {
        return file_exists($this->templatePath);
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    // =====================================================================
    // HELPER PEMBACAAN SEL (tahan terhadap rumus yang gagal dihitung)
    // =====================================================================

    /**
     * Baca satu sel dengan aman. Jika rumus gagal dihitung (mis. "internal error"),
     * pakai nilai hasil hitung Excel yang tersimpan di dalam file.
     *
     * @return array [raw, formatted, isDate]
     */
    protected function readCell(Cell $cell): array
    {
        $raw = $cell->getValue();
        try {
            $formatted = $cell->getFormattedValue();
            $isDate = ExcelDate::isDateTime($cell);
        } catch (\Throwable $e) {
            $cached = $cell->getOldCalculatedValue();
            $raw = $cached;
            $formatted = $cached === null ? '' : (string) $cached;
            $isDate = false;
        }

        return [$raw, $formatted, $isDate];
    }

    /**
     * Teks sel yang sudah di-trim. Rumus yang gagal dihitung memakai nilai tersimpan.
     */
    protected function safeValue(Cell $cell): string
    {
        try {
            return trim((string) $cell->getCalculatedValue());
        } catch (\Throwable $e) {
            return trim((string) $cell->getOldCalculatedValue());
        }
    }

    /**
     * Teks sel pada kolom (index 0-based) dan baris tertentu. Null jika kolom tidak ada.
     */
    protected function valAt(Worksheet $sheet, ?int $idx, int $row): ?string
    {
        if ($idx === null) {
            return null;
        }

        return $this->safeValue($sheet->getCell([$idx + 1, $row]));
    }

    /**
     * Pilih sheet: "PRIORITAS" jika ada, jika tidak sheet pertama.
     */
    protected function pickSheet(Spreadsheet $spreadsheet): Worksheet
    {
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strcasecmp(trim($name), 'PRIORITAS') === 0) {
                return $spreadsheet->getSheetByName($name);
            }
        }

        return $spreadsheet->getSheet(0);
    }

    /**
     * Baca baris header. Mengembalikan [daftar header, peta nama-huruf-kecil => index 0-based]
     */
    protected function readHeader(Worksheet $sheet, int $highestColumnIndex): array
    {
        $headerRow = [];
        $colMap = [];

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $val = $this->safeValue($sheet->getCell([$col, 1]));
            $headerRow[] = $val;

            $key = strtolower($val);
            if ($key !== '' && !isset($colMap[$key])) {
                $colMap[$key] = $col - 1; // 0-based
            }
        }

        return [$headerRow, $colMap];
    }

    /**
     * Membaca dan menormalisasi nilai kolom Booth Number atau Hall Number.
     * Jika berupa sel tanggal di Excel, diubah kembali menjadi format "bulan-hari" (contoh: 8-30).
     *
     * @param mixed $cellVal
     * @param string|null $formattedVal
     * @param bool $isDateTime
     * @param bool &$wasConverted Output flag apakah sel ini dikonversi dari tanggal
     * @return string
     */
    public function normalizeBoothOrHallValue($cellVal, ?string $formattedVal = null, bool $isDateTime = false, bool &$wasConverted = false): string
    {
        $wasConverted = false;

        // 1. Jika terdeteksi sebagai Excel DateTime numerik
        if ($isDateTime && is_numeric($cellVal)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject($cellVal);
                $wasConverted = true;
                return $dt->format('n-j');
            } catch (\Exception $e) {
                // Lanjut ke pemeriksaan string di bawah
            }
        }

        $checkVal = trim((string) ($formattedVal ?: $cellVal));
        if ($checkVal === '') {
            return '';
        }

        // 2. Format teks tanggal standar Excel: 30-Aug, 03-Jun, d-mmm
        if (preg_match('/^(\d{1,2})-([A-Za-z]{3})$/i', $checkVal, $m)) {
            $time = strtotime('2026-' . $m[2] . '-' . $m[1]);
            if ($time !== false) {
                $wasConverted = true;
                return date('n-j', $time);
            }
        }

        // 3. Format: Aug-30, mmm-d
        if (preg_match('/^([A-Za-z]{3})-(\d{1,2})$/i', $checkVal, $m)) {
            $time = strtotime('2026-' . $m[1] . '-' . $m[2]);
            if ($time !== false) {
                $wasConverted = true;
                return date('n-j', $time);
            }
        }

        // 4. Format ISO: 2026-08-30 atau 2024-08-30
        if (preg_match('/^\d{4}-(\d{1,2})-(\d{1,2})$/', $checkVal, $m)) {
            $wasConverted = true;
            return (int) $m[1] . '-' . (int) $m[2];
        }

        // 5. Format tanggal US Excel: 8/30/2026 atau 08/30/2026
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/\d{2,4}$/', $checkVal, $m)) {
            $wasConverted = true;
            return (int) $m[1] . '-' . (int) $m[2];
        }

        // 6. Format tanggal dengan jam: 2026-08-30 00:00:00
        if (preg_match('/^\d{4}-(\d{1,2})-(\d{1,2})\s+\d{2}:\d{2}:\d{2}$/', $checkVal, $m)) {
            $wasConverted = true;
            return (int) $m[1] . '-' . (int) $m[2];
        }

        return $checkVal;
    }

    // =====================================================================
    // PREVIEW
    // =====================================================================

    /**
     * Membaca preview 5-10 baris pertama dari file Excel
     */
    public function previewExcel(string $filePath, int $maxRows = 10): array
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false); // agar format tanggal & tipe sel terbaca
            if ($reader instanceof \PhpOffice\PhpSpreadsheet\Reader\Csv) {
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setInputEncoding('UTF-8');
            }
            $spreadsheet = $reader->load($filePath);

            $sheetNames = $spreadsheet->getSheetNames();
            $sheet = $this->pickSheet($spreadsheet);
            $targetSheetName = $sheet->getTitle();

            $highestRow = min($sheet->getHighestRow(), $maxRows + 1); // +1 untuk header
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            [$headerRow, $colMap] = $this->readHeader($sheet, $highestColumnIndex);

            // Identifikasi kolom Booth Number dan Hall Number
            $idxBooth = $this->findColIndex($colMap, ['booth number', 'booth_number', 'booth', 'no booth', 'nomor booth']);
            $idxHall = $this->findColIndex($colMap, ['hall number', 'hall_number', 'hall', 'no hall', 'nomor hall']);

            $previewRows = [];
            $convertedCells = [];
            $dateConvertedCount = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowCells = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cell = $sheet->getCell([$col, $row]);
                    [$rawVal, $formattedVal, $isDate] = $this->readCell($cell);
                    $colIdx0 = $col - 1;

                    $isBoothOrHall = ($colIdx0 === $idxBooth || $colIdx0 === $idxHall);

                    if ($isBoothOrHall || $isDate) {
                        $wasConverted = false;
                        $val = $this->normalizeBoothOrHallValue($rawVal, $formattedVal, $isDate, $wasConverted);
                        if ($wasConverted) {
                            $rowIdx0 = $row - 2;
                            $convertedCells[$rowIdx0 . '_' . $colIdx0] = [
                                'original' => $formattedVal ?: (string) $rawVal,
                                'converted' => $val,
                                'column' => $headerRow[$colIdx0] ?? '',
                            ];
                            $dateConvertedCount++;
                        }
                    } else {
                        $val = ($formattedVal !== null && $formattedVal !== '') ? (string) $formattedVal : (string) $rawVal;
                    }
                    $rowCells[] = $val;
                }
                $previewRows[] = $rowCells;
            }

            return [
                'success' => true,
                'sheet_name' => $targetSheetName,
                'all_sheets' => $sheetNames,
                'total_rows' => $sheet->getHighestRow() - 1,
                'header' => $headerRow,
                'preview_rows' => $previewRows,
                'converted_cells' => $convertedCells,
                'date_converted_count' => $dateConvertedCount,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Gagal membaca preview Excel: ' . $e->getMessage(),
            ];
        }
    }

    // =====================================================================
    // IMPORT
    // =====================================================================

    /**
     * Mengimpor data dari file Excel ke tabel `kontak_email` (lewat PerusahaanModel)
     *
     * @param string $filePath Path file excel
     * @param string $mode 'append' (tambah & perbarui). Mode 'replace' dinonaktifkan.
     * @return array [success => bool, imported => int, updated => int, skipped => int, failed => int, ...]
     */
    public function importExcel(string $filePath, string $mode = 'append'): array
    {
        // Mode replace dulu mengosongkan tabel `perusahaan`; sekarang datanya ada di kontak_email
        // bersama data kontak lain, jadi menghapus semuanya terlalu berbahaya.
        if ($mode === 'replace') {
            return [
                'success' => false,
                'error' => 'Mode "Ganti semua data" dinonaktifkan karena akan menghapus seluruh isi tabel kontak. Gunakan mode tambah/perbarui.',
            ];
        }

        try {
            @set_time_limit(300);

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false); // agar number format & date type terbaca
            if ($reader instanceof \PhpOffice\PhpSpreadsheet\Reader\Csv) {
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setInputEncoding('UTF-8');
            }
            $spreadsheet = $reader->load($filePath);

            $targetSheet = $this->pickSheet($spreadsheet);

            $highestRow = $targetSheet->getHighestRow();
            $highestColumn = $targetSheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            [$headerRow, $colMap] = $this->readHeader($targetSheet, $highestColumnIndex);

            // Cari index masing-masing kolom
            $idxNo = $this->findColIndex($colMap, ['no', 'nomor', 'id']);
            $idxNama = $this->findColIndex($colMap, ['nama perusahaan', 'nama_perusahaan', 'perusahaan', 'company name']);
            $idxEmail = $this->findColIndex($colMap, ['alamat email', 'email', 'e-mail']);
            $idxMainCat = $this->findColIndex($colMap, ['main categories', 'main category', 'kategori utama']);
            $idxSubCat = $this->findColIndex($colMap, ['sub categories', 'sub category', 'sub kategori']);
            $idxProfile = $this->findColIndex($colMap, ['company profile', 'profil perusahaan', 'profile']);
            $idxWebsite = $this->findColIndex($colMap, ['website', 'web', 'site']);
            $idxCatalog = $this->findColIndex($colMap, ['link catalog', 'catalog', 'katalog', 'link_catalog']);
            $idxProdukAuto = $this->findColIndex($colMap, ['produk yang dijual (auto)', 'produk_yang_dijual_auto', 'produk dijual ai']);
            $idxBooth = $this->findColIndex($colMap, ['booth number', 'booth_number', 'booth', 'no booth', 'nomor booth']);
            $idxHall = $this->findColIndex($colMap, ['hall number', 'hall_number', 'hall', 'no hall', 'nomor hall']);

            if ($idxNama === null) {
                return [
                    'success' => false,
                    'error' => 'Kolom "Nama Perusahaan" tidak ditemukan di sheet "' . $targetSheet->getTitle() . '".',
                ];
            }

            $perusahaanModel = new PerusahaanModel();

            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $failed = 0;
            $errors = [];
            $now = date('Y-m-d H:i:s');

            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    $namaPerusahaan = (string) $this->valAt($targetSheet, $idxNama, $row);
                    if ($namaPerusahaan === '') {
                        $skipped++;
                        continue;
                    }

                    $nomorExcel = $idxNo !== null ? (string) $this->valAt($targetSheet, $idxNo, $row) : (string) ($row - 1);
                    $email = $idxEmail !== null ? strtolower((string) $this->valAt($targetSheet, $idxEmail, $row)) : '';
                    $mainCat = $this->valAt($targetSheet, $idxMainCat, $row);
                    $subCat = $this->valAt($targetSheet, $idxSubCat, $row);
                    $profile = $this->valAt($targetSheet, $idxProfile, $row);
                    $website = $this->valAt($targetSheet, $idxWebsite, $row);
                    $catalog = $this->valAt($targetSheet, $idxCatalog, $row);

                    $produkAutoRaw = (string) $this->valAt($targetSheet, $idxProdukAuto, $row);

                    // KETENTUAN 1: nilai kosong, '-', atau 'n/a' BUKAN hasil produk dan tidak boleh menimpa hasil AI
                    $incomingProdukValid = $produkAutoRaw !== '' && $produkAutoRaw !== '-' && strtolower($produkAutoRaw) !== 'n/a';
                    $cleanProdukAuto = $incomingProdukValid ? $produkAutoRaw : null;

                    // KETENTUAN 2: Normalisasi Booth Number dan Hall Number jika bertipe tanggal di template
                    foreach ([$idxBooth, $idxHall] as $idxBH) {
                        if ($idxBH === null) {
                            continue;
                        }
                        $cellBH = $targetSheet->getCell([$idxBH + 1, $row]);
                        [$bhRaw, $bhFmt, $bhIsDate] = $this->readCell($cellBH);
                        $wasConv = false;
                        $bhVal = $this->normalizeBoothOrHallValue($bhRaw, $bhFmt, $bhIsDate, $wasConv);
                        if ($wasConv) {
                            $cellBH->setValueExplicit($bhVal, DataType::TYPE_STRING);
                            $cellBH->getStyle()->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                        }
                    }

                    $dataRow = [
                        'nomor_excel' => $nomorExcel,
                        'nama_perusahaan' => $namaPerusahaan,
                        'main_categories' => $mainCat,
                        'sub_categories' => $subCat,
                        'company_profile' => $profile,
                        'website' => $website,
                        'link_catalog' => $catalog,
                        'updated_at' => $now,
                    ];
                    if ($email !== '') {
                        $dataRow['email'] = $email;
                    }

                    // Cari data yang sudah ada: email -> nomor excel -> nama perusahaan
                    $existing = null;
                    if ($email !== '') {
                        $existing = $perusahaanModel->where('email', $email)->first();
                    }
                    if (!$existing && $nomorExcel !== '') {
                        $existing = $perusahaanModel->where('nomor_excel', $nomorExcel)->first();
                    }
                    if (!$existing) {
                        $existing = $perusahaanModel->where('nama_perusahaan', $namaPerusahaan)->first();
                    }

                    if ($existing) {
                        // KETENTUAN 1: jangan timpa hasil analisis AI yang sudah ada dengan nilai kosong atau "-".
                        $existingHasAnalysis = !empty($existing['produk_yang_dijual_auto'])
                            || in_array($existing['status_analisis'] ?? '', ['sukses', 'manual'], true);

                        if (!$existingHasAnalysis && $incomingProdukValid) {
                            $dataRow['produk_yang_dijual_auto'] = $cleanProdukAuto;
                            $dataRow['status_analisis'] = 'sukses';
                        }

                        if ($email !== '') {
                            $dataRow['unique_hash'] = hash('sha256', $email);
                        }

                        $perusahaanModel->update($existing['id'], $dataRow);
                        $updated++;
                    } else {
                        // Data baru (insert)
                        $dataRow['produk_yang_dijual_auto'] = $cleanProdukAuto;
                        $dataRow['status_analisis'] = $cleanProdukAuto ? 'sukses' : 'belum_dianalisis';
                        $dataRow['unique_hash'] = hash(
                            'sha256',
                            $email !== '' ? $email : 'perusahaan:' . mb_strtolower($namaPerusahaan, 'UTF-8')
                        );
                        $dataRow['created_at'] = $now;

                        $perusahaanModel->insert($dataRow);
                        $imported++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    if (count($errors) < 10) {
                        $errors[] = "Baris {$row}: " . $e->getMessage();
                    }
                }
            }

            // Simpan file template yang telah dinormalisasi Booth & Hall Number-nya (sheet lain tetap utuh).
            // Tidak kritis: kegagalan menyimpan tidak membatalkan hasil import.
            try {
                $writer = new Xlsx($spreadsheet);
                $writer->setPreCalculateFormulas(false); // jangan hitung ulang rumus saat menyimpan
                $writer->save($this->templatePath);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal menyimpan template Excel: ' . $e->getMessage());
            }

            $message = "Berhasil memproses Excel: {$imported} perusahaan baru ditambahkan, {$updated} diperbarui.";
            if ($failed > 0) {
                $message .= " {$failed} baris gagal disimpan.";
            }

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'failed' => $failed,
                'errors' => $errors,
                'total' => $highestRow - 1,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Terjadi kesalahan saat mengimpor Excel: ' . $e->getMessage(),
            ];
        }
    }

    // =====================================================================
    // EXPORT
    // =====================================================================

    /**
     * Memperbarui kolom "Produk Yang Dijual (Auto)" pada template Excel asli
     * dengan mempertahankan sheet lain dan seluruh format aslinya.
     *
     * @return string Path berkas Excel yang siap diunduh
     */
    public function exportUpdatedExcel(): string
    {
        $perusahaanModel = new PerusahaanModel();
        $allPerusahaan = $perusahaanModel->findAll();

        // Index data berdasarkan nomor_excel dan nama_perusahaan untuk pencarian cepat
        $byNomor = [];
        $byNama = [];
        foreach ($allPerusahaan as $p) {
            if (!empty($p['nomor_excel'])) {
                $byNomor[trim((string) $p['nomor_excel'])] = $p;
            }
            $byNama[strtolower(trim((string) $p['nama_perusahaan']))] = $p;
        }

        // Jika template asli ada, buka file asli untuk mempertahankan sheet lain dan format
        if (file_exists($this->templatePath)) {
            $reader = IOFactory::createReaderForFile($this->templatePath);
            // JANGAN setReadDataOnly(true) agar styling dan formula tetap utuh
            $spreadsheet = $reader->load($this->templatePath);
        } else {
            // Buat spreadsheet baru jika template belum ada
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->setTitle('PRIORITAS');
            $sheet7 = $spreadsheet->createSheet();
            $sheet7->setTitle('Sheet7');
        }

        $sheet = $this->pickSheet($spreadsheet);

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Cari letak kolom "No", "Nama Perusahaan", dan "Produk Yang Dijual (Auto)"
        $colNo = null;
        $colNama = null;
        $colTarget = null;

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $val = $this->safeValue($sheet->getCell([$col, 1]));
            $valLower = strtolower($val);

            if (in_array($valLower, ['no', 'nomor', 'id'], true)) {
                $colNo = $col;
            }
            if (in_array($valLower, ['nama perusahaan', 'nama_perusahaan', 'perusahaan', 'company name'], true)) {
                $colNama = $col;
            }
            if (strpos($valLower, 'produk yang dijual (auto)') !== false || strpos($valLower, 'produk_yang_dijual_auto') !== false) {
                $colTarget = $col;
            }
        }

        // Jika kolom Produk Yang Dijual (Auto) belum ada di header, tambahkan di kolom terakhir
        if ($colTarget === null) {
            $colTarget = $highestColumnIndex + 1;
            $sheet->setCellValue([$colTarget, 1], 'Produk Yang Dijual (Auto)');
        }

        // Tulis hasil analisis ke setiap baris
        for ($row = 2; $row <= $highestRow; $row++) {
            $nomorVal = $colNo ? $this->safeValue($sheet->getCell([$colNo, $row])) : '';
            $namaVal = $colNama ? $this->safeValue($sheet->getCell([$colNama, $row])) : '';

            $match = null;
            if ($nomorVal !== '' && isset($byNomor[$nomorVal])) {
                $match = $byNomor[$nomorVal];
            } elseif ($namaVal !== '' && isset($byNama[strtolower($namaVal)])) {
                $match = $byNama[strtolower($namaVal)];
            }

            if ($match && !empty($match['produk_yang_dijual_auto'])) {
                $sheet->setCellValue([$colTarget, $row], $match['produk_yang_dijual_auto']);
            }
        }

        // Simpan ke berkas sementara untuk diunduh
        $exportFileName = 'DATA_SEMUA_WEB_TEI_ANALYZED_' . date('Ymd_His') . '.xlsx';
        $exportFilePath = $this->storageDir . DIRECTORY_SEPARATOR . $exportFileName;

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false); // jangan hitung ulang rumus saat menyimpan
        $writer->save($exportFilePath);

        return $exportFilePath;
    }

    // =====================================================================

    /**
     * Cari index kolom dari berbagai variasi kata kunci.
     * Kata kunci pendek (<= 3 huruf, mis. "no", "id") hanya dicocokkan persis,
     * supaya tidak salah menangkap header lain yang kebetulan memuat huruf itu.
     */
    protected function findColIndex(array $colMap, array $candidates): ?int
    {
        foreach ($candidates as $cand) {
            if (isset($colMap[$cand])) {
                return $colMap[$cand];
            }
        }

        // Fuzzy match
        foreach ($colMap as $headerName => $idx) {
            foreach ($candidates as $cand) {
                if (strlen($cand) <= 3) {
                    continue;
                }
                if (strpos((string) $headerName, $cand) !== false) {
                    return $idx;
                }
            }
        }

        return null;
    }
}