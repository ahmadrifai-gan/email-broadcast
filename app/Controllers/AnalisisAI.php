<?php

namespace App\Controllers\admin\analyzer;

use App\Controllers\admin\AdminAuthBase;
use App\Libraries\AIService;
use App\Libraries\CatalogExtractor;
use App\Libraries\WebsiteExtractor;
use App\Models\PengaturanAiModel;
use App\Models\PerusahaanModel;
use App\Models\ProdukPerusahaanModel;
use App\Models\RiwayatAnalisisModel;
use App\Models\SumberAnalisisModel;
use Exception;

class AnalisisAI extends AdminAuthBase
{
    public function index()
    {
        if ($auth = $this->checkAuth()) {
            return $auth;
        }

        $perusahaanModel = new PerusahaanModel();
        $aiService       = new AIService();

        $stats     = $perusahaanModel->getStatistics();
        $hasApiKey = $aiService->hasApiKey();
        $aiModel   = $aiService->getModel();

        // Ambil seluruh daftar perusahaan untuk dropdown single & multi-select (tanpa batas LIMIT)
        $companies = $perusahaanModel->select('id, nama_perusahaan, status_analisis')
            ->orderBy('nama_perusahaan', 'ASC')
            ->findAll();

        $data = [
            'title'       => 'Analisis Produk AI - AI Product Analyzer',
            'active_menu' => 'analyzer_analisis',
            'stats'       => $stats,
            'aiModel'     => $aiModel,
            'hasApiKey'   => $hasApiKey,
            'companies'   => $companies,
            'selectedId'  => $this->request->getGet('id') ?: '',
        ];

        return view('admin/analyzer/analisis/index', $data);
    }

    /**
     * Mendapatkan daftar ID antrean perusahaan berdasarkan mode analisis
     */
    public function getQueue()
    {
        if ($auth = $this->checkAuth()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $mode = $this->request->getGet('mode') ?: 'pending';
        $perusahaanModel = new PerusahaanModel();

        $builder = $perusahaanModel->select('id, nama_perusahaan, status_analisis');

        if ($mode === 'single') {
            $id = (int) $this->request->getGet('id');
            $builder->where('id', $id);
        } elseif ($mode === 'selected') {
            $ids = explode(',', (string) $this->request->getGet('ids'));
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $builder->whereIn('id', $ids);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada perusahaan yang dipilih.']);
            }
        } elseif ($mode === 'failed') {
            $builder->where('status_analisis', 'gagal');
        } elseif ($mode === 'pending') {
            $builder->whereIn('status_analisis', ['belum_dianalisis', 'gagal']);
        }
        // mode 'all' tidak diberi filter

        $queue = $builder->orderBy('id', 'ASC')->findAll();

        return $this->response->setJSON([
            'success' => true,
            'total'   => count($queue),
            'queue'   => $queue,
        ]);
    }

    /**
     * Memproses analisis satu perusahaan (dipanggil berulang via AJAX untuk real-time progress)
     */
    public function processOne()
    {
        // Matikan output error langsung agar respons selalu berupa JSON murni
        ini_set('display_errors', '0');
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

        if ($auth = $this->checkAuth()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        // Perpanjang execution time limit PHP agar tidak timeout saat scraping & panggil AI
        set_time_limit(180);

        try {
            $id = (int) $this->request->getPost('id');
            if (!$id) {
                return $this->response->setJSON(['success' => false, 'error' => 'ID perusahaan tidak valid.']);
            }

            $perusahaanModel = new PerusahaanModel();
            $produkModel     = new ProdukPerusahaanModel();
            $riwayatModel    = new RiwayatAnalisisModel();
            $sumberModel     = new SumberAnalisisModel();

            $company = $perusahaanModel->find($id);
            if (!$company) {
                return $this->response->setJSON(['success' => false, 'error' => 'Data perusahaan tidak ditemukan.']);
            }

            $namaPerusahaan = $company['nama_perusahaan'] ?? '-';
            $websiteUrl     = trim($company['website'] ?? '');
            $catalogUrl     = trim($company['link_catalog'] ?? '');
            $profile        = trim($company['company_profile'] ?? '');

            $sourcesUsed = [];
            $webText = '';
            $catalogText = '';
            $candidatesText = '';
            $candidatesList = [];
            $groupCategories = [];
            $stepLogs = [];
            $now = date('Y-m-d H:i:s');

            // 1. Catat Company Profile jika ada (Prioritas Utama)
            if (!empty($profile)) {
                $sourcesUsed[] = 'company_profile';
                $sumberModel->logSource($id, 'company_profile', null, 'sukses', $profile, null);
                $stepLogs[] = 'Profil Perusahaan berhasil dibaca.';
            } else {
                $stepLogs[] = 'Profil Perusahaan kosong.';
            }

            // 2. Crawl Website jika URL tersedia (Sumber Pendukung)
            if (!empty($websiteUrl) && $websiteUrl !== '-' && strtolower($websiteUrl) !== 'n/a') {
                $webExtractor = new WebsiteExtractor();
                $webRes = $webExtractor->extractText($websiteUrl);

                if ($webRes['success'] && !empty(trim($webRes['content']))) {
                    $webText         = $webRes['content'];
                    $candidatesText  = $webRes['candidates_text'] ?? '';
                    $candidatesList  = $webRes['candidates'] ?? [];
                    $groupCategories = $webRes['groups'] ?? [];

                    $sourcesUsed[] = 'website';
                    $sumberModel->logSource($id, 'website', $webRes['url'], 'sukses', $webText, null);
                    $stepLogs[] = 'Website berhasil dibaca (' . mb_strlen($webText) . ' karakter, ' . count($candidatesList) . ' kandidat produk).';
                    if (empty($webRes['menu_read'])) {
                        $stepLogs[] = 'Menu produk tidak terbaca (menggunakan teks halaman yang ada).';
                    }
                } else {
                    $sumberModel->logSource($id, 'website', $websiteUrl, 'gagal', null, $webRes['error'] ?? 'Gagal membaca konten website.');
                    $stepLogs[] = 'Website dilewati/gagal: ' . ($webRes['error'] ?? 'Tidak ada teks.');
                }
            } else {
                $stepLogs[] = 'URL Website tidak tersedia.';
            }

            // 3. Ekstrak Katalog PDF jika URL tersedia (Sumber Pendukung)
            if (!empty($catalogUrl) && $catalogUrl !== '-' && strtolower($catalogUrl) !== 'n/a') {
                $catExtractor = new CatalogExtractor();
                $catRes = $catExtractor->extractText($catalogUrl);

                if ($catRes['success'] && !empty(trim($catRes['content']))) {
                    $catalogText = $catRes['content'];
                    $sourcesUsed[] = 'catalog';
                    $sumberModel->logSource($id, 'catalog', $catRes['url'], 'sukses', $catalogText, null);
                    $stepLogs[] = 'Katalog PDF berhasil dibaca (' . mb_strlen($catalogText) . ' karakter).';
                } else {
                    $sumberModel->logSource($id, 'catalog', $catalogUrl, 'gagal', null, $catRes['error'] ?? 'Gagal membaca berkas katalog.');
                    $stepLogs[] = 'Katalog dilewati/gagal: ' . ($catRes['error'] ?? 'Tidak ada teks.');
                }
            } else {
                $stepLogs[] = 'Link Katalog tidak tersedia.';
            }

            // PERUBAHAN 3: PENANGANAN SUMBER KOSONG SEBELUM MEMANGGIL AI
            // Jika profil, website, dan katalog kosong/gagal total -> JANGAN panggil AI (hemat kuota)
            if (empty($profile) && empty($webText) && empty($catalogText)) {
                $statusSumber = 'Sumber tidak ada';
                $perusahaanModel->update($id, [
                    'status_analisis'     => $statusSumber,
                    'terakhir_dianalisis' => $now,
                ]);

                // Jangan anggap ini sebagai "Gagal", catat ke riwayat sebagai info/dilewati
                $riwayatModel->insert([
                    'perusahaan_id'    => $id,
                    'status'           => 'sukses',
                    'sumber_digunakan' => 'tidak ada',
                    'pesan_error'      => 'Dilewati: tidak ada sumber informasi',
                    'waktu_analisis'   => $now,
                ]);

                $stepLogs[] = 'Dilewati: tidak ada sumber informasi (AI tidak dipanggil untuk menghemat kuota).';

                return $this->response->setJSON([
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
                'website_failed'   => empty($webText),
                'catalog_failed'   => empty($catalogText),
            ];

            $aiService = new AIService();
            if (!$aiService->hasApiKey()) {
                $errorMsg = 'API Key Google Gemini belum diatur di file .env (gemini.apiKey).';
                $riwayatModel->insert([
                    'perusahaan_id'    => $id,
                    'status'           => 'gagal',
                    'sumber_digunakan' => implode(', ', $sourcesUsed),
                    'pesan_error'      => $errorMsg,
                    'waktu_analisis'   => $now,
                ]);
                return $this->response->setJSON([
                    'success' => false,
                    'error'   => $errorMsg,
                    'logs'    => array_merge($stepLogs, [$errorMsg]),
                ]);
            }

            $stepLogs[] = 'Mengirim data ke Google Gemini API (' . $aiService->getModel() . ')...';
            $aiResult = $aiService->analyzeCompanyProducts($companyPayload);

            // PERUBAHAN 1: PENANGANAN HTTP 429 KUOTA HABIS
            if (!empty($aiResult['is_quota_exceeded'])) {
                // JANGAN tandai perusahaan sebagai gagal
                $riwayatModel->insert([
                    'perusahaan_id'    => $id,
                    'status'           => 'gagal',
                    'sumber_digunakan' => implode(', ', $sourcesUsed),
                    'pesan_error'      => 'Kuota AI habis (HTTP 429)',
                    'waktu_analisis'   => $now,
                ]);

                $stepLogs[] = 'Peringatan: Kuota AI habis (HTTP 429). Antrean dihentikan.';

                return $this->response->setJSON([
                    'success'         => false,
                    'quota_exceeded'  => true,
                    'company_id'      => $id,
                    'nama_perusahaan' => $namaPerusahaan,
                    'error'           => 'Kuota AI habis, coba lagi nanti',
                    'logs'            => $stepLogs,
                ]);
            }

            // PERUBAHAN 2: JIKA AI MERESPONS "TIDAK ADA"
            if ($aiResult['success'] && !empty($aiResult['is_no_product'])) {
                $statusNoProd = 'Tanpa produk';
                $perusahaanModel->update($id, [
                    'produk_yang_dijual_auto' => '',
                    'status_analisis'         => $statusNoProd,
                    'terakhir_dianalisis'     => $now,
                ]);

                // Kosongkan produk terurai di tabel anak
                $produkModel->replaceProducts($id, []);

                $riwayatModel->insert([
                    'perusahaan_id'    => $id,
                    'status'           => 'sukses',
                    'sumber_digunakan' => implode(', ', $sourcesUsed),
                    'hasil_mentah'     => $aiResult['raw'] ?? 'TIDAK ADA',
                    'confidence'       => 'low',
                    'pesan_error'      => null,
                    'waktu_analisis'   => $now,
                ]);

                $stepLogs[] = 'AI menyimpulkan: TIDAK ADA produk fisik yang dijual. Status diatur ke "' . $statusNoProd . '".';

                return $this->response->setJSON([
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

            // SUKSES DENGAN DAFTAR PRODUK
            if ($aiResult['success']) {
                $products      = $aiResult['products'];
                $formattedText = $aiResult['formatted_text'];
                $used          = implode(', ', $sourcesUsed);

                // Evaluasi status "Perlu diperiksa" dan produk yang dibuang
                $isPerluDiperiksa = !empty($aiResult['is_perlu_diperiksa']);
                $discardedCount   = (int) ($aiResult['discarded_count'] ?? 0);
                $statusAnalisisDb = $isPerluDiperiksa ? 'belum_dianalisis' : 'sukses';
                $statusDisplay    = $isPerluDiperiksa ? 'Perlu diperiksa' : 'sukses';
                $confidence       = $isPerluDiperiksa ? 'low' : 'high';
                $pesanError       = $isPerluDiperiksa ? ($aiResult['perlu_diperiksa_reason'] ?? 'Perlu diperiksa') : null;
                if ($discardedCount > 0) {
                    $pesanError = ($pesanError ? $pesanError . ' | ' : '') . "Jumlah produk dibuang: {$discardedCount} item.";
                }

                // Simpan hasil ke tabel perusahaan
                $perusahaanModel->update($id, [
                    'produk_yang_dijual_auto' => $formattedText,
                    'status_analisis'         => $statusAnalisisDb,
                    'terakhir_dianalisis'     => $now,
                ]);

                // Simpan rincian produk ke tabel produk_perusahaan
                $produkModel->replaceProducts($id, $products);

                // Catat ke riwayat
                $riwayatModel->insert([
                    'perusahaan_id'    => $id,
                    'status'           => $isPerluDiperiksa ? 'gagal' : 'sukses',
                    'sumber_digunakan' => $used,
                    'hasil_mentah'     => $aiResult['raw'] ?? '',
                    'confidence'       => $confidence,
                    'pesan_error'      => $pesanError,
                    'waktu_analisis'   => $now,
                ]);

                $logMsg = $isPerluDiperiksa 
                    ? 'Analisis AI selesai dengan status "Perlu diperiksa": ' . $pesanError
                    : 'Analisis AI sukses! Ditemukan ' . count($products) . ' nama produk murni.';
                if ($discardedCount > 0) {
                    $logMsg .= " ({$discardedCount} produk dibuang oleh filter validasi).";
                }
                $stepLogs[] = $logMsg;

                return $this->response->setJSON([
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

            // PERUBAHAN 4: JIKA GAGAL (Koneksi putus, AI error)
            $errorMsg = $aiResult['error'] ?? 'Terjadi kesalahan tidak diketahui pada AI Service.';
            $used     = implode(', ', $sourcesUsed);

            // JANGAN hapus atau timpa hasil lama jika sebelumnya sudah pernah sukses/manual_edit/Tanpa produk
            $updateData = ['terakhir_dianalisis' => $now];
            if (!in_array($company['status_analisis'], ['sukses', 'manual_edit', 'Tanpa produk'])) {
                $updateData['status_analisis'] = 'gagal';
            }
            $perusahaanModel->update($id, $updateData);

            // Catat log kegagalan ke riwayat
            $riwayatModel->insert([
                'perusahaan_id'    => $id,
                'status'           => 'gagal',
                'sumber_digunakan' => $used,
                'hasil_mentah'     => $aiResult['raw'] ?? null,
                'pesan_error'      => $errorMsg,
                'waktu_analisis'   => $now,
            ]);

            $stepLogs[] = 'Analisis AI gagal: ' . $errorMsg;

            return $this->response->setJSON([
                'success'        => false,
                'company_id'     => $id,
                'nama_perusahaan'=> $namaPerusahaan,
                'error'          => $errorMsg,
                'logs'           => $stepLogs,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Kesalahan sistem: ' . $e->getMessage(),
                'logs'    => ['Pengecualian sistem tidak terduga: ' . $e->getMessage()],
            ]);
        }
    }
}

