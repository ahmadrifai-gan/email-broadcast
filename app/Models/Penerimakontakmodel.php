<?php

namespace App\Models;

/**
 * PenerimaKontakModel
 * ---------------------------------------------------------------
 * Query untuk menu "Laporan Penerima & Kontak" (read-only, SQL langsung dengan binding "?").
 *
 * Definisi:
 *   - Tanggal penerima  = COALESCE(terkirim_pada, dibuat_pada)
 *   - Berhasil          = penerima_kampanye.status_pengiriman = 'terkirim'
 *   - Gagal             = penerima_kampanye.status_pengiriman = 'gagal'
 *   - Menunggu          = status selain 'terkirim' dan 'gagal'
 *   - Kontak bermasalah = (pernah gagal DAN belum pernah berhasil) ATAU kontak_email.jumlah_tidak_terkirim > 0
 */
class PenerimaKontakModel
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

    public function semuaKategori(): array
    {
        return $this->rows('SELECT id, nama FROM kategori ORDER BY nama ASC');
    }

    public function opsiKampanye(): array
    {
        return $this->rows('SELECT id, nama_kampanye, dibuat_pada FROM kampanye_email ORDER BY id DESC');
    }

    /** Kondisi periode & kampanye pada tabel penerima_kampanye. */
    private function kondisiPenerima(array $f, string $a = 'pk'): array
    {
        $w = [];
        $b = [];
        $tgl = "COALESCE($a.terkirim_pada, $a.dibuat_pada)";
        if (!empty($f['dari']))     { $w[] = "$tgl >= ?"; $b[] = $f['dari'] . ' 00:00:00'; }
        if (!empty($f['sampai']))   { $w[] = "$tgl <= ?"; $b[] = $f['sampai'] . ' 23:59:59'; }
        if (!empty($f['kampanye'])) { $w[] = "$a.kampanye_email_id = ?"; $b[] = (int) $f['kampanye']; }
        return [$w, $b];
    }

    /** Subquery statistik pengiriman per kontak (alias "s"). Mengembalikan [sql, binds]. */
    private function statistikKontak(array $f): array
    {
        [$w, $b] = $this->kondisiPenerima($f, 'pk');
        array_unshift($w, 'pk.kontak_email_id IS NOT NULL');

        $sql = "(
            SELECT pk.kontak_email_id,
                   COUNT(*) AS total_kirim,
                   SUM(pk.status_pengiriman = 'terkirim') AS berhasil,
                   SUM(pk.status_pengiriman = 'gagal')    AS gagal,
                   COUNT(DISTINCT pk.kampanye_email_id)   AS jml_kampanye,
                   MAX(pk.terkirim_pada)                  AS terakhir_terkirim
            FROM penerima_kampanye pk
            WHERE " . implode(' AND ', $w) . "
            GROUP BY pk.kontak_email_id
        ) s ON s.kontak_email_id = c.id";

        return [$sql, $b];
    }

    /** Kondisi kategori pada kontak (kolom c.kategori_id). */
    private function kondisiKategori(array $f, string $col = 'c.kategori_id'): array
    {
        $k = $f['kategori'] ?? '';
        if ($k === 'none') return [["$col IS NULL"], []];
        if ($k !== '' && ctype_digit((string) $k)) return [["$col = ?"], [(int) $k]];
        return [[], []];
    }

    private const SQL_TOTAL    = 'COALESCE(s.total_kirim, 0)';
    private const SQL_BERHASIL = 'COALESCE(s.berhasil, 0)';
    private const SQL_GAGAL    = 'COALESCE(s.gagal, 0)';

    private function sqlBermasalah(): string
    {
        return '((' . self::SQL_GAGAL . ' > 0 AND ' . self::SQL_BERHASIL . ' = 0) OR COALESCE(c.jumlah_tidak_terkirim, 0) > 0)';
    }

    private function kondisiStatusKontak(string $sk): string
    {
        return match ($sk) {
            'belum'       => self::SQL_TOTAL . ' = 0',
            'pernah'      => self::SQL_TOTAL . ' > 0',
            'bermasalah'  => $this->sqlBermasalah(),
            'tanpa_email' => "(c.email IS NULL OR c.email = '')",
            default       => '',
        };
    }

    /** Kartu ringkasan kontak (mengikuti filter kategori, periode, kampanye). */
    public function kartuKontak(array $f): array
    {
        [$subSql, $subBinds] = $this->statistikKontak($f);
        [$wk, $bk] = $this->kondisiKategori($f);
        $where = $wk ? 'WHERE ' . implode(' AND ', $wk) : '';

        $sql = "
            SELECT COUNT(*) AS total_kontak,
                   SUM(c.email IS NULL OR c.email = '')                   AS tanpa_email,
                   SUM(" . self::SQL_TOTAL . " > 0)                       AS pernah_dikirim,
                   SUM(" . self::SQL_TOTAL . " = 0)                       AS belum_dikirim,
                   SUM(" . $this->sqlBermasalah() . ")                    AS bermasalah,
                   SUM(" . self::SQL_BERHASIL . ")                        AS email_berhasil,
                   SUM(" . self::SQL_GAGAL . ")                           AS email_gagal
            FROM kontak_email c
            LEFT JOIN $subSql
            $where
        ";

        $r = $this->rows($sql, array_merge($subBinds, $bk))[0] ?? [];
        return array_map('intval', $r + [
            'total_kontak' => 0, 'tanpa_email' => 0, 'pernah_dikirim' => 0,
            'belum_dikirim' => 0, 'bermasalah' => 0, 'email_berhasil' => 0, 'email_gagal' => 0,
        ]);
    }

    /** Rekap per kategori kontak (semua kategori ditampilkan; filter kategori tidak dipakai di sini). */
    public function perKategori(array $f): array
    {
        [$subSql, $subBinds] = $this->statistikKontak($f);

        $sql = "
            SELECT COALESCE(c.kategori_id, 0) AS kategori_id,
                   COALESCE(kt.nama, 'Tanpa kategori') AS nama,
                   COUNT(*) AS jumlah_kontak,
                   SUM(" . self::SQL_TOTAL . " > 0)    AS pernah_dikirim,
                   SUM(" . self::SQL_TOTAL . " = 0)    AS belum_dikirim,
                   SUM(" . $this->sqlBermasalah() . ") AS bermasalah,
                   SUM(" . self::SQL_BERHASIL . ")     AS email_berhasil,
                   SUM(" . self::SQL_GAGAL . ")        AS email_gagal
            FROM kontak_email c
            LEFT JOIN kategori kt ON kt.id = c.kategori_id
            LEFT JOIN $subSql
            GROUP BY COALESCE(c.kategori_id, 0), COALESCE(kt.nama, 'Tanpa kategori')
        ";
        $hasil = [];
        foreach ($this->rows($sql, $subBinds) as $r) {
            $hasil[(int) $r['kategori_id']] = array_map(fn($v) => is_numeric($v) ? (int) $v : $v, $r);
        }

        // Tambahkan kategori yang belum punya kontak (0 kontak)
        foreach ($this->semuaKategori() as $kat) {
            $id = (int) $kat['id'];
            if (!isset($hasil[$id])) {
                $hasil[$id] = [
                    'kategori_id' => $id, 'nama' => $kat['nama'], 'jumlah_kontak' => 0,
                    'pernah_dikirim' => 0, 'belum_dikirim' => 0, 'bermasalah' => 0,
                    'email_berhasil' => 0, 'email_gagal' => 0,
                ];
            }
        }

        $hasil = array_values($hasil);
        usort($hasil, function ($a, $b) {
            if ($a['kategori_id'] === 0) return 1;      // "Tanpa kategori" selalu di bawah
            if ($b['kategori_id'] === 0) return -1;
            return [$b['jumlah_kontak'], $a['nama']] <=> [$a['jumlah_kontak'], $b['nama']];
        });
        return $hasil;
    }

    /** Daftar kontak + statistik pengirimannya (mengikuti semua filter). */
    public function daftarKontak(array $f): array
    {
        [$subSql, $subBinds] = $this->statistikKontak($f);
        [$wk, $bk] = $this->kondisiKategori($f);

        $w = $wk;
        $st = $this->kondisiStatusKontak($f['status_kontak'] ?? '');
        if ($st !== '') $w[] = $st;
        $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';

        $sql = "
            SELECT c.id, c.nama, c.nama_perusahaan, c.email, c.no_hp, c.kota_kabupaten, c.provinsi,
                   c.jumlah_tidak_terkirim, c.kategori_id,
                   COALESCE(kt.nama, 'Tanpa kategori') AS kategori_nama,
                   " . self::SQL_TOTAL . "    AS total_kirim,
                   " . self::SQL_BERHASIL . " AS berhasil,
                   " . self::SQL_GAGAL . "    AS gagal,
                   COALESCE(s.jml_kampanye, 0) AS jml_kampanye,
                   s.terakhir_terkirim
            FROM kontak_email c
            LEFT JOIN kategori kt ON kt.id = c.kategori_id
            LEFT JOIN $subSql
            $where
            ORDER BY c.nama ASC, c.id ASC
        ";

        return $this->rows($sql, array_merge($subBinds, $bk));
    }

    /** WHERE untuk daftar penerima. Mengembalikan [sqlWhere, binds]. */
    private function wherePenerima(array $f): array
    {
        [$w, $b] = $this->kondisiPenerima($f, 'pk');
        [$wk, $bk] = $this->kondisiKategori($f);
        $w = array_merge($w, $wk);
        $b = array_merge($b, $bk);

        $sk = $f['status_kirim'] ?? '';
        if ($sk === 'terkirim' || $sk === 'gagal') {
            $w[] = 'pk.status_pengiriman = ?';
            $b[] = $sk;
        } elseif ($sk === 'menunggu') {
            $w[] = "pk.status_pengiriman NOT IN ('terkirim','gagal')";
        }

        return [$w ? 'WHERE ' . implode(' AND ', $w) : '', $b];
    }

    public function hitungPenerima(array $f): int
    {
        [$where, $b] = $this->wherePenerima($f);
        $sql = "SELECT COUNT(*) AS jml
                FROM penerima_kampanye pk
                LEFT JOIN kontak_email c ON c.id = pk.kontak_email_id
                $where";
        return (int) ($this->rows($sql, $b)[0]['jml'] ?? 0);
    }

    /** Daftar penerima kampanye. $limit = null berarti tanpa batas (dipakai untuk ekspor). */
    public function daftarPenerima(array $f, ?int $limit = null): array
    {
        [$where, $b] = $this->wherePenerima($f);
        $lim = $limit ? 'LIMIT ' . (int) $limit : '';

        $sql = "
            SELECT pk.id, pk.kampanye_email_id, k.nama_kampanye,
                   pk.alamat_email_penerima, pk.nama_penerima, pk.kontak_email_id,
                   COALESCE(kt.nama, 'Tanpa kategori') AS kategori_nama,
                   pk.status_pengiriman, pk.jumlah_percobaan, pk.terkirim_pada, pk.dibuat_pada,
                   COALESCE(pk.terkirim_pada, pk.dibuat_pada) AS waktu,
                   CASE WHEN pk.status_pengiriman = 'gagal' THEN pk.pesan_kesalahan END AS pesan_kesalahan
            FROM penerima_kampanye pk
            JOIN kampanye_email k      ON k.id  = pk.kampanye_email_id
            LEFT JOIN kontak_email c   ON c.id  = pk.kontak_email_id
            LEFT JOIN kategori kt      ON kt.id = c.kategori_id
            $where
            ORDER BY COALESCE(pk.terkirim_pada, pk.dibuat_pada) DESC, pk.id DESC
            $lim
        ";

        return $this->rows($sql, $b);
    }
}