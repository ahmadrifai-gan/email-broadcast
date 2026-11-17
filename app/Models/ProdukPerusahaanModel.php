<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukPerusahaanModel extends Model
{
    protected $table            = 'produk_perusahaan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'perusahaan_id',
        'nama_produk_id',
        'nama_produk_en',
        'urutan',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan produk berdasarkan ID perusahaan urut berdasarkan urutan
     */
    public function getByPerusahaan(int $perusahaanId): array
    {
        return $this->where('perusahaan_id', $perusahaanId)
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }

    /**
     * Hapus produk lama dan simpan daftar produk baru untuk perusahaan
     */
    public function replaceProducts(int $perusahaanId, array $products): void
    {
        $this->where('perusahaan_id', $perusahaanId)->delete();

        $urutan = 1;
        $batch = [];
        $now = date('Y-m-d H:i:s');

        foreach ($products as $p) {
            $batch[] = [
                'perusahaan_id'  => $perusahaanId,
                'nama_produk_id' => trim($p['id'] ?? $p['nama_produk_id'] ?? ''),
                'nama_produk_en' => trim($p['en'] ?? $p['nama_produk_en'] ?? ''),
                'urutan'         => $urutan++,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }
    }
}

