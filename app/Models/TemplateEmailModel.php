<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateEmailModel extends Model
{
    protected $table            = 'template_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true; 
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'kategori_template_id', 
        'nama_template', 
        'subjek_email', 
        'isi_html', 
        'isi_teks', 
        'status', 
        'versi', 
        'status_pemeriksaan',
        'attachment_pdf_url', // TAMBAHKAN INI
        'tipe_pdf'            // TAMBAHKAN INI
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dibuat_pada';
    protected $updatedField  = 'diperbarui_pada';
    protected $deletedField  = 'dihapus_pada';

    public function getAllWithKategori()
    {
        return $this->select('template_email.*, kategori_template_email.nama_kategori')
                    ->join('kategori_template_email', 'kategori_template_email.id = template_email.kategori_template_id', 'left')
                    ->findAll();
    }
}