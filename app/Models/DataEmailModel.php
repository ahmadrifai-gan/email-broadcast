<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tabel EMAIL milik kontak (one-to-many): kontak_email_hasil.
 * Satu perusahaan (kontak_email) bisa punya banyak email.
 *
 * Kesalahan validasi dilempar sebagai \InvalidArgumentException
 * (pesannya aman ditampilkan ke pengguna).
 */
class DataEmailModel extends Model
{
    protected $table         = 'kontak_email_hasil';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;          // created_at & updated_at
    protected $allowedFields = ['kontak_id', 'email', 'label', 'sumber_url', 'dipakai', 'last_check'];

    private const LIMIT = 5000;

    /* ---------------------------- READ ---------------------------- */

    /** Semua email beserta nama perusahaannya (opsional hanya milik satu kontak). */
    public function daftar(int $kontakId = 0): array
    {
        $b = $this->db->table('kontak_email_hasil t')
            ->select('t.*, k.nama AS nama_kontak, k.nama_perusahaan')
            ->join('kontak_email k', 'k.id = t.kontak_id');
        if ($kontakId > 0) {
            $b->where('t.kontak_id', $kontakId);
        }
        return $b->orderBy('t.id', 'DESC')->limit(self::LIMIT)->get()->getResultArray();
    }

    /* --------------------------- CREATE --------------------------- */

    public function tambah(array $in): array
    {
        $kontakId = (int) ($in['kontak_id'] ?? 0);
        $this->pastikanKontak($kontakId);

        $data = $this->bersihkan($in);
        $this->pastikanUnik($kontakId, $data['email'], 0);

        $data['kontak_id'] = $kontakId;
        $data['dipakai']   = 0;

        $id = $this->insert($data, true);
        if (! $id) {
            throw new \RuntimeException('Gagal menyimpan email');
        }
        return $this->find($id) ?? [];
    }

    /* --------------------------- UPDATE --------------------------- */

    public function ubah(int $id, array $in): array
    {
        $row = $this->find($id);
        if (! $row) {
            throw new \InvalidArgumentException('Email tidak ditemukan');
        }

        $data = $this->bersihkan($in);
        $this->pastikanUnik((int) $row['kontak_id'], $data['email'], $id);

        // email diganti -> dianggap belum pernah dipakai
        if (strtolower((string) $row['email']) !== $data['email']) {
            $data['dipakai'] = 0;
        }

        $this->update($id, $data);
        return $this->find($id) ?? [];
    }

    /* --------------------------- DELETE --------------------------- */

    public function hapusData(int $id): bool
    {
        if (! $this->find($id)) {
            return false;
        }
        return (bool) $this->delete($id);
    }

    /* ------------------------- VALIDASI --------------------------- */

    private function bersihkan(array $in): array
    {
        $email = strtolower(trim((string) ($in['email'] ?? '')));
        if ($email === '' || strlen($email) > 150 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Format email tidak valid');
        }
        $label = trim((string) ($in['label'] ?? ''));

        return [
            'email' => $email,
            'label' => $label !== '' ? mb_substr($label, 0, 255) : 'Manual',
        ];
    }

    private function pastikanKontak(int $kontakId): void
    {
        if ($kontakId <= 0 || $this->db->table('kontak_email')->where('id', $kontakId)->countAllResults() < 1) {
            throw new \InvalidArgumentException('Perusahaan belum dipilih atau tidak ditemukan');
        }
    }

    private function pastikanUnik(int $kontakId, string $email, int $exceptId): void
    {
        $b = $this->db->table('kontak_email_hasil')->where('kontak_id', $kontakId)->where('email', $email);
        if ($exceptId > 0) {
            $b->where('id !=', $exceptId);
        }
        if ($b->countAllResults() > 0) {
            throw new \InvalidArgumentException('Email ini sudah ada di perusahaan tersebut');
        }
    }
}