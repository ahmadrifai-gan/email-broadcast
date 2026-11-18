<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\TravelModel;

class Travel extends Controller
{
    protected $travelModel;

    public function __construct()
    {
        $this->travelModel = new TravelModel();
    }

    // ============================================================
    // HALAMAN UTAMA
    // ============================================================

    public function index()
    {
        try {

            $history = $this->getSearchHistory();
        } catch (\Throwable $e) {

            log_message(
                'error',
                'TRAVEL INDEX ERROR: ' .
                    $e->getMessage()
            );

            $history = [];
        }

        return view('scrapper/travel_view', [
            'history' => $history
        ]);
    }


    // ============================================================
    // SCRAPE GOOGLE MAPS
    // ============================================================

    public function scrape()
    {
        try {

            // ------------------------------------------------------
            // INPUT
            // ------------------------------------------------------

            $input = trim(
                (string) $this->request->getPost('url')
            );

            if ($input === '') {

                return $this->jsonError(
                    'Keyword atau URL Google Maps tidak boleh kosong.'
                );
            }


            // ------------------------------------------------------
            // KEYWORD
            // ------------------------------------------------------

            $searchQuery = trim(
                $this->extractKeyword($input)
            );

            if ($searchQuery === '') {

                return $this->jsonError(
                    'Keyword pencarian tidak valid.'
                );
            }


            // ------------------------------------------------------
            // SERPAPI KEY
            // ------------------------------------------------------

            $apiKey = trim(
                (string) env('SERPAPI_KEY')
            );

            if ($apiKey === '') {

                $apiKey = trim(
                    (string) (
                        $_ENV['SERPAPI_KEY'] ?? ''
                    )
                );
            }

            if ($apiKey === '') {

                log_message(
                    'error',
                    'SERPAPI_KEY tidak ditemukan.'
                );

                return $this->jsonError(
                    'SERPAPI_KEY belum tersedia di file .env.'
                );
            }


            // ------------------------------------------------------
            // TIME LIMIT
            // ------------------------------------------------------

            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }


            // ------------------------------------------------------
            // VARIABLE
            // ------------------------------------------------------

            $results = [];

            $newData = [];

            $existingData = [];

            $seen = [];

            $start = 0;

            $page = 0;

            $maxPages = 500;

            $pageSize = 20;


            // ======================================================
            // SCRAPING LOOP
            // ======================================================

            while ($page < $maxPages) {

                $params = [
                    'engine'  => 'google_maps',
                    'q'       => $searchQuery,
                    'hl'      => 'id',
                    'gl'      => 'id',
                    'start'   => $start,
                    'api_key' => $apiKey,
                ];


                $apiUrl =
                    'https://serpapi.com/search.json?' .
                    http_build_query($params);


                // --------------------------------------------------
                // HTTP REQUEST
                // --------------------------------------------------

                $httpResult = $this->httpGetJson(
                    $apiUrl,
                    60,
                    'Accept: application/json'
                );


                $response = $httpResult['response'];
                $curlError = $httpResult['error'];
                $httpCode = $httpResult['http_code'];


                if ($response === false) {

                    log_message('error', 'SERPAPI REQUEST ERROR: ' . $curlError);

                    if ($page === 0) {
                        return $this->jsonError('Gagal menghubungi SerpApi. ' . $curlError);
                    }

                    break;
                }


                if ($httpCode >= 400) {

                    log_message('error', 'SERPAPI HTTP ERROR: ' . $httpCode . ' | ' . substr((string) $response, 0, 1000));

                    if ($page === 0) {
                        return $this->jsonError('SerpApi mengembalikan HTTP ' . $httpCode . '. Periksa API Key atau kuota SerpApi.');
                    }

                    break;
                }


                $json = json_decode((string) $response, true);


                if (!is_array($json)) {

                    log_message('error', 'SERPAPI INVALID JSON: ' . substr((string) $response, 0, 1000));

                    if ($page === 0) {
                        return $this->jsonError('Response dari SerpApi tidak valid.');
                    }

                    break;
                }


                if (isset($json['error'])) {

                    log_message('error', 'SERPAPI ERROR: ' . $json['error']);

                    if ($page === 0) {
                        return $this->jsonError('SerpApi Error: ' . $json['error']);
                    }

                    break;
                }


                $localResults = $json['local_results'] ?? [];


                if (!is_array($localResults) || empty($localResults)) {
                    break;
                }


                // ==================================================
                // PROSES DATA
                // ==================================================

                foreach ($localResults as $place) {

                    if (!is_array($place)) {
                        continue;
                    }


                    $nama = trim((string) ($place['title'] ?? ''));

                    if ($nama === '') {
                        continue;
                    }


                    $alamat = trim((string) ($place['address'] ?? ''));
                    $nomor = trim((string) ($place['phone'] ?? ''));
                    $website = trim((string) ($place['website'] ?? ''));
                    $email = trim((string) ($place['email'] ?? (is_array($place['emails'] ?? null) ? ($place['emails'][0] ?? '') : '')));


                    if ($email === '' && $website !== '') {
                        $email = $this->extractEmailFromWebsite($website);
                    }


                    $mapsUrl = '';

                    if (isset($place['gps_coordinates']) && is_array($place['gps_coordinates'])) {

                        $lat = $place['gps_coordinates']['latitude'] ?? null;
                        $lng = $place['gps_coordinates']['longitude'] ?? null;

                        if ($lat !== null && $lng !== null) {
                            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($lat . ',' . $lng);
                        }
                    }

                    if ($mapsUrl === '' && !empty($place['link'])) {
                        $mapsUrl = trim((string) $place['link']);
                    }


                    $uniqueHash = $this->generateUniqueHash($nama, $alamat, $nomor);


                    if (isset($seen[$uniqueHash])) {
                        continue;
                    }

                    $seen[$uniqueHash] = true;


                    // =================================================
                    // CEK DATABASE
                    // =================================================

                    $existing = $this->travelModel->where('unique_hash', $uniqueHash)->first();


                    $waStatus = null;
                    $waCheckedAt = null;


                    if ($existing) {

                        $waStatus = $existing['wa_status'] ?? null;
                        $waCheckedAt = $existing['wa_checked_at'] ?? null;

                        if (!empty($existing['email'])) {
                            $email = $existing['email'];
                        }
                    }


                    $dataItem = [
                        'id' => $existing['id'] ?? null,
                        'keyword' => $searchQuery,
                        'nama' => $nama,
                        'alamat' => $alamat,
                        'nomor' => $nomor,
                        'email' => $email,
                        'website' => $website,
                        'maps_url' => $mapsUrl,
                        'unique_hash' => $uniqueHash,
                        'wa_status' => $waStatus,
                        'wa_checked_at' => $waCheckedAt,
                        'status' => $existing ? 'existing' : 'new',
                    ];


                    $results[] = $dataItem;


                    if ($existing) {
                        $existingData[] = $dataItem;
                        continue;
                    }


                    $insertData = [
                        'nama_perusahaan' => $nama,
                        'email'           => $email !== '' ? $email : null,
                        'no_hp'           => $nomor !== '' ? $nomor : null,
                        'website'         => $website !== '' ? $website : null,
                        'catatan'         => $alamat !== '' ? $alamat : null,
                        'sub_categories'  => $searchQuery,
                        'sumber_data'     => 'Google Maps',

                        // Kolom tambahan Gmaps
                        'keyword'         => $searchQuery,
                        'alamat'          => $alamat !== '' ? $alamat : null,
                        'nomor'           => $nomor !== '' ? $nomor : null,
                        'maps_url'        => $mapsUrl !== '' ? $mapsUrl : null,
                        'wa_status'       => null,
                        'wa_checked_at'   => null,
                        'unique_hash'     => $uniqueHash,
                    ];


                    try {

                        $inserted = $this->travelModel->insert($insertData, true);

                        if ($inserted === false) {
                            log_message('error', 'DATABASE INSERT FAILED: ' . json_encode($this->travelModel->errors(), JSON_UNESCAPED_UNICODE));
                            continue;
                        }

                        $insertId = $this->travelModel->getInsertID();
                        $dataItem['id'] = $insertId;
                        $newData[] = $dataItem;
                    } catch (\Throwable $dbError) {

                        log_message('error', 'DATABASE INSERT ERROR: ' . $dbError->getMessage() . ' | FILE: ' . $dbError->getFile() . ' | LINE: ' . $dbError->getLine());
                        continue;
                    }
                }


                $page++;
                $start += $pageSize;


                if (count($localResults) < $pageSize) {
                    break;
                }
            }


            if (empty($results)) {
                return $this->jsonError('Tidak ada data tempat yang berhasil ditemukan.');
            }


            try {
                $history = $this->getSearchHistory();
            } catch (\Throwable $historyError) {
                log_message('error', 'HISTORY AFTER SCRAPE ERROR: ' . $historyError->getMessage());
                $history = [];
            }


            return $this->response
                ->setStatusCode(200)
                ->setJSON([
                    'status' => 'success',
                    'message' => 'Data berhasil diambil dari Google Maps.',
                    'keyword' => $searchQuery,
                    'data' => $results,
                    'total' => count($results),
                    'new_total' => count($newData),
                    'existing_total' => count($existingData),
                    'new_data' => $newData,
                    'existing_data' => $existingData,
                    'history' => $history,
                    'csrf' => $this->getCsrf(),
                ]);
        } catch (\Throwable $e) {

            log_message('error', 'TRAVEL SCRAPE ERROR: ' . $e->getMessage() . ' | FILE: ' . $e->getFile() . ' | LINE: ' . $e->getLine());

            return $this->jsonError('Terjadi kesalahan pada server. Periksa log aplikasi untuk detailnya.');
        }
    }


    // ============================================================
    // GENERATE UNIQUE HASH
    // ============================================================

    private function generateUniqueHash(string $nama, string $alamat, string $nomor): string
    {
        $value = strtolower(trim($nama . '|' . $alamat . '|' . $nomor));
        return hash('sha256', $value);
    }


    // ============================================================
    // DAFTAR KEYWORD
    // ============================================================

    public function editData()
    {
        try {

            $keywords = $this->travelModel
                ->select('keyword, COUNT(*) AS total_data')
                ->where('keyword IS NOT NULL')
                ->where('keyword !=', '')
                ->groupBy('keyword')
                ->orderBy('keyword', 'ASC')
                ->findAll();


            return view('scrapper/travel_edit', [
                'keywords' => $keywords,
                'totalKeyword' => count($keywords),
            ]);
        } catch (\Throwable $e) {

            log_message('error', 'EDIT DATA ERROR: ' . $e->getMessage());

            return redirect()->to(site_url('scrapper'))->with('error', 'Gagal mengambil daftar keyword.');
        }
    }


    // ============================================================
    // DATA BERDASARKAN KEYWORD
    // ============================================================

    public function editDataKeyword($keyword = null)
    {
        try {

            $keyword = trim((string) $keyword);

            if ($keyword === '') {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Keyword tidak valid.');
            }


            $items = $this->travelModel
                ->where('keyword', $keyword)
                ->orderBy('id', 'DESC')
                ->findAll();


            if (empty($items)) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Data untuk keyword "' . $keyword . '" tidak ditemukan.');
            }


            return view('scrapper/travel_edit_keyword', [
                'keyword' => $keyword,
                'items' => $items,
                'totalData' => count($items),
            ]);
        } catch (\Throwable $e) {

            log_message('error', 'EDIT KEYWORD ERROR: ' . $e->getMessage());

            return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Gagal mengambil data keyword.');
        }
    }


    // ============================================================
    // EXPORT EXCEL PER KEYWORD
    // ============================================================

    public function exportExcel($keyword = null)
    {
        try {

            $keyword = trim((string) $keyword);

            if ($keyword === '') {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Keyword tidak valid.');
            }


            $items = $this->travelModel
                ->where('keyword', $keyword)
                ->orderBy('id', 'DESC')
                ->findAll();


            if (empty($items)) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Data untuk keyword "' . $keyword . '" tidak ditemukan.');
            }


            $headers = [
                'No',
                'Nama',
                'Alamat',
                'Nomor HP',
                'Email',
                'Website',
                'Status Website',
                'Status WhatsApp',
                'Link Google Maps',
                'Tanggal Disimpan',
            ];


            $rows = [];

            foreach ($items as $index => $item) {
                $rows[] = [
                    $index + 1,
                    (string) ($item['nama'] ?? ''),
                    (string) ($item['alamat'] ?? ''),
                    (string) ($item['nomor'] ?? ''),
                    (string) ($item['email'] ?? ''),
                    (string) ($item['website'] ?? ''),
                    $this->labelWebsiteStatus((string) ($item['website_status'] ?? '')),
                    $this->labelWaStatus((string) ($item['wa_status'] ?? '')),
                    (string) ($item['maps_url'] ?? ''),
                    (string) ($item['created_at'] ?? ''),
                ];
            }


            $binary = $this->buildXlsx($headers, $rows, $keyword);

            if ($binary === '') {
                return redirect()->to(site_url('scrapper/edit-data/' . rawurlencode($keyword)))->with('error', 'Gagal membuat file Excel. Pastikan ekstensi ZipArchive aktif di server.');
            }


            $filename = 'data-' . preg_replace('/[^A-Za-z0-9\-]+/', '-', strtolower($keyword)) . '-' . date('Ymd-His') . '.xlsx';


            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Content-Length', (string) strlen($binary))
                ->setHeader('Cache-Control', 'max-age=0')
                ->setBody($binary);
        } catch (\Throwable $e) {

            log_message('error', 'EXPORT EXCEL ERROR: ' . $e->getMessage());

            return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Gagal export data ke Excel.');
        }
    }


    private function labelWaStatus(string $status): string
    {
        if ($status === 'active') return 'Aktif WA';
        if ($status === 'inactive') return 'Tidak Aktif WA';
        return 'Belum Dicek';
    }


    private function labelWebsiteStatus(string $status): string
    {
        if ($status === 'active') return 'Website Aktif';
        if ($status === 'inactive') return 'Website Tidak Aktif';
        return 'Belum Dicek';
    }


    private function buildXlsx(array $headers, array $rows, string $sheetTitle): string
    {
        try {

            $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

            if ($tempFile === false) return '';

            @unlink($tempFile);

            $zip = new \ZipArchive();

            if ($zip->open($tempFile, \ZipArchive::CREATE) !== true) return '';


            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');

            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');

            $safeSheetName = substr((string) preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetTitle), 0, 31);
            if (trim($safeSheetName) === '') $safeSheetName = 'Data';

            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $this->xmlSafeText($safeSheetName) . '" sheetId="1" r:id="rId1"/></sheets></workbook>');

            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');

            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="1"><fill><patternFill patternType="none"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs></styleSheet>');

            $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildSheetXml($headers, $rows));

            $zip->close();

            $binary = file_get_contents($tempFile);
            @unlink($tempFile);

            return $binary === false ? '' : $binary;
        } catch (\Throwable $e) {
            log_message('error', 'BUILD XLSX ERROR: ' . $e->getMessage());
            return '';
        }
    }


    private function buildSheetXml(array $headers, array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        $rowIndex = 1;
        $xml .= '<row r="' . $rowIndex . '">';

        foreach ($headers as $colIndex => $label) {
            $cellRef = $this->excelColumnLetter($colIndex) . $rowIndex;
            $xml .= '<c r="' . $cellRef . '" t="inlineStr" s="1"><is><t xml:space="preserve">' . $this->xmlSafeText((string) $label) . '</t></is></c>';
        }

        $xml .= '</row>';

        foreach ($rows as $row) {
            $rowIndex++;
            $xml .= '<row r="' . $rowIndex . '">';
            foreach ($row as $colIndex => $value) {
                $cellRef = $this->excelColumnLetter($colIndex) . $rowIndex;
                $xml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t xml:space="preserve">' . $this->xmlSafeText((string) $value) . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';
        return $xml;
    }


    private function excelColumnLetter(int $index): string
    {
        $letter = '';
        $index++;

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = intdiv($index - $mod, 26);
        }

        return $letter;
    }


    private function xmlSafeText(string $value): string
    {
        $value = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }


    // ============================================================
    // FORM EDIT
    // ============================================================

    public function edit($id)
    {
        try {
            $id = (int) $id;

            if ($id <= 0) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'ID data tidak valid.');
            }

            $data = $this->travelModel->find($id);

            if (!$data) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Data tidak ditemukan.');
            }

            return view('scrapper/travel_edit_form', [
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'EDIT FORM ERROR: ' . $e->getMessage());
            return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Gagal membuka form edit.');
        }
    }


    // ============================================================
    // UPDATE
    // ============================================================

    public function update($id)
    {
        try {
            $id = (int) $id;

            if ($id <= 0) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'ID data tidak valid.');
            }

            $oldData = $this->travelModel->find($id);

            if (!$oldData) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Data tidak ditemukan.');
            }

            $rules = [
                'keyword' => 'required|max_length[255]',
                'nama' => 'required|max_length[255]',
                'alamat' => 'permit_empty|max_length[500]',
                'nomor' => 'permit_empty|max_length[50]',
                'email' => 'permit_empty|valid_email|max_length[255]',
                'website' => 'permit_empty|max_length[500]',
                'maps_url' => 'permit_empty|max_length[1000]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $keyword = trim((string) $this->request->getPost('keyword'));
            $nama = trim((string) $this->request->getPost('nama'));
            $alamat = trim((string) $this->request->getPost('alamat'));
            $nomor = trim((string) $this->request->getPost('nomor'));
            $email = trim((string) $this->request->getPost('email'));
            $website = trim((string) $this->request->getPost('website'));
            $mapsUrl = trim((string) $this->request->getPost('maps_url'));

            $uniqueHash = $this->generateUniqueHash($nama, $alamat, $nomor);

            $duplicate = $this->travelModel->where('unique_hash', $uniqueHash)->where('id !=', $id)->first();

            if ($duplicate) {
                return redirect()->back()->withInput()->with('error', 'Data dengan nama, alamat, dan nomor tersebut sudah ada.');
            }

            $waStatus = $oldData['wa_status'] ?? null;
            $waCheckedAt = $oldData['wa_checked_at'] ?? null;

            $updateData = [
                'keyword' => $keyword,
                'nama' => $nama,
                'alamat' => $alamat !== '' ? $alamat : null,
                'nomor' => $nomor !== '' ? $nomor : null,
                'email' => $email !== '' ? $email : null,
                'website' => $website !== '' ? $website : null,
                'maps_url' => $mapsUrl !== '' ? $mapsUrl : null,
                'wa_status' => $waStatus,
                'wa_checked_at' => $waCheckedAt,
                'unique_hash' => $uniqueHash,
            ];

            $updated = $this->travelModel->update($id, $updateData);

            if (!$updated) {
                log_message('error', 'TRAVEL UPDATE FAILED: ' . json_encode($this->travelModel->errors(), JSON_UNESCAPED_UNICODE));
                return redirect()->back()->withInput()->with('error', 'Data gagal diperbarui.');
            }

            return redirect()->to(site_url('scrapper/edit-data/' . rawurlencode($keyword)))->with('success', 'Data berhasil diperbarui.');
        } catch (\Throwable $e) {
            log_message('error', 'UPDATE TRAVEL ERROR: ' . $e->getMessage() . ' | FILE: ' . $e->getFile() . ' | LINE: ' . $e->getLine());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }


    // ============================================================
    // DELETE
    // ============================================================

    public function delete($id)
    {
        try {
            $id = (int) $id;

            if ($id <= 0) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'ID data tidak valid.');
            }

            $data = $this->travelModel->find($id);

            if (!$data) {
                return redirect()->to(site_url('scrapper/edit-data'))->with('error', 'Data tidak ditemukan.');
            }

            $keyword = trim((string) ($data['keyword'] ?? ''));

            $deleted = $this->travelModel->delete($id);

            if (!$deleted) {
                return redirect()->back()->with('error', 'Data gagal dihapus.');
            }

            $remaining = $this->travelModel->where('keyword', $keyword)->countAllResults();

            if ($remaining > 0) {
                return redirect()->to(site_url('scrapper/edit-data/' . rawurlencode($keyword)))->with('success', 'Data berhasil dihapus.');
            }

            return redirect()->to(site_url('scrapper/edit-data'))->with('success', 'Data berhasil dihapus.');
        } catch (\Throwable $e) {
            log_message('error', 'DELETE TRAVEL ERROR: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Data gagal dihapus.');
        }
    }


    // ============================================================
    // CEK STATUS WEBSITE
    // ============================================================

    public function checkWebsite($id)
    {
        try {
            $id = (int) $id;

            if ($id <= 0) return $this->jsonError('ID data tidak valid.');

            $data = $this->travelModel->find($id);

            if (!$data) return $this->jsonError('Data tidak ditemukan.');

            $website = trim((string) ($data['website'] ?? ''));

            if ($website === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data ini tidak memiliki alamat website.',
                ]);
            }

            if (!preg_match('#^https?://#i', $website)) {
                $website = 'https://' . $website;
            }

            $result = $this->httpGetJson($website, 15, 'Accept: text/html,application/xhtml+xml');

            $httpCode = (int) ($result['http_code'] ?? 0);

            $isReachable = $httpCode >= 200 && $httpCode < 400;

            $status = $isReachable ? 'active' : 'inactive';
            $checkedAt = date('Y-m-d H:i:s');

            $this->travelModel->update($id, [
                'website_status' => $status,
                'website_checked_at' => $checkedAt,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'website_status' => $status,
                'website_checked_at' => $checkedAt,
                'http_code' => $httpCode,
                'message' => $isReachable ? 'Website dapat diakses.' : 'Website tidak dapat diakses.',
                'csrf' => $this->getCsrf(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'CHECK WEBSITE ERROR: ' . $e->getMessage());
            return $this->jsonError('Gagal memeriksa status website. ' . $e->getMessage());
        }
    }


    // ============================================================
    // CEK STATUS WEBSITE (BULK)
    // ============================================================

    public function checkWebsiteBulk()
    {
        try {
            $ids = $this->request->getPost('ids');

            if (!is_array($ids) || empty($ids)) {
                return $this->jsonError('Tidak ada data yang dipilih untuk dicek.');
            }

            $results = [];

            foreach ($ids as $rawId) {
                $itemId = (int) $rawId;
                if ($itemId <= 0) continue;

                $data = $this->travelModel->find($itemId);

                if (!$data) {
                    $results[] = ['id' => $itemId, 'success' => false, 'message' => 'Data tidak ditemukan.'];
                    continue;
                }

                $website = trim((string) ($data['website'] ?? ''));

                if ($website === '') {
                    $results[] = ['id' => $itemId, 'success' => false, 'message' => 'Tidak ada website.'];
                    continue;
                }

                if (!preg_match('#^https?://#i', $website)) {
                    $website = 'https://' . $website;
                }

                $result = $this->httpGetJson($website, 15, 'Accept: text/html,application/xhtml+xml');
                $httpCode = (int) ($result['http_code'] ?? 0);
                $isReachable = $httpCode >= 200 && $httpCode < 400;
                $status = $isReachable ? 'active' : 'inactive';
                $checkedAt = date('Y-m-d H:i:s');

                $this->travelModel->update($itemId, [
                    'website_status' => $status,
                    'website_checked_at' => $checkedAt,
                ]);

                $results[] = ['id' => $itemId, 'success' => true, 'website_status' => $status, 'website_checked_at' => $checkedAt];
            }

            return $this->response->setJSON([
                'success' => true,
                'results' => $results,
                'csrf' => $this->getCsrf(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'CHECK WEBSITE BULK ERROR: ' . $e->getMessage());
            return $this->jsonError('Gagal memeriksa status website secara massal.');
        }
    }


    // ============================================================
    // CEK STATUS WHATSAPP (VIA FONNTE)
    // ============================================================

    private function fonnteValidateNumber(string $phone): array
    {
        $token = trim((string) (env('FONNTE_TOKEN') ?? ''));

        if ($token === '') {
            return ['success' => false, 'message' => 'FONNTE_TOKEN belum diatur di file .env.'];
        }

        $result = $this->httpPostForm('https://api.fonnte.com/validate', [
            'target' => $phone,
            'countryCode' => '62',
        ], ['Authorization: ' . $token], 30);

        if (empty($result['response']) || $result['error'] !== '') {
            return ['success' => false, 'message' => 'Gagal menghubungi server Fonnte. ' . ($result['error'] ?? '')];
        }

        $decoded = json_decode((string) $result['response'], true);

        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Respons Fonnte tidak valid.'];
        }

        if (empty($decoded['status'])) {
            return ['success' => false, 'message' => $decoded['reason'] ?? 'Fonnte menolak permintaan (periksa token / status device).'];
        }

        $registered = is_array($decoded['registered'] ?? null) ? $decoded['registered'] : [];
        $notRegistered = is_array($decoded['not_registered'] ?? null) ? $decoded['not_registered'] : [];

        return [
            'success' => true,
            'is_registered' => !empty($registered),
            'registered' => $registered,
            'not_registered' => $notRegistered,
        ];
    }


    public function checkWaNumber($id)
    {
        try {
            $id = (int) $id;
            if ($id <= 0) return $this->jsonError('ID data tidak valid.');

            $data = $this->travelModel->find($id);
            if (!$data) return $this->jsonError('Data tidak ditemukan.');

            $nomor = trim((string) ($data['nomor'] ?? ''));

            if ($nomor === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data ini tidak memiliki nomor HP.',
                ]);
            }

            $check = $this->fonnteValidateNumber($nomor);

            if (empty($check['success'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $check['message'] ?? 'Gagal memeriksa status WhatsApp.',
                ]);
            }

            $status = $check['is_registered'] ? 'active' : 'inactive';
            $checkedAt = date('Y-m-d H:i:s');

            $this->travelModel->update($id, [
                'wa_status' => $status,
                'wa_checked_at' => $checkedAt,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'wa_status' => $status,
                'wa_checked_at' => $checkedAt,
                'message' => $status === 'active' ? 'Nomor terdaftar/aktif di WhatsApp.' : 'Nomor tidak terdaftar di WhatsApp.',
                'csrf' => $this->getCsrf(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'CHECK WA NUMBER ERROR: ' . $e->getMessage());
            return $this->jsonError('Gagal memeriksa status WhatsApp. ' . $e->getMessage());
        }
    }


    public function checkWaNumberBulk()
    {
        try {
            $ids = $this->request->getPost('ids');

            if (!is_array($ids) || empty($ids)) {
                return $this->jsonError('Tidak ada data yang dipilih untuk dicek.');
            }

            $results = [];

            foreach ($ids as $rawId) {
                $itemId = (int) $rawId;
                if ($itemId <= 0) continue;

                $data = $this->travelModel->find($itemId);

                if (!$data) {
                    $results[] = ['id' => $itemId, 'success' => false, 'message' => 'Data tidak ditemukan.'];
                    continue;
                }

                $nomor = trim((string) ($data['nomor'] ?? ''));

                if ($nomor === '') {
                    $results[] = ['id' => $itemId, 'success' => false, 'message' => 'Tidak ada nomor HP.'];
                    continue;
                }

                $check = $this->fonnteValidateNumber($nomor);

                if (empty($check['success'])) {
                    $results[] = ['id' => $itemId, 'success' => false, 'message' => $check['message'] ?? 'Gagal memeriksa status WhatsApp.'];
                    continue;
                }

                $status = $check['is_registered'] ? 'active' : 'inactive';
                $checkedAt = date('Y-m-d H:i:s');

                $this->travelModel->update($itemId, [
                    'wa_status' => $status,
                    'wa_checked_at' => $checkedAt,
                ]);

                $results[] = ['id' => $itemId, 'success' => true, 'wa_status' => $status, 'wa_checked_at' => $checkedAt];

                usleep(400000);
            }

            return $this->response->setJSON([
                'success' => true,
                'results' => $results,
                'csrf' => $this->getCsrf(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'CHECK WA NUMBER BULK ERROR: ' . $e->getMessage());
            return $this->jsonError('Gagal memeriksa status WhatsApp secara massal.');
        }
    }


    // ============================================================
    // HISTORY
    // ============================================================

    private function getSearchHistory()
    {
        return $this->travelModel
            ->select([
                'keyword',
                'COUNT(*) AS total_data',
                'MAX(created_at) AS terakhir'
            ])
            ->where('keyword IS NOT NULL')
            ->where('keyword !=', '')
            ->groupBy('keyword')
            ->orderBy('terakhir', 'DESC')
            ->findAll();
    }


    public function historyData()
    {
        try {
            $keyword = trim((string) $this->request->getGet('keyword'));

            if ($keyword === '') {
                return $this->jsonError('Keyword riwayat tidak ditemukan.');
            }

            $data = $this->travelModel
                ->where('keyword', $keyword)
                ->orderBy('id', 'DESC')
                ->findAll();

            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'success',
                'keyword' => $keyword,
                'data' => $data,
                'total' => count($data),
                'csrf' => $this->getCsrf(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'HISTORY DATA ERROR: ' . $e->getMessage());
            return $this->jsonError('Gagal mengambil data history.');
        }
    }


    // ============================================================
    // EXTRACT KEYWORD
    // ============================================================

    private function extractKeyword(string $input): string
    {
        $input = trim($input);

        if (!filter_var($input, FILTER_VALIDATE_URL)) {
            return $input;
        }

        $path = parse_url($input, PHP_URL_PATH);

        if ($path) {
            if (preg_match('#/maps/search/([^/]+)#', $path, $matches)) {
                return trim(urldecode($matches[1]));
            }
        }

        $query = parse_url($input, PHP_URL_QUERY);

        if ($query) {
            parse_str($query, $queryParams);
            if (isset($queryParams['q'])) {
                return trim((string) $queryParams['q']);
            }
        }

        return $input;
    }


    // ============================================================
    // HTTP POST FORM
    // ============================================================

    private function httpPostForm(string $url, array $postFields, array $headers = [], int $timeoutSeconds = 30): array
    {
        $curlReady = function_exists('curl_init') && function_exists('curl_setopt_array') && function_exists('curl_exec') && function_exists('curl_getinfo') && function_exists('curl_error') && function_exists('curl_close');

        if ($curlReady) {
            $ch = curl_init();

            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => $timeoutSeconds,
                    CURLOPT_CONNECTTIMEOUT => min(15, $timeoutSeconds),
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query($postFields),
                    CURLOPT_HTTPHEADER => $headers,
                ]);

                $response = curl_exec($ch);
                $error = curl_error($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                return ['response' => $response, 'http_code' => $httpCode, 'error' => $error];
            }
        }

        $allowUrlFopen = function_exists('ini_get') && filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);

        if (function_exists('file_get_contents') && $allowUrlFopen) {
            $headerString = implode("\r\n", $headers) . "\r\n" . 'Content-Type: application/x-www-form-urlencoded';
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => $headerString,
                    'content' => http_build_query($postFields),
                    'timeout' => $timeoutSeconds,
                    'ignore_errors' => true,
                ],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);

            $response = @file_get_contents($url, false, $context);
            $httpCode = 0;

            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $headerLine) {
                    if (preg_match('#^HTTP/\S+\s+(\d+)#', $headerLine, $matches)) {
                        $httpCode = (int) $matches[1];
                    }
                }
            }

            return ['response' => $response, 'http_code' => $httpCode, 'error' => $response === false ? 'Gagal mengirim data menggunakan file_get_contents.' : ''];
        }

        return ['response' => false, 'http_code' => 0, 'error' => 'Server tidak mendukung request HTTP keluar. Aktifkan cURL atau allow_url_fopen di hosting.'];
    }


    // ============================================================
    // HTTP GET JSON
    // ============================================================

    private function httpGetJson(string $url, int $timeoutSeconds = 60, string $acceptHeader = 'Accept: application/json'): array
    {
        $curlReady = function_exists('curl_init') && function_exists('curl_setopt_array') && function_exists('curl_exec') && function_exists('curl_getinfo') && function_exists('curl_error') && function_exists('curl_close');

        if ($curlReady) {
            $ch = curl_init();

            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => $timeoutSeconds,
                    CURLOPT_CONNECTTIMEOUT => min(15, $timeoutSeconds),
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTPHEADER => [$acceptHeader],
                ]);

                $response = curl_exec($ch);
                $error = curl_error($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                return ['response' => $response, 'http_code' => $httpCode, 'error' => $error];
            }
        }

        $allowUrlFopen = function_exists('ini_get') && filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);

        if (function_exists('file_get_contents') && $allowUrlFopen) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => $acceptHeader . "\r\n",
                    'timeout' => $timeoutSeconds,
                    'ignore_errors' => true,
                ],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);

            $response = @file_get_contents($url, false, $context);
            $httpCode = 0;

            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $headerLine) {
                    if (preg_match('#^HTTP/\S+\s+(\d+)#', $headerLine, $matches)) {
                        $httpCode = (int) $matches[1];
                    }
                }
            }

            return ['response' => $response, 'http_code' => $httpCode, 'error' => $response === false ? 'Gagal mengambil data menggunakan file_get_contents.' : ''];
        }

        return ['response' => false, 'http_code' => 0, 'error' => 'Server tidak mendukung request HTTP keluar. Aktifkan cURL atau allow_url_fopen di hosting.'];
    }


    // ============================================================
    // EXTRACT EMAIL
    // ============================================================

    private function extractEmailFromWebsite(string $website): string
    {
        try {
            $website = trim($website);
            if ($website === '') return '';

            if (!preg_match('#^https?://#i', $website)) {
                $website = 'https://' . $website;
            }

            if (!filter_var($website, FILTER_VALIDATE_URL)) return '';

            $result = $this->httpGetJson($website, 8, 'Accept: text/html');
            $html = $result['response'];

            if ($html === false || $html === '' || !is_string($html)) return '';

            $html = substr($html, 0, 300000);

            if (!preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $html, $matches)) return '';

            $ignoredPatterns = [
                'example.com',
                'yourdomain.com',
                'domain.com',
                'email.com',
                'sentry.io',
                'wixpress.com',
                'godaddy.com',
                'schema.org',
                'w3.org',
                'gstatic.com',
                'googleapis.com',
                'google.com',
                'fontawesome.com',
                '.png',
                '.jpg',
                '.jpeg',
                '.gif',
                '.svg',
                '.webp',
                'wix.com',
            ];

            foreach ($matches[0] as $candidate) {
                $candidateLower = strtolower($candidate);
                $isIgnored = false;

                foreach ($ignoredPatterns as $ignored) {
                    if (str_contains($candidateLower, $ignored)) {
                        $isIgnored = true;
                        break;
                    }
                }

                if (!$isIgnored) return $candidate;
            }

            return '';
        } catch (\Throwable $e) {
            log_message('error', 'EXTRACT EMAIL ERROR: ' . $e->getMessage());
            return '';
        }
    }


    // ============================================================
    // JSON ERROR
    // ============================================================

    private function jsonError(string $message)
    {
        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => $message,
            'csrf' => $this->getCsrf(),
        ]);
    }


    // ============================================================
    // CSRF
    // ============================================================

    private function getCsrf(): array
    {
        return [
            'name' => csrf_token(),
            'hash' => csrf_hash(),
        ];
    }

    public function importKeKontak()
    {
        // 1. Ambil semua data dari tabel travel
        $dataTravel = $this->travelModel->findAll();

        if (empty($dataTravel)) {
            return redirect()->to(site_url('scrapper'))->with('error', 'Tidak ada data scrapper untuk di-import.');
        }

        $db = \Config\Database::connect();
        $inserted = 0;
        $skipped = 0;

        foreach ($dataTravel as $row) {
            // 2. Cek duplikasi di tabel kontak_email (berdasarkan email atau no_hp)
            $exists = $db->table('kontak_email')
                ->groupStart()
                ->where('email', $row['email'])
                ->orWhere('no_hp', $row['nomor'])
                ->groupEnd()
                ->countAllResults();

            if ($exists > 0) {
                $skipped++;
                continue;
            }

            // 3. Petakan data dari tabel travel ke tabel kontak_email
            $dataKontak = [
                'nama_perusahaan' => $row['nama'],
                'email'           => $row['email'] ?? null,
                'no_hp'           => $row['nomor'] ?? null,
                'website'         => $row['website'] ?? null,
                'catatan'         => $row['alamat'] ?? null,
                'sub_categories'  => $row['keyword'] ?? null,
                'sumber_data'     => 'Google Maps',
                'dibuat_pada'     => date('Y-m-d H:i:s'),
                'diperbarui_pada' => date('Y-m-d H:i:s'),
            ];

            // 4. Simpan ke tabel kontak_email
            $db->table('kontak_email')->insert($dataKontak);
            $inserted++;
        }

        return redirect()->to(site_url('scrapper'))->with('success', "Berhasil memindahkan {$inserted} data ke Kontak Email. ({$skipped} data duplikat dilewati).");
    }
}
