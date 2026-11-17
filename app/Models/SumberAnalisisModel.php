<?php

namespace App\Models;

use CodeIgniter\Model;

class SumberAnalisisModel extends Model
{
    protected $table            = 'sumber_analisis';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kontak_email_id',
        'jenis_sumber',
        'url_sumber',
        'status_pengambilan',
        'isi_sumber',
        'pesan_error',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Dapatkan sumber yang dicatat untuk perusahaan
     */
    public function getByPerusahaan(int $perusahaanId): array
    {
        return $this->where('kontak_email_id', $perusahaanId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Log sumber analisis
     */
    public function logSource(
        int $perusahaanId,
        string $jenis,
        ?string $url,
        string $status,
        ?string $isi = null,
        ?string $error = null
    ): int {
        return $this->insert([
            'kontak_email_id'      => $perusahaanId,
            'jenis_sumber'       => $jenis,
            'url_sumber'         => $url,
            'status_pengambilan' => $status,
            'isi_sumber'         => $isi ? mb_substr($isi, 0, 50000) : null,
            'pesan_error'        => $error,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);
    }
}

