<?php

namespace App\Models;

use CodeIgniter\Model;

class SmtpSettingModel extends Model
{
    protected $table            = 'smtp_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'nama', 'host', 'username', 'password',
        'port', 'crypto', 'from_email', 'from_name', 'is_active',
    ];

    protected $validationRules = [
        'nama'       => 'required|min_length[3]|max_length[100]',
        'host'       => 'required|max_length[150]',
        'username'   => 'required|max_length[150]',
        'password'   => 'required|max_length[500]',
        'port'       => 'required|integer|greater_than[0]|less_than[65536]',
        'crypto'     => 'required|in_list[tls,ssl,none]',
        'from_email' => 'required|valid_email|max_length[150]',
        'from_name'  => 'required|max_length[100]',
    ];

    protected $validationMessages = [
        'nama'       => ['required' => 'Nama konfigurasi wajib diisi.'],
        'host'       => ['required' => 'Host SMTP wajib diisi.'],
        'username'   => ['required' => 'Username SMTP wajib diisi.'],
        'password'   => ['required' => 'Password SMTP wajib diisi.'],
        'port'       => ['required' => 'Port wajib diisi.'],
        'from_email' => ['required' => 'Email pengirim wajib diisi.',
                         'valid_email' => 'Format email tidak valid.'],
        'from_name'  => ['required' => 'Nama pengirim wajib diisi.'],
    ];

    public function getActive(): ?array
    {
        return $this->where('is_active', 1)->first();
    }

    /** Aktifkan satu, nonaktifkan yang lain (atomik). */
    public function setActive(int $id): bool
    {
        $db = $this->db;
        $db->transBegin();
        $db->table($this->table)->update(['is_active' => 0]);
        $this->update($id, ['is_active' => 1]);
        $db->transCommit();

        return $db->transStatus() !== false;
    }
}