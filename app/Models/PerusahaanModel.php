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
        'kategori_id',
        'nama',
        'nama_perusahaan',
        'email',
        'kota_kabupaten',
        'no_hp',
        'website',
        'provinsi',
        'sumber_data',
        'catatan',
        'jumlah_tidak_terkirim',
        'main_categories',
        'sub_categories',
        'company_profile',
        'link_catalog',
        'produk_yang_dijual_auto',
        'status_analisis',
        'terakhir_dianalisis',
        'keyword',
        'alamat',
        'nomor',
        'maps_url',
        'wa_status',
        'wa_checked_at',
        'website_status',
        'website_checked_at',
        'unique_hash',
    ];

    // Dates
    // Kolom dibuat_pada & diperbarui_pada di tabel adalah kolom lama,
    // timestamp otomatis memakai created_at & updated_at.
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

        $totalPerusahaan = $this->countAllResults();

        $counts = $this->select('status_analisis, COUNT(*) as total')
            ->groupBy('status_analisis')
            ->findAll();

        $stats = [
            'total'            => $totalPerusahaan,
            'belum'            => 0,
            'sukses'           => 0,
            'gagal'            => 0,
            'manual_edit'      => 0,
            'total_dianalisis' => 0,
            'total_produk'     => 0,
        ];

        foreach ($counts as $row) {
            $status = $row['status_analisis'];
            if ($status === 'belum_dianalisis') {
                $stats['belum'] = (int) $row['total'];
            } elseif ($status === 'manual') {
                // enum di database bernama 'manual', key view tetap 'manual_edit'
                $stats['manual_edit'] = (int) $row['total'];
            } elseif (isset($stats[$status])) {
                $stats[$status] = (int) $row['total'];
            }
        }

        $stats['total_dianalisis'] = $stats['sukses'] + $stats['manual_edit'];

        // Hitung total produk di tabel produk_perusahaan (jika tabelnya sudah ada)
        if ($db->tableExists('produk_perusahaan')) {
            $stats['total_produk'] = (int) $db->table('produk_perusahaan')->countAll();
        }

        return $stats;
    }

    /**
     * Terapkan filter status & search ke builder
     */
    private function applyFilter($builder, array $filter)
    {
        if (!empty($filter['status'])) {
            $builder->where('status_analisis', $filter['status']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $builder->groupStart()
                ->like('nama_perusahaan', $search)
                ->orLike('nama', $search)
                ->orLike('email', $search)
                ->orLike('main_categories', $search)
                ->orLike('sub_categories', $search)
                ->orLike('nomor_excel', $search)
                ->orLike('website', $search)
                ->groupEnd();
        }

        return $builder;
    }

    /**
     * Filter list perusahaan dengan pagination dan search
     */
    public function getFiltered(array $filter = [], int $limit = 20, int $offset = 0): array
    {
        $builder = $this->applyFilter($this->builder(), $filter);

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
        $builder = $this->applyFilter($this->builder(), $filter);

        return $builder->countAllResults();
    }
}