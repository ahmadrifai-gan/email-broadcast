<?php

namespace App\Models;

use CodeIgniter\Model;

class PerusahaanModel extends Model
{
    protected $table            = 'perusahaan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'provinsi', // Kolom baru ditambahkan
        'nama_perusahaan', 
        'kbli', 
        'jenis', 
        'kecamatan', 
        'kabupaten', 
        'no_telepon', 
        'email', 
        'website'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}