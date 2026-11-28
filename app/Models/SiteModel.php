<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteModel extends Model
{
    protected $table          = 'sites';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $allowedFields  = ['url', 'status', 'note', 'processed_at'];
    protected $useTimestamps  = true;
}