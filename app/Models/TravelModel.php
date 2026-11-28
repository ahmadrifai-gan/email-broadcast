<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelModel extends Model
{
    // UBAH DARI 'travel' MENJADI 'kontak_email'
    protected $table            = 'kontak_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // Sesuaikan allowedFields dengan kolom yang ada di kontak_email
    protected $allowedFields    = [
        'nama_perusahaan', 
        'email', 
        'no_hp', 
        'website', 
        'catatan', // Alamat disimpan di sini
        'sub_categories', // Keyword disimpan di sini
        'sumber_data',
        'dibuat_pada',
        'diperbarui_pada',
        // Kolom tambahan dari Gmaps yang sudah digabung
        'gmaps_id', 'keyword', 'alamat', 'nomor', 'maps_url', 
        'wa_status', 'wa_checked_at', 'website_status', 'website_checked_at', 'unique_hash'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dibuat_pada'; // Sesuaikan dengan nama kolom Anda
    protected $updatedField  = 'diperbarui_pada';
}