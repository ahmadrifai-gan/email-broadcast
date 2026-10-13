<?php

namespace App\Models;

use CodeIgniter\Model;

class KontakModel extends Model
{
    protected $table            = 'kontak_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama_perusahaan', 'email', 'domisili'];

    // Validasi (email unik, sesuai ikon kunci di database)
    protected $validationRules = [
        'id'              => 'permit_empty|is_natural_no_zero', // wajib ada untuk placeholder {id}
        'nama_perusahaan' => 'permit_empty|max_length[150]',
        'email'           => 'permit_empty|valid_email|max_length[254]|is_unique[kontak_email.email,id,{id}]',
        'domisili'        => 'permit_empty|max_length[150]',
    ];

    protected $validationMessages = [
        'email' => [
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Email sudah terdaftar.',
        ],
    ];

    // ---------- CRUD ----------

    // Create
    public function tambah(array $data)
    {
        return $this->insert($data);
    }

    // Read (semua data)
    public function semua()
    {
        return $this->orderBy('id', 'DESC')->findAll();
    }

    // Read (satu data)
    public function cari(int $id)
    {
        return $this->find($id);
    }

    // Update
    public function ubah(int $id, array $data)
    {
        return $this->update($id, $data);
    }

    // Delete
    public function hapus(int $id)
    {
        return $this->delete($id);
    }
}