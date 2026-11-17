<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatAnalisisModel extends Model
{
    protected $table            = 'riwayat_analisis';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'perusahaan_id',
        'status',
        'sumber_digunakan',
        'hasil_mentah',
        'confidence',
        'pesan_error',
        'waktu_analisis',
    ];

    protected $useTimestamps = false;

    /**
     * Mengambil riwayat terbaru beserta nama perusahaan
     */
    public function getRecentWithPerusahaan(int $limit = 10): array
    {
        return $this->select('riwayat_analisis.*, perusahaan.nama_perusahaan, perusahaan.nomor_excel')
            ->join('perusahaan', 'perusahaan.id = riwayat_analisis.perusahaan_id', 'left')
            ->orderBy('riwayat_analisis.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Filter riwayat dengan pagination
     */
    public function getFiltered(array $filter = [], int $limit = 20, int $offset = 0): array
    {
        $builder = $this->select('riwayat_analisis.*, perusahaan.nama_perusahaan, perusahaan.nomor_excel')
            ->join('perusahaan', 'perusahaan.id = riwayat_analisis.perusahaan_id', 'left');

        if (!empty($filter['status'])) {
            $builder->where('riwayat_analisis.status', $filter['status']);
        }

        if (!empty($filter['perusahaan_id'])) {
            $builder->where('riwayat_analisis.perusahaan_id', $filter['perusahaan_id']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $builder->groupStart()
                ->like('perusahaan.nama_perusahaan', $search)
                ->orLike('riwayat_analisis.pesan_error', $search)
                ->orLike('riwayat_analisis.sumber_digunakan', $search)
                ->groupEnd();
        }

        return $builder->orderBy('riwayat_analisis.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Hitung total untuk filter riwayat
     */
    public function countFiltered(array $filter = []): int
    {
        $builder = $this->join('perusahaan', 'perusahaan.id = riwayat_analisis.perusahaan_id', 'left');

        if (!empty($filter['status'])) {
            $builder->where('riwayat_analisis.status', $filter['status']);
        }

        if (!empty($filter['perusahaan_id'])) {
            $builder->where('riwayat_analisis.perusahaan_id', $filter['perusahaan_id']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $builder->groupStart()
                ->like('perusahaan.nama_perusahaan', $search)
                ->orLike('riwayat_analisis.pesan_error', $search)
                ->orLike('riwayat_analisis.sumber_digunakan', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }
}

