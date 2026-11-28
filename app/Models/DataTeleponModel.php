<?php

namespace App\Models;

use App\Libraries\ContactStore;
use CodeIgniter\Model;

/**
 * Tabel NO HP / TELEPON milik kontak (one-to-many): kontak_telepon.
 * Satu perusahaan (kontak_email) bisa punya banyak nomor.
 *
 * Kesalahan validasi dilempar sebagai \InvalidArgumentException
 * (pesannya aman ditampilkan ke pengguna).
 */
class DataTeleponModel extends Model
{
    protected $table         = 'kontak_telepon';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;          // created_at & updated_at
    protected $allowedFields = ['kontak_id', 'nomor', 'nomor_normal', 'label', 'sumber_url', 'dipakai', 'last_check'];

    private const LIMIT = 5000;

    /* ---------------------------- READ ---------------------------- */

    public function daftar(int $kontakId = 0): array
    {
        $b = $this->db->table('kontak_telepon t')
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
        $this->pastikanUnik($kontakId, $data['nomor_normal'], 0);

        $data['kontak_id'] = $kontakId;
        $data['dipakai']   = 0;

        $id = $this->insert($data, true);
        if (! $id) {
            throw new \RuntimeException('Gagal menyimpan nomor');
        }
        return $this->find($id) ?? [];
    }

    /* --------------------------- UPDATE --------------------------- */

    public function ubah(int $id, array $in): array
    {
        $row = $this->find($id);
        if (! $row) {
            throw new \InvalidArgumentException('Nomor tidak ditemukan');
        }

        $data = $this->bersihkan($in);
        $this->pastikanUnik((int) $row['kontak_id'], $data['nomor_normal'], $id);

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
        $n = ContactStore::normalizePhone((string) ($in['nomor'] ?? ''));
        if ($n === null) {
            throw new \InvalidArgumentException('Nomor tidak valid. Gunakan nomor Indonesia, mis. 0812-3456-7890 atau (021) 6914511');
        }
        $label = trim((string) ($in['label'] ?? ''));

        return [
            'nomor'        => $n[0],
            'nomor_normal' => $n[1],
            'label'        => $label !== '' ? mb_substr($label, 0, 100) : 'Manual',
        ];
    }

    private function pastikanKontak(int $kontakId): void
    {
        if ($kontakId <= 0 || $this->db->table('kontak_email')->where('id', $kontakId)->countAllResults() < 1) {
            throw new \InvalidArgumentException('Perusahaan belum dipilih atau tidak ditemukan');
        }
    }

    private function pastikanUnik(int $kontakId, string $normal, int $exceptId): void
    {
        $b = $this->db->table('kontak_telepon')->where('kontak_id', $kontakId)->where('nomor_normal', $normal);
        if ($exceptId > 0) {
            $b->where('id !=', $exceptId);
        }
        if ($b->countAllResults() > 0) {
            throw new \InvalidArgumentException('Nomor ini sudah ada di perusahaan tersebut');
        }
    }
}