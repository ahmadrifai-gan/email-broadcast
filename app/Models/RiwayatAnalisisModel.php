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
        'kontak_email_id',
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
     * (relasi: riwayat_analisis.kontak_email_id -> kontak_email.id)
     */
    public function getRecentWithPerusahaan(int $limit = 10): array
    {
        return $this->select('riwayat_analisis.*, kontak_email.nama_perusahaan, kontak_email.nomor_excel')
            ->join('kontak_email', 'kontak_email.id = riwayat_analisis.kontak_email_id', 'left')
            ->orderBy('riwayat_analisis.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Filter riwayat dengan pagination
     */
    public function getFiltered(array $filter = [], int $limit = 20, int $offset = 0): array
    {
        $builder = $this->select('riwayat_analisis.*, kontak_email.nama_perusahaan, kontak_email.nomor_excel')
            ->join('kontak_email', 'kontak_email.id = riwayat_analisis.kontak_email_id', 'left');

        $this->applyFilter($builder, $filter);

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
        $builder = $this->join('kontak_email', 'kontak_email.id = riwayat_analisis.kontak_email_id', 'left');

        $this->applyFilter($builder, $filter);

        return $builder->countAllResults();
    }

    /**
     * Filter bersama untuk getFiltered() dan countFiltered()
     */
    private function applyFilter($builder, array $filter): void
    {
        if (!empty($filter['status'])) {
            $builder->where('riwayat_analisis.status', $filter['status']);
        }

        if (!empty($filter['kontak_email_id'])) {
            $builder->where('riwayat_analisis.kontak_email_id', $filter['kontak_email_id']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $builder->groupStart()
                ->like('kontak_email.nama_perusahaan', $search)
                ->orLike('riwayat_analisis.pesan_error', $search)
                ->orLike('riwayat_analisis.sumber_digunakan', $search)
                ->groupEnd();
        }
    }
}