<?php

namespace App\Models;

use CodeIgniter\Model;

class Kontakmodel extends Model
{
    protected $table         = 'kontak_email';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nama',
        'nama_perusahaan',
        'email',
        'kota_kabupaten',
        'no_hp',
        'website',
        'provinsi',
        'sumber_data',
        'catatan',
        'kategori_id',
    ];

    public function tanpaEmail(): array
    {
        // Menggunakan Query Builder agar lebih aman dan tidak bentrok nama tabel
        $builder = $this->db->table('kontak_email k');

        $builder->select('k.id, k.nama, k.nama_perusahaan, k.email, k.website, k.kota_kabupaten');
        $builder->select("COALESCE(e.status, 'pending') AS status, e.note, e.found_count");
        $builder->select('(SELECT COUNT(*) FROM kontak_email_hasil h WHERE h.kontak_id = k.id) AS n', false);

        $builder->join('kontak_ekstraksi e', 'e.kontak_id = k.id', 'left');

        // Filter: Ambil yang emailnya NULL atau string kosong
        $builder->groupStart()
            ->where('k.email IS NULL')
            ->orWhere('k.email', '')
            ->groupEnd();

        $builder->orderBy('k.id', 'ASC');

        $rows = $builder->get()->getResultArray();

        // Proses validasi URL website
        foreach ($rows as &$r) {
            $url = \App\Libraries\EmailExtractor::normalizeWebsite($r['website'] ?? null);
            $r['has_web'] = $url !== null ? 1 : 0;
            $r['url']     = $url ?? '';
        }
        unset($r);

        return $rows;
    }

    public function hasilEmail(): array
    {
        return $this->db->table('kontak_email_hasil h')
            ->select('h.id, h.kontak_id, h.email, h.label, h.dipakai, k.nama, k.nama_perusahaan, k.website')
            ->join('kontak_email k', 'k.id = h.kontak_id')
            ->orderBy('h.id', 'DESC')
            ->get()->getResultArray();
    }

    public function isiEmail(int $id, string $email): bool
    {
        $email = trim(strtolower($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        // Cek bentrok email dengan kontak lain
        $dup = $this->db->table('kontak_email')->where('email', $email)->where('id !=', $id)->countAllResults();
        if ($dup > 0) return false;

        // Cek apakah kontak masih kosong
        $row = $this->db->table('kontak_email')->select('email')->where('id', $id)->get()->getRowArray();
        if (! $row || trim((string) $row['email']) !== '') return false;

        // Update email kontak
        $this->db->table('kontak_email')->where('id', $id)->update([
            'email'      => $email,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Tandai sebagai dipakai
        $this->db->table('kontak_email_hasil')
            ->where('kontak_id', $id)
            ->where('email', $email)
            ->update(['dipakai' => 1]);

        return true;
    }
}
