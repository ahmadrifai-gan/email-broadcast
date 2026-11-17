<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table         = 'extract_jobs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    
    // Pastikan semua kolom ini ada di tabel extract_jobs
    protected $allowedFields = [
        'status', 'api_key', 'model', 'rate', 'hours', 'token_limit', 'auto_fill', 'site_ids',
        'stop_requested', 'processed_count', 'found_count', 'tokens_used', 'message',
        'current_url', 'started_at', 'deadline_at',
    ];
}