<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriKontakModel extends Model
{
    protected $table         = 'kategori';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nama'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dibuat_pada';
    protected $updatedField  = 'diperbarui_pada';
}