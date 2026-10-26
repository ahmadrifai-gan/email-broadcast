<?php

namespace App\Models;

use CodeIgniter\Model;

class Kontakmodel extends Model
{
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