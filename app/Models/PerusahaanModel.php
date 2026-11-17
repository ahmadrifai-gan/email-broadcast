<?php

namespace App\Models;

use CodeIgniter\Model;

class PerusahaanModel extends Model
{
    protected $table            = 'kontak_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nomor_excel',
        'nama_perusahaan',
        'main_categories',
        'sub_categories',
        'company_profile',
        'website',
        'link_catalog',
        'produk_yang_dijual_auto',
        'status_analisis',
        'terakhir_dianalisis',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Hitung statistik untuk Dashboard
     */
    public function getStatistics(): array
    {
        $db = \Config\Database::connect();

        $totalPerusahaan = $this->countAllResults(false);

        $counts = $this->select('status_analisis, COUNT(*) as total')
            ->groupBy('status_analisis')
            ->findAll();

        $stats = [
            'total'          => $totalPerusahaan,
            'belum'          => 0,
            'sukses'         => 0,
            'gagal'          => 0,
            'manual_edit'    => 0,
            'total_dianalisis' => 0,
            'total_produk'   => 0,
        ];

        foreach ($counts as $row) {
            $status = $row['status_analisis'];
            if ($status === 'belum_dianalisis') {
                $stats['belum'] = (int) $row['total'];
            } elseif (isset($stats[$status])) {
                $stats[$status] = (int) $row['total'];
            }
        }

        $stats['total_dianalisis'] = $stats['sukses'] + $stats['manual_edit'];

        // Hitung total produk di tabel produk_perusahaan
        $stats['total_produk'] = (int) $db->table('produk_perusahaan')->countAll();

        return $stats;
    }

    /**
     * Filter list perusahaan dengan pagination dan search
     */
    public function getFiltered(array $filter = [], int $limit = 20, int $offset = 0): array
    {
        $builder = $this->builder();

        if (!empty($filter['status'])) {
            $builder->where('status_analisis', $filter['status']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $builder->groupStart()
                ->like('nama_perusahaan', $search)
                ->orLike('main_categories', $search)
                ->orLike('sub_categories', $search)
                ->orLike('nomor_excel', $search)
                ->orLike('website', $search)
                ->groupEnd();
        }

        return $builder->orderBy('id', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Count untuk pagination filter
     */
    public function countFiltered(array $filter = []): int
    {
        $builder = $this->builder();

        if (!empty($filter['status'])) {
            $builder->where('status_analisis', $filter['status']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $builder->groupStart()
                ->like('nama_perusahaan', $search)
                ->orLike('main_categories', $search)
                ->orLike('sub_categories', $search)
                ->orLike('nomor_excel', $search)
                ->orLike('website', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }
}
