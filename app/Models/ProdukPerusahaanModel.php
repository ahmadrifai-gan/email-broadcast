<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukPerusahaanModel extends Model
{
    protected $table            = 'produks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kontak_email_id',
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
        return $this->where('kontak_email_id', $perusahaanId)
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }

    /**
     * Hapus produk lama dan simpan daftar produk baru untuk perusahaan
     */
    public function replaceProducts(int $perusahaanId, array $products): void
    {
        $this->where('kontak_email_id', $perusahaanId)->delete();

        $urutan = 1;
        $batch = [];
        $now = date('Y-m-d H:i:s');

        foreach ($products as $p) {
            $batch[] = [
                'kontak_email_id'  => $perusahaanId,
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

