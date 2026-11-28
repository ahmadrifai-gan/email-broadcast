<?php

namespace App\Models;

use CodeIgniter\Model;

class KontakModel extends Model
{
    protected $table         = 'tb_perusahaan';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nama', 'nama_perusahaan', 'email',
        'kota_kabupaten', 'no_hp', 'website', 'provinsi',
        'sumber_data', 'catatan', 'kategori_id',
    ];

    /**
     * Kontak yang BELUM punya email (NULL, kosong, atau hanya spasi).
     *
     * @param string $check all   = semua
     *                      fresh = last_check KURANG dari 30 hari (sudah pernah dicari baru-baru ini)
     *                      stale = belum pernah dicek / sudah lebih dari 30 hari
     */
    public function tanpaEmail(string $check = 'all'): array
    {
        $days    = \App\Libraries\ContactStore::STALE_DAYS;
        $builder = $this->db->table('kontak_email k');
        $builder->select('k.id, k.nama, k.nama_perusahaan, k.email, k.website, k.kota_kabupaten, k.last_check');
        $builder->select("COALESCE(e.status, 'pending') AS status, e.note, e.found_count");
        $builder->select('DATEDIFF(NOW(), k.last_check) AS hari_lalu', false);
        $builder->select('(SELECT COUNT(*) FROM kontak_email_hasil h WHERE h.kontak_id = k.id) AS n', false);
        $builder->select('(SELECT COUNT(*) FROM kontak_telepon t WHERE t.kontak_id = k.id) AS n_telepon', false);
        $builder->select('(SELECT COUNT(*) FROM kontak_website w WHERE w.kontak_id = k.id) AS n_website', false);
        $builder->join('kontak_ekstraksi e', 'e.kontak_id = k.id', 'left');
        $builder->groupStart()
            ->where('k.email IS NULL', null, false)
            ->orWhere("TRIM(k.email) = ''", null, false)
            ->groupEnd();

        if ($check === 'fresh') {
            $builder->where('k.last_check IS NOT NULL', null, false)
                ->where("k.last_check >= DATE_SUB(NOW(), INTERVAL {$days} DAY)", null, false);
        } elseif ($check === 'stale') {
            $builder->groupStart()
                ->where('k.last_check IS NULL', null, false)
                ->orWhere("k.last_check < DATE_SUB(NOW(), INTERVAL {$days} DAY)", null, false)
                ->groupEnd();
        }
        $builder->orderBy('k.id', 'ASC');

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$r) {
            $url = \App\Libraries\EmailExtractor::normalizeWebsite($r['website'] ?? null);
            $r['has_web']   = $url !== null ? 1 : 0;
            $r['url']       = $url ?? '';
            $r['n']         = (int) ($r['n'] ?? 0);
            $r['n_telepon'] = (int) ($r['n_telepon'] ?? 0);
            $r['n_website'] = (int) ($r['n_website'] ?? 0);
            $r['note']      = $r['note'] ?? '';
            $r['fresh']     = \App\Libraries\ContactStore::isFresh($r['last_check'] ?? null, $days) ? 1 : 0;
            $r['hari_lalu'] = $r['hari_lalu'] === null ? null : (int) $r['hari_lalu'];
        }
        unset($r);
        return $rows;
    }

    /** Semua email, no HP, dan website milik satu kontak (one-to-many). */
    public function detailKontak(int $id): ?array
    {
        $k = $this->db->table('kontak_email')
            ->select('id, nama, nama_perusahaan, email, website, no_hp, kota_kabupaten, last_check')
            ->where('id', $id)->get()->getRowArray();
        if (! $k) {
            return null;
        }
        $k['emails'] = $this->db->table('kontak_email_hasil')
            ->select('id, email, label, sumber_url, dipakai, last_check')
            ->where('kontak_id', $id)->orderBy('id', 'ASC')->get()->getResultArray();
        $k['telepon'] = $this->db->table('kontak_telepon')
            ->select('id, nomor, nomor_normal, label, sumber_url, dipakai, last_check')
            ->where('kontak_id', $id)->orderBy('id', 'ASC')->get()->getResultArray();
        $k['websites'] = $this->db->table('kontak_website')
            ->select('id, url, host, label, is_primary, last_check')
            ->where('kontak_id', $id)->orderBy('is_primary', 'DESC')->orderBy('id', 'ASC')->get()->getResultArray();
        return $k;
    }

    /** Kontak milik sebuah job (urut sesuai antrean) beserta status ekstraksinya. */
    public function itemsByIds(array $ids): array
    {
        if (! $ids) {
            return [];
        }
        $rows = [];
        foreach (array_chunk($ids, 500) as $chunk) {
            $part = $this->db->table('kontak_email k')
                ->select('k.id, k.nama, k.nama_perusahaan, k.kota_kabupaten, k.website, k.email, e.status, e.note, e.found_count')
                ->join('kontak_ekstraksi e', 'e.kontak_id = k.id', 'left')
                ->whereIn('k.id', $chunk)
                ->get()->getResultArray();
            foreach ($part as $r) {
                $rows[(int) $r['id']] = $r;
            }
        }
        $out = [];
        foreach ($ids as $i => $id) {
            if (! isset($rows[$id])) continue;
            $r           = $rows[$id];
            $r['id']     = (int) $id;
            $r['pos']    = $i;
            $r['status'] = $r['status'] ?: 'pending';
            $out[]       = $r;
        }
        return $out;
    }

    /** Semua email hasil ekstraksi. email_kontak dipakai halaman untuk menyembunyikan tombol "Pakai". */
    public function hasilEmail(): array
    {
        return $this->db->table('kontak_email_hasil h')
            ->select('h.id, h.kontak_id, h.email, h.label, h.dipakai, h.last_check, k.nama, k.nama_perusahaan, k.website, k.email AS email_kontak')
            ->select('(SELECT MAX(i.job_id) FROM extract_job_items i WHERE i.kontak_id = h.kontak_id) AS job_id', false)
            ->join('kontak_email k', 'k.id = h.kontak_id')
            ->orderBy('h.id', 'DESC')
            ->limit(2000)
            ->get()->getResultArray();
    }

    /**
     * Isi email kontak. False bila email tidak valid, sudah dipakai kontak lain,
     * atau kontak sudah punya email.
     */
    public function isiEmail(int $id, string $email): bool
    {
        $email = trim(strtolower($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        try {
            $dup = $this->db->table('kontak_email')->where('email', $email)->where('id !=', $id)->countAllResults();
            if ($dup > 0) return false;

            // Satu UPDATE bersyarat: tidak menimpa email yang baru saja diisi pihak lain
            $now = date('Y-m-d H:i:s');
            $b   = $this->db->table('kontak_email')->where('id', $id);
            $b->groupStart()->where('email IS NULL', null, false)->orWhere("TRIM(email) = ''", null, false)->groupEnd();
            $b->update(['email' => $email, 'updated_at' => $now, 'diperbarui_pada' => $now]);

            if ($this->db->affectedRows() < 1) return false;

            $this->db->table('kontak_email_hasil')
                ->where('kontak_id', $id)
                ->where('email', $email)
                ->update(['dipakai' => 1]);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Reset status ekstraksi kontak yang baru masuk antrean. */
    public function resetEkstraksi(array $ids): void
    {
        if (! $ids) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        foreach (array_chunk($ids, 500) as $chunk) {
            $exist = array_map('intval', array_column(
                $this->db->table('kontak_ekstraksi')->select('kontak_id')->whereIn('kontak_id', $chunk)->get()->getResultArray(),
                'kontak_id'
            ));
            if ($exist) {
                $this->db->table('kontak_ekstraksi')->whereIn('kontak_id', $exist)->update([
                    'status' => 'pending', 'note' => 'Dalam antrean', 'found_count' => 0, 'updated_at' => $now,
                ]);
            }
            $baru = [];
            foreach (array_diff($chunk, $exist) as $id) {
                $baru[] = [
                    'kontak_id' => $id, 'status' => 'pending', 'note' => 'Dalam antrean',
                    'found_count' => 0, 'created_at' => $now, 'updated_at' => $now,
                ];
            }
            if ($baru) {
                $this->db->table('kontak_ekstraksi')->insertBatch($baru);
            }
        }
    }

    /** Angka ringkasan untuk halaman index. */
    public function ringkasanEmail(): array
    {
        $kosong = function ($b, string $col) {
            return $b->groupStart()->where("{$col} IS NULL", null, false)->orWhere("TRIM({$col}) = ''", null, false)->groupEnd();
        };

        $total = $this->db->table('kontak_email')->countAllResults();

        $b     = $this->db->table('kontak_email');
        $tanpa = $kosong($b, 'email')->countAllResults();

        $hasil = $this->db->table('kontak_email_hasil')->countAllResults();

        $b     = $this->db->table('kontak_email_hasil h')->join('kontak_email k', 'k.id = h.kontak_id')->where('h.dipakai', 0);
        $belum = $kosong($b, 'k.email')->countAllResults();

        $status = [];
        foreach ($this->db->table('kontak_ekstraksi')->select('status, COUNT(*) AS n')->groupBy('status')->get()->getResultArray() as $r) {
            $status[$r['status']] = (int) $r['n'];
        }

        return [
            'kontak_total'        => $total,
            'tanpa_email'         => $tanpa,
            'punya_email'         => max(0, $total - $tanpa),
            'hasil_total'         => $hasil,
            'hasil_belum_dipakai' => $belum,
            'status'              => $status,
        ];
    }

    /** Riwayat pencarian per kontak (status, catatan, jumlah email). */
    public function riwayatEkstraksi(): array
    {
        return $this->db->table('kontak_ekstraksi e')
            ->select('e.id, e.kontak_id, e.status, e.note, e.found_count, e.processed_at, k.nama, k.nama_perusahaan, k.kota_kabupaten, k.website, k.email')
            ->select('(SELECT MAX(i.job_id) FROM extract_job_items i WHERE i.kontak_id = e.kontak_id) AS job_id', false)
            ->join('kontak_email k', 'k.id = e.kontak_id')
            ->orderBy('e.processed_at', 'DESC')
            ->orderBy('e.id', 'DESC')
            ->limit(2000)
            ->get()->getResultArray();
    }

    /** Daftar perusahaan/kontak untuk dropdown pada form email, no HP, dan website. */
    public function opsiKontak(): array
    {
        return $this->db->table('kontak_email')
            ->select('id, nama, nama_perusahaan')
            ->orderBy('nama_perusahaan', 'ASC')->orderBy('nama', 'ASC')
            ->get()->getResultArray();
    }
}