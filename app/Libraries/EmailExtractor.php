<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class EmailExtractor
{
    public const DEFAULT_MODEL = 'gemini-3.5-flash-lite';

    private const API_BASE    = 'https://generativelanguage.googleapis.com/v1beta/';
    private const MAX_PAGES   = 5;
    private const MAX_CONTEXT = 16000;
    private const MAX_EMAILS  = 30;

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
    private CURLRequest $http;
    private ?CURLRequest $loose = null;

    public function __construct(string $apiKey, string $model = '')
    {
        $this->apiKey = trim($apiKey);
        $this->model  = trim($model) !== '' ? trim($model) : self::DEFAULT_MODEL;
        $this->http   = $this->client($this->verifyOption());
    }

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

    public function extract(string $url): array
    {
        [$url, $html] = $this->open($url);
        $host  = $this->bareHost($url);
        $pages = $this->findPages($url, $html, $host);

        $chunks = [];
        $strong = [];
        $weak   = [];
        foreach ($pages as $i => $page) {
            try {
                $h = $i === 0 ? $html : $this->get($page);
            } catch (\Throwable $e) {
                continue;
            }
            $found    = $this->scanEmails($h);
            $strong   = array_merge($strong, $found['strong']);
            $weak     = array_merge($weak, $found['weak']);
            // Kirim info apakah ini halaman kontak atau bukan
            $isContactPage = (bool) preg_match('/contact|kontak|hubungi/i', $page);
            $chunks[] = $this->toChunk($page, $h, $found['strong'], $isContactPage);
        }
        $context = mb_substr(implode("\n\n", $chunks), 0, self::MAX_CONTEXT);

        $tokens = 0;
        $note   = '';
        $ai     = null;
        if ($this->apiKey !== '') {
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
        } else {
            $note = 'tanpa API key: hanya regex';
        }

        $list = [];
        if (is_array($ai) && count($ai) > 0) {
            foreach ($ai as $d) {
                if (is_array($d)) {
                    $list[] = [(string) ($d['email'] ?? ''), (string) ($d['label'] ?? '')];
                } elseif (is_string($d)) {
                    $list[] = [$d, ''];
                }
            }
            // Tambahkan email dari mailto: (karena ini 100% valid)
            foreach ($strong as $e) {
                $list[] = [$e, 'Dari link mailto/Cloudflare'];
            }
        } else {
            // Fallback: Jika AI gagal atau tidak menemukan, gunakan Regex secara menyeluruh
            foreach (array_unique(array_merge($strong, $weak)) as $e) {
                $list[] = [$e, 'Ditemukan via Regex'];
            }
        }

        $emails = [];
        foreach ($list as [$email, $label]) {
            $email = $this->cleanEmail($email);
            if ($email === null || isset($emails[$email])) {
                continue;
            }
            $emails[$email] = [$email, mb_substr(trim($label), 0, 255)];
            if (count($emails) >= self::MAX_EMAILS) {
                break;
            }
        }

        return ['emails' => array_values($emails), 'tokens' => $tokens, 'note' => $note, 'url' => $url];
    }

    public function listModels(): array
    {
        try {
            $res = $this->http->get(self::API_BASE . 'models?pageSize=200', [
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

    private function verifyOption()
    {
        $ca = trim((string) env('EXTRACTOR_CA_BUNDLE', ''));
        if ($ca !== '' && is_file($ca)) return $ca;
        $v = filter_var(env('EXTRACTOR_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $v ?? true;
    }

    private function client($verify): CURLRequest
    {
        return Services::curlrequest([
            'timeout'         => 20,
            'connect_timeout' => 10,
            'http_errors'     => false,
            'verify'          => $verify,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (compatible; EmailFinder/2.0)',
                'Accept'          => 'text/html,application/xhtml+xml,application/json;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id,en;q=0.8',
            ],
        ], null, null, false);
    }

    private function loose(): CURLRequest
    {
        return $this->loose ??= $this->client(false);
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

    private function open(string $url): array
    {
        try {
            return [$url, $this->get($url)];
        } catch (\Throwable $e) {
            if (stripos($url, 'https://') === 0 && str_starts_with($e->getMessage(), 'Gagal membuka')) {
                $alt = 'http://' . substr($url, 8);
                try {
                    return [$alt, $this->get($alt)];
                } catch (\Throwable $e2) {}
            }
            throw $e;
        }
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

    /**
     * LOGIKA BARU: 
     * Jika Halaman Kontak (isContactPage = true): Ambil teks dari awal sampai akhir.
     * Jika Bukan Halaman Kontak (isContactPage = false): Ambil teks 1200 karakter terakhir (Footer).
     */
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
            // Jika halaman Kontak, ambil seluruh teks (karena biasanya isinya sedikit)
            $out[] = "[AREA HALAMAN KONTAK - SELURUH TEKS]\n" . $text;
        } else {
            // Jika halaman Home/Lainnya, fokus ke Footer
            $footerStart = max(0, $len - 1200);
            $footer = substr($text, $footerStart);
            if ($footer !== '') {
                $out[] = "[AREA FOOTER / BAWAH HALAMAN]\n" . $footer;
            }
            
            // Tetap cek jika ada kata "kontak" di tengah halaman
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
                if ($a >= $footerStart) continue; // Skip jika sudah ada di footer
                $out[] = mb_scrub(substr($text, $a, $b - $a), 'UTF-8');
            }
        }

        $header = mb_substr($text, 0, 300);
        return "## PAGE {$page}\nMAILTO: " . implode(', ', array_unique($mails)) . "\nHEADER: {$header}\n\n" . implode("\n...\n", $out);
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

    private function askGemini(string $context): array
    {
        $prompt = "Dari teks website berikut, ambil semua alamat email kontak milik perusahaan. "
            . "PENTING: Halaman ini mungkin halaman 'Kontak' atau halaman biasa. "
            . "Jika ada label [AREA HALAMAN KONTAK], ambil email dari situ. "
            . "Jika ada label [AREA FOOTER], prioritaskan email di bagian footer/bawah. "
            . "Termasuk email yang disamarkan (contoh: 'info [at] domain [dot] com'). "
            . "Abaikan email contoh/placeholder, nama file gambar, dan email pihak ketiga. "
            . 'Balas HANYA JSON array: [{"email":"...","label":"nama departemen jika ada"}]. '
            . "Jika tidak ada, balas [].\n\n" . $context;

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

    private function call(string $prompt, bool $json, int $maxTry): array
    {
        $url = self::API_BASE . 'models/' . rawurlencode($this->model) . ':generateContent';
        $gen = ['temperature' => 0, 'maxOutputTokens' => 4096];
        if ($json) $gen['responseMimeType'] = 'application/json';
        
        $body = [
            'contents'         => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => $gen,
        ];

        $last    = null;
        $limited = false;
        for ($try = 1; $try <= $maxTry; $try++) {
            try {
                $res = $this->http->post($url, [
                    'json'    => $body,
                    'timeout' => 90,
                    'headers' => ['x-goog-api-key' => $this->apiKey],
                ]);
            } catch (\Throwable $e) {
                $last = $this->connectionError($e);
                if ($last instanceof QuotaException) throw $last;
                if ($try < $maxTry) sleep(3 * $try);
                continue;
            }

            $code = $res->getStatusCode();
            $raw  = (string) $res->getBody();

            if ($code === 200) return $this->readResponse($raw);

            if ($code === 429) {
                $limited = true;
                $flat    = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $raw));
                if (str_contains($flat, 'perday')) {
                    throw new QuotaException('Kuota harian Gemini habis.');
                }
                if (preg_match('/limit:\s*0\b/i', $raw)) {
                    throw new QuotaException("Model '{$this->model}' tidak tersedia untuk API key ini (kuota 0).");
                }
                $wait = preg_match('/"retryDelay"\s*:\s*"(\d+(?:\.\d+)?)s"/', $raw, $m)
                    ? (int) ceil((float) $m[1]) + 2
                    : 20 * $try;
                if ($try < $maxTry) sleep(min(70, $wait));
                continue;
            }

            if ($code >= 500) {
                $last = new \RuntimeException("Gemini sedang gangguan (HTTP {$code})");
                if ($try < $maxTry) sleep(5 * $try);
                continue;
            }

            throw $this->apiException($code, $raw);
        }

        if ($limited) {
            throw new QuotaException('Limit per menit Gemini terlewati. Turunkan "Kontak per menit".');
        }
        throw $last ?? new \RuntimeException('Gemini tidak merespons');
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
                'Sertifikat SSL tidak dikenali PHP. Perbaiki dengan set curl.cainfo di php.ini atau isi EXTRACTOR_CA_BUNDLE di .env.'
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
            return new QuotaException("Model '{$this->model}' tidak ditemukan. Silakan ganti model di pengaturan.");
        }
        if ($code === 401 || $code === 403 || ($code === 400 && (stripos($msg, 'api key') !== false || $status === 'API_KEY_INVALID'))) {
            return new QuotaException('API key tidak valid: ' . $this->short($msg));
        }
        return new \RuntimeException("Gemini HTTP {$code}: " . $this->short($msg));
    }
}