<?php

namespace App\Models;

/**
 * RingkasanKampanyeModel
 * ---------------------------------------------------------------
 * Query untuk menu "Ringkasan Kampanye" (read-only, SQL langsung dengan binding "?").
 *
 * Definisi:
 *   - Tanggal kampanye = COALESCE(mulai_dikirim_pada, jadwal_pengiriman, dibuat_pada)
 *   - Berhasil         = penerima_kampanye.status_pengiriman = 'terkirim'
 *   - Gagal            = penerima_kampanye.status_pengiriman = 'gagal'
 *   - Menunggu         = status selain 'terkirim' dan 'gagal'
 */
class RingkasanKampanyeModel
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    private function rows(string $sql, array $binds = []): array
    {
        return $this->db->query($sql, $binds)->getResultArray();
    }

    /**
     * Daftar kampanye + hasil pengirimannya.
     * Angka penerima dihitung dari penerima_kampanye (sumber data sebenarnya);
     * bila kampanye belum punya baris penerima, dipakai counter di kampanye_email.
     */
    public function daftarKampanye(?string $dari, ?string $sampai): array
    {
        $tgl = 'COALESCE(k.mulai_dikirim_pada, k.jadwal_pengiriman, k.dibuat_pada)';
        $w = [];
        $b = [];
        if ($dari)   { $w[] = "$tgl >= ?"; $b[] = $dari . ' 00:00:00'; }
        if ($sampai) { $w[] = "$tgl <= ?"; $b[] = $sampai . ' 23:59:59'; }
        $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';

        $sql = "
            SELECT k.id, k.nama_kampanye, k.subjek_email, k.status, k.tipe_pengiriman,
                   k.total_putaran, k.jumlah_putaran,
                   k.jadwal_pengiriman, k.mulai_dikirim_pada, k.selesai_dikirim_pada, k.dibuat_pada,
                   $tgl AS tanggal_acuan,
                   t.nama_template,
                   s.nama AS smtp_nama,
                   COALESCE(p.total,    k.jumlah_penerima) AS total_penerima,
                   COALESCE(p.berhasil, k.jumlah_berhasil) AS berhasil,
                   COALESCE(p.gagal,    k.jumlah_gagal)    AS gagal,
                   COALESCE(p.menunggu, 0)                 AS menunggu,
                   TIMESTAMPDIFF(SECOND, k.mulai_dikirim_pada, k.selesai_dikirim_pada) AS durasi_detik
            FROM kampanye_email k
            LEFT JOIN template_email t ON t.id = k.template_email_id
            LEFT JOIN smtp_settings  s ON s.id = k.smtp_setting_id
            LEFT JOIN (
                SELECT kampanye_email_id,
                       COUNT(*) AS total,
                       SUM(status_pengiriman = 'terkirim') AS berhasil,
                       SUM(status_pengiriman = 'gagal')    AS gagal,
                       SUM(status_pengiriman NOT IN ('terkirim','gagal')) AS menunggu
                FROM penerima_kampanye
                GROUP BY kampanye_email_id
            ) p ON p.kampanye_email_id = k.id
            $where
            ORDER BY $tgl DESC, k.id DESC
        ";

        return $this->rows($sql, $b);
    }

    /** Aktivitas percobaan kirim per hari (dari riwayat_pengiriman_email) untuk kampanye tertentu. */
    public function aktivitasHarian(array $kampanyeIds): array
    {
        $kampanyeIds = array_values(array_filter(array_map('intval', $kampanyeIds)));
        if (!$kampanyeIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($kampanyeIds), '?'));

        $sql = "
            SELECT DATE(r.terjadi_pada) AS tanggal,
                   SUM(r.jenis_aktivitas = 'terkirim') AS terkirim,
                   SUM(r.jenis_aktivitas = 'gagal')    AS gagal
            FROM riwayat_pengiriman_email r
            JOIN penerima_kampanye pk ON pk.id = r.penerima_kampanye_id
            WHERE pk.kampanye_email_id IN ($in)
            GROUP BY DATE(r.terjadi_pada)
            ORDER BY tanggal ASC
        ";

        return $this->rows($sql, $kampanyeIds);
    }
}