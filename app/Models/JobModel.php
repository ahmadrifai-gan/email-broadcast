<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table         = 'extract_jobs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'status', 'api_key', 'model',
        'rate', 'rate_min', 'rate_max', 'mode', 'hours',
        'batch_size', 'total_limit', 'per_hour_limit', 'delay_account',
        'token_limit', 'token_sisa', 'auto_fill',
        'site_ids', 'stop_requested',
        'processed_count', 'found_count', 'tokens_used', 'message',
        'current_url', 'started_at', 'deadline_at',
        'next_run_at', 'wait_reason', 'hour_window_start', 'hour_count',
        'config', 'hour_limit_now',
    ];

    /**
     * Simpan API key terenkripsi jika `encryption.key` sudah di-set di .env
     * (php spark key:generate). Kalau belum, disimpan apa adanya.
     */
    public static function packKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }
        try {
            return 'enc:' . base64_encode(service('encrypter')->encrypt($key));
        } catch (\Throwable $e) {
            return $key;
        }
    }

    public static function unpackKey(?string $stored): string
    {
        $stored = trim((string) $stored);
        if (! str_starts_with($stored, 'enc:')) {
            return $stored;
        }
        try {
            $raw = base64_decode(substr($stored, 4), true);
            return $raw === false ? '' : (string) service('encrypter')->decrypt($raw);
        } catch (\Throwable $e) {
            return '';
        }
    }
}