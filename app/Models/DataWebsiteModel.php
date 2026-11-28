<?php

namespace App\Models;

use App\Libraries\ContactStore;
use App\Libraries\EmailExtractor;
use CodeIgniter\Model;

/**
 * Tabel WEBSITE milik kontak (one-to-many): kontak_website.
 * Satu perusahaan (kontak_email) bisa punya banyak website; satu di antaranya "utama".
 *
 * Kesalahan validasi dilempar sebagai \InvalidArgumentException
 * (pesannya aman ditampilkan ke pengguna).
 */
class DataWebsiteModel extends Model
{
    protected $table         = 'kontak_website';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;          // created_at & updated_at
    protected $allowedFields = ['kontak_id', 'url', 'host', 'label', 'is_primary', 'last_check'];

    private const LIMIT = 5000;

    /* ---------------------------- READ ---------------------------- */

    public function daftar(int $kontakId = 0): array
    {
        $b = $this->db->table('kontak_website t')
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
        $this->pastikanUnik($kontakId, $data['host'], 0);

        // website pertama otomatis jadi utama
        $ada = $this->db->table('kontak_website')->where('kontak_id', $kontakId)->countAllResults();
        if ($ada === 0) {
            $data['is_primary'] = 1;
        }
        if (! empty($data['is_primary'])) {
            $this->db->table('kontak_website')->where('kontak_id', $kontakId)->update(['is_primary' => 0]);
        }

        $data['kontak_id'] = $kontakId;

        $id = $this->insert($data, true);
        if (! $id) {
            throw new \RuntimeException('Gagal menyimpan website');
        }
        return $this->find($id) ?? [];
    }

    /* --------------------------- UPDATE --------------------------- */

    public function ubah(int $id, array $in): array
    {
        $row = $this->find($id);
        if (! $row) {
            throw new \InvalidArgumentException('Website tidak ditemukan');
        }
        $kontakId = (int) $row['kontak_id'];

        $data = $this->bersihkan($in);
        $this->pastikanUnik($kontakId, $data['host'], $id);

        if (! empty($data['is_primary'])) {
            $this->db->table('kontak_website')->where('kontak_id', $kontakId)->where('id !=', $id)->update(['is_primary' => 0]);
        } elseif ((int) $row['is_primary'] === 1) {
            // status utama tidak bisa dilepas begitu saja: jadikan website LAIN sebagai utama untuk memindahkannya
            $data['is_primary'] = 1;
        }

        $this->update($id, $data);
        return $this->find($id) ?? [];
    }

    /* --------------------------- DELETE --------------------------- */

    public function hapusData(int $id): bool
    {
        $row = $this->find($id);
        if (! $row) {
            return false;
        }
        $this->delete($id);

        // website utama dihapus -> website tertua yang tersisa jadi utama
        if ((int) $row['is_primary'] === 1) {
            $next = $this->db->table('kontak_website')->select('id')
                ->where('kontak_id', (int) $row['kontak_id'])->orderBy('id', 'ASC')->get(1)->getRowArray();
            if ($next) {
                $this->db->table('kontak_website')->where('id', (int) $next['id'])->update(['is_primary' => 1]);
            }
        }
        return true;
    }

    /* ------------------------- VALIDASI --------------------------- */

    private function bersihkan(array $in): array
    {
        $url = EmailExtractor::normalizeWebsite((string) ($in['url'] ?? ''));
        if ($url === null) {
            throw new \InvalidArgumentException('Alamat website tidak valid, mis. https://contoh.co.id');
        }
        $host = ContactStore::hostOf($url);
        if ($host === '' || strlen($host) > 190) {
            throw new \InvalidArgumentException('Alamat website tidak valid');
        }
        $label = trim((string) ($in['label'] ?? ''));

        return [
            'url'        => mb_substr($url, 0, 500),
            'host'       => $host,
            'label'      => $label !== '' ? mb_substr($label, 0, 100) : null,
            'is_primary' => ! empty($in['is_primary']) ? 1 : 0,
        ];
    }

    private function pastikanKontak(int $kontakId): void
    {
        if ($kontakId <= 0 || $this->db->table('kontak_email')->where('id', $kontakId)->countAllResults() < 1) {
            throw new \InvalidArgumentException('Perusahaan belum dipilih atau tidak ditemukan');
        }
    }

    private function pastikanUnik(int $kontakId, string $host, int $exceptId): void
    {
        $b = $this->db->table('kontak_website')->where('kontak_id', $kontakId)->where('host', $host);
        if ($exceptId > 0) {
            $b->where('id !=', $exceptId);
        }
        if ($b->countAllResults() > 0) {
            throw new \InvalidArgumentException('Website ini sudah ada di perusahaan tersebut');
        }
    }
}