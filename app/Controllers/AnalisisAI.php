<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\AIService;
use App\Libraries\CatalogExtractor;
use App\Libraries\WebsiteExtractor;
use App\Models\PerusahaanModel;
use App\Models\ProdukPerusahaanModel;
use App\Models\RiwayatAnalisisModel;
use App\Models\SumberAnalisisModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller Analisis Produk AI.
 *
 * Versi tanpa sistem login: tidak ada AdminAuthBase / checkAuth().
 * Semua endpoint bisa diakses langsung. Jika nanti ada login,
 * cukup pasang filter 'auth' pada route group (lihat Routes.php).
 */
class AnalisisAI extends BaseController
{
    /** Mode antrean yang diizinkan */
    private const QUEUE_MODES = ['single', 'selected', 'failed', 'pending', 'all'];

    /** Nilai website/katalog yang dianggap kosong */
    private const EMPTY_URL_VALUES = ['', '-', 'n/a'];

    /**
     * Halaman utama analisis
     */
    public function index()
    {
        $perusahaanModel = new PerusahaanModel();
        $aiService       = new AIService();

        // Seluruh perusahaan untuk dropdown single & multi-select
        $companies = $perusahaanModel
            ->select('id, nama_perusahaan, status_analisis')
            ->orderBy('nama_perusahaan', 'ASC')
            ->findAll();

        $data = [
            'title'       => 'Analisis Produk AI - AI Product Analyzer',
            'active_menu' => 'analyzer_analisis',
            'stats'       => $perusahaanModel->getStatistics(),
            'aiModel'     => $aiService->getModel(),
            'hasApiKey'   => $aiService->hasApiKey(),
            'companies'   => $companies,
            'selectedId'  => (int) $this->request->getGet('id') ?: '',
        ];

        // File view: app/Views/analisis/index.php
        return view('analisis/index', $data);
    }

    /**
     * Mendapatkan daftar antrean perusahaan berdasarkan mode analisis
     */
    public function getQueue(): ResponseInterface
    {
        $mode = (string) ($this->request->getGet('mode') ?: 'pending');
        if (! in_array($mode, self::QUEUE_MODES, true)) {
            $mode = 'pending';
        }

        $builder = (new PerusahaanModel())->select('id, nama_perusahaan, status_analisis');

        switch ($mode) {
            case 'single':
                $id = (int) $this->request->getGet('id');
                if ($id <= 0) {
                    return $this->json(['success' => false, 'message' => 'ID perusahaan tidak valid.']);
                }
                $builder->where('id', $id);
                break;

            case 'selected':
                $ids = array_values(array_unique(array_filter(
                    array_map('intval', explode(',', (string) $this->request->getGet('ids')))
                )));
                if (empty($ids)) {
                    return $this->json(['success' => false, 'message' => 'Tidak ada perusahaan yang dipilih.']);
                }
                $builder->whereIn('id', $ids);
                break;

            case 'failed':
                $builder->where('status_analisis', 'gagal');
                break;

            case 'pending':
                $builder->whereIn('status_analisis', ['belum_dianalisis', 'gagal']);
                break;

            // mode 'all' tanpa filter
        }

        $queue = $builder->orderBy('id', 'ASC')->findAll();

        return $this->json([
            'success' => true,
            'total'   => count($queue),
            'queue'   => $queue,
        ]);
    }

    /**
     * Memproses analisis SATU perusahaan.
     * Dipanggil berulang via AJAX dari halaman untuk progress real-time.
     */
    public function processOne(): ResponseInterface
    {
        // Pastikan respons selalu JSON murni
        ini_set('display_errors', '0');
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

        // Scraping + panggilan AI bisa lama
        set_time_limit(180);

        $id = 0;

        try {
            // Mendukung form-data maupun JSON body
            $id = (int) ($this->request->getPost('id') ?? $this->request->getJSON(true)['id'] ?? 0);
            if ($id <= 0) {
                return $this->json(['success' => false, 'error' => 'ID perusahaan tidak valid.']);
            }

            $perusahaanModel = new PerusahaanModel();
            $produkModel     = new ProdukPerusahaanModel();
            $sumberModel     = new SumberAnalisisModel();

            $company = $perusahaanModel->find($id);
            if (! $company) {
                return $this->json(['success' => false, 'error' => 'Data perusahaan tidak ditemukan.']);
            }

            $namaPerusahaan = $company['nama_perusahaan'] ?? '-';
            $websiteUrl     = trim($company['website'] ?? '');
            $catalogUrl     = trim($company['link_catalog'] ?? '');
            $profile        = trim($company['company_profile'] ?? '');

            $sourcesUsed     = [];
            $webText         = '';
            $catalogText     = '';
            $candidatesText  = '';
            $candidatesList  = [];
            $groupCategories = [];
            $stepLogs        = [];
            $now             = date('Y-m-d H:i:s');

            // 1. Company Profile (prioritas utama)
            if ($profile !== '') {
                $sourcesUsed[] = 'company_profile';
                $sumberModel->logSource($id, 'company_profile', null, 'sukses', $profile, null);
                $stepLogs[] = 'Profil Perusahaan berhasil dibaca.';
            } else {
                $stepLogs[] = 'Profil Perusahaan kosong.';
            }

            // 2. Website (sumber pendukung)
            if ($this->hasValue($websiteUrl)) {
                try {
                    $webRes = (new WebsiteExtractor())->extractText($websiteUrl);
                } catch (\Throwable $e) {
                    log_message('error', '[AnalisisAI] WebsiteExtractor ID {id}: {msg}', ['id' => $id, 'msg' => $e->getMessage()]);
                    $webRes = ['success' => false, 'error' => 'Error extractor: ' . $e->getMessage()];
                }

                if (! empty($webRes['success']) && trim($webRes['content'] ?? '') !== '') {
                    $webText         = $webRes['content'];
                    $candidatesText  = $webRes['candidates_text'] ?? '';
                    $candidatesList  = $webRes['candidates'] ?? [];
                    $groupCategories = $webRes['groups'] ?? [];

                    $sourcesUsed[] = 'website';
                    $sumberModel->logSource($id, 'website', $webRes['url'] ?? $websiteUrl, 'sukses', $webText, null);
                    $stepLogs[] = 'Website berhasil dibaca (' . mb_strlen($webText) . ' karakter, '
                        . count($candidatesList) . ' kandidat produk).';

                    if (empty($webRes['menu_read'])) {
                        $stepLogs[] = 'Menu produk tidak terbaca (menggunakan teks halaman yang ada).';
                    }
                } else {
                    $err = $webRes['error'] ?? 'Gagal membaca konten website.';
                    $sumberModel->logSource($id, 'website', $websiteUrl, 'gagal', null, $err);
                    $stepLogs[] = 'Website dilewati/gagal: ' . $err;
                }
            } else {
                $stepLogs[] = 'URL Website tidak tersedia.';
            }

            // 3. Katalog PDF (sumber pendukung)
            if ($this->hasValue($catalogUrl)) {
                try {
                    $catRes = (new CatalogExtractor())->extractText($catalogUrl);
                } catch (\Throwable $e) {
                    log_message('error', '[AnalisisAI] CatalogExtractor ID {id}: {msg}', ['id' => $id, 'msg' => $e->getMessage()]);
                    $catRes = ['success' => false, 'error' => 'Error extractor: ' . $e->getMessage()];
                }

                if (! empty($catRes['success']) && trim($catRes['content'] ?? '') !== '') {
                    $catalogText   = $catRes['content'];
                    $sourcesUsed[] = 'catalog';
                    $sumberModel->logSource($id, 'catalog', $catRes['url'] ?? $catalogUrl, 'sukses', $catalogText, null);
                    $stepLogs[] = 'Katalog PDF berhasil dibaca (' . mb_strlen($catalogText) . ' karakter).';
                } else {
                    $err = $catRes['error'] ?? 'Gagal membaca berkas katalog.';
                    $sumberModel->logSource($id, 'catalog', $catalogUrl, 'gagal', null, $err);
                    $stepLogs[] = 'Katalog dilewati/gagal: ' . $err;
                }
            } else {
                $stepLogs[] = 'Link Katalog tidak tersedia.';
            }

            // Semua sumber kosong -> jangan panggil AI (hemat kuota)
            if ($profile === '' && $webText === '' && $catalogText === '') {
                $statusSumber = 'Sumber tidak ada';

                $perusahaanModel->update($id, [
                    'status_analisis'     => $statusSumber,
                    'terakhir_dianalisis' => $now,
                ]);

                $this->saveHistory($id, 'sukses', 'tidak ada', $now, [
                    'pesan_error' => 'Dilewati: tidak ada sumber informasi',
                ]);

                $stepLogs[] = 'Dilewati: tidak ada sumber informasi (AI tidak dipanggil untuk menghemat kuota).';

                return $this->json([
                    'success'         => true,
                    'skipped'         => true,
                    'company_id'      => $id,
                    'nama_perusahaan' => $namaPerusahaan,
                    'status'          => $statusSumber,
                    'message'         => 'Dilewati: tidak ada sumber informasi',
                    'logs'            => $stepLogs,
                ]);
            }

            // 4. Siapkan data untuk AI Service (Google Gemini)
            $companyPayload = [
                'nama_perusahaan'  => $namaPerusahaan,
                'main_categories'  => $company['main_categories'] ?? '',
                'sub_categories'   => $company['sub_categories'] ?? '',
                'company_profile'  => $profile,
                'website_text'     => $webText,
                'catalog_text'     => $catalogText,
                'candidates_text'  => $candidatesText,
                'candidates'       => $candidatesList,
                'group_categories' => $groupCategories,
                'website_failed'   => $webText === '',
                'catalog_failed'   => $catalogText === '',
            ];

            $usedStr   = implode(', ', $sourcesUsed);
            $aiService = new AIService();

            if (! $aiService->hasApiKey()) {
                $errorMsg = 'API Key Google Gemini belum diatur di file .env (gemini.apiKey).';

                $this->saveHistory($id, 'gagal', $usedStr, $now, ['pesan_error' => $errorMsg]);

                return $this->json([
                    'success' => false,
                    'error'   => $errorMsg,
                    'logs'    => array_merge($stepLogs, [$errorMsg]),
                ]);
            }

            $stepLogs[] = 'Mengirim data ke Google Gemini API (' . $aiService->getModel() . ')...';
            $aiResult   = $aiService->analyzeCompanyProducts($companyPayload);

            // --- Kuota AI habis (HTTP 429): status perusahaan TIDAK diubah ---
            if (! empty($aiResult['is_quota_exceeded'])) {
                $this->saveHistory($id, 'gagal', $usedStr, $now, [
                    'pesan_error' => 'Kuota AI habis (HTTP 429)',
                ]);

                $stepLogs[] = 'Peringatan: Kuota AI habis (HTTP 429). Antrean dihentikan.';

                return $this->json([
                    'success'         => false,
                    'quota_exceeded'  => true,
                    'company_id'      => $id,
                    'nama_perusahaan' => $namaPerusahaan,
                    'error'           => 'Kuota AI habis, coba lagi nanti',
                    'logs'            => $stepLogs,
                ]);
            }

            // --- AI menyimpulkan "TIDAK ADA" produk ---
            if (! empty($aiResult['success']) && ! empty($aiResult['is_no_product'])) {
                $statusNoProd = 'Tanpa produk';

                $perusahaanModel->update($id, [
                    'produk_yang_dijual_auto' => '',
                    'status_analisis'         => $statusNoProd,
                    'terakhir_dianalisis'     => $now,
                ]);

                $produkModel->replaceProducts($id, []);

                $this->saveHistory($id, 'sukses', $usedStr, $now, [
                    'hasil_mentah' => $aiResult['raw'] ?? 'TIDAK ADA',
                    'confidence'   => 'low',
                ]);

                $stepLogs[] = 'AI menyimpulkan: TIDAK ADA produk fisik yang dijual. Status diatur ke "' . $statusNoProd . '".';

                return $this->json([
                    'success'         => true,
                    'no_product'      => true,
                    'company_id'      => $id,
                    'nama_perusahaan' => $namaPerusahaan,
                    'status'          => $statusNoProd,
                    'formatted_text'  => '',
                    'products'        => [],
                    'logs'            => $stepLogs,
                ]);
            }

            // --- Sukses dengan daftar produk ---
            if (! empty($aiResult['success'])) {
                $products      = $aiResult['products'] ?? [];
                $formattedText = $aiResult['formatted_text'] ?? '';

                $isPerluDiperiksa = ! empty($aiResult['is_perlu_diperiksa']);
                $discardedCount   = (int) ($aiResult['discarded_count'] ?? 0);

                $statusAnalisisDb = $isPerluDiperiksa ? 'belum_dianalisis' : 'sukses';
                $statusDisplay    = $isPerluDiperiksa ? 'Perlu diperiksa' : 'sukses';
                $confidence       = $isPerluDiperiksa ? 'low' : 'high';
                $pesanError       = $isPerluDiperiksa
                    ? ($aiResult['perlu_diperiksa_reason'] ?? 'Perlu diperiksa')
                    : null;

                if ($discardedCount > 0) {
                    $pesanError = ($pesanError ? $pesanError . ' | ' : '')
                        . "Jumlah produk dibuang: {$discardedCount} item.";
                }

                $perusahaanModel->update($id, [
                    'produk_yang_dijual_auto' => $formattedText,
                    'status_analisis'         => $statusAnalisisDb,
                    'terakhir_dianalisis'     => $now,
                ]);

                $produkModel->replaceProducts($id, $products);

                $this->saveHistory($id, $isPerluDiperiksa ? 'gagal' : 'sukses', $usedStr, $now, [
                    'hasil_mentah' => $aiResult['raw'] ?? '',
                    'confidence'   => $confidence,
                    'pesan_error'  => $pesanError,
                ]);

                $logMsg = $isPerluDiperiksa
                    ? 'Analisis AI selesai dengan status "Perlu diperiksa": ' . $pesanError
                    : 'Analisis AI sukses! Ditemukan ' . count($products) . ' nama produk murni.';

                if ($discardedCount > 0) {
                    $logMsg .= " ({$discardedCount} produk dibuang oleh filter validasi).";
                }
                $stepLogs[] = $logMsg;

                return $this->json([
                    'success'            => true,
                    'company_id'         => $id,
                    'nama_perusahaan'    => $namaPerusahaan,
                    'status'             => $statusDisplay,
                    'is_perlu_diperiksa' => $isPerluDiperiksa,
                    'discarded_count'    => $discardedCount,
                    'products'           => $products,
                    'formatted_text'     => $formattedText,
                    'sources_used'       => $sourcesUsed,
                    'logs'               => $stepLogs,
                ]);
            }

            // --- Gagal (koneksi putus, AI error, dll) ---
            $errorMsg = $aiResult['error'] ?? 'Terjadi kesalahan tidak diketahui pada AI Service.';

            // Jangan timpa hasil lama yang sudah sukses / manual / tanpa produk
            $updateData = ['terakhir_dianalisis' => $now];
            if (! in_array($company['status_analisis'], ['sukses', 'manual_edit', 'Tanpa produk'], true)) {
                $updateData['status_analisis'] = 'gagal';
            }
            $perusahaanModel->update($id, $updateData);

            $this->saveHistory($id, 'gagal', $usedStr, $now, [
                'hasil_mentah' => $aiResult['raw'] ?? null,
                'pesan_error'  => $errorMsg,
            ]);

            $stepLogs[] = 'Analisis AI gagal: ' . $errorMsg;

            return $this->json([
                'success'         => false,
                'company_id'      => $id,
                'nama_perusahaan' => $namaPerusahaan,
                'error'           => $errorMsg,
                'logs'            => $stepLogs,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[AnalisisAI::processOne] ID {id}: {msg}', [
                'id'  => $id,
                'msg' => $e->getMessage(),
            ]);

            return $this->json([
                'success'    => false,
                'company_id' => $id ?: null,
                'error'      => 'Kesalahan sistem: ' . $e->getMessage(),
                'logs'       => ['Pengecualian sistem tidak terduga: ' . $e->getMessage()],
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    /**
     * Cek apakah URL/teks berisi nilai nyata (bukan kosong, "-", atau "n/a").
     */
    private function hasValue(string $value): bool
    {
        return ! in_array(strtolower(trim($value)), self::EMPTY_URL_VALUES, true);
    }

    /**
     * Simpan satu baris ke tabel riwayat analisis.
     */
    private function saveHistory(int $companyId, string $status, string $sources, string $time, array $extra = []): void
    {
        (new RiwayatAnalisisModel())->insert(array_merge([
            'kontak_email_id'  => $companyId,
            'status'           => $status,
            'sumber_digunakan' => $sources,
            'waktu_analisis'   => $time,
        ], $extra));
    }

    /**
     * Respons JSON singkat.
     */
    private function json(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}