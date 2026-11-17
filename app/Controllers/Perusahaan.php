<?php

namespace App\Controllers;

use App\Models\PerusahaanModel;
use App\Models\ProdukPerusahaanModel;
use App\Models\RiwayatAnalisisModel;
use App\Models\SumberAnalisisModel;
use App\Models\SupplierModel;
use App\Models\SupplierProductModel;

class Perusahaan extends BaseController
{
    public function index()
    {
        $perusahaanModel = new PerusahaanModel();

        $status = $this->request->getGet('status') ?: '';
        $search = $this->request->getGet('search') ?: '';
        $page   = (int) ($this->request->getGet('page') ?: 1);
        if ($page < 1) $page = 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $filter = [
            'status' => $status,
            'search' => $search,
        ];

        $companies = $perusahaanModel->getFiltered($filter, $perPage, $offset);
        $totalRows = $perusahaanModel->countFiltered($filter);
        $totalPages = ceil($totalRows / $perPage);

        // Ambil produk terhubung untuk tiap perusahaan di halaman ini
        $produkModel = new ProdukPerusahaanModel();
        foreach ($companies as &$comp) {
            $comp['produk_list'] = $produkModel->getByPerusahaan($comp['id']);
        }
        unset($comp); // putus reference agar tidak menimpa elemen terakhir

        $data = [
            'title'       => 'Data Perusahaan - AI Product Analyzer',
            'active_menu' => 'analyzer_perusahaan',
            'companies'   => $companies,
            'totalRows'   => $totalRows,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'filter'      => $filter,
            'stats'       => $perusahaanModel->getStatistics(),
        ];

        return view('kontak/index', $data);
    }

    /**
     * Dapatkan detail perusahaan dalam bentuk JSON untuk Modal
     */
    public function detail($id)
    {
        $perusahaanModel = new PerusahaanModel();
        $produkModel     = new ProdukPerusahaanModel();
        $riwayatModel    = new RiwayatAnalisisModel();
        $sumberModel     = new SumberAnalisisModel();

        $company = $perusahaanModel->find($id);
        if (!$company) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Perusahaan tidak ditemukan']);
        }

        $products = $produkModel->getByPerusahaan($id);
        $history  = $riwayatModel->where('perusahaan_id', $id)->orderBy('id', 'DESC')->findAll(5);
        $sources  = $sumberModel->getByPerusahaan($id);

        return $this->response->setJSON([
            'success'  => true,
            'company'  => $company,
            'products' => $products,
            'history'  => $history,
            'sources'  => $sources,
        ]);
    }

    /**
     * Hapus perusahaan
     */
    public function delete($id)
    {
        $perusahaanModel = new PerusahaanModel();
        $perusahaanModel->delete($id);

        return redirect()->to(base_url('perusahaan'))
            ->with('success', 'Data perusahaan berhasil dihapus.');
    }

    /**
     * Bulk delete
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (!is_array($ids) || empty($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
        }

        $perusahaanModel = new PerusahaanModel();
        $perusahaanModel->whereIn('id', $ids)->delete();

        return $this->response->setJSON(['success' => true, 'message' => count($ids) . ' perusahaan berhasil dihapus.']);
    }

    /**
     * Import satu data perusahaan ke tb_supplier dan tb_produk_supplier
     */
    public function importSingle($id)
    {
        $perusahaanModel = new PerusahaanModel();
        $produkModel     = new ProdukPerusahaanModel();

        $company = $perusahaanModel->find($id);
        if (!$company) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'    => false,
                'error'      => 'Data perusahaan tidak ditemukan',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $products = $produkModel->getByPerusahaan((int) $id);
        $result   = $this->processImportCompany($company, $products);

        $response = [
            'success'          => $result['success'],
            'company_name'     => $company['nama_perusahaan'],
            'is_new_supplier'  => $result['is_new_supplier'],
            'supplier_id'      => $result['supplier_id'],
            'products_added'   => $result['products_added'],
            'products_skipped' => $result['products_skipped'],
            'is_skipped'       => $result['is_skipped'],
            'reason'           => $result['reason'],
            'error'            => $result['error'],
            'csrf_token'       => csrf_token(),
            'csrf_hash'        => csrf_hash(),
        ];

        if ($result['success']) {
            if ($result['is_new_supplier']) {
                $response['message'] = "Berhasil membuat supplier baru '{$company['nama_perusahaan']}' dan menambahkan {$result['products_added']} produk.";
            } else {
                $response['message'] = "Supplier '{$company['nama_perusahaan']}' sudah terdaftar (data lama tidak ditimpa). Berhasil menambahkan {$result['products_added']} produk baru ({$result['products_skipped']} produk sudah ada sebelumnya).";
            }
        } else {
            $response['message'] = $result['reason'] ?? $result['error'] ?? 'Gagal mengimpor perusahaan ke supplier.';
        }

        return $this->response->setJSON($response);
    }

    /**
     * Preview ringkasan import keseluruhan untuk modal konfirmasi
     */
    public function importPreview()
    {
        $perusahaanModel = new PerusahaanModel();
        $supplierModel   = new SupplierModel();

        $companies = $perusahaanModel->select('id, nama_perusahaan, status_analisis')->findAll();
        $totalPerusahaan = count($companies);

        // Ambil pemetaan jumlah produk per perusahaan
        $db = \Config\Database::connect();
        $productCounts = $db->table('produk_perusahaan')
            ->select('perusahaan_id, COUNT(*) as cnt')
            ->groupBy('perusahaan_id')
            ->get()
            ->getResultArray();

        $productCountMap = [];
        foreach ($productCounts as $pc) {
            $productCountMap[(int) $pc['perusahaan_id']] = (int) $pc['cnt'];
        }

        // Ambil semua nama_kunci yang sudah ada di tb_supplier
        $existingKeys = $supplierModel->select('nama_kunci')->findAll();
        $existingKeyMap = [];
        foreach ($existingKeys as $ek) {
            if (!empty($ek['nama_kunci'])) {
                $existingKeyMap[$ek['nama_kunci']] = true;
            }
        }

        $eligibleCount     = 0;
        $eligibleProducts  = 0;
        $newSuppliers      = 0;
        $existingSuppliers = 0;

        $skippedBreakdown = [
            'belum_dianalisis' => ['label' => 'Belum Dianalisis', 'count' => 0],
            'gagal'            => ['label' => 'Status Analisis Gagal', 'count' => 0],
            'tanpa_produk'     => ['label' => 'Tanpa Produk Terdeteksi', 'count' => 0],
            'lainnya'          => ['label' => 'Status Lainnya', 'count' => 0],
        ];

        foreach ($companies as $comp) {
            $status = $comp['status_analisis'];
            $pCount = $productCountMap[(int) $comp['id']] ?? 0;

            if (!in_array($status, ['sukses', 'manual'], true)) {
                if ($status === 'belum_dianalisis') {
                    $skippedBreakdown['belum_dianalisis']['count']++;
                } elseif ($status === 'gagal') {
                    $skippedBreakdown['gagal']['count']++;
                } else {
                    $skippedBreakdown['lainnya']['count']++;
                }
                continue;
            }

            if ($pCount === 0) {
                $skippedBreakdown['tanpa_produk']['count']++;
                continue;
            }

            $eligibleCount++;
            $eligibleProducts += $pCount;

            $key = SupplierModel::makeKey($comp['nama_perusahaan']);
            if (isset($existingKeyMap[$key])) {
                $existingSuppliers++;
            } else {
                $newSuppliers++;
            }
        }

        return $this->response->setJSON([
            'success'          => true,
            'total_perusahaan' => $totalPerusahaan,
            'akan_diimpor'     => [
                'perusahaan'         => $eligibleCount,
                'produk'             => $eligibleProducts,
                'supplier_baru'      => $newSuppliers,
                'supplier_sudah_ada' => $existingSuppliers,
            ],
            'dilewati'         => [
                'total'     => $totalPerusahaan - $eligibleCount,
                'breakdown' => array_values($skippedBreakdown),
            ],
            'csrf_token'       => csrf_token(),
            'csrf_hash'        => csrf_hash(),
        ]);
    }

    /**
     * Import seluruh perusahaan yang memenuhi syarat ke tb_supplier & tb_produk_supplier
     */
    public function importAll()
    {
        set_time_limit(300);

        $perusahaanModel = new PerusahaanModel();
        $produkModel     = new ProdukPerusahaanModel();

        $companies = $perusahaanModel->findAll();

        $summary = [
            'total_diproses'     => count($companies),
            'supplier_baru'      => 0,
            'supplier_sudah_ada' => 0,
            'produk_ditambahkan' => 0,
            'produk_dilewati'    => 0,
            'dilewati'           => [],
            'gagal'              => [],
        ];

        foreach ($companies as $comp) {
            $products = $produkModel->getByPerusahaan((int) $comp['id']);
            $res = $this->processImportCompany($comp, $products);

            if ($res['success']) {
                if ($res['is_new_supplier']) {
                    $summary['supplier_baru']++;
                } else {
                    $summary['supplier_sudah_ada']++;
                }
                $summary['produk_ditambahkan'] += $res['products_added'];
                $summary['produk_dilewati']    += $res['products_skipped'];
            } elseif (!empty($res['is_skipped'])) {
                $summary['dilewati'][] = [
                    'nama'   => $comp['nama_perusahaan'],
                    'alasan' => $res['reason'],
                ];
            } else {
                $summary['gagal'][] = [
                    'nama'   => $comp['nama_perusahaan'],
                    'pesan'  => $res['error'] ?? $res['reason'] ?? 'Kesalahan tidak diketahui',
                ];
            }
        }

        return $this->response->setJSON([
            'success'    => true,
            'summary'    => $summary,
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Memproses import satu record perusahaan ke tb_supplier & tb_produk_supplier
     * Menggunakan transaksi terisolasi per perusahaan.
     *
     * @param array $company
     * @param array $products
     * @return array
     */
    private function processImportCompany(array $company, array $products): array
    {
        $status = $company['status_analisis'] ?? '';
        if (!in_array($status, ['sukses', 'manual'], true)) {
            $reason = ($status === 'belum_dianalisis')
                ? 'Status analisis masih belum dianalisis'
                : (($status === 'gagal') ? 'Status analisis gagal' : "Status analisis tidak memenuhi syarat ({$status})");

            return [
                'success'          => false,
                'is_skipped'       => true,
                'reason'           => $reason,
                'is_new_supplier'  => false,
                'supplier_id'      => null,
                'products_added'   => 0,
                'products_skipped' => 0,
                'error'            => null,
            ];
        }

        if (empty($products)) {
            return [
                'success'          => false,
                'is_skipped'       => true,
                'reason'           => 'Tidak memiliki daftar produk terdeteksi',
                'is_new_supplier'  => false,
                'supplier_id'      => null,
                'products_added'   => 0,
                'products_skipped' => 0,
                'error'            => null,
            ];
        }

        $namaPerusahaan = trim($company['nama_perusahaan'] ?? '');
        if ($namaPerusahaan === '') {
            return [
                'success'          => false,
                'is_skipped'       => true,
                'reason'           => 'Nama perusahaan kosong',
                'is_new_supplier'  => false,
                'supplier_id'      => null,
                'products_added'   => 0,
                'products_skipped' => 0,
                'error'            => null,
            ];
        }

        $namaKunci = SupplierModel::makeKey($namaPerusahaan);
        if (empty($namaKunci)) {
            $namaKunci = 'supplier' . $company['id'];
        }

        $db = \Config\Database::connect();
        $supplierModel        = new SupplierModel();
        $supplierProductModel = new SupplierProductModel();

        $db->transBegin();

        try {
            // Cocokkan nama_kunci atau lowercase clean name
            $existingSupplier = $supplierModel->where('nama_kunci', $namaKunci)->first();
            if (!$existingSupplier) {
                $cleanName = trim(preg_replace('/\s+/', ' ', $namaPerusahaan));
                $existingSupplier = $supplierModel->where('LOWER(TRIM(nama_perusahaan))', mb_strtolower($cleanName, 'UTF-8'))->first();
            }

            $isNewSupplier = false;
            $idSupplier    = null;

            if ($existingSupplier) {
                $idSupplier    = (int) $existingSupplier['id_supplier'];
                $isNewSupplier = false;
                // Jangan timpa data supplier yang sudah ada
            } else {
                $kategori = [];
                if (!empty($company['main_categories']) && $company['main_categories'] !== '-') {
                    $kategori[] = trim($company['main_categories']);
                }
                if (!empty($company['sub_categories']) && $company['sub_categories'] !== '-') {
                    $kategori[] = trim($company['sub_categories']);
                }
                $kategoriEkspor = !empty($kategori) ? implode(' - ', $kategori) : null;

                $website          = (!empty($company['website']) && $company['website'] !== '-') ? trim($company['website']) : null;
                $rawDipasarkan    = (!empty($company['produk_yang_dijual_auto']) && $company['produk_yang_dijual_auto'] !== '-') ? trim($company['produk_yang_dijual_auto']) : null;
                $produkDipasarkan = null;
                if (!empty($rawDipasarkan)) {
                    $aiSvc = new \App\Libraries\AIService();
                    $parsed = $aiSvc->parseProductLines($rawDipasarkan);
                    $produkDipasarkan = !empty($parsed) ? $aiSvc->formatToBilingualText($parsed) : $rawDipasarkan;
                }
                $keunggulan       = (!empty($company['company_profile']) && $company['company_profile'] !== '-') ? trim($company['company_profile']) : null;

                $supplierData = [
                    'nama_perusahaan'   => $namaPerusahaan,
                    'nama_kunci'        => $namaKunci,
                    'website'           => $website,
                    'produk_dipasarkan' => $produkDipasarkan,
                    'kategori_ekspor'   => $kategoriEkspor,
                    'keunggulan_produk' => $keunggulan,
                    'sumber_tahun'      => date('Y'),
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ];

                $supplierModel->insert($supplierData);
                $idSupplier    = (int) $supplierModel->getInsertID();
                $isNewSupplier = true;
            }

            // Ambil produk supplier yang sudah ada untuk id_supplier ini
            $existingProds = $supplierProductModel->where('id_supplier', $idSupplier)->findAll();
            $existingKeys  = [];
            foreach ($existingProds as $ep) {
                if (!empty($ep['nama_produk'])) {
                    $existingKeys[] = mb_strtolower(trim($ep['nama_produk']), 'UTF-8');
                }
            }

            $productsAdded     = 0;
            $productsSkipped   = 0;
            $seenKeysThisBatch = [];

            foreach ($products as $p) {
                // Utamakan nama produk Bahasa Indonesia
                $namaProdukTarget = !empty($p['nama_produk_id']) ? trim($p['nama_produk_id']) : trim($p['nama_produk_en'] ?? '');
                // Jika nama_produk_id berupa kalimat deskripsi panjang (data lama), gunakan nama_produk_en
                if (\App\Libraries\AIService::isDescription($namaProdukTarget) && !empty($p['nama_produk_en']) && !\App\Libraries\AIService::isDescription($p['nama_produk_en'])) {
                    $namaProdukTarget = trim($p['nama_produk_en']);
                }

                if ($namaProdukTarget === '') {
                    $productsSkipped++;
                    continue;
                }

                $normKey = mb_strtolower($namaProdukTarget, 'UTF-8');
                if (in_array($normKey, $existingKeys, true) || in_array($normKey, $seenKeysThisBatch, true)) {
                    // Jangan buat duplikat jika produk sudah ada
                    $productsSkipped++;
                    continue;
                }

                $seenKeysThisBatch[] = $normKey;

                // Jangan pernah memasukkan kalimat deskripsi ke spesifikasi
                $brand = !empty($p['brand']) && \App\Libraries\AIService::isValidBrand($p['brand']) ? trim($p['brand']) : null;
                $spesifikasi = $brand ? 'Merk: ' . $brand : null;

                $supplierProductModel->insert([
                    'id_supplier' => $idSupplier,
                    'nama_produk' => $namaProdukTarget,
                    'spesifikasi' => $spesifikasi,
                    'views'       => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $productsAdded++;
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return [
                    'success'          => false,
                    'is_skipped'       => false,
                    'reason'           => 'Transaksi database gagal',
                    'is_new_supplier'  => false,
                    'supplier_id'      => null,
                    'products_added'   => 0,
                    'products_skipped' => 0,
                    'error'            => 'Database transaction status false',
                ];
            }

            $db->transCommit();

            return [
                'success'          => true,
                'is_skipped'       => false,
                'reason'           => null,
                'is_new_supplier'  => $isNewSupplier,
                'supplier_id'      => $idSupplier,
                'products_added'   => $productsAdded,
                'products_skipped' => $productsSkipped,
                'error'            => null,
            ];

        } catch (\Throwable $e) {
            $db->transRollback();
            return [
                'success'          => false,
                'is_skipped'       => false,
                'reason'           => 'Terjadi error: ' . $e->getMessage(),
                'is_new_supplier'  => false,
                'supplier_id'      => null,
                'products_added'   => 0,
                'products_skipped' => 0,
                'error'            => $e->getMessage(),
            ];
        }
    }
}