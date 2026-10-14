<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriTemplateEmailModel extends Model
{
    protected $table            = 'kategori_template_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama_kategori',
        'deskripsi',
        'status',
        'dibuat_pada',
        'diperbarui_pada',
    ];

    protected $useTimestamps = false;
}