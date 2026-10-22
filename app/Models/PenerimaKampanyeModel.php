<?php

namespace App\Models;

use CodeIgniter\Model;

class PenerimaKampanyeModel extends Model
{
    protected $table            = 'penerima_kampanye';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kampanye_email_id', 'kontak_email_id', 'alamat_email_penerima',
        'nama_penerima', 'status_pengiriman', 'jumlah_percobaan',
        'id_pesan_penyedia', 'terkirim_pada', 'pesan_kesalahan',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'dibuat_pada';
    protected $updatedField  = 'diperbarui_pada';

    // Status 'mengirim' yang lebih lama dari 5 menit dianggap macet & boleh diambil ulang
    private function kondisiAntrian(): string
    {
        $batas = date('Y-m-d H:i:s', time() - 300);
        return "(status_pengiriman = 'menunggu'"
            . " OR (status_pengiriman = 'gagal' AND jumlah_percobaan < 3)"
            . " OR (status_pengiriman = 'mengirim' AND diperbarui_pada < '{$batas}'))";
    }

    public function getByKampanye(int $kampanyeId)
    {
        return $this->where('kampanye_email_id', $kampanyeId)
            ->orderBy('id', 'ASC')->findAll();
    }

    public function getPendingBatch(int $kampanyeId, int $limit = 5)
    {
        return $this->where('kampanye_email_id', $kampanyeId)
            ->where($this->kondisiAntrian(), null, false)
            ->orderBy('id', 'ASC')
            ->findAll($limit);
    }

    // Sisa = masih antre + sedang dikirim proses lain
    public function getPendingCount(int $kampanyeId): int
    {
        return $this->where('kampanye_email_id', $kampanyeId)
            ->where("(status_pengiriman IN ('menunggu','mengirim')"
                . " OR (status_pengiriman = 'gagal' AND jumlah_percobaan < 3))", null, false)
            ->countAllResults();
    }

    /**
     * "Kunci" 1 penerima agar tidak dikirim ganda oleh 2 proses (mis. 2 tab / cron + browser).
     */
    public function klaim(int $id): bool
    {
        $this->db->table($this->table)
            ->where('id', $id)
            ->where($this->kondisiAntrian(), null, false)
            ->update([
                'status_pengiriman' => 'mengirim',
                'diperbarui_pada'   => date('Y-m-d H:i:s'),
            ]);

        return $this->db->affectedRows() > 0;
    }
}