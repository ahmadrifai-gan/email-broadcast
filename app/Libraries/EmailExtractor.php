<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class EmailExtractor
{
    public const DEFAULT_MODEL = 'gemini-3.5-flash-lite';

    private const API_BASE     = 'https://generativelanguage.googleapis.com/v1beta/';
    private const MAX_PAGES    = 5;
    private const MAX_CONTEXT  = 16000;
    private const MAX_EMAILS   = 30;
    private const COMMON_PATHS = ['/contact', '/contact-us', '/kontak', '/kontak-kami', '/hubungi-kami', '/hubungi'];
    /** Media sosial / marketplace / pemendek link: bukan situs resmi, isinya butuh login & tidak memuat email. */
    private const SKIP_HOSTS = [
        'facebook.com', 'fb.com', 'fb.me', 'instagram.com', 'tiktok.com', 'twitter.com', 'x.com', 'youtube.com', 'youtu.be',
        'linkedin.com', 'linktr.ee', 'wa.me', 'whatsapp.com', 't.me', 'tokopedia.com', 'shopee.co.id', 'bukalapak.com',
        'lazada.co.id', 'blibli.com', 'google.com', 'goo.gl', 'bit.ly',
    ];

    private const JUNK_EXT = [
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'avif', 'ico', 'css', 'js', 'map', 'json',
        'woff', 'woff2', 'ttf', 'eot', 'php', 'html', 'htm', 'mp4', 'webm',
    ];
    private const FAKE_LOCAL = [
        'example', 'test', 'yourname', 'your.name', 'your-name', 'yourmail', 'youremail',
        'your-email', 'username', 'user', 'name', 'nama', 'email', 'johndoe', 'john.doe', 'sentry',
    ];
    private const FAKE_DOMAIN = [
        'example.com', 'example.org', 'example.net', 'domain.com', 'yourdomain.com',
        'yoursite.com', 'email.com', 'mysite.com', 'sentry.io', 'wixpress.com',
    ];
    private const PREFERRED = [
        'info', 'contact', 'kontak', 'sales', 'marketing', 'hello', 'halo', 'cs',
        'customerservice', 'office', 'admin', 'support', 'humas',
    ];

    private string $apiKey;
    private string $model;
    private CURLRequest $http;   // khusus mengunduh website
    private CURLRequest $api;    // khusus Gemini (API key TIDAK PERNAH ikut terkirim ke website)
    private ?CURLRequest $loose = null;

    public function __construct(string $apiKey, string $model = '')
    {
        $this->apiKey = trim($apiKey);
        $this->model  = trim($model) !== '' ? trim($model) : self::DEFAULT_MODEL;
        $this->http   = $this->client($this->verifyOption());
        $this->api    = $this->client($this->verifyOption(), true);
    }

    /* ==================================================================
       STATIC HELPERS
       ================================================================== */

    public static function normalizeWebsite(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') return null;
        $raw = (string) (preg_split('/[\s,;|]+/', $raw)[0] ?? '');
        $raw = trim($raw, " \t\n\r\0\x0B\"'<>()[]");
        if ($raw === '' || strlen($raw) > 500) return null;
        if (! preg_match('#^https?://#i', $raw)) $raw = 'https://' . ltrim($raw, '/');
        $p = parse_url($raw);
        if (! $p || empty($p['host'])) return null;
        $host = strtolower($p['host']);
        if (! preg_match('/^(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,24}$/', $host)) return null;

        return strtolower($p['scheme']) . '://' . $host
            . (isset($p['port']) ? ':' . $p['port'] : '')
            . ($p['path'] ?? '')
            . (isset($p['query']) ? '?' . $p['query'] : '');
    }

    public static function rank(array $emails, string $url): array
    {
        $host   = strtolower((string) preg_replace('/^www\./i', '', (string) parse_url($url, PHP_URL_HOST)));
        $scored = [];
        foreach (array_values($emails) as $i => $row) {
            $email = is_array($row) ? (string) $row[0] : (string) $row;
            $parts = explode('@', $email, 2) + [1 => ''];
            [$local, $dom] = $parts;

            $score = 0.0;
            if ($dom !== '' && $host !== '' && ($dom === $host || str_ends_with($host, '.' . $dom) || str_ends_with($dom, '.' . $host))) {
                $score += 100;
            }
            $k = array_search((string) preg_replace('/[^a-z]/', '', $local), self::PREFERRED, true);
            if ($k !== false) {
                $score += 50 - $k;
            }
            $scored[] = [$email, $score - $i * 0.001];
        }
        usort($scored, static fn ($a, $b) => $b[1] <=> $a[1]);

        return array_column($scored, 0);
    }

    /* ==================================================================
       ALUR UTAMA: collect() (tanpa AI) lalu analyze() (AI + regex)
       Dipisah supaya worker bisa mencoba beberapa model Gemini
       tanpa mengunduh ulang website.
       ================================================================== */

    public function extract(string $url): array
    {
        return $this->analyze($this->collect($url));
    }

    /** Unduh homepage + halaman kontak/tentang, kumpulkan email dan konteks teks. */
    public function collect(string $url): array
    {
        $this->assertOfficialSite($url);
        $origin       = $url;
        [$url, $html] = $this->open($url);
        $host         = $this->bareHost($url);

        $pages = [$url => $html];
        foreach ($this->findPages($url, $html, $host) as $u) {
            if (isset($pages[$u])) continue;
            try {
                $pages[$u] = $this->get($u);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // Tidak ada link kontak di homepage -> tebak path umum (maks 3 percobaan)
        $hasContact = false;
        foreach (array_keys($pages) as $u) {
            if (preg_match('/contact|kontak|hubungi/i', (string) $u)) { $hasContact = true; break; }
        }
        if (! $hasContact) {
            $p    = parse_url($url);
            $root = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
            $try  = 0;
            foreach (self::COMMON_PATHS as $path) {
                if ($try++ >= 3) break;
                try {
                    $pages[$root . $path] = $this->get($root . $path);
                    break;
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        $chunks = [];
        $strong = [];
        $weak   = [];
        $phones = [];
        foreach ($pages as $page => $h) {
            $found  = $this->scanEmails($h);
            $strong = array_merge($strong, $found['strong']);
            $weak   = array_merge($weak, $found['weak']);
            $phones = array_merge($phones, $this->scanPhones($h, (string) $page));
            $isContactPage = (bool) preg_match('/contact|kontak|hubungi/i', (string) $page);
            $chunks[] = $this->toChunk((string) $page, $h, $found['strong'], $isContactPage);
        }

        return [
            'url'     => $url,
            'context' => mb_substr(implode("\n\n", $chunks), 0, self::MAX_CONTEXT),
            'strong'  => array_values(array_unique($strong)),
            'weak'    => array_values(array_unique($weak)),
            'phones'  => $this->uniquePhones($phones),
            'websites' => array_values(array_unique([$origin, $url])),
        ];
    }

    /** Analisis hasil collect(): tanya Gemini, validasi jawabannya, gabung dengan regex. */
    public function analyze(array $b, bool $useAi = true, string $skipReason = ''): array
    {
        $url     = (string) $b['url'];
        $context = (string) $b['context'];
        $strong  = $b['strong'];
        $weak    = $b['weak'];
        $host    = $this->bareHost($url);

        // Himpunan email yang BENAR-BENAR ada di halaman (dipakai menyaring halusinasi AI)
        $known = [];
        foreach (array_merge($strong, $weak) as $e) {
            $c = $this->cleanEmail($e);
            if ($c !== null) $known[$c] = true;
        }

        $tokens = 0;
        $note   = '';
        $ai     = null;

        $adaPola = $known !== [] || (bool) preg_match('/@|\[\s*at\s*\]|\(\s*at\s*\)|\bat\b.{1,40}?\bdot\b/iu', $context);

        if (! $useAi) {
            $note = $skipReason !== '' ? $skipReason : 'AI dilewati: hanya regex';
        } elseif ($this->apiKey === '') {
            $note = 'tanpa API key: hanya regex';
        } elseif (! $adaPola) {
            $note = 'tidak ada pola email di halaman (AI dilewati, hemat token)';
        } else {
            try {
                [$ai, $tokens] = $this->askGemini($context);
                if ($ai === null) {
                    $note = 'AI tidak mengembalikan JSON valid, memakai regex';
                }
            } catch (QuotaException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $note = 'AI gagal (' . $this->short($e->getMessage()) . '), memakai regex';
            }
        }

        // Kandidat dari AI, hanya diterima kalau terbukti ada di halaman
        $ctxLower   = mb_strtolower($context);
        $aiCand     = [];
        $dropped    = 0;
        if (is_array($ai)) {
            foreach ($ai as $d) {
                $email = is_array($d) ? (string) ($d['email'] ?? '') : (is_string($d) ? $d : '');
                $label = is_array($d) ? (string) ($d['label'] ?? '') : '';
                $clean = $this->cleanEmail($email);
                if ($clean === null) continue;

                [$local, $dom] = explode('@', $clean, 2);
                $domSama = $host !== '' && ($dom === $host || str_ends_with($dom, '.' . $host) || str_ends_with($host, '.' . $dom));
                if (isset($known[$clean]) || ($domSama && str_contains($ctxLower, $local))) {
                    $aiCand[] = [$clean, $label];
                } else {
                    $dropped++;
                }
            }
            if ($dropped > 0) {
                $note = trim($note . " {$dropped} email dari AI dibuang (tidak ditemukan di halaman)");
            }
        }

        $emails = [];
        $push = function (array $pairs, string $defaultLabel = '') use (&$emails): void {
            foreach ($pairs as $pair) {
                [$email, $label] = is_array($pair) ? $pair + [1 => ''] : [$pair, ''];
                $clean = $this->cleanEmail((string) $email);
                if ($clean === null || isset($emails[$clean])) continue;
                $label = trim((string) $label) !== '' ? (string) $label : $defaultLabel;
                $emails[$clean] = [$clean, mb_substr(trim($label), 0, 255)];
                if (count($emails) >= self::MAX_EMAILS) return;
            }
        };

        $push($aiCand);
        $push(array_map(static fn ($e) => [$e, ''], array_unique($strong)), 'Dari link mailto/Cloudflare');
        if (count($emails) === 0) {
            $push(array_map(static fn ($e) => [$e, ''], array_unique($weak)), 'Ditemukan via Regex');
            if (count($emails) > 0 && $note === '') $note = 'AI tidak menemukan email, fallback ke regex';
        } elseif (! is_array($ai)) {
            // AI tidak dipakai / gagal: email dari teks biasa (mis. "info [at] domain [dot] com") milik domain situs tetap diambil
            $sameSite = static function (string $e) use ($host): bool {
                $d = (string) substr((string) strrchr($e, '@'), 1);
                return $d !== '' && $host !== '' && ($d === $host || str_ends_with($d, '.' . $host) || str_ends_with($host, '.' . $d));
            };
            $push(array_map(static fn ($e) => [$e, ''], array_filter(array_unique($weak), $sameSite)), 'Ditemukan via Regex');
        }

        if (count($emails) > 0) {
            $ordered = [];
            foreach (self::rank(array_values($emails), $url) as $rEmail) {
                if (isset($emails[$rEmail])) $ordered[$rEmail] = $emails[$rEmail];
            }
            if (count($ordered) === count($emails)) $emails = $ordered;
        }

        return [
            'emails' => array_values($emails),
            'tokens' => $tokens,
            'note'   => $note,
            'url'    => $url,
            'phones'   => $b['phones'] ?? [],
            'websites' => $b['websites'] ?? [],
        ];
    }

    /* ==================================================================
       GEMINI
       ================================================================== */

    public function listModels(): array
    {
        try {
            $res = $this->api->get(self::API_BASE . 'models?pageSize=200', [
                'headers' => ['x-goog-api-key' => $this->apiKey],
            ]);
        } catch (\Throwable $e) {
            throw $this->connectionError($e);
        }
        $code = $res->getStatusCode();
        $raw  = (string) $res->getBody();
        if ($code !== 200) throw $this->apiException($code, $raw);

        $out = [];
        foreach ((json_decode($raw, true)['models'] ?? []) as $m) {
            $id = (string) preg_replace('#^models/#', '', (string) ($m['name'] ?? ''));
            if (! str_starts_with($id, 'gemini-') || ! in_array('generateContent', $m['supportedGenerationMethods'] ?? [], true)) {
                continue;
            }
            if (preg_match('/image|tts|live|audio|embedding|robotics|computer-use|native|aqa|customtools/i', $id)) {
                continue;
            }
            $out[] = ['id' => $id, 'name' => (string) ($m['displayName'] ?? $id)];
        }
        usort($out, static fn ($a, $b) => strcmp($a['id'], $b['id']));
        return $out;
    }

    public function ping(): array
    {
        [$txt, $tokens] = $this->call('Balas hanya dengan satu kata: OK', false, 1);
        return ['reply' => trim(mb_substr($txt, 0, 40)), 'tokens' => $tokens];
    }

    private function askGemini(string $context): array
    {
        $prompt = "Kamu adalah ekstraktor email. Dari teks website berikut, ambil HANYA alamat email "
            . "yang benar-benar tertulis (termasuk yang disamarkan seperti 'info [at] domain [dot] com').\n"
            . "PENTING:\n"
            . "- Jika ada [AREA HALAMAN KONTAK], prioritaskan email dari situ.\n"
            . "- Jika ada [AREA FOOTER], periksa juga email di footer.\n"
            . "- JANGAN mengarang email. Jika tidak yakin, jangan sertakan.\n"
            . "- Abaikan placeholder (example.com, yourname@, test@), nama file gambar, "
            . "dan email pihak ketiga (sentry, wixpress, dll).\n"
            . "- Jika tidak ada email, balas [].\n"
            . "Balas HANYA JSON array tanpa penjelasan:\n"
            . "[{\"email\":\"...\",\"label\":\"departemen jika ada\"}]\n\n"
            . "=== TEKS WEBSITE ===\n" . $context;

        [$txt, $tokens] = $this->call($prompt, true, 4);
        return [$this->parseJson($txt), $tokens];
    }

    private function parseJson(string $txt): ?array
    {
        $txt = trim((string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($txt)));
        $d   = json_decode($txt, true);
        if (! is_array($d) && preg_match('/\[.*\]/s', $txt, $m)) $d = json_decode($m[0], true);
        if (is_array($d) && isset($d['emails']) && is_array($d['emails'])) $d = $d['emails'];
        if (is_array($d) && isset($d['email'])) $d = [$d];
        return is_array($d) ? $d : null;
    }

    private function call(string $prompt, bool $json, int $maxTry = 3): array
    {
        $url = self::API_BASE . 'models/' . rawurlencode($this->model) . ':generateContent';
        // maxOutputTokens dilonggarkan: model "thinking" ikut menghitung token berpikirnya di sini
        $gen = ['temperature' => 0, 'maxOutputTokens' => 8192];
        if ($json) $gen['responseMimeType'] = 'application/json';

        $body = [
            'contents'         => [['role' => 'user', 'parts' => [['text' => mb_scrub($prompt, 'UTF-8')]]]],
            'generationConfig' => $gen,
        ];
        // JSON disusun manual: byte UTF-8 yang rusak diganti, bukan membuat json_encode gagal diam-diam
        $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($payload === false) {
            throw new \RuntimeException('Gagal menyusun permintaan ke Gemini');
        }

        $last       = null;
        $limited    = false;
        $overloaded = false;
        for ($try = 1; $try <= $maxTry; $try++) {
            try {
                $res = $this->api->post($url, [
                    'body'    => $payload,
                    'timeout' => 90,
                    'headers' => ['Content-Type' => 'application/json', 'x-goog-api-key' => $this->apiKey],
                ]);
            } catch (\Throwable $e) {
                $last = $this->connectionError($e);
                if ($last instanceof QuotaException) throw $last;
                $overloaded = true;
                if ($try < $maxTry) $this->backoff($try);
                continue;
            }

            $code = $res->getStatusCode();
            $raw  = (string) $res->getBody();

            if ($code === 200) return $this->readResponse($raw);

            if ($code === 429) {
                $limited = true;
                $flat    = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $raw));
                if (str_contains($flat, 'perday')) {
                    throw new QuotaException("Kuota harian model '{$this->model}' habis.", QuotaException::DAILY);
                }
                if (preg_match('/limit:\s*0\b/i', $raw)) {
                    throw new QuotaException("Model '{$this->model}' tidak tersedia untuk API key ini (kuota 0).", QuotaException::MODEL_MISSING);
                }
                $wait = preg_match('/"retryDelay"\s*:\s*"(\d+(?:\.\d+)?)s"/', $raw, $m)
                    ? (int) ceil((float) $m[1]) + 2
                    : 20 * $try;
                if ($try < $maxTry) sleep(min(70, $wait));
                continue;
            }

            if ($code >= 500) {
                // 500 / 502 / 503 / 504: Gemini sedang sibuk -> tunggu makin lama lalu coba lagi
                $overloaded = true;
                $last       = new \RuntimeException("Gemini sedang gangguan (HTTP {$code})");
                if ($try < $maxTry) $this->backoff($try);
                continue;
            }

            throw $this->apiException($code, $raw);
        }

        if ($limited) {
            throw new QuotaException("Limit per menit model '{$this->model}' terlewati.", QuotaException::RATE);
        }
        if ($overloaded) {
            throw new QuotaException(
                "Gemini sedang sibuk untuk model '{$this->model}'" . ($last ? ' (' . $this->short($last->getMessage()) . ')' : ''),
                QuotaException::UNAVAILABLE
            );
        }
        throw $last ?? new \RuntimeException('Gemini tidak merespons');
    }

    /** Jeda antar percobaan ulang: 4, 8, 16 detik (+ acak 0-2 dtk), maks 30. */
    private function backoff(int $try): void
    {
        sleep(min(30, 4 * (2 ** ($try - 1))) + random_int(0, 2));
    }

    private function readResponse(string $raw): array
    {
        $j      = json_decode($raw, true) ?: [];
        $tokens = (int) ($j['usageMetadata']['totalTokenCount'] ?? 0);

        if (! empty($j['promptFeedback']['blockReason'])) {
            throw new \RuntimeException('Konten ditolak AI (' . $j['promptFeedback']['blockReason'] . ')');
        }
        $txt = '';
        foreach ($j['candidates'][0]['content']['parts'] ?? [] as $part) {
            if (! empty($part['thought'])) continue;
            $txt .= (string) ($part['text'] ?? '');
        }
        if (trim($txt) === '') {
            throw new \RuntimeException('Respons AI kosong (' . ($j['candidates'][0]['finishReason'] ?? 'tanpa alasan') . ')');
        }

        return [$txt, $tokens];
    }

    private function connectionError(\Throwable $e): \RuntimeException
    {
        if ($this->isSslError($e)) {
            return new QuotaException(
                'Sertifikat SSL tidak dikenali PHP. Perbaiki dengan set curl.cainfo di php.ini atau isi EXTRACTOR_CA_BUNDLE di .env.',
                QuotaException::SSL
            );
        }
        return new \RuntimeException('Gagal menghubungi Gemini: ' . $this->short($e->getMessage()));
    }

    private function apiException(int $code, string $raw): \RuntimeException
    {
        $j      = json_decode($raw, true);
        $msg    = (string) ($j['error']['message'] ?? substr($raw, 0, 200));
        $status = (string) ($j['error']['status'] ?? '');

        if ($code === 404) {
            return new QuotaException("Model '{$this->model}' tidak ditemukan. Silakan ganti model di pengaturan.", QuotaException::MODEL_MISSING);
        }
        if ($code === 401 || $code === 403 || ($code === 400 && (stripos($msg, 'api key') !== false || $status === 'API_KEY_INVALID'))) {
            return new QuotaException('API key tidak valid: ' . $this->short($msg), QuotaException::AUTH);
        }
        return new \RuntimeException("Gemini HTTP {$code}: " . $this->short($msg));
    }

    /* ==================================================================
       HTTP KE WEBSITE
       ================================================================== */

    private function verifyOption()
    {
        $ca = trim((string) env('EXTRACTOR_CA_BUNDLE', ''));
        if ($ca !== '' && is_file($ca)) return $ca;
        $v = filter_var(env('EXTRACTOR_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $v ?? true;
    }

    private function client($verify, bool $api = false): CURLRequest
    {
        $headers = $api
            ? ['Accept' => 'application/json']
            : [
                // Banyak website (WAF/hosting) menolak User-Agent kustom dengan HTTP 403, jadi dibuat seperti browser biasa
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
            ];

        return Services::curlrequest([
            'timeout'         => $api ? 90 : 20,
            'connect_timeout' => 10,
            'http_errors'     => false,
            'verify'          => $verify,
            'headers'         => $headers,
        ], null, null, false);
    }

    private function loose(): CURLRequest
    {
        return $this->loose ??= $this->client(false);   // klien website tanpa verifikasi SSL
    }

    private function isSslError(\Throwable $e): bool
    {
        return (bool) preg_match('/cURL error (35|51|58|60|77|83|90)\b|SSL/i', $e->getMessage());
    }

    private function short(string $m): string
    {
        $m = (string) preg_replace('/\s*\(see https?:\/\/\S+\)/i', '', $m);
        return mb_substr(trim((string) preg_replace('/\s+/', ' ', $m)), 0, 200);
    }

    /**
     * Buka website. Bila gagal, coba variasi lain: https<->http, dengan/tanpa "www.",
     * lalu halaman utama (kalau URL di database berupa path yang sudah mati).
     * Mengembalikan [url yang berhasil dibuka, isi HTML].
     */
    private function open(string $url): array
    {
        $p      = parse_url($url) ?: [];
        $host   = (string) ($p['host'] ?? '');
        $port   = isset($p['port']) ? ':' . $p['port'] : '';
        $rest   = ($p['path'] ?? '') . (isset($p['query']) ? '?' . $p['query'] : '');
        $scheme = strtolower((string) ($p['scheme'] ?? 'https')) === 'http' ? ['http', 'https'] : ['https', 'http'];
        $hosts  = [$host, str_starts_with($host, 'www.') ? substr($host, 4) : 'www.' . $host];

        $paths = [$rest];
        if ($rest !== '' && $rest !== '/') {
            $paths[] = '/';   // URL di database berupa path: coba juga halaman utamanya
        }

        $cands = [[$host, $url]];
        foreach ($paths as $path) {
            foreach ($hosts as $h) {
                foreach ($scheme as $s) {
                    $cands[] = [$h, "{$s}://{$h}{$port}{$path}"];
                }
            }
        }

        $first = null;
        $dead  = [];
        $seen  = [];
        foreach ($cands as [$h, $c]) {
            if (isset($seen[$c]) || isset($dead[$h])) continue;
            $seen[$c] = true;
            try {
                return [$c, $this->get($c)];
            } catch (\Throwable $e) {
                $first ??= $e;   // pesan yang dilaporkan = percobaan pertama (URL asli)
                if (stripos($e->getMessage(), 'resolve host') !== false) $dead[$h] = true;   // DNS mati: jangan buang waktu
            }
        }
        throw $first ?? new \RuntimeException('Gagal membuka website');
    }

    private function get(string $url): string
    {
        try {
            $res = $this->http->get($url);
        } catch (\Throwable $e) {
            if (! $this->isSslError($e)) {
                throw new \RuntimeException('Gagal membuka website: ' . $this->short($e->getMessage()));
            }
            try {
                $res = $this->loose()->get($url);
            } catch (\Throwable $e2) {
                throw new \RuntimeException('Gagal membuka website: ' . $this->short($e2->getMessage()));
            }
        }

        $code = $res->getStatusCode();
        if ($code >= 400) throw new \RuntimeException('Website membalas HTTP ' . $code);
        $type = strtolower($res->getHeaderLine('Content-Type'));
        if ($type !== '' && ! preg_match('#text/|html|xml#', $type)) {
            throw new \RuntimeException('Bukan halaman web (' . $type . ')');
        }

        $body = (string) $res->getBody();
        if (! mb_check_encoding($body, 'UTF-8')) {
            $body = mb_convert_encoding($body, 'UTF-8', 'Windows-1252');
        }
        return mb_strcut($body, 0, 1500000, 'UTF-8');
    }

    /* ==================================================================
       PARSING HTML
       ================================================================== */

    private function findPages(string $base, string $html, string $host): array
    {
        $pages = [$base];
        $cand  = [];
        if (preg_match_all('/<a\s[^>]*?href\s*=\s*["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $a) {
                $hay = $a[1] . ' ' . strip_tags($a[2]);
                if (! preg_match('/contact|kontak|hubungi|about|tentang|profil|support|bantuan|kemitraan/iu', $hay)) {
                    continue;
                }
                $abs = $this->absolute($base, $a[1]);
                if ($abs === null || $this->bareHost($abs) !== $host) continue;
                if (preg_match('/\.(pdf|jpe?g|png|gif|webp|svg|zip|docx?|xlsx?|mp4)(\?|$)/i', $abs)) continue;
                $abs = (string) preg_replace('/#.*$/', '', $abs);
                if (! isset($cand[$abs])) {
                    $cand[$abs] = preg_match('/contact|kontak|hubungi/iu', $hay) ? 2 : 1;
                }
            }
        }
        arsort($cand);
        foreach (array_keys($cand) as $u) {
            if (rtrim($u, '/') === rtrim($base, '/')) continue;
            $pages[] = $u;
            if (count($pages) >= self::MAX_PAGES) break;
        }
        return $pages;
    }

    private function scanEmails(string $html): array
    {
        $strong = [];
        if (preg_match_all('/href\s*=\s*["\']mailto:([^"\'?#]+)/i', $html, $m)) {
            foreach ($m[1] as $x) {
                foreach (preg_split('/[,;]/', html_entity_decode($x)) ?: [] as $y) {
                    $strong[] = $y;
                }
            }
        }
        if (preg_match_all('/data-cfemail\s*=\s*["\']([0-9a-f]+)["\']/i', $html, $m)) {
            foreach ($m[1] as $hex) {
                $d = $this->decodeCf($hex);
                if ($d !== '') $strong[] = $d;
            }
        }

        $weak = [];
        $raw  = $this->deobfuscate(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (preg_match_all('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9](?:[A-Za-z0-9.\-]*[A-Za-z0-9])?\.[A-Za-z]{2,24}/', $raw, $m)) {
            $weak = $m[0];
        }
        return ['strong' => $strong, 'weak' => $weak];
    }

    /** Maksimum no HP yang disimpan per kontak. */
    private const MAX_PHONES = 10;

    /**
     * Cari no HP / telepon di sebuah halaman: link tel:/wa.me (kuat) dan teks biasa (lemah).
     * @return array<int,array{0:string,1:string}>  [[nomorMentah, label], ...]
     */
    private function scanPhones(string $html, string $page = ''): array
    {
        $out = [];

        if (preg_match_all('/href\s*=\s*["\']tel:([^"\'?#]+)/i', $html, $m)) {
            foreach ($m[1] as $x) {
                $out[] = [urldecode(html_entity_decode($x)), 'Link telepon'];
            }
        }
        if (preg_match_all('/(?:wa\.me\/|api\.whatsapp\.com\/send\?[^"\']*phone=)(\+?\d{9,15})/i', $html, $m)) {
            foreach ($m[1] as $x) {
                $out[] = [$x, 'WhatsApp'];
            }
        }

        $text = preg_replace('#<(script|style|noscript)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match_all('/(?<![\d\w])(?:\+62|62|0)[\s.\-()]*8\d[\d\s.\-]{7,12}\d|(?<![\d\w])(?:\+62|62|\(?0)\s*[2-7]\d{1,2}\)?[\s.\-]*\d{3,4}[\s.\-]*\d{3,5}/', $text, $m)) {
            foreach ($m[0] as $x) {
                $out[] = [trim($x), 'Dari teks halaman'];
            }
        }
        return $out;
    }

    /** Normalkan & hilangkan duplikat nomor. */
    private function uniquePhones(array $phones): array
    {
        $res = [];
        foreach ($phones as [$raw, $label]) {
            $n = \App\Libraries\ContactStore::normalizePhone((string) $raw);
            if ($n === null) continue;
            if (! isset($res[$n[1]]) || $label === 'WhatsApp' || $label === 'Link telepon') {
                $res[$n[1]] = [$n[0], $label];
            }
            if (count($res) >= self::MAX_PHONES) break;
        }
        return array_values($res);
    }

    private function decodeCf(string $hex): string
    {
        if (strlen($hex) < 4 || strlen($hex) % 2 !== 0) return '';
        $key = hexdec(substr($hex, 0, 2));
        $out = '';
        for ($i = 2, $n = strlen($hex); $i < $n; $i += 2) {
            $out .= chr(hexdec(substr($hex, $i, 2)) ^ $key);
        }
        return $out;
    }

    private function deobfuscate(string $t): string
    {
        $t = preg_replace('/\s*[\[\(\{]\s*(?:at|@)\s*[\]\)\}]\s*/i', '@', $t) ?? $t;
        return preg_replace('/\s*[\[\(\{]\s*dot\s*[\]\)\}]\s*/i', '.', $t) ?? $t;
    }

    private function toChunk(string $page, string $html, array $mails, bool $isContactPage = false): string
    {
        $clean = preg_replace('#<(script|style|noscript|svg)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $clean = preg_replace('#<!--.*?-->#s', ' ', $clean) ?? $clean;
        $text  = html_entity_decode(preg_replace('/<[^>]+>/', ' ', $clean) ?? $clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text  = mb_scrub($text, 'UTF-8');
        $text  = trim((string) preg_replace('/\s+/u', ' ', $this->deobfuscate($text)));

        $len = strlen($text);
        $out = [];

        if ($isContactPage) {
            $out[] = "[AREA HALAMAN KONTAK - SELURUH TEKS]\n" . mb_substr($text, 0, 6000);
        } else {
            $footerStart = max(0, $len - 1200);
            $footer      = mb_scrub(substr($text, $footerStart), 'UTF-8');
            if ($footer !== '') {
                $out[] = "[AREA FOOTER / BAWAH HALAMAN]\n" . $footer;
            }
            $spans = [];
            if (preg_match_all('/@|e-?mail|surel|kontak|hubungi/i', $text, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $hit) {
                    $a = max(0, $hit[1] - 100);
                    $b = min($len, $hit[1] + 100);
                    $n = count($spans);
                    if ($n && $a <= $spans[$n - 1][1]) {
                        $spans[$n - 1][1] = $b;
                    } else {
                        if ($n >= 15) break;
                        $spans[] = [$a, $b];
                    }
                }
            }
            foreach ($spans as [$a, $b]) {
                if ($a >= $footerStart) continue;
                $out[] = mb_scrub(substr($text, $a, $b - $a), 'UTF-8');
            }
        }

        $header = mb_substr($text, 0, 300);
        return "## PAGE {$page}\nMAILTO: " . implode(', ', array_unique($mails)) . "\nHEADER: {$header}\n\n" . implode("\n...\n", $out);
    }

    private function assertOfficialSite(string $url): void
    {
        $host = $this->bareHost($url);
        foreach (self::SKIP_HOSTS as $d) {
            if ($host === $d || str_ends_with($host, '.' . $d)) {
                throw new \RuntimeException('Website berupa media sosial/marketplace (' . $d . '), bukan situs resmi - dilewati');
            }
        }
    }

    private function bareHost(string $url): string
    {
        return strtolower((string) preg_replace('/^www\./i', '', (string) parse_url($url, PHP_URL_HOST)));
    }

    private function absolute(string $base, string $href): ?string
    {
        $href = trim(html_entity_decode($href));
        if ($href === '' || preg_match('/^(#|mailto:|tel:|javascript:|data:)/i', $href)) return null;
        if (preg_match('#^https?://#i', $href)) return $href;
        $p = parse_url($base);
        if (empty($p['scheme']) || empty($p['host'])) return null;
        $root = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
        if (strpos($href, '//') === 0) return $p['scheme'] . ':' . $href;
        if ($href[0] === '/') return $root . $this->dots($href);
        $path = $p['path'] ?? '/';
        if ($href[0] === '?') return $root . $path . $href;
        $dir = substr($path, 0, (int) strrpos($path, '/') + 1);
        return $root . $this->dots($dir . $href);
    }

    private function dots(string $path): string
    {
        $q = '';
        if (($i = strpos($path, '?')) !== false) {
            $q    = substr($path, $i);
            $path = substr($path, 0, $i);
        }
        $out = [];
        foreach (explode('/', $path) as $seg) {
            if ($seg === '' || $seg === '.') continue;
            if ($seg === '..') {
                array_pop($out);
                continue;
            }
            $out[] = $seg;
        }
        $res = '/' . implode('/', $out);
        if ($out && substr($path, -1) === '/') $res .= '/';
        return $res . $q;
    }

    private function cleanEmail(string $e): ?string
    {
        $e = strtolower(trim(rawurldecode($e), " \t\n\r\0\x0B.,;:<>()[]{}'\"/\\"));
        $e = (string) preg_replace('/^mailto:/', '', $e);
        if ($e === '' || strlen($e) > 150 || ! filter_var($e, FILTER_VALIDATE_EMAIL)) return null;
        [$local, $domain] = explode('@', $e, 2);
        if (strpos($domain, '.') === false) return null;
        $tld = substr((string) strrchr($domain, '.'), 1);
        if (in_array($tld, self::JUNK_EXT, true) || in_array($local, self::FAKE_LOCAL, true)) return null;
        foreach (self::FAKE_DOMAIN as $d) {
            if ($domain === $d || str_ends_with($domain, '.' . $d)) return null;
        }
        return $e;
    }
}