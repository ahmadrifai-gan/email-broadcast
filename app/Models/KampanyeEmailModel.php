<?php

namespace App\Models;

use CodeIgniter\Model;

class KampanyeEmailModel extends Model
{
    protected $table            = 'kampanye_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'template_email_id', 'nama_kampanye', 'subjek_email', 'status',
        'jadwal_pengiriman', 'mulai_dikirim_pada', 'selesai_dikirim_pada',
        'jumlah_penerima', 'jumlah_berhasil', 'jumlah_gagal',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'dibuat_pada';
    protected $updatedField  = 'diperbarui_pada';

    public function getWithTemplate()
    {
        return $this->select('kampanye_email.*, template_email.nama_template')
            ->join('template_email', 'template_email.id = kampanye_email.template_email_id', 'left')
            ->orderBy('kampanye_email.id', 'DESC')
            ->findAll();
    }
}