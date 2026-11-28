<?php

namespace App\Libraries;

use Exception;

class AIService
{
    public const PROMPT_TEMPLATE = 'Kamu mengekstrak NAMA PRODUK dan MERK PRODUK dari sumber perusahaan untuk katalog pameran ekspor. Ambil SEBANYAK-BANYAKNYA seluruh produk fisik yang ada di website/katalog tanpa batas jumlah. Kamu hanya menyalin nama produk dan menerjemahkannya ke Bahasa Indonesia. Kamu tidak menulis deskripsi, tidak meringkas, dan tidak menjelaskan.

Aturan Wajib:
1. HANYA NAMA PRODUK FISIK yang benar-benar tercantum di teks sumber.
2. NAMA PRODUK WAJIB DIBUAT DALAM BAHASA INDONESIA, JANGAN BAHASA INGGRIS.
   - Kolom "name_id": Wajib NAMA PRODUK DALAM BAHASA INDONESIA yang jelas. Sertakan nama varian/model/aroma spesifik jika ada (contoh: "Ekstrak Parfum Velvet Rouge", "Ekstrak Parfum Biru Samudra", "Serum Tubuh Glukinamida", "Kondisioner Rambut Garam Laut", "Lulur Tubuh Kopi") agar seluruh varian produk yang ada di website tercatat lengkap satu per satu.
   - DILARANG KERAS menulis kalimat deskripsi, manfaat, fungsi, kalimat penjelas, bahan, atau spesifikasi (contoh salah / DILARANG: "Parfum mewah beraroma tahan lama untuk pria dan wanita", "Serum tubuh wangi yang membantu mencerahkan kulit"). Hasil HARUS MURNI HANYA NAMA PRODUK SAJA.
3. Dilarang kata promosi/deskripsi: premium, mewah, berkualitas, terbaik, tahan lama, pilihan, unggulan, untuk, yang, dengan, membantu, adalah, merupakan.
4. "brand": Merk produk yang menaungi jika tercantum di sumber (contoh: "Scarlett", atau kosongkan "" jika tidak ada).
5. "name_source": Nama persis yang tertulis di sumber (buang kata promo seperti [New Launch], [Promo], dll).
6. "name_en": Nama asli atau padanan bahasa Inggris singkat (sertakan varian jika ada).
7. Bukan produk: judul kelompok (Face, Body, Hair, Bundling, All Categories, dll.), nama perusahaan saja, slogan, kategori umum ("Skincare and Personal Care Products").
8. AMBIL SEBANYAK-BANYAKNYA seluruh produk fisik yang ditemukan di sumber, tanpa batas jumlah produk. Samakan sebanyak mungkin dengan katalog produk di website perusahaan.

Jawab HANYA format JSON valid tanpa pembuka atau penutup:
{"products":[{"name_source":"...","brand":"...","name_id":"...","name_en":"...","source":"website|catalog|profile","evidence":"..."}]}

Nama perusahaan: {nama}
Kategori: {kategori} / {sub}

Potongan sumber ({nomor_potongan} dari {total_potongan}):
{potongan}';

    protected string $apiKey = '';
    protected string $model = 'gemini-3.5-flash-lite';
    protected float $temperature = 0.0;
    protected int $maxTokens = 8192;
    protected string $customPrompt = '';

    public function __construct()
    {
        // 1. Baca API Key HANYA dari file .env (gemini.apiKey)
        $this->apiKey = $this->readEnvValue('gemini.apiKey');
        $this->model  = $this->readEnvValue('gemini.model') ?: 'gemini-3.5-flash-lite';

        // 2. Baca konfigurasi model & prompt dari database pengaturan_ai jika ada
        try {
            if (class_exists(\App\Models\PengaturanAiModel::class)) {
                $cfgModel = new \App\Models\PengaturanAiModel();
                $config = $cfgModel->getActiveConfig();
                if ($config) {
                    if (!empty($config['nama_model'])) {
                        $this->model = trim($config['nama_model']);
                    }
                    if (isset($config['temperature'])) {
                        $this->temperature = (float) $config['temperature'];
                    }
                    if (!empty($config['max_tokens'])) {
                        $this->maxTokens = (int) $config['max_tokens'];
                    }
                    if (!empty($config['instruksi_ai'])) {
                        $this->customPrompt = trim($config['instruksi_ai']);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Gunakan default jika database tidak dapat diakses
        }
    }

    /**
     * Membaca nilai dari .env dengan fallback bertingkat
     */
    protected function readEnvValue(string $key): string
    {
        if (function_exists('env')) {
            $val = env($key);
            if (!empty($val)) {
                return trim((string)$val);
            }
        }

        $val = getenv($key);
        if (!empty($val)) {
            return trim((string)$val);
        }

        if (!empty($_ENV[$key])) {
            return trim((string)$_ENV[$key]);
        }
        if (!empty($_SERVER[$key])) {
            return trim((string)$_SERVER[$key]);
        }

        $rootPath = defined('ROOTPATH') ? ROOTPATH : (dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
        $envFile = $rootPath . '.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0) continue;
                if (preg_match('/^' . preg_quote($key, '/') . '\s*=\s*(.*)$/', $line, $matches)) {
                    return trim($matches[1], " \t\n\r\0\x0B\"'");
                }
            }
        }

        return '';
    }

    /**
     * Memeriksa apakah API Key Gemini valid dan terisi di .env
     */
    public function hasApiKey(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'ISI_KEY_GEMINI';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Menguji koneksi ke Google Gemini API
     */
    public function testConnection(): array
    {
        if (!$this->hasApiKey()) {
            return [
                'success' => false,
                'message' => 'API Key Gemini belum diisi di file .env. Silakan isi gemini.apiKey di file .env.',
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Ping']
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0
            ]
        ];

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'x-goog-api-key: ' . $this->apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 8,
            ]);

            $rawBody  = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($rawBody === false || !empty($curlErr)) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghubungi Gemini API: ' . $curlErr,
                ];
            }

            $body = json_decode($rawBody, true);

            if ($httpCode === 429) {
                return [
                    'success' => false,
                    'message' => 'Kuota Gemini API habis (HTTP 429). Silakan coba beberapa saat lagi.',
                ];
            }

            if ($httpCode !== 200 || !isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                $err = $body['error']['message'] ?? ('HTTP Error ' . $httpCode);
                return [
                    'success' => false,
                    'message' => 'Gemini API Error: ' . $err,
                ];
            }

            return [
                'success' => true,
                'message' => 'Koneksi ke Google Gemini berhasil! Model aktif: ' . $this->model,
                'model'   => $this->model,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Pengecualian: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengirim prompt teks ke Google Gemini API dengan konfigurasi JSON mode
     *
     * @param string $prompt
     * @param bool $isJson
     * @return array [success => bool, is_quota_exceeded => bool, text => string, raw => string, error => ?string]
     */
    public function sendGeminiPrompt(string $prompt, bool $isJson = true): array
    {
        if (!$this->hasApiKey()) {
            return [
                'success'           => false,
                'is_quota_exceeded' => false,
                'error'             => 'API Key Gemini belum diisi di file .env. Silakan isi gemini.apiKey di file .env.',
                'raw'               => '',
                'text'              => '',
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $genConfig = [
            'temperature'     => $this->temperature,
            'maxOutputTokens' => $this->maxTokens,
        ];

        if ($isJson) {
            $genConfig['responseMimeType'] = 'application/json';
        }

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => $genConfig
        ];

        try {
            $maxRetries = 2;
            $attempt    = 0;
            $rawBody    = '';
            $httpCode   = 0;
            $curlErr    = '';

            while ($attempt <= $maxRetries) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_HTTPHEADER     => [
                        'x-goog-api-key: ' . $this->apiKey,
                        'Content-Type: application/json',
                    ],
                    CURLOPT_POSTFIELDS     => json_encode($payload),
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT        => 45,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ]);

                $rawBody  = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr  = curl_error($ch);
                curl_close($ch);

                // Jika model overload (503/500), jeda 2 detik dan coba lagi
                if (in_array($httpCode, [500, 502, 503, 504], true) && $attempt < $maxRetries) {
                    $attempt++;
                    sleep(2);
                    continue;
                }
                break;
            }

            if ($rawBody === false || !empty($curlErr)) {
                return [
                    'success'           => false,
                    'is_quota_exceeded' => false,
                    'error'             => 'Koneksi ke Gemini terputus: ' . $curlErr,
                    'raw'               => '',
                    'text'              => '',
                ];
            }

            if ($httpCode === 429) {
                return [
                    'success'           => false,
                    'is_quota_exceeded' => true,
                    'error'             => 'Kuota AI habis, coba lagi nanti',
                    'raw'               => $rawBody,
                    'text'              => '',
                ];
            }

            $body = json_decode($rawBody, true);

            if ($httpCode !== 200 || !isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                $err = $body['error']['message'] ?? ('HTTP Error ' . $httpCode . ': ' . $rawBody);
                return [
                    'success'           => false,
                    'is_quota_exceeded' => false,
                    'error'             => 'Gemini: ' . $err,
                    'raw'               => $rawBody,
                    'text'              => '',
                ];
            }

            $responseText = trim($body['candidates'][0]['content']['parts'][0]['text']);
            // Bersihkan markdown code fences jika ada
            $cleanText = preg_replace('/^```(?:json|text)?\s*/i', '', $responseText);
            $cleanText = preg_replace('/\s*```$/', '', $cleanText);
            $cleanText = trim($cleanText);

            return [
                'success'           => true,
                'is_quota_exceeded' => false,
                'text'              => $cleanText,
                'raw'               => $responseText,
                'error'             => null,
            ];
        } catch (Exception $e) {
            return [
                'success'           => false,
                'is_quota_exceeded' => false,
                'error'             => 'Kesalahan eksekusi Gemini: ' . $e->getMessage(),
                'raw'               => '',
                'text'              => '',
            ];
        }
    }

    /**
     * Membagi teks panjang menjadi potongan-potongan teratur (chunking) tanpa memutus kalimat/baris
     */
    public function splitIntoChunks(string $text, int $maxChunkSize = 12000): array
    {
        $text = trim($text);
        if (mb_strlen($text) <= $maxChunkSize) {
            return [$text];
        }

        $chunks = [];
        $paragraphs = preg_split('/(\r\n|\n){2,}/u', $text);
        $currentChunk = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) continue;

            if (mb_strlen($para) > $maxChunkSize) {
                $lines = preg_split('/\r\n|\n/u', $para);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    if (mb_strlen($line) > $maxChunkSize) {
                        $parts = mb_str_split($line, $maxChunkSize);
                        foreach ($parts as $p) {
                            if (mb_strlen($currentChunk) + mb_strlen($p) + 1 > $maxChunkSize) {
                                if (!empty(trim($currentChunk))) {
                                    $chunks[] = trim($currentChunk);
                                }
                                $currentChunk = $p;
                            } else {
                                $currentChunk .= (empty($currentChunk) ? '' : "\n") . $p;
                            }
                        }
                    } else {
                        if (mb_strlen($currentChunk) + mb_strlen($line) + 1 > $maxChunkSize) {
                            if (!empty(trim($currentChunk))) {
                                $chunks[] = trim($currentChunk);
                            }
                            $currentChunk = $line;
                        } else {
                            $currentChunk .= (empty($currentChunk) ? '' : "\n") . $line;
                        }
                    }
                }
            } else {
                if (mb_strlen($currentChunk) + mb_strlen($para) + 2 > $maxChunkSize) {
                    if (!empty(trim($currentChunk))) {
                        $chunks[] = trim($currentChunk);
                    }
                    $currentChunk = $para;
                } else {
                    $currentChunk .= (empty($currentChunk) ? '' : "\n\n") . $para;
                }
            }
        }

        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }

        return !empty($chunks) ? $chunks : [$text];
    }

    /**
     * Menganalisis produk perusahaan menggunakan Google Gemini API
     * Output murni nama produk terstruktur (JSON) dengan validasi ketat sesuai bukti sumber.
     *
     * @param array $companyData
     * @return array
     */
    public function analyzeCompanyProducts(array $companyData): array
    {
        if (!$this->hasApiKey()) {
            return [
                'success'           => false,
                'is_quota_exceeded' => false,
                'error'             => 'API Key Gemini belum diisi di file .env. Silakan isi gemini.apiKey di file .env.',
            ];
        }

        $namaPerusahaan = $companyData['nama_perusahaan'] ?? '-';
        $mainCat        = $companyData['main_categories'] ?: '-';
        $subCat         = $companyData['sub_categories'] ?: '-';
        $profile        = !empty($companyData['company_profile']) ? $companyData['company_profile'] : '';
        $catText        = !empty($companyData['catalog_text']) ? $companyData['catalog_text'] : '';
        $webText        = !empty($companyData['website_text']) ? $companyData['website_text'] : '';
        $kandidatText   = !empty($companyData['candidates_text']) ? $companyData['candidates_text'] : '';

        // Gabungkan teks sumber dalam bagian-bagian terstruktur tanpa batasan karakter buatan
        $sourceSections = [];
        if (!empty($kandidatText) && $kandidatText !== '(Menu produk tidak terbaca atau tidak ditemukan kandidat khusus)') {
            $sourceSections[] = "=== DAFTAR KANDIDAT DARI MENU & STRUKTUR WEBSITE ===\n" . $kandidatText;
        }
        if (!empty($webText) && $webText !== '(Tidak ada teks website)') {
            $sourceSections[] = "=== TEKS DARI HALAMAN WEBSITE ===\n" . $webText;
        }
        if (!empty($catText) && $catText !== '(Tidak ada teks katalog)') {
            $sourceSections[] = "=== TEKS DARI KATALOG PERUSAHAAN ===\n" . $catText;
        }
        if (!empty($profile) && $profile !== '(Tidak ada profil)') {
            $sourceSections[] = "=== PROFIL PERUSAHAAN ===\n" . $profile;
        }

        $fullSource = implode("\n\n", $sourceSections);
        if (empty(trim($fullSource))) {
            return [
                'success'                => true,
                'is_no_product'          => true,
                'is_quota_exceeded'      => false,
                'is_perlu_diperiksa'     => true,
                'perlu_diperiksa_reason' => 'Tidak ada teks sumber yang dapat diproses.',
                'formatted_text'         => '',
                'products'               => [],
                'products_en'            => [],
                'products_id'            => [],
                'discarded_count'        => 0,
                'discarded_reasons'      => [],
                'raw'                    => '[]',
            ];
        }

        // PERUBAHAN 3: Chunking ~12.000 karakter per potongan
        $chunks = $this->splitIntoChunks($fullSource, 12000);
        $totalChunks = count($chunks);

        $template = !empty($this->customPrompt) ? $this->customPrompt : self::PROMPT_TEMPLATE;

        $allRawProducts = [];
        $rawResponses   = [];
        $quotaExceeded  = false;
        $lastError      = null;

        foreach ($chunks as $idx => $chunkContent) {
            $chunkIndex = $idx + 1;

            // Beri jeda 2 detik antar potongan jika ada lebih dari 1 potongan
            if ($idx > 0) {
                sleep(2);
            }

            // Susun prompt untuk potongan ini
            if (strpos($template, '{potongan}') !== false) {
                $prompt = str_replace(
                    ['{nama}', '{kategori}', '{sub}', '{nomor_potongan}', '{total_potongan}', '{potongan}'],
                    [$namaPerusahaan, $mainCat, $subCat, (string)$chunkIndex, (string)$totalChunks, $chunkContent],
                    $template
                );
            } else {
                // Fallback jika masih menggunakan placeholder versi lama
                $prompt = str_replace(
                    ['{nama}', '{kategori}', '{sub}', '{kandidat}', '{website}', '{katalog}', '{profil}'],
                    [$namaPerusahaan, $mainCat, $subCat, $kandidatText, $chunkContent, '', ''],
                    $template
                );
            }

            $apiResult = $this->sendGeminiPrompt($prompt, true);

            // Tangani HTTP 429 kuota habis: jangan hapus produk yang sudah didapat
            if (!empty($apiResult['is_quota_exceeded'])) {
                $quotaExceeded = true;
                $lastError     = $apiResult['error'];
                break;
            }

            // Jika respons gagal atau JSON tidak valid, coba sekali lagi untuk format JSON
            $cleanText = $apiResult['text'] ?? '';
            $parsed = json_decode($cleanText, true);

            if (!is_array($parsed) || !isset($parsed['products'])) {
                // Percobaan ulang 1 kali untuk format JSON
                $retryPrompt = $prompt . "\n\nCATATAN PENTING: Jawaban sebelumnya bukan format JSON yang valid. Balas HANYA dengan format JSON valid persis seperti: {\"products\":[{\"name_source\":\"...\",\"brand\":\"...\",\"name_en\":\"...\",\"name_id\":\"...\",\"source\":\"website|catalog|profile\",\"evidence\":\"...\"}]}";
                $retryRes = $this->sendGeminiPrompt($retryPrompt, true);
                if ($retryRes['success'] && !empty($retryRes['text'])) {
                    $cleanText = $retryRes['text'];
                    $parsed = json_decode($cleanText, true);
                }
            }

            if (is_array($parsed) && isset($parsed['products']) && is_array($parsed['products'])) {
                foreach ($parsed['products'] as $p) {
                    $allRawProducts[] = $p;
                }
                $rawResponses[] = [
                    'chunk'    => "{$chunkIndex}/{$totalChunks}",
                    'products' => $parsed['products'],
                ];
            } else {
                $rawResponses[] = [
                    'chunk' => "{$chunkIndex}/{$totalChunks}",
                    'error' => $apiResult['error'] ?? 'Gagal mem-parse JSON hasil AI pada potongan ini.',
                ];
            }
        }

        // Jika kuota habis dan belum sempat mendapatkan produk apa pun
        if ($quotaExceeded && empty($allRawProducts)) {
            return [
                'success'           => false,
                'is_quota_exceeded' => true,
                'error'             => 'Kuota AI habis, coba lagi nanti',
                'raw'               => json_encode($rawResponses),
            ];
        }

        // PERUBAHAN 5: Validasi dan pemfilteran ketat produk di kode PHP
        $filterResult = $this->validateAndFilterProducts($allRawProducts, $catText, $webText, $profile, $companyData);

        $validProducts       = $filterResult['products'];
        $productsEn          = $filterResult['products_en'];
        $productsId          = $filterResult['products_id'];
        $isPerluDiperiksa    = $filterResult['is_perlu_diperiksa'];
        $perluDiperiksaNote  = $filterResult['perlu_diperiksa_reason'];
        $discardedCount      = $filterResult['discarded_count'];
        $discardedReasons    = $filterResult['discarded_reasons'];

        // Format bilingual standar sistem: "Nomor. Nama English – Nama Indonesia – Merk" (tanpa deskripsi, tanpa titik di akhir)
        $formattedText = $this->formatToBilingualText($validProducts);

        // Simpan JSON mentah lengkap (termasuk source, brand, evidence, dan info validasi)
        $rawJsonData = json_encode([
            'products'          => $validProducts,
            'discarded_count'   => $discardedCount,
            'discarded_reasons' => $discardedReasons,
            'is_perlu_diperiksa'=> $isPerluDiperiksa,
            'note'              => $perluDiperiksaNote,
            'chunks_processed'  => $totalChunks,
            'quota_hit'         => $quotaExceeded,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return [
            'success'                => true,
            'is_no_product'          => empty($validProducts),
            'is_quota_exceeded'      => $quotaExceeded && empty($validProducts),
            'is_perlu_diperiksa'     => $isPerluDiperiksa,
            'perlu_diperiksa_reason' => $perluDiperiksaNote,
            'formatted_text'         => $formattedText,
            'products'               => $validProducts,
            'products_en'            => $productsEn,
            'products_id'            => $productsId,
            'discarded_count'        => $discardedCount,
            'discarded_reasons'      => $discardedReasons,
            'raw'                    => $rawJsonData,
        ];
    }

    /**
     * Memvalidasi dan menyaring produk sesuai ketentuan PERUBAHAN 5:
     * - Discard jika name_source atau evidence tidak ditemukan di teks sumber/kandidat.
     * - Jika brand tidak ditemukan di teks sumber, kosongkan brand (jangan buang produk).
     * - Discard jika nama > 8 kata.
     * - Discard jika nama mengandung tanda baca kalimat (titik di tengah kalimat, koma berantai) atau kata promosi/deskripsi.
     * - Discard jika entri sama dengan nama kelompok/kategori (Face, Body, Hair, Bundling, dll.).
     * - Hapus duplikat berdasarkan name_en + '|' + brand (case-insensitive).
     * - Tanpa batas jumlah produk.
     * - Beri status "Perlu diperiksa" jika 0 produk atau hasil hanya dari profil saat website/katalog gagal.
     */
    protected function validateAndFilterProducts(array $rawProducts, string $catText, string $webText, string $profileText, array $companyData): array
    {
        $candidates = (array) ($companyData['candidates'] ?? []);
        $groupCategories = (array) ($companyData['group_categories'] ?? []);
        $kandidatText = (string) ($companyData['candidates_text'] ?? '');

        // Normalisasi kandidat ke array string murni
        $candStrings = [];
        foreach ($candidates as $cand) {
            if (is_array($cand)) {
                if (!empty($cand['name'])) $candStrings[] = (string) $cand['name'];
            } elseif (is_string($cand) && !empty($cand)) {
                $candStrings[] = $cand;
            }
        }

        // Gabungkan seluruh teks sumber asli dan normalkan untuk pencocokan (lowercase dan spasi tunggal)
        $rawSourcePool = $kandidatText . "\n" . $catText . "\n" . $webText . "\n" . $profileText . "\n" . implode("\n", $candStrings);
        $normSourcePool = preg_replace('/\s+/u', ' ', mb_strtolower($rawSourcePool, 'UTF-8'));

        $normCatText = preg_replace('/\s+/u', ' ', mb_strtolower($catText, 'UTF-8'));
        $normWebText = preg_replace('/\s+/u', ' ', mb_strtolower($webText, 'UTF-8'));
        $normProfileText = preg_replace('/\s+/u', ' ', mb_strtolower($profileText, 'UTF-8'));

        // Kata terlarang untuk deskripsi / promosi (PERUBAHAN 4 & 5)
        $forbiddenPattern = '/\b(?:premium|mewah|berkualitas|terbaik|tahan\s+lama|pilihan|unggulan|untuk|yang|dengan|membantu|adalah|merupakan|aroma|beraroma|digunakan|dijadikan|sebagai|penambah|bertekstur|berformulasi|memperbaiki|memperkuat|melindungi|berbahan|cita\s*rasa|serpihan|lembaran)\b/iu';

        // Daftar nama kelompok/kategori (BUKAN produk)
        $knownGroups = [
            'face', 'body', 'parfum', 'perfume', 'fragrance', 'hair', 'bundling', 'bundle', 'packages', 'paket',
            'skincare', 'skin care', 'bodycare', 'body care', 'haircare', 'hair care',
            'all', 'shop all', 'all categories', 'categories', 'kategori', 'semua',
            'men', 'women', 'pria', 'wanita', 'berbagai produk', 'skincare and personal care products',
            'personal care', 'beauty', 'cosmetics', 'kosmetik',
        ];
        foreach ($groupCategories as $gc) {
            $normGc = trim(mb_strtolower($gc, 'UTF-8'));
            if (!empty($normGc) && !in_array($normGc, $knownGroups, true)) {
                $knownGroups[] = $normGc;
            }
        }

        $validProducts = [];
        $productsEn = [];
        $productsId = [];
        $seenKeys = [];
        $discarded = [];

        foreach ($rawProducts as $idx => $p) {
            $nameSource = rtrim(trim($p['name_source'] ?? $p['en'] ?? $p['id'] ?? ''), '.');
            $brand      = rtrim(trim($p['brand'] ?? ''), '.');
            $en         = rtrim(trim($p['name_en'] ?? $p['en'] ?? ''), '.');
            $id         = rtrim(trim($p['name_id'] ?? $p['id'] ?? ''), '.');
            $evidence   = trim($p['evidence'] ?? '');
            $sourceRaw  = strtolower(trim($p['source'] ?? 'website'));

            // Standarisasi nama sumber: katalog, website, atau profil
            if ($sourceRaw === 'catalog') $source = 'katalog';
            elseif ($sourceRaw === 'profile') $source = 'profil';
            else $source = 'website';

            // 1. Buang jika seluruh nama kosong
            if (empty($nameSource) && empty($en) && empty($id)) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => '-',
                    'reason' => 'Nama produk kosong.',
                ];
                continue;
            }

            // Bersihkan awalan promosi e-commerce seperti [New Launch], [New], New!, dll
            $nameSource = preg_replace('/^(?:\[[^\]]+\]|new!|hot!|promo!)\s*/iu', '', $nameSource);
            $en         = preg_replace('/^(?:\[[^\]]+\]|new!|hot!|promo!)\s*/iu', '', $en);
            $id         = preg_replace('/^(?:\[[^\]]+\]|new!|hot!|promo!)\s*/iu', '', $id);

            if (empty($nameSource)) $nameSource = $en ?: $id;
            if (empty($en)) $en = $nameSource ?: $id;
            if (empty($id)) $id = $nameSource ?: $en;

            $normNameSource = preg_replace('/\s+/u', ' ', mb_strtolower($nameSource, 'UTF-8'));
            $normEn         = preg_replace('/\s+/u', ' ', mb_strtolower($en, 'UTF-8'));
            $normId         = preg_replace('/\s+/u', ' ', mb_strtolower($id, 'UTF-8'));
            $normEvidence   = preg_replace('/\s+/u', ' ', mb_strtolower($evidence, 'UTF-8'));

            // 2. Buang entri yang sama dengan nama kelompok/kategori (Face, Body, Parfum, Hair, Bundling, dst)
            if (in_array($normNameSource, $knownGroups, true) || in_array($normEn, $knownGroups, true) || in_array($normId, $knownGroups, true)) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => $nameSource,
                    'reason' => "Entri merupakan nama kelompok/kategori ({$nameSource}), bukan produk fisik.",
                ];
                continue;
            }

            // 3. Buang produk jika panjang nama produk hasil melebihi 8 kata (kalimat panjang)
            $wordsEn     = preg_split('/\s+/u', $en, -1, PREG_SPLIT_NO_EMPTY);
            $wordsId     = preg_split('/\s+/u', $id, -1, PREG_SPLIT_NO_EMPTY);
            if (count($wordsEn) > 8 || count($wordsId) > 8) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => $nameSource,
                    'reason' => "Panjang nama produk melebihi batas 8 kata (EN: " . count($wordsEn) . " kata, ID: " . count($wordsId) . " kata).",
                ];
                continue;
            }

            // 4. Buang jika mengandung tanda baca kalimat lengkap (titik di tengah kalimat atau koma berantai)
            if (preg_match('/[.!?]\s+[A-Za-z0-9]/u', $id) || substr_count($id, ',') > 1) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => $nameSource,
                    'reason' => "Nama produk mengandung tanda baca kalimat atau kalimat deskripsi.",
                ];
                continue;
            }

            // 5. Buang produk jika nama produk mengandung kata terlarang (deskripsi/promosi: untuk, yang, dengan, membantu, premium, mewah, dll.)
            if (preg_match($forbiddenPattern, $id) || preg_match($forbiddenPattern, $en)) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => $nameSource,
                    'reason' => "Nama produk ('{$id}') mengandung kata promosi / kalimat deskripsi terlarang.",
                ];
                continue;
            }

            // 6. Validasi bukti teks sumber: name_source atau name_en harus ada di sumber pool atau kandidat
            $foundInSource = false;
            if (mb_stripos($normSourcePool, $normNameSource) !== false || mb_stripos($normSourcePool, $normEn) !== false) {
                $foundInSource = true;
            } else {
                foreach ($candStrings as $candStr) {
                    $normCand = preg_replace('/\s+/u', ' ', mb_strtolower($candStr, 'UTF-8'));
                    if (mb_stripos($normCand, $normNameSource) !== false || mb_stripos($normNameSource, $normCand) !== false) {
                        $foundInSource = true;
                        break;
                    }
                }
            }

            if (!$foundInSource) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => $nameSource,
                    'reason' => "Nama produk ({$nameSource}) tidak ditemukan di daftar kandidat maupun teks sumber.",
                ];
                continue;
            }

            // 7. Buang produk jika "evidence" terisi tapi tidak ditemukan di teks sumber
            if (!empty($normEvidence) && mb_stripos($normSourcePool, $normEvidence) === false) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => $nameSource,
                    'reason' => "Kutipan 'evidence' ({$evidence}) tidak ditemukan di teks sumber.",
                ];
                continue;
            }

            // 8. Validasi MERK PRODUK: jika brand tidak ditemukan di teks sumber, kosongkan brand (jangan buang produk)
            if (!empty($brand)) {
                $normBrand = preg_replace('/\s+/u', ' ', mb_strtolower($brand, 'UTF-8'));
                if (mb_stripos($normSourcePool, $normBrand) === false) {
                    // Merk tidak terverifikasi di teks sumber, kosongkan saja
                    $brand = '';
                }
            }

            // 9. Hapus duplikat berdasarkan name_en + '|' + brand (case-insensitive)
            $dupeKey = $normEn . '|' . mb_strtolower($brand, 'UTF-8');
            if (isset($seenKeys[$dupeKey])) {
                $discarded[] = [
                    'item'   => $idx + 1,
                    'name'   => $nameSource,
                    'reason' => "Duplikat dari produk yang sudah terdaftar sebelumnya.",
                ];
                continue;
            }
            $seenKeys[$dupeKey] = true;

            // 10. Deteksi asal sumber spesifik
            if (!empty($normEvidence)) {
                if (mb_stripos($normCatText, $normEvidence) !== false) {
                    $source = 'katalog';
                } elseif (mb_stripos($normWebText, $normEvidence) !== false) {
                    $source = 'website';
                } elseif (mb_stripos($normProfileText, $normEvidence) !== false) {
                    $source = 'profil';
                }
            }

            $productEntry = [
                'nomor'       => count($validProducts) + 1,
                'name_source' => $nameSource,
                'brand'       => $brand,
                'en'          => $en,
                'id'          => $id,
                'source'      => $source,
                'evidence'    => $evidence,
            ];

            $validProducts[] = $productEntry;
            $productsEn[]    = $en;
            $productsId[]    = $id;
        }

        // 11. Evaluasi status "Perlu diperiksa"
        $isPerluDiperiksa = false;
        $perluDiperiksaReason = null;

        if (empty($validProducts)) {
            $isPerluDiperiksa = true;
            if (!empty($rawProducts)) {
                $perluDiperiksaReason = 'Semua kandidat produk (' . count($rawProducts) . ' item) dibuang karena tidak memenuhi kriteria bukti sumber atau batasan nama.';
            } else {
                $perluDiperiksaReason = 'AI tidak menemukan produk fisik yang jelas tertulis pada sumber informasi yang diberikan.';
            }
        } else {
            // Cek kondisi: semua produk hanya dari profil sementara website dan katalog gagal dibaca
            $webTextRaw = trim($companyData['website_text'] ?? '');
            $catTextRaw = trim($companyData['catalog_text'] ?? '');
            $webFailedOrEmpty = empty($webTextRaw) || $webTextRaw === '(Tidak ada teks website)' || !empty($companyData['website_failed']);
            $catFailedOrEmpty = empty($catTextRaw) || $catTextRaw === '(Tidak ada teks katalog)' || !empty($companyData['catalog_failed']);

            if ($webFailedOrEmpty && $catFailedOrEmpty) {
                $allFromProfile = true;
                foreach ($validProducts as $vp) {
                    if ($vp['source'] !== 'profil') {
                        $allFromProfile = false;
                        break;
                    }
                }

                if ($allFromProfile) {
                    $isPerluDiperiksa = true;
                    $perluDiperiksaReason = 'Seluruh produk hanya bersumber dari Profil Perusahaan sementara Website dan Katalog gagal dibaca atau tidak tersedia.';
                }
            }
        }

        return [
            'products'               => $validProducts,
            'products_en'            => $productsEn,
            'products_id'            => $productsId,
            'is_perlu_diperiksa'     => $isPerluDiperiksa,
            'perlu_diperiksa_reason' => $perluDiperiksaReason,
            'discarded_count'        => count($discarded),
            'discarded_reasons'      => $discarded,
        ];
    }

    /**
     * Memeriksa apakah suatu teks adalah kalimat deskripsi (bukan nama produk murni)
     */
    public static function isDescription(string $text): bool
    {
        $text = trim($text);
        if ($text === '') return false;
        // Kalimat deskripsi biasanya diakhiri tanda titik/seru/tanya dan cukup panjang
        if (preg_match('/[.!?]$/u', $text) && mb_strlen($text) > 25) return true;
        // Frasa khas kalimat penjelasan atau deskripsi fungsional
        if (preg_match('/\b(yang\s+(?:biasa\s+)?(?:digunakan|dijadikan|membantu|berfungsi|dibungkus)|berbahan\s+dasar|untuk\s+(?:membuat|penambah|mencerahkan\s+wajah|pria\s+dan\s+wanita)|beraroma\s+tahan\s+lama|cita\s*rasa\s+gurih|makanan\s+ringan\s+berbahan|serpihan\s+rumput\s+laut|lembaran\s+rumput\s+laut|adalah\s+(?:sebuah|produk|salah\s+satu)|diformulasikan\s+untuk|cocok\s+untuk|berkhasiat\s+untuk|aroma\s+tahan\s+lama)\b/iu', $text)) {
            return true;
        }
        // Kalimat narasi panjang yang bukan judul produk
        if (mb_strlen($text) > 75 && count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY)) > 8) {
            return true;
        }
        return false;
    }

    /**
     * Memeriksa apakah teks adalah nama merk/brand yang valid (singkat, padat, bukan deskripsi)
     */
    public static function isValidBrand(string $brand): bool
    {
        $brand = trim($brand);
        if ($brand === '') return false;
        if (mb_strlen($brand) > 25) return false;
        if (count(preg_split('/\s+/u', $brand, -1, PREG_SPLIT_NO_EMPTY)) > 3) return false;
        if (preg_match('/[.!?]$/u', $brand)) return false;
        if (self::isDescription($brand)) return false;
        return true;
    }

    /**
     * Mengubah array produk ke format teks standar sistem:
     * "Nomor. Nama Produk (Bahasa Indonesia) – Merk" atau "Nomor. Nama Produk (Bahasa Indonesia)"
     * Nama produk dibuat dalam Bahasa Indonesia (bukan bahasa Inggris) dan tanpa deskripsi.
     */
    public function formatToBilingualText(array $products): string
    {
        $lines = [];
        $no = 1;
        foreach ($products as $p) {
            $id    = rtrim(trim($p['id'] ?? $p['nama_produk_id'] ?? ''), '.');
            $en    = rtrim(trim($p['en'] ?? $p['nama_produk_en'] ?? ''), '.');
            $brand = rtrim(trim($p['brand'] ?? $p['merk'] ?? ''), '.');

            // NAMA PRODUK WAJIB DIBUAT DALAM BAHASA INDONESIA, JANGAN BAHASA INGGRIS.
            // Jika $id adalah kalimat deskripsi (data lama), gunakan $en jika tidak berupa deskripsi
            if (self::isDescription($id) && !self::isDescription($en) && !empty($en)) {
                $namaProduk = $en;
            } else {
                $namaProduk = $id ?: $en;
            }

            // Pastikan nama produk bukan kalimat deskripsi
            if (self::isDescription($namaProduk)) {
                $parts = preg_split('/\s+[–—\-]\s+/u', $namaProduk);
                if (!empty($parts[0]) && !self::isDescription($parts[0])) {
                    $namaProduk = trim($parts[0]);
                }
            }

            // Validasi brand: jika berupa deskripsi, kosongkan
            if (!self::isValidBrand($brand)) {
                $brand = '';
            }

            if (!empty($namaProduk)) {
                $line = "{$no}. {$namaProduk}";
                if (!empty($brand)) {
                    $line .= " – {$brand}";
                }
                $lines[] = $line;
                $no++;
            }
        }
        return implode("\n", $lines);
    }

    /**
     * Mem-parse teks produk baris demi baris menjadi array produk
     * Format: "Nomor. Nama Produk (Bahasa Indonesia) – Merk" atau "Nomor. Nama Produk (Bahasa Indonesia)"
     */
    public function parseProductLines(string $text): array
    {
        $lines = explode("\n", $text);
        $products = [];
        $no = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Pisahkan nomor urut di awal: "1. " atau "1) "
            if (preg_match('/^\d+[\.\)]\s*(.*?)$/u', $line, $mNum)) {
                $line = trim($mNum[1]);
            }

            // Pisahkan dengan tanda hubung dash/en-dash
            $parts = preg_split('/\s+[–—\-]\s+/u', $line);
            if (count($parts) >= 3) {
                $p1 = rtrim(trim($parts[0]), '.');
                $p2 = rtrim(trim($parts[1]), '.');
                $p3 = rtrim(trim($parts[2]), '.');

                $brand = self::isValidBrand($p3) ? $p3 : '';
                $id = (!self::isDescription($p2) && !empty($p2)) ? $p2 : $p1;

                $products[] = [
                    'nomor'       => $no++,
                    'name_source' => $p1,
                    'brand'       => $brand,
                    'en'          => $p1,
                    'id'          => $id,
                    'source'      => 'website',
                    'evidence'    => '',
                ];
            } elseif (count($parts) === 2) {
                $p1 = rtrim(trim($parts[0]), '.');
                $p2 = rtrim(trim($parts[1]), '.');

                // Cek apakah p2 adalah merk yang valid atau kalimat deskripsi
                if (self::isValidBrand($p2)) {
                    $brand = $p2;
                    $id = $p1;
                } else {
                    // p2 adalah deskripsi atau bukan merk valid -> abaikan deskripsinya!
                    $brand = '';
                    if (!self::isDescription($p2) && mb_strlen($p2) <= 25) {
                        $id = $p2;
                    } else {
                        $id = $p1;
                    }
                }

                $products[] = [
                    'nomor'       => $no++,
                    'name_source' => $p1,
                    'brand'       => $brand,
                    'en'          => $p1,
                    'id'          => $id,
                    'source'      => 'website',
                    'evidence'    => '',
                ];
            } else {
                $clean = rtrim(trim($line), '.');
                if (!empty($clean)) {
                    $products[] = [
                        'nomor'       => $no++,
                        'name_source' => $clean,
                        'brand'       => '',
                        'en'          => $clean,
                        'id'          => $clean,
                        'source'      => 'website',
                        'evidence'    => '',
                    ];
                }
            }
        }

        return $products;
    }
}
