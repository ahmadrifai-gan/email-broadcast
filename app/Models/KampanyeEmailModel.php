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
        'template_email_id',
        'smtp_setting_id',              // ← baru: SMTP per-kampanye (NULL = pakai SMTP aktif default)
        'nama_kampanye',
        'subjek_email',
        'status',
        'tipe_pengiriman',
        'jadwal_pengiriman',
        'total_putaran',
        'jumlah_putaran',
        'kecepatan_per_menit',
        'kecepatan_maks',
        'mulai_dikirim_pada',
        'selesai_dikirim_pada',
        'jumlah_penerima',
        'jumlah_berhasil',
        'jumlah_gagal',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'dibuat_pada';
    protected $updatedField  = 'diperbarui_pada';

    /**
     * Daftar kampanye beserta nama template dan info SMTP yang dipakai.
     * - smtp_nama / smtp_from: dari smtp_settings jika kampanye memilih SMTP tertentu
     * - NULL jika kampanye tidak memilih (artinya memakai SMTP aktif default / .env)
     */
    public function getWithTemplate()
    {
        return $this->select('kampanye_email.*, template_email.nama_template,
                              smtp_settings.nama       AS smtp_nama,
                              smtp_settings.from_email AS smtp_from')
            ->join('template_email', 'template_email.id = kampanye_email.template_email_id', 'left')
            ->join('smtp_settings',  'smtp_settings.id  = kampanye_email.smtp_setting_id',   'left')
            ->orderBy('kampanye_email.id', 'DESC')
            ->findAll();
    }
}