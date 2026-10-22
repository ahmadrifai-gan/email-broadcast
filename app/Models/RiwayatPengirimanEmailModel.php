<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatPengirimanEmailModel extends Model
{
    protected $table            = 'riwayat_pengiriman_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'penerima_kampanye_id', 'jenis_aktivitas', 'penyedia_email',
        'id_pesan_penyedia', 'detail_aktivitas', 'terjadi_pada',
    ];
    protected $useTimestamps = false;

    public function getByPenerima(int $penerimaId)
    {
        return $this->where('penerima_kampanye_id', $penerimaId)
            ->orderBy('terjadi_pada', 'DESC')->findAll();
    }
}