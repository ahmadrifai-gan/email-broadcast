<?php

namespace App\Models;

use CodeIgniter\Model;

class Kontakmodel extends Model
{
    public function catatTidakTerkirim(string $email): void
    {
        $this->builder()
            ->where('email', strtolower($email))
            ->set('jumlah_tidak_terkirim', 'jumlah_tidak_terkirim + 1', false)
            ->update();
    }

    protected $table         = 'kontak_email';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'kategori_id',
        'nama',
        'nama_perusahaan',
        'no_hp',
        'email',
        'website',
        'kota_kabupaten',
        'provinsi',
        'produk_perusahaan',
        'sumber_data',
        'catatan',
        'jumlah_tidak_terkirim',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dibuat_pada';
    protected $updatedField  = 'diperbarui_pada';
}