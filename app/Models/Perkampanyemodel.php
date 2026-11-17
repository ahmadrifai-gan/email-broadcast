<?php

namespace App\Models;

/**
 * PerKampanyeModel
 * ---------------------------------------------------------------
 * Query untuk menu "Laporan Per Kampanye" (read-only, SQL langsung dengan binding "?").
 */
class PerKampanyeModel
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

    private function row(string $sql, array $binds = []): ?array
    {
        $r = $this->db->query($sql, $binds)->getRowArray();
        return $r ?: null;
    }

    public function opsiKampanye(): array
    {
        return $this->rows('SELECT id, nama_kampanye, status, jumlah_penerima, dibuat_pada
                            FROM kampanye_email ORDER BY id DESC');
    }

    public function kampanye(int $id): ?array
    {
        return $this->row("
            SELECT k.*, t.nama_template, s.nama AS smtp_nama, s.from_email AS smtp_from
            FROM kampanye_email k
            LEFT JOIN template_email t ON t.id = k.template_email_id
            LEFT JOIN smtp_settings  s ON s.id = k.smtp_setting_id
            WHERE k.id = ?
        ", [$id]);
    }

    public function ringkasPenerima(int $id): array
    {
        $r = $this->row("
            SELECT COUNT(*) AS total,
                   COALESCE(SUM(status_pengiriman = 'terkirim'), 0) AS berhasil,
                   COALESCE(SUM(status_pengiriman = 'gagal'), 0)    AS gagal,
                   COALESCE(SUM(status_pengiriman NOT IN ('terkirim','gagal')), 0) AS menunggu,
                   COALESCE(SUM(jumlah_percobaan), 0) AS percobaan
            FROM penerima_kampanye
            WHERE kampanye_email_id = ?
        ", [$id]);

        $r = is_array($r) ? $r : [];
        $result = [
            'total'     => (int) ($r['total']     ?? 0),
            'berhasil'  => (int) ($r['berhasil']  ?? 0),
            'gagal'     => (int) ($r['gagal']     ?? 0),
            'menunggu'  => (int) ($r['menunggu']  ?? 0),
            'percobaan' => (int) ($r['percobaan'] ?? 0),
        ];
        return $result;
    }

    public function ringkasRiwayat(int $id): array
    {
        $r = $this->row("
            SELECT COALESCE(SUM(r.jenis_aktivitas = 'terkirim'), 0) AS terkirim,
                   COALESCE(SUM(r.jenis_aktivitas = 'gagal'), 0)    AS gagal,
                   MIN(r.terjadi_pada) AS awal,
                   MAX(r.terjadi_pada) AS akhir
            FROM riwayat_pengiriman_email r
            JOIN penerima_kampanye pk ON pk.id = r.penerima_kampanye_id
            WHERE pk.kampanye_email_id = ?
        ", [$id]);

        return [
            'terkirim' => (int) ($r['terkirim'] ?? 0),
            'gagal'    => (int) ($r['gagal']    ?? 0),
            'awal'     => $r['awal']  ?? null,
            'akhir'    => $r['akhir'] ?? null,
        ];
    }

    public function linimasa(int $id, ?string $awal, ?string $akhir): array
    {
        if (!$awal || !$akhir) {
            return ['satuan' => 'menit', 'data' => []];
        }

        $span = max(0, strtotime($akhir) - strtotime($awal));
        if ($span <= 3 * 3600)        { $fmt = '%Y-%m-%d %H:%i'; $satuan = 'menit'; }
        elseif ($span <= 3 * 86400)   { $fmt = '%Y-%m-%d %H:00'; $satuan = 'jam'; }
        else                          { $fmt = '%Y-%m-%d';       $satuan = 'hari'; }

        $data = $this->rows("
            SELECT DATE_FORMAT(r.terjadi_pada, '$fmt') AS bucket,
                   SUM(r.jenis_aktivitas = 'terkirim') AS terkirim,
                   SUM(r.jenis_aktivitas = 'gagal')    AS gagal
            FROM riwayat_pengiriman_email r
            JOIN penerima_kampanye pk ON pk.id = r.penerima_kampanye_id
            WHERE pk.kampanye_email_id = ?
            GROUP BY bucket
            ORDER BY bucket ASC
        ", [$id]);

        return ['satuan' => $satuan, 'data' => $data];
    }

    public function perPutaran(int $id): array
    {
        return $this->rows("
            SELECT j.id, j.putaran_ke, j.waktu_kirim, j.status, j.dimulai_pada, j.selesai_pada,
                   COALESCE(p.total,    j.jumlah_penerima) AS total,
                   COALESCE(p.berhasil, j.jumlah_berhasil) AS berhasil,
                   COALESCE(p.gagal,    j.jumlah_gagal)    AS gagal,
                   COALESCE(p.menunggu, 0)                 AS menunggu,
                   TIMESTAMPDIFF(SECOND, j.waktu_kirim,  j.dimulai_pada) AS terlambat_detik,
                   TIMESTAMPDIFF(SECOND, j.dimulai_pada, j.selesai_pada) AS durasi_detik
            FROM jadwal_kampanye j
            LEFT JOIN (
                SELECT jadwal_kampanye_id,
                       COUNT(*) AS total,
                       SUM(status_pengiriman = 'terkirim') AS berhasil,
                       SUM(status_pengiriman = 'gagal')    AS gagal,
                       SUM(status_pengiriman NOT IN ('terkirim','gagal')) AS menunggu
                FROM penerima_kampanye
                WHERE kampanye_email_id = ?
                GROUP BY jadwal_kampanye_id
            ) p ON p.jadwal_kampanye_id = j.id
            WHERE j.kampanye_email_id = ?
            ORDER BY j.putaran_ke ASC
        ", [$id, $id]);
    }

    public function perKategori(int $id): array
    {
        return $this->rows("
            SELECT COALESCE(kt.nama, 'Tanpa kategori') AS nama,
                   COUNT(*) AS total,
                   SUM(pk.status_pengiriman = 'terkirim') AS berhasil,
                   SUM(pk.status_pengiriman = 'gagal')    AS gagal,
                   SUM(pk.status_pengiriman NOT IN ('terkirim','gagal')) AS menunggu
            FROM penerima_kampanye pk
            LEFT JOIN kontak_email c ON c.id  = pk.kontak_email_id
            LEFT JOIN kategori    kt ON kt.id = c.kategori_id
            WHERE pk.kampanye_email_id = ?
            GROUP BY COALESCE(kt.nama, 'Tanpa kategori')
            ORDER BY total DESC, nama ASC
        ", [$id]);
    }

    public function daftarPenerima(int $id, string $status = ''): array
    {
        $w = ['pk.kampanye_email_id = ?'];
        $b = [$id];
        if ($status === 'terkirim' || $status === 'gagal') {
            $w[] = 'pk.status_pengiriman = ?';
            $b[] = $status;
        } elseif ($status === 'menunggu') {
            $w[] = "pk.status_pengiriman NOT IN ('terkirim','gagal')";
        }

        return $this->rows("
            SELECT pk.id, pk.alamat_email_penerima, pk.nama_penerima, pk.status_pengiriman,
                   pk.jumlah_percobaan, pk.terkirim_pada, pk.id_pesan_penyedia,
                   j.putaran_ke,
                   COALESCE(kt.nama, 'Tanpa kategori') AS kategori_nama,
                   CASE WHEN pk.status_pengiriman = 'gagal' THEN pk.pesan_kesalahan END AS pesan_kesalahan
            FROM penerima_kampanye pk
            LEFT JOIN jadwal_kampanye j ON j.id  = pk.jadwal_kampanye_id
            LEFT JOIN kontak_email    c ON c.id  = pk.kontak_email_id
            LEFT JOIN kategori       kt ON kt.id = c.kategori_id
            WHERE " . implode(' AND ', $w) . "
            ORDER BY pk.id ASC
        ", $b);
    }

    public function riwayat(int $id, ?int $limit = null): array
    {
        $lim = $limit ? 'LIMIT ' . (int) $limit : '';

        return $this->rows("
            SELECT r.id, r.terjadi_pada, r.jenis_aktivitas, r.penyedia_email, r.id_pesan_penyedia,
                   pk.id AS penerima_id, pk.alamat_email_penerima,
                   CASE WHEN r.jenis_aktivitas = 'gagal' THEN r.detail_aktivitas END AS detail_gagal
            FROM riwayat_pengiriman_email r
            JOIN penerima_kampanye pk ON pk.id = r.penerima_kampanye_id
            WHERE pk.kampanye_email_id = ?
            ORDER BY r.terjadi_pada DESC, r.id DESC
            $lim
        ", [$id]);
    }

    public function hitungRiwayat(int $id): int
    {
        return (int) ($this->row("
            SELECT COUNT(*) AS jml
            FROM riwayat_pengiriman_email r
            JOIN penerima_kampanye pk ON pk.id = r.penerima_kampanye_id
            WHERE pk.kampanye_email_id = ?
        ", [$id])['jml'] ?? 0);
    }

    public function kirimGanda(int $id): array
    {
        return $this->rows("
            SELECT pk.id, pk.alamat_email_penerima, pk.nama_penerima,
                   COUNT(*) AS kali, MIN(r.terjadi_pada) AS pertama, MAX(r.terjadi_pada) AS terakhir
            FROM riwayat_pengiriman_email r
            JOIN penerima_kampanye pk ON pk.id = r.penerima_kampanye_id
            WHERE pk.kampanye_email_id = ? AND r.jenis_aktivitas = 'terkirim'
            GROUP BY pk.id, pk.alamat_email_penerima, pk.nama_penerima
            HAVING COUNT(*) > 1
            ORDER BY kali DESC, pk.id ASC
        ", [$id]);
    }
}