<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanAiModel extends Model
{
    protected $table            = 'pengaturan_ai';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_model',
        'provider',
        'instruksi_ai',
        'temperature',
        'max_tokens',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan konfigurasi AI aktif (atau baris pertama)
     * Catatan: api_key TIDAK PERNAH dibaca dari database sesuai kebijakan keamanan.
     */
    public function getActiveConfig(): ?array
    {
        $config = $this->where('status', 'aktif')->orderBy('id', 'ASC')->first();
        if (!$config) {
            $config = $this->orderBy('id', 'ASC')->first();
        }

        if ($config) {
            unset($config['api_key']);
        }

        return $config;
    }
}

