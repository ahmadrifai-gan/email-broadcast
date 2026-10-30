<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalKampanyeModel extends Model
{
    protected $table            = 'jadwal_kampanye';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kampanye_email_id', 'putaran_ke', 'waktu_kirim', 'status', 'jumlah_penerima',
        'dimulai_pada', 'selesai_pada', 'jumlah_berhasil', 'jumlah_gagal',
        'dibuat_pada', 'diperbarui_pada',
    ];
    protected $useTimestamps = false;

    public function getByKampanye(int $kampanyeId): array
    {
        return $this->where('kampanye_email_id', $kampanyeId)->orderBy('putaran_ke', 'ASC')->findAll();
    }

    /** Putaran berikutnya yang masih menunggu (urutan terkecil). */
    public function getBerikutnya(int $kampanyeId): ?array
    {
        return $this->where('kampanye_email_id', $kampanyeId)
            ->where('status', 'menunggu')
            ->orderBy('putaran_ke', 'ASC')
            ->first();
    }

    public function getBerjalan(int $kampanyeId): ?array
    {
        return $this->where('kampanye_email_id', $kampanyeId)
            ->where('status', 'berjalan')
            ->orderBy('putaran_ke', 'ASC')
            ->first();
    }

    public function hitungSelesai(int $kampanyeId): int
    {
        return $this->where('kampanye_email_id', $kampanyeId)->where('status', 'selesai')->countAllResults();
    }
}