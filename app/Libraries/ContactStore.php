<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Menyimpan SEMUA email / no HP / website hasil pencarian milik satu kontak
 * (one-to-many) dan mencatat last_check.
 *
 *   kontak_email        (1) ──< kontak_email_hasil   (email, banyak)
 *                       (1) ──< kontak_telepon       (no HP, banyak)
 *                       (1) ──< kontak_website       (website, banyak)
 */
class ContactStore
{
    /** Kontak yang terakhir dicek kurang dari sekian hari dianggap "baru dicek". */
    public const STALE_DAYS = 30;

    /** Apakah last_check masih dalam rentang STALE_DAYS hari terakhir? */
    public static function isFresh(?string $lastCheck, int $days = self::STALE_DAYS): bool
    {
        if ($lastCheck === null || $lastCheck === '' || $lastCheck === '0000-00-00 00:00:00') {
            return false;
        }
        $t = strtotime($lastCheck);
        return $t !== false && $t >= strtotime("-{$days} days");
    }

    /** Catat bahwa kontak ini baru saja dicek. */
    public static function touch(BaseConnection $db, int $kontakId, ?string $now = null): void
    {
        $now = $now ?? date('Y-m-d H:i:s');
        try {
            $db->table('kontak_email')->where('id', $kontakId)->update(['last_check' => $now]);
        } catch (\Throwable $e) {
        }
    }

    /* ------------------------------------------------------------------
       EMAIL  (tabel kontak_email_hasil)
       ------------------------------------------------------------------ */

    /**
     * Simpan SEMUA email yang ditemukan (bukan hanya satu).
     * Email yang sudah ada diperbarui (flag `dipakai` tetap), yang tidak ditemukan lagi
     * dan belum dipakai dihapus.
     *
     * @param array $pairs  [[email, label], ...]
     * @return array{count:int, list:array<int,array{email:string,label:string}>}
     */
    public static function saveEmails(BaseConnection $db, int $kontakId, array $pairs, string $now, string $sourceUrl = ''): array
    {
        $list  = [];
        $seen  = [];
        $table = $db->table('kontak_email_hasil');

        foreach ($pairs as $pair) {
            [$email, $label] = is_array($pair) ? $pair + [1 => ''] : [$pair, ''];
            $email = strtolower(trim((string) $email));
            if ($email === '' || isset($seen[$email])) {
                continue;
            }
            $seen[$email] = true;
            $label        = trim((string) $label);

            $db->query(
                'INSERT INTO kontak_email_hasil (kontak_id, email, label, sumber_url, dipakai, last_check, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 0, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE label = VALUES(label), sumber_url = VALUES(sumber_url), last_check = VALUES(last_check), updated_at = VALUES(updated_at)',
                [$kontakId, $email, $label !== '' ? mb_substr($label, 0, 255) : null, $sourceUrl !== '' ? mb_substr($sourceUrl, 0, 500) : null, $now, $now, $now]
            );
            $list[] = ['email' => $email, 'label' => $label];
        }

        // Buang email lama yang sudah tidak ada di website & belum dipakai
        $old = $table->select('id, email')->where('kontak_id', $kontakId)->where('dipakai', 0)->get()->getResultArray();
        foreach ($old as $o) {
            if (! isset($seen[strtolower((string) $o['email'])])) {
                $db->table('kontak_email_hasil')->where('id', (int) $o['id'])->delete();
            }
        }

        return ['count' => count($list), 'list' => $list];
    }

    /* ------------------------------------------------------------------
       NO HP  (tabel kontak_telepon)
       ------------------------------------------------------------------ */

    /**
     * Ubah nomor mentah jadi [tampilan, normal]. Normal = angka saja berawalan 62.
     * Mengembalikan null bila bukan nomor Indonesia yang masuk akal.
     */
    public static function normalizePhone(string $raw): ?array
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '620')) {          // +62 0812... salah ketik
            $digits = '62' . substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            return null;
        }
        $len = strlen($digits);
        if ($len < 10 || $len > 14) {
            return null;
        }
        // semua digit sama / pola sampah
        if (preg_match('/^62(\d)\1{7,}$/', $digits)) {
            return null;
        }

        $local   = substr($digits, 2);
        $display = '+62 ' . substr($local, 0, 3) . '-' . substr($local, 3, 4) . '-' . substr($local, 7);
        return [rtrim($display, '-'), $digits];
    }

    /** @param array $phones [[nomorMentah, label], ...] atau [nomorMentah, ...] */
    public static function savePhones(BaseConnection $db, int $kontakId, array $phones, string $now, string $sourceUrl = ''): int
    {
        $seen = [];
        foreach ($phones as $p) {
            [$raw, $label] = is_array($p) ? $p + [1 => ''] : [$p, ''];
            $n = self::normalizePhone((string) $raw);
            if ($n === null || isset($seen[$n[1]])) {
                continue;
            }
            $seen[$n[1]] = true;

            $db->query(
                'INSERT INTO kontak_telepon (kontak_id, nomor, nomor_normal, label, sumber_url, dipakai, last_check, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE label = COALESCE(VALUES(label), label), sumber_url = VALUES(sumber_url), last_check = VALUES(last_check), updated_at = VALUES(updated_at)',
                [$kontakId, $n[0], $n[1], trim((string) $label) !== '' ? mb_substr(trim((string) $label), 0, 100) : null, $sourceUrl !== '' ? mb_substr($sourceUrl, 0, 500) : null, $now, $now, $now]
            );
        }

        $old = $db->table('kontak_telepon')->select('id, nomor_normal')->where('kontak_id', $kontakId)->where('dipakai', 0)->get()->getResultArray();
        foreach ($old as $o) {
            if (! isset($seen[$o['nomor_normal']])) {
                $db->table('kontak_telepon')->where('id', (int) $o['id'])->delete();
            }
        }
        return count($seen);
    }

    /* ------------------------------------------------------------------
       WEBSITE  (tabel kontak_website)
       ------------------------------------------------------------------ */

    public static function hostOf(string $url): string
    {
        $h = strtolower((string) parse_url($url, PHP_URL_HOST));
        return (string) preg_replace('/^www\./', '', $h);
    }

    /**
     * Simpan website kontak. $primaryUrl = website di data kontak (ditandai is_primary).
     * @param string[] $urls  website tambahan (mis. alamat akhir setelah redirect)
     */
    public static function saveWebsites(BaseConnection $db, int $kontakId, array $urls, ?string $primaryUrl, string $now): int
    {
        $all = [];
        if ($primaryUrl) {
            $all[] = [$primaryUrl, 1];
        }
        foreach ($urls as $u) {
            $all[] = [(string) $u, 0];
        }

        $seen = [];
        foreach ($all as [$url, $primary]) {
            $host = self::hostOf($url);
            if ($host === '' || strlen($host) > 190) {
                continue;
            }
            if (isset($seen[$host])) {
                continue;
            }
            $seen[$host] = true;

            $db->query(
                'INSERT INTO kontak_website (kontak_id, url, host, is_primary, last_check, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE url = VALUES(url), is_primary = GREATEST(is_primary, VALUES(is_primary)), last_check = VALUES(last_check), updated_at = VALUES(updated_at)',
                [$kontakId, mb_substr($url, 0, 500), $host, $primary, $now, $now, $now]
            );
        }
        return count($seen);
    }
}