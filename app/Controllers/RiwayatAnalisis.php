<?php

namespace App\Controllers\admin\analyzer;

use App\Controllers\admin\AdminAuthBase;
use App\Models\RiwayatAnalisisModel;

class RiwayatAnalisis extends AdminAuthBase
{
    public function index()
    {
        if ($auth = $this->checkAuth()) {
            return $auth;
        }

        $riwayatModel = new RiwayatAnalisisModel();

        $status = $this->request->getGet('status') ?: '';
        $search = $this->request->getGet('search') ?: '';
        $page   = (int) ($this->request->getGet('page') ?: 1);
        if ($page < 1) $page = 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $filter = [
            'status' => $status,
            'search' => $search,
        ];

        $records = $riwayatModel->getFiltered($filter, $perPage, $offset);
        $totalRows = $riwayatModel->countFiltered($filter);
        $totalPages = ceil($totalRows / $perPage);

        $data = [
            'title'       => 'Riwayat Analisis - AI Product Analyzer',
            'active_menu' => 'analyzer_riwayat',
            'records'     => $records,
            'totalRows'   => $totalRows,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'filter'      => $filter,
        ];

        return view('admin/analyzer/riwayat/index', $data);
    }

    /**
     * Dapatkan detail log riwayat analisis untuk modal inspeksi (JSON)
     */
    public function detail($id)
    {
        if ($auth = $this->checkAuth()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $riwayatModel = new RiwayatAnalisisModel();
        $record = $riwayatModel->select('riwayat_analisis.*, perusahaan.nama_perusahaan, perusahaan.website, perusahaan.link_catalog, perusahaan.company_profile')
            ->join('perusahaan', 'perusahaan.id = riwayat_analisis.perusahaan_id', 'left')
            ->find($id);

        if (!$record) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Data riwayat tidak ditemukan']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $record,
        ]);
    }

    /**
     * Hapus semua log riwayat analisis
     */
    public function clear()
    {
        if ($auth = $this->checkAuth()) {
            return $auth;
        }

        $riwayatModel = new RiwayatAnalisisModel();
        $riwayatModel->emptyTable();

        return redirect()->to(base_url('admin/analyzer/riwayat'))
            ->with('success', 'Seluruh riwayat analisis berhasil dibersihkan.');
    }
}

