<?php

namespace App\Controllers;

use App\Models\KategoriKontakModel;
use App\Models\PerusahaanModel;
use App\Models\ProdukPerusahaanModel;
use App\Models\RiwayatAnalisisModel;
use App\Models\SumberAnalisisModel;
use App\Models\SupplierModel;
use App\Models\SupplierProductModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KontakController extends BaseController
{
    private const PER_HALAMAN    = 15;
    private const TABEL_KATEGORI = 'kategori'; // sama dengan $table di KategoriKontakModel

    /**
     * Satu halaman: daftar kontak + kategori + status analisis + produk.
     * Filter: cari, kategori, status.
     */
    public function index()
    {
        $kontakModel   = new PerusahaanModel();
        $kategoriModel = new KategoriKontakModel();
        $tabelKategori = self::TABEL_KATEGORI;

        $cari          = trim((string) $this->request->getGet('cari'));
        $statusDipilih = trim((string) $this->request->getGet('status'));
        $kategori      = $this->request->getGet('kategori');
        $kategori      = ($kategori !== null && $kategori !== '') ? (int) $kategori : '';

        $kontakModel
            ->select("kontak_email.*, {$tabelKategori}.nama AS kategori_nama, kontak_email.produk_yang_dijual_auto AS produk_perusahaan")
            ->join($tabelKategori, "{$tabelKategori}.id = kontak_email.kategori_id", 'left');

        if ($cari !== '') {
            $kontakModel->groupStart()
                ->like('kontak_email.nama', $cari)
                ->orLike('kontak_email.nama_perusahaan', $cari)
                ->orLike('kontak_email.email', $cari)
                ->orLike('kontak_email.no_hp', $cari)
                ->orLike('kontak_email.kota_kabupaten', $cari)
                ->orLike('kontak_email.provinsi', $cari)
                ->orLike('kontak_email.main_categories', $cari)
                ->orLike('kontak_email.sub_categories', $cari)
                ->orLike('kontak_email.website', $cari)
                ->groupEnd();
        }

        if ($kategori !== '') {
            $kontakModel->where('kontak_email.kategori_id', $kategori);
        }

        if ($statusDipilih !== '') {
            $kontakModel->where('kontak_email.status_analisis', $statusDipilih);
        }

        $kontak = $kontakModel->orderBy('kontak_email.id', 'DESC')->paginate(self::PER_HALAMAN);
        $pager  = $kontakModel->pager;

        // Produk terhubung untuk tiap baris (dipakai tombol import ke supplier)
        $produkModel = new ProdukPerusahaanModel();
        foreach ($kontak as &$k) {
            try {
                $k['produk_list'] = $produkModel->getByPerusahaan((int) $k['id']);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal memuat produk perusahaan: ' . $e->getMessage());
                $k['produk_list'] = [];
            }
        }
        unset($k);

        return view('kontak_email/index', [
            'title'           => 'Data Kontak',
            'kontak'          => $kontak,
            'pager'           => $pager,
            'daftarKategori'  => $kategoriModel->orderBy('nama', 'ASC')->findAll(),
            'cari'            => $cari,
            'kategoriDipilih' => $kategori,
            'statusDipilih'   => $statusDipilih,
            'stats'           => (new PerusahaanModel())->getStatistics(),
        ]);
    }

    /**
     * Simpan kontak baru
     */
    public function simpan()
    {
        if (! $this->validate($this->aturan(), $this->pesan())) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('modal_aksi', site_url('kontak/simpan'));
        }

        (new PerusahaanModel())->insert($this->ambilData());

        return redirect()->to(site_url('kontak'))->with('sukses', 'Kontak berhasil ditambahkan.');
    }

    /**
     * Perbarui kontak
     */
    public function perbarui(int $id)
    {
        $model = new PerusahaanModel();

        if (! $model->find($id)) {
            return redirect()->to(site_url('kontak'))->with('gagal', 'Kontak tidak ditemukan.');
        }

        if (! $this->validate($this->aturan($id), $this->pesan())) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('modal_aksi', site_url('kontak/perbarui/' . $id));
        }

        $model->update($id, $this->ambilData());

        return redirect()->to(site_url('kontak'))->with('sukses', 'Kontak berhasil diperbarui.');
    }

    /**
     * Hapus kontak
     */
    public function hapus(int $id)
    {
        $model = new PerusahaanModel();

        if (! $model->find($id)) {
            return redirect()->to(site_url('kontak'))->with('gagal', 'Kontak tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to(site_url('kontak'))->with('sukses', 'Kontak berhasil dihapus.');
    }

    /**
     * Import kontak dari Excel / CSV
     */
    public function import()
    {
        if (! class_exists(IOFactory::class)) {
            return redirect()->to(site_url('kontak'))->with(
                'gagal',
                'Library PhpSpreadsheet belum terpasang. Jalankan: composer require phpoffice/phpspreadsheet'
            );
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('kontak'))->with('gagal', 'File belum dipilih atau gagal diunggah.');
        }

        if (! in_array(strtolower($file->getClientExtension()), ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to(site_url('kontak'))->with('gagal', 'Format file harus .xlsx, .xls, atau .csv.');
        }

        @set_time_limit(300);

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('kontak'))->with('gagal', 'File tidak dapat dibaca: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return redirect()->to(site_url('kontak'))->with('gagal', 'File kosong atau hanya berisi header.');
        }

        // Peta header -> nama field
        $peta = [
            'KATEGORI'          => 'kategori',
            'NAMA'              => 'nama',
            'NAMA PERUSAHAAN'   => 'nama_perusahaan',
            'PERUSAHAAN'        => 'nama_perusahaan',
            'ALAMAT EMAIL'      => 'email',
            'EMAIL'             => 'email',
            'NO HP'             => 'no_hp',
            'NO. HP'            => 'no_hp',
            'NOMOR HP'          => 'no_hp',
            'KOTA/KABUPATEN'    => 'kota_kabupaten',
            'KOTA / KABUPATEN'  => 'kota_kabupaten',
            'KOTA KABUPATEN'    => 'kota_kabupaten',
            'KOTA'              => 'kota_kabupaten',
            'PROVINSI'          => 'provinsi',
            'WEBSITE'           => 'website',
            'SUMBER DATA'       => 'sumber_data',
            'CATATAN'           => 'catatan',
        ];

        $kolom = [];
        foreach ($rows[0] as $idx => $judul) {
            $kunci = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $judul)));
            if (isset($peta[$kunci])) {
                $kolom[$idx] = $peta[$kunci];
            }
        }

        $adaEmail      = in_array('email', $kolom, true);
        $adaPerusahaan = in_array('nama_perusahaan', $kolom, true) || in_array('nama', $kolom, true);
        if (! $adaEmail || ! $adaPerusahaan) {
            return redirect()->to(site_url('kontak'))->with(
                'gagal',
                'Header tidak sesuai. Kolom wajib: ALAMAT EMAIL dan NAMA PERUSAHAAN (atau NAMA). Unduh template untuk contoh.'
            );
        }

        $perbarui      = (bool) $this->request->getPost('perbarui');
        $kontakModel   = new PerusahaanModel();
        $kategoriModel = new KategoriKontakModel();

        $petaKategori = [];
        foreach ($kategoriModel->findAll() as $kat) {
            $petaKategori[mb_strtolower(trim((string) $kat['nama']))] = (int) $kat['id'];
        }

        $tambah = $ubah = $lewati = 0;
        $detail = [];
        $catat  = static function (string $pesan) use (&$detail) {
            if (count($detail) < 30) {
                $detail[] = $pesan;
            }
        };

        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            $baris = $i + 1;

            $d = [];
            foreach ($kolom as $idx => $field) {
                $d[$field] = trim((string) ($row[$idx] ?? ''));
            }
            if (implode('', $d) === '') {
                continue; // baris kosong
            }

            $email      = strtolower($d['email'] ?? '');
            $perusahaan = $d['nama_perusahaan'] ?? '';
            if ($perusahaan === '') {
                $perusahaan = $d['nama'] ?? '';
            }

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $lewati++;
                $catat("Baris {$baris}: email kosong atau tidak valid.");
                continue;
            }
            if ($perusahaan === '') {
                $lewati++;
                $catat("Baris {$baris}: nama perusahaan kosong.");
                continue;
            }

            try {
                $idKategori = null;
                $namaKat    = $d['kategori'] ?? '';
                if ($namaKat !== '') {
                    $kunciKat = mb_strtolower($namaKat);
                    if (! isset($petaKategori[$kunciKat])) {
                        $petaKategori[$kunciKat] = (int) $kategoriModel->insert(['nama' => $namaKat], true);
                    }
                    $idKategori = $petaKategori[$kunciKat];
                }

                $data = [
                    'kategori_id'     => $idKategori,
                    'nama'            => $d['nama'] ?? '',
                    'nama_perusahaan' => $perusahaan,
                    'email'           => $email,
                    'no_hp'           => $d['no_hp'] ?? '',
                    'kota_kabupaten'  => $d['kota_kabupaten'] ?? '',
                    'provinsi'        => $d['provinsi'] ?? '',
                    'website'         => $d['website'] ?? '',
                    'sumber_data'     => $d['sumber_data'] ?? '',
                    'catatan'         => $d['catatan'] ?? '',
                    'unique_hash'     => hash('sha256', $email),
                ];

                $ada = $kontakModel->select('id')->where('email', $email)->first();

                if ($ada) {
                    if ($perbarui) {
                        $isi = array_filter($data, static fn($v) => $v !== '' && $v !== null);
                        $kontakModel->update($ada['id'], $isi);
                        $ubah++;
                    } else {
                        $lewati++;
                        $catat("Baris {$baris}: email {$email} sudah terdaftar (dilewati).");
                    }
                    continue;
                }

                $data['jumlah_tidak_terkirim'] = 0;
                $kontakModel->insert($data);
                $tambah++;
            } catch (\Throwable $e) {
                $lewati++;
                $catat("Baris {$baris}: gagal disimpan ({$e->getMessage()}).");
            }
        }

        return redirect()->to(site_url('kontak'))
            ->with('sukses', "Import selesai: {$tambah} ditambahkan, {$ubah} diperbarui, {$lewati} dilewati.")
            ->with('detail_import', $detail);
    }

    /**
     * Unduh template Excel untuk import
     */
    public function template()
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->to(site_url('kontak'))->with(
                'gagal',
                'Library PhpSpreadsheet belum terpasang. Jalankan: composer require phpoffice/phpspreadsheet'
            );
        }

        $sheet = new Spreadsheet();
        $ws    = $sheet->getActiveSheet();
        $ws->setTitle('Kontak');

        $ws->fromArray([
            ['KATEGORI', 'NAMA', 'NAMA PERUSAHAAN', 'ALAMAT EMAIL', 'NO HP', 'KOTA/KABUPATEN', 'PROVINSI', 'WEBSITE', 'SUMBER DATA', 'CATATAN'],
            ['Makanan', 'Budi', 'PT Contoh Sejahtera', 'budi@contoh.co.id', '081234567890', 'Malang', 'Jawa Timur', 'contoh.co.id', 'Pameran 2026', ''],
        ], null, 'A1');

        foreach (range('A', 'J') as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }
        $ws->getStyle('A1:J1')->getFont()->setBold(true);

        ob_start();
        (new Xlsx($sheet))->save('php://output');
        $isi = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="template_kontak.xlsx"')
            ->setBody($isi);
    }

    // ==================================================================
    // FITUR PERUSAHAAN (detail, hapus massal, import ke supplier)
    // Route: kontak/detail, kontak/bulk-delete, kontak/import-*
    // ==================================================================

    /**
     * Dapatkan detail perusahaan dalam bentuk JSON untuk Modal
     */
   public function detail($id)
{
    $company = (new PerusahaanModel())->find($id);
    if (! $company) {
        return $this->response->setStatusCode(404)->setJSON([
            'success' => false,
            'error'   => 'Perusahaan tidak ditemukan.',
        ]);
    }

    $products = [];
    $history  = [];
    $sources  = [];

    // Data pendukung opsional: kegagalannya tidak boleh menggagalkan seluruh detail
    try {
        $products = (new ProdukPerusahaanModel())->getByPerusahaan($id);
    } catch (\Throwable $e) {
        log_message('warning', '[detail] Produk tidak dapat dimuat: ' . $e->getMessage());
    }

    try {
        $history = (new RiwayatAnalisisModel())->where('perusahaan_id', $id)->orderBy('id', 'DESC')->findAll(5);
    } catch (\Throwable $e) {
        log_message('warning', '[detail] Riwayat tidak dapat dimuat: ' . $e->getMessage());
    }

    try {
        $sources = (new SumberAnalisisModel())->getByPerusahaan($id);
    } catch (\Throwable $e) {
        log_message('warning', '[detail] Sumber tidak dapat dimuat: ' . $e->getMessage());
    }

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

        return redirect()->to(site_url('kontak'))
            ->with('sukses', 'Data perusahaan berhasil dihapus.');
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

    // ------------------------------------------------------------------

    private function aturan(?int $id = null): array
    {
        $emailUnik = $id !== null
            ? "is_unique[kontak_email.email,id,{$id}]"
            : 'is_unique[kontak_email.email]';
        $tabelKategori = self::TABEL_KATEGORI;

        return [
            'kategori_id'           => "permit_empty|is_natural_no_zero|is_not_unique[{$tabelKategori}.id]",
            'nama'                  => 'permit_empty|max_length[150]',
            'nama_perusahaan'       => 'required|max_length[150]',
            'email'                 => "required|valid_email|max_length[254]|{$emailUnik}",
            'no_hp'                 => 'permit_empty|max_length[20]',
            'website'               => 'permit_empty|max_length[255]',
            'kota_kabupaten'        => 'permit_empty|max_length[100]',
            'provinsi'              => 'permit_empty|max_length[100]',
            'sumber_data'           => 'permit_empty|max_length[150]',
            'jumlah_tidak_terkirim' => 'permit_empty|is_natural',
        ];
    }

    private function pesan(): array
    {
        return [
            'nama_perusahaan' => ['required' => 'Nama perusahaan wajib diisi.'],
            'email'           => [
                'required'    => 'Email wajib diisi.',
                'valid_email' => 'Format email tidak valid.',
                'is_unique'   => 'Email ini sudah terdaftar.',
            ],
            'kategori_id'     => ['is_not_unique' => 'Kategori yang dipilih tidak ditemukan.'],
        ];
    }

    private function ambilData(): array
    {
        $teks     = fn(string $k): string => trim((string) $this->request->getPost($k));
        $kategori = $teks('kategori_id');
        $email    = strtolower($teks('email'));

        return [
            'kategori_id'             => $kategori !== '' ? (int) $kategori : null,
            'nama'                    => $teks('nama'),
            'nama_perusahaan'         => $teks('nama_perusahaan'),
            'email'                   => $email,
            'no_hp'                   => $teks('no_hp'),
            'website'                 => $teks('website'),
            'kota_kabupaten'          => $teks('kota_kabupaten'),
            'provinsi'                => $teks('provinsi'),
            'sumber_data'             => $teks('sumber_data'),
            'catatan'                 => $teks('catatan'),
            'jumlah_tidak_terkirim'   => (int) $teks('jumlah_tidak_terkirim'),
            'produk_yang_dijual_auto' => $teks('produk_perusahaan'),
            'unique_hash'             => hash('sha256', $email),
        ];
    }
}