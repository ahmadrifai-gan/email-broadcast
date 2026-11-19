<?php

namespace App\Libraries;

use Exception;

class WebsiteExtractor
{
    protected int $timeout = 15;              // Timeout 15 detik per halaman (PERUBAHAN 1)
    protected int $maxBytesPerPage = 2097152; // 2 MB per halaman
    protected int $maxPagesPerCompany = 30;  // Maksimal 30 halaman per perusahaan (responsif & cepat)
    protected int $maxDepth = 2;             // Kedalaman 2 tautan dari halaman utama
    protected int $delaySeconds = 0;         // Tanpa jeda lambat agar selesai dalam hitungan detik
    protected string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /**
     * Ekstrak teks informatif dan kandidat produk + merk dari website perusahaan
     * Menggunakan 4 urutan sumber:
     * a. Data terstruktur (JSON-LD Schema.org Product/ItemList, OpenGraph, Meta)
     * b. Sitemap XML (robots.txt dan /sitemap.xml)
     * c. Menu dan halaman daftar produk/kategori serta pagination
     * d. Teks halaman produk, judul item, heading, dan baris Merk/Brand
     *
     * @param string|null $url URL website
     * @return array [success => bool, content => string, url => string, candidates => array, candidates_text => string, groups => array, menu_read => bool, error => ?string, logs => array, pages_crawled => int]
     */
    public function extractText(?string $url): array
    {
        $rawUrl = trim($url ?? '');
        if (empty($rawUrl) || $rawUrl === '-' || $rawUrl === 'n/a') {
            return [
                'success'         => false,
                'content'         => '',
                'url'             => '',
                'candidates'      => [],
                'candidates_text' => '',
                'groups'          => [],
                'menu_read'       => false,
                'error'           => 'URL website tidak tersedia atau kosong.',
                'logs'            => ['URL website kosong.'],
                'pages_crawled'   => 0,
            ];
        }

        $logs = [];
        $normalizedUrl = $this->normalizeUrl($rawUrl);

        // Validasi keamanan URL dan SSRF (hanya http/https, tolak localhost & IP privat)
        $securityError = null;
        if (!$this->isValidSafeUrl($normalizedUrl, $securityError)) {
            return [
                'success'         => false,
                'content'         => '',
                'url'             => $normalizedUrl,
                'candidates'      => [],
                'candidates_text' => '',
                'groups'          => [],
                'menu_read'       => false,
                'error'           => 'Keamanan URL: ' . $securityError,
                'logs'            => ['Keamanan URL: ' . $securityError],
                'pages_crawled'   => 0,
            ];
        }

        $mainHost = strtolower((string) parse_url($normalizedUrl, PHP_URL_HOST));
        $logs[] = 'Mengakses halaman utama: ' . $normalizedUrl;

        // Ambil HTML halaman utama (coba HTTPS, jika SSL gagal coba HTTP)
        $mainResult = $this->fetchHtml($normalizedUrl, $mainHost);
        if (!$mainResult['success'] && str_starts_with($normalizedUrl, 'https://')) {
            $httpUrl = 'http://' . substr($normalizedUrl, 8);
            $retrySec = null;
            if ($this->isValidSafeUrl($httpUrl, $retrySec)) {
                $logs[] = 'Percobaan ulang via HTTP: ' . $httpUrl;
                $retryRes = $this->fetchHtml($httpUrl, $mainHost);
                if ($retryRes['success']) {
                    $mainResult = $retryRes;
                    $normalizedUrl = $httpUrl;
                    $mainHost = strtolower((string) parse_url($normalizedUrl, PHP_URL_HOST));
                }
            }
        }

        if (!$mainResult['success']) {
            $errMsg = $mainResult['error'] ?? 'Gagal mengambil konten website.';
            $logs[] = 'Gagal membaca halaman utama: ' . $errMsg;
            return [
                'success'         => false,
                'content'         => '',
                'url'             => $normalizedUrl,
                'candidates'      => [],
                'candidates_text' => '',
                'groups'          => [],
                'menu_read'       => false,
                'error'           => $errMsg,
                'logs'            => $logs,
                'pages_crawled'   => 0,
            ];
        }

        $mainHtml = $mainResult['html'];
        $effectiveMainUrl = $mainResult['url'];
        $pagesCrawled = 1;

        $allCandidates = []; // Array of ['name' => ..., 'brand' => ..., 'source' => ...]
        $allGroups     = [];
        $menuRead      = false;

        // a. Ekstrak Data Terstruktur dari Halaman Utama
        $structHome = $this->extractStructuredData($mainHtml, $effectiveMainUrl);
        foreach ($structHome as $p) {
            $allCandidates[] = $p;
            $menuRead = true;
        }

        // Ekstrak kandidat nama produk dan kelompok dari HTML Halaman Utama
        $mainExtracted = $this->extractCandidatesAndGroupsFromHtml($mainHtml, $effectiveMainUrl);
        foreach ($mainExtracted['candidates'] as $c) {
            $allCandidates[] = $c;
        }
        $allGroups = array_merge($allGroups, $mainExtracted['groups']);
        if ($mainExtracted['menu_read'] || !empty($structHome)) {
            $menuRead = true;
        }

        $cleanMainText = $this->cleanHtmlContent($mainHtml);
        $combinedText = '';
        if (!empty($cleanMainText)) {
            $combinedText .= "--- Halaman: {$effectiveMainUrl} ---\n" . $cleanMainText . "\n\n";
        }

        // Inisialisasi Queue perayapan dengan depth tracking
        // Format item queue: ['url' => string, 'depth' => int, 'type' => 'product'|'category'|'general']
        $queue = [];
        $visitedUrls = [rtrim($effectiveMainUrl, '/') => true, rtrim($normalizedUrl, '/') => true];

        // b. Sitemap Discovery: robots.txt dan /sitemap.xml
        $logs[] = 'Memeriksa robots.txt dan sitemap.xml...';
        $sitemapProductUrls = $this->discoverSitemapProductUrls($effectiveMainUrl, $mainHost);
        if (!empty($sitemapProductUrls)) {
            $logs[] = 'Ditemukan ' . count($sitemapProductUrls) . ' URL produk dari sitemap.';
            $queuedSitemap = 0;
            foreach ($sitemapProductUrls as $pUrl) {
                // Ekstrak nama produk kandidat langsung dari slug URL sitemap
                $pPath = trim((string) parse_url($pUrl, PHP_URL_PATH), '/');
                $pSlug = basename($pPath);
                if (!empty($pSlug) && $pSlug !== 'product' && $pSlug !== 'produk') {
                    $candName = str_replace(['-', '_'], ' ', $pSlug);
                    $candName = preg_replace('/\s+/u', ' ', ucwords($candName));
                    if (mb_strlen($candName) >= 3) {
                        $allCandidates[] = [
                            'name'   => $candName,
                            'brand'  => '',
                            'source' => 'sitemap',
                        ];
                    }
                }

                $cleanP = rtrim($pUrl, '/');
                if (!isset($visitedUrls[$cleanP]) && $queuedSitemap < 25) {
                    $visitedUrls[$cleanP] = true;
                    $queue[] = ['url' => $pUrl, 'depth' => 1, 'type' => 'product'];
                    $queuedSitemap++;
                }
            }
        } else {
            $logs[] = 'Sitemap tidak memuat URL produk khusus atau tidak tersedia.';
        }

        // c. Menu & Halaman Kategori/Daftar dari Halaman Utama
        $homeInternalLinks = $this->findCategoryAndProductLinks($mainHtml, $effectiveMainUrl);
        $logs[] = 'Ditemukan ' . count($homeInternalLinks) . ' tautan menu/kategori/produk dari halaman utama.';
        foreach ($homeInternalLinks as $iLink) {
            $cleanL = rtrim($iLink['url'], '/');
            if (!isset($visitedUrls[$cleanL])) {
                $visitedUrls[$cleanL] = true;
                $queue[] = ['url' => $iLink['url'], 'depth' => 1, 'type' => $iLink['type']];
            }
        }

        // Jalankan antrean perayapan hingga batas maksimal 200 halaman per perusahaan dan kedalaman 2 tautan
        while (!empty($queue) && $pagesCrawled < $this->maxPagesPerCompany) {
            $task = array_shift($queue);
            $targetUrl = $task['url'];
            $currentDepth = $task['depth'];

            if ($currentDepth > $this->maxDepth) {
                continue;
            }

            // Validasi keamanan URL sebelum dikunjungi
            $secErr = null;
            if (!$this->isValidSafeUrl($targetUrl, $secErr)) {
                continue;
            }

            // Jeda 1 detik antar halaman (PERUBAHAN 1)
            if ($this->delaySeconds > 0) {
                sleep($this->delaySeconds);
            }

            $pageRes = $this->fetchHtml($targetUrl, $mainHost);
            if (!$pageRes['success']) {
                $logs[] = "Gagal membaca (" . ($pageRes['error'] ?? 'Error') . "): {$targetUrl}";
                continue;
            }

            $pagesCrawled++;
            $pageHtml = $pageRes['html'];
            $pageEffUrl = $pageRes['url'];

            // a. Data terstruktur dari halaman ini
            $structPage = $this->extractStructuredData($pageHtml, $pageEffUrl);
            foreach ($structPage as $sp) {
                $allCandidates[] = $sp;
                $menuRead = true;
            }

            // Ekstrak kandidat nama produk dan kelompok dari HTML
            $pageExtracted = $this->extractCandidatesAndGroupsFromHtml($pageHtml, $pageEffUrl);
            foreach ($pageExtracted['candidates'] as $pc) {
                $allCandidates[] = $pc;
            }
            $allGroups = array_merge($allGroups, $pageExtracted['groups']);
            if ($pageExtracted['menu_read'] || !empty($structPage)) {
                $menuRead = true;
            }

            // d. Deteksi teks Brand/Merk di halaman produk
            $brandFound = $this->extractBrandFromText($pageHtml);
            if (!empty($brandFound)) {
                // Berikan brand ini ke kandidat yang berasal dari halaman ini jika belum punya brand
                foreach ($allCandidates as &$cand) {
                    if (empty($cand['brand']) && ($cand['url'] ?? '') === $pageEffUrl) {
                        $cand['brand'] = $brandFound;
                    }
                }
                unset($cand);
            }

            // Teks halaman yang bersih
            $cleanText = $this->cleanHtmlContent($pageHtml);
            if (!empty($cleanText)) {
                $combinedText .= "--- Halaman: {$pageEffUrl} ---\n" . $cleanText . "\n\n";
            }

            // c. Pagination & sub-kategori jika kedalaman masih < maxDepth
            if ($currentDepth < $this->maxDepth) {
                $subLinks = $this->findCategoryAndProductLinks($pageHtml, $pageEffUrl);
                foreach ($subLinks as $sLink) {
                    $cleanSub = rtrim($sLink['url'], '/');
                    if (!isset($visitedUrls[$cleanSub]) && count($visitedUrls) < ($this->maxPagesPerCompany * 2)) {
                        $visitedUrls[$cleanSub] = true;
                        $queue[] = ['url' => $sLink['url'], 'depth' => $currentDepth + 1, 'type' => $sLink['type']];
                    }
                }
            }
        }

        $logs[] = "Selesai merayapi {$pagesCrawled} halaman website.";

        // Jika menu atau daftar produk tidak ada di HTML (dimuat JavaScript), catat di log
        if (!$menuRead && empty($allCandidates)) {
            $logs[] = 'Daftar produk tidak terbaca (menu/daftar produk dimuat JavaScript).';
        }

        // Dedup dan format daftar kandidat
        $uniqueCandidates = $this->deduplicateCandidates($allCandidates);
        $uniqueGroups     = array_values(array_unique(array_filter($allGroups)));

        // Buat teks representasi daftar kandidat untuk dikirim ke AI
        $candLines = [];
        foreach ($uniqueCandidates as $cand) {
            $nameStr = $cand['name'];
            $brandStr = !empty($cand['brand']) ? " (Merk: {$cand['brand']})" : "";
            $candLines[] = "- {$nameStr}{$brandStr}";
        }
        $candidatesText = !empty($candLines) 
            ? implode("\n", $candLines) 
            : "(Daftar produk tidak terbaca atau tidak ditemukan kandidat khusus)";

        if (!empty($uniqueGroups)) {
            $candidatesText .= "\n\nKelompok/kategori (BUKAN produk fisik): " . implode(', ', $uniqueGroups);
        }

        return [
            'success'         => true,
            'content'         => trim($combinedText), // Teks lengkap tanpa batas karakter (akan di-chunk oleh AIService)
            'url'             => $normalizedUrl,
            'candidates'      => $uniqueCandidates,
            'candidates_text' => $candidatesText,
            'groups'          => $uniqueGroups,
            'menu_read'       => $menuRead || !empty($uniqueCandidates),
            'error'           => null,
            'logs'            => $logs,
            'pages_crawled'   => $pagesCrawled,
        ];
    }

    /**
     * a. Ekstraksi Data Terstruktur: JSON-LD Schema.org (Product, ItemList), OpenGraph, dan Meta tags
     */
    protected function extractStructuredData(string $html, string $pageUrl): array
    {
        $products = [];

        // 1. JSON-LD Schema.org
        if (preg_match_all('/<script\b[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            foreach ($matches[1] as $jsonStr) {
                $data = json_decode(trim($jsonStr), true);
                if (!$data) continue;

                $items = isset($data['@graph']) && is_array($data['@graph']) ? $data['@graph'] : [$data];
                foreach ($items as $item) {
                    $type = $item['@type'] ?? '';
                    if ($type === 'Product') {
                        $name = trim($item['name'] ?? '');
                        $brand = '';
                        if (!empty($item['brand'])) {
                            $brand = is_array($item['brand']) ? trim($item['brand']['name'] ?? '') : trim($item['brand']);
                        }
                        if (!empty($name)) {
                            $products[] = [
                                'name'   => $name,
                                'brand'  => $brand,
                                'url'    => $item['url'] ?? $pageUrl,
                                'source' => 'jsonld',
                            ];
                        }
                    } elseif ($type === 'ItemList' && !empty($item['itemListElement']) && is_array($item['itemListElement'])) {
                        foreach ($item['itemListElement'] as $elem) {
                            $subItem = $elem['item'] ?? $elem;
                            $name = trim($subItem['name'] ?? '');
                            $brand = '';
                            if (!empty($subItem['brand'])) {
                                $brand = is_array($subItem['brand']) ? trim($subItem['brand']['name'] ?? '') : trim($subItem['brand']);
                            }
                            if (!empty($name)) {
                                $products[] = [
                                'name'   => $name,
                                'brand'  => $brand,
                                'url'    => $subItem['url'] ?? $pageUrl,
                                'source' => 'jsonld_itemlist',
                            ];
                            }
                        }
                    }
                }
            }
        }

        // 2. OpenGraph & Meta tags pada halaman produk
        $ogTitle = '';
        $ogBrand = '';
        if (preg_match('/<meta\b[^>]*property=["\']og:title["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)) {
            $ogTitle = trim($m[1]);
        }
        if (preg_match('/<meta\b[^>]*(?:property|name)=["\'](?:og:brand|product:brand|brand)["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)) {
            $ogBrand = trim($m[1]);
        }
        if (!empty($ogTitle) && preg_match('#/(?:product|produk|item|p)/#i', $pageUrl)) {
            // Bersihkan nama situs dari og:title jika ada (misal: "Product Name | Site Name")
            $cleanTitle = preg_replace('/\s*[-|]\s*[^|]+$/', '', $ogTitle);
            $products[] = [
                'name'   => $cleanTitle ?: $ogTitle,
                'brand'  => $ogBrand,
                'url'    => $pageUrl,
                'source' => 'meta_og',
            ];
        }

        return $products;
    }

    /**
     * b. Sitemap Discovery: membaca robots.txt dan /sitemap.xml untuk menemukan URL produk di domain yang sama
     */
    protected function discoverSitemapProductUrls(string $baseUrl, string $mainHost): array
    {
        $productUrls = [];
        $sitemapUrls = [];

        // 1. Cek robots.txt
        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? $mainHost;
        $robotsUrl = "{$scheme}://{$host}/robots.txt";

        $resRobots = $this->fetchHtml($robotsUrl, $mainHost);
        if ($resRobots['success'] && !empty($resRobots['html'])) {
            if (preg_match_all('/^Sitemap:\s*(https?:\/\/\S+)/mi', $resRobots['html'], $smMatches)) {
                foreach ($smMatches[1] as $sm) {
                    $sitemapUrls[] = trim($sm);
                }
            }
        }

        // Fallback sitemap default jika belum ada
        if (empty($sitemapUrls)) {
            $sitemapUrls[] = "{$scheme}://{$host}/sitemap.xml";
            $sitemapUrls[] = "{$scheme}://{$host}/sitemap_index.xml";
        }

        // Baca hingga 3 file sitemap
        $sitemapsRead = 0;
        foreach (array_unique($sitemapUrls) as $smUrl) {
            if ($sitemapsRead >= 3) break;
            $resSm = $this->fetchHtml($smUrl, $mainHost);
            if (!$resSm['success'] || empty($resSm['html'])) continue;
            $sitemapsRead++;

            $xml = $resSm['html'];

            // Jika sitemap index yang memuat sitemap produk
            if (preg_match_all('/<sitemap>\s*<loc>(https?:\/\/[^<]+)<\/loc>/i', $xml, $subSitemaps)) {
                foreach ($subSitemaps[1] as $subSm) {
                    if (preg_match('/(?:product|produk|catalog|item|shop)/i', $subSm)) {
                        $resSub = $this->fetchHtml(trim($subSm), $mainHost);
                        if ($resSub['success'] && !empty($resSub['html'])) {
                            if (preg_match_all('/<loc>(https?:\/\/[^<]+)<\/loc>/i', $resSub['html'], $pLocs)) {
                                foreach ($pLocs[1] as $loc) {
                                    $loc = trim($loc);
                                    if ($this->isProductUrl($loc, $mainHost)) {
                                        $productUrls[] = $loc;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Jika urlset langsung
            if (preg_match_all('/<loc>(https?:\/\/[^<]+)<\/loc>/i', $xml, $locs)) {
                foreach ($locs[1] as $loc) {
                    $loc = trim($loc);
                    if ($this->isProductUrl($loc, $mainHost)) {
                        $productUrls[] = $loc;
                    }
                }
            }
        }

        return array_values(array_unique($productUrls));
    }

    /**
     * Memeriksa apakah URL merupakan halaman produk
     */
    protected function isProductUrl(string $url, string $mainHost): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) return false;
        if (!$this->isSameDomain($parsed['host'], $mainHost)) return false;

        $path = $parsed['path'] ?? '';
        // Abaikan asset statis
        if (preg_match('/\.(?:jpg|jpeg|png|gif|svg|webp|pdf|zip|rar|tar|gz|css|js|json|xml|ico|woff|ttf)$/i', $path)) {
            return false;
        }

        // Abaikan artikel, blog, author, tag, kategori umum
        if (preg_match('/(?:\/blog\/|\/article\/|\/artikel\/|\/news\/|\/author\/|\/tag\/)/i', $path)) {
            return false;
        }

        return (bool) preg_match('/(?:\/product\/|\/produk\/|\/item\/|\/p\/)/i', $path);
    }

    /**
     * c. Ekstrak tautan menu, kategori, pagination, dan halaman produk dari HTML
     */
    protected function findCategoryAndProductLinks(string $html, string $baseUrl): array
    {
        $baseHost = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
        preg_match_all('/<a\b[^>]*href=["\']([^"\'#\s]+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

        $results = [];
        $nonProductWords = 'login|masuk|sign\s*in|register|daftar|keranjang|cart|checkout|tentang\s*kami|tentang|about\s*us|about|karier|karir|career|careers|kontak|kontak\s*kami|contact\s*us|contact|blog|news|berita|kebijakan\s*privasi|privacy\s*policy|terms\s*of\s*service|syarat\s*dan\s*ketentuan|terms\s*&\s*conditions|faq|bantuan|help|bahasa|language|pilih\s*bahasa';

        foreach ($matches as $m) {
            $raw = trim($m[1]);
            $text = trim(strip_tags($m[2]));

            if (empty($raw) || str_starts_with($raw, 'javascript:') || str_starts_with($raw, 'mailto:') || str_starts_with($raw, 'tel:')) {
                continue;
            }

            if (preg_match('/^(?:' . $nonProductWords . ')$/iu', $text)) {
                continue;
            }

            $absolute = $this->resolveRelativeUrl($raw, $baseUrl);
            $parsed = parse_url($absolute);
            if (!$parsed || empty($parsed['host'])) continue;

            $linkHost = strtolower($parsed['host']);
            if (!$this->isSameDomain($linkHost, $baseHost)) continue;

            $path = $parsed['path'] ?? '';
            $query = $parsed['query'] ?? '';

            if (preg_match('/\.(?:jpg|jpeg|png|gif|svg|webp|pdf|zip|rar|css|js|json|xml|ico|woff|ttf)$/i', $path)) {
                continue;
            }
            if (preg_match('/(?:\/blog\/|\/article\/|\/artikel\/|\/news\/|\/author\/|\/tag\/)/i', $path)) {
                continue;
            }

            $cleanLink = rtrim($absolute, '/');
            if ($cleanLink === rtrim($baseUrl, '/')) continue;

            // Deteksi jenis link: produk, kategori, atau pagination
            $type = 'category';
            if ($this->isProductUrl($cleanLink, $baseHost)) {
                $type = 'product';
            } elseif (preg_match('/(?:page=\d+|p=\d+|\/page\/\d+|next|selanjutnya)/i', $path . ' ' . $query . ' ' . $text)) {
                $type = 'pagination';
            } elseif (!preg_match('/(?:product|produk|catalog|katalog|shop|collection|category|kategori|items|all)/i', $path . ' ' . $query . ' ' . $text)) {
                continue;
            }

            $results[] = [
                'url'  => $cleanLink,
                'type' => $type,
            ];
        }

        return $results;
    }

    /**
     * d. Ekstrak Brand/Merk dari kolom/baris di HTML
     */
    protected function extractBrandFromText(string $html): ?string
    {
        // Pola: Brand: Scarlett atau Merk: ... atau Merek: ...
        if (preg_match('/(?:Brand|Merk|Merek)\s*[:\-]\s*<[^>]+>\s*([^<]+)<\/[^>]+>/i', $html, $m)) {
            $brand = trim(strip_tags($m[1]));
            if (!empty($brand) && mb_strlen($brand) <= 50) return $brand;
        }
        if (preg_match('/\b(?:Brand|Merk|Merek)\s*[:\-]\s*([A-Za-z0-9\s\-&]+)(?:<|\n|$)/i', $html, $m)) {
            $brand = trim(strip_tags($m[1]));
            if (!empty($brand) && mb_strlen($brand) <= 50) return $brand;
        }
        return null;
    }

    /**
     * Ekstrak kandidat nama produk dan kelompok dari teks tautan & heading di HTML
     */
    public function extractCandidatesAndGroupsFromHtml(string $html, string $baseUrl = ''): array
    {
        $candidates = [];
        $groups = [];
        $menuRead = false;

        // Bersihkan script, style, dan Emotion CSS
        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', ' ', $html);
        $cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', ' ', $cleaned);
        $cleaned = preg_replace('/\.css-[a-zA-Z0-9_-]+\s*\{[^}]*\}/s', ' ', $cleaned);

        $nonProductRegex = '/^(?:login|masuk|sign\s*in|register|daftar|keranjang|cart|checkout|tentang\s*kami|tentang|about\s*us|about|karier|karir|career|careers|kontak|kontak\s*kami|contact\s*us|contact|blog|news|berita|kebijakan\s*privasi|privacy\s*policy|terms\s*of\s*service|syarat\s*dan\s*ketentuan|terms\s*&\s*conditions|faq|bantuan|help|bahasa|language|pilih\s*bahasa|home|beranda|search|cari|menu|see\s*all|view\s*all|shop\s*all|keunggulan|kenapa\s*memilih(?:\s*kami)?|mengapa\s*memilih(?:\s*kami)?|visi\s*(?:&|dan)?\s*misi|sertifikasi|sertifikat|fasilitas|metode\s*produksi|proses\s*produksi|testimoni|mitra|klien|galeri|gallery|download|unduh|lokasi|alamat)$/i';
        $groupRegex = '/^(?:all|shop\s*all|all\s*categories|categories|kategori|semua|face|face\s*care|body|body\s*care|parfum|perfume|fragrance|hair|hair\s*care|bundling|bundle|packages|paket|skincare|bodycare|haircare|men|women|pria|wanita)$/i';

        // 1. Tangkap kelompok dari buttons / tabs
        preg_match_all('/<(?:button|a|span|li|h[1-5])\b[^>]*>(.*?)<\/(?:button|a|span|li|h[1-5])>/is', $cleaned, $tagsMatch);
        foreach ($tagsMatch[1] as $inner) {
            $t = trim(strip_tags($inner));
            if (preg_match($groupRegex, $t)) {
                $groups[] = $t;
                $menuRead = true;
            }
        }

        // 2. Ekstrak dari tautan <a> produk/kategori
        preg_match_all('/<a\b([^>]*)>(.*?)<\/a>/is', $cleaned, $aMatches, PREG_SET_ORDER);
        foreach ($aMatches as $m) {
            $attrs = $m[1];
            $inner = $m[2];

            $href = '';
            if (preg_match('/href=["\']([^"\']*)["\']/i', $attrs, $h)) {
                $href = trim($h[1]);
            }

            $text = strip_tags($inner);
            $text = $this->cleanProductTitleText($text);

            if (empty($text) || mb_strlen($text) < 3) continue;
            if (preg_match($nonProductRegex, $text)) continue;

            if (preg_match($groupRegex, $text)) {
                $groups[] = $text;
                $menuRead = true;
                continue;
            }

            if (preg_match('/(?:\/blog\/|\/article\/|\/artikel\/|\/news\/|\/author\/)/i', $href)) {
                continue;
            }

            $isProductLink = preg_match('/(?:\/product\/|\/produk\/|\/item\/|\/p\/)/i', $href);
            $isProductMenu = preg_match('/(?:product|produk|katalog|catalog|shop)/i', $attrs . ' ' . $href);

            if ($isProductLink || $isProductMenu) {
                $menuRead = true;
                $candidates[] = [
                    'name'   => $text,
                    'brand'  => '',
                    'url'    => $href ? $this->resolveRelativeUrl($href, $baseUrl) : $baseUrl,
                    'source' => $isProductLink ? 'product_link' : 'menu_link',
                ];
            }
        }

        // 3. Ekstrak dari headings (h1-h4)
        preg_match_all('/<(?:h[1-4]|div\b[^>]*class=["\'][^"\']*(?:title|product-name|product-title|item-name)[^"\']*["\'])[^>]*>(.*?)<\/(?:h[1-4]|div)>/is', $cleaned, $hMatches);
        foreach ($hMatches[1] as $inner) {
            $text = strip_tags($inner);
            $text = $this->cleanProductTitleText($text);

            if (empty($text) || mb_strlen($text) < 3 || mb_strlen($text) > 150) continue;
            if (preg_match($nonProductRegex, $text)) continue;

            if (preg_match($groupRegex, $text)) {
                $groups[] = $text;
                continue;
            }

            $candidates[] = [
                'name'   => $text,
                'brand'  => '',
                'url'    => $baseUrl,
                'source' => 'heading',
            ];
        }

        return [
            'candidates' => $candidates,
            'groups'     => array_values(array_unique($groups)),
            'menu_read'  => $menuRead,
        ];
    }

    /**
     * Deduplikasi array kandidat berdasarkan nama (case-insensitive)
     */
    protected function deduplicateCandidates(array $candidates): array
    {
        $unique = [];
        $seen = [];

        foreach ($candidates as $c) {
            $name = trim($c['name'] ?? '');
            if (empty($name)) continue;

            $key = mb_strtolower(preg_replace('/\s+/u', ' ', $name), 'UTF-8');
            if (isset($seen[$key])) {
                // Jika entri lama belum punya brand dan entri baru punya brand, perbarui brand-nya
                if (empty($seen[$key]['brand']) && !empty($c['brand'])) {
                    $seen[$key]['brand'] = $c['brand'];
                    foreach ($unique as &$u) {
                        if (mb_strtolower(preg_replace('/\s+/u', ' ', $u['name']), 'UTF-8') === $key) {
                            $u['brand'] = $c['brand'];
                            break;
                        }
                    }
                    unset($u);
                }
                continue;
            }

            $entry = [
                'name'   => $name,
                'brand'  => trim($c['brand'] ?? ''),
                'url'    => $c['url'] ?? '',
                'source' => $c['source'] ?? 'website',
            ];

            $seen[$key] = $entry;
            $unique[] = $entry;
        }

        return $unique;
    }

    /**
     * Membersihkan judul produk dari harga, diskon, label promo, dan tombol aksi
     */
    protected function cleanProductTitleText(string $text): string
    {
        $text = preg_replace('/\.css-[a-zA-Z0-9_-]+\s*\{[^}]*\}/s', ' ', $text);
        $text = preg_replace('/(?:Rp\.?|IDR|USD|\$|EUR|£)\s*[0-9\.,]+/i', ' ', $text);
        $text = preg_replace('/\b[0-9]{1,3}(?:\.[0-9]{3})+(?:,[0-9]{2})?\s*(?:IDR|rupiah)?\b/i', ' ', $text);
        $text = preg_replace('/\b(?:disc(?:ount)?|diskon)?\s*[0-9]{1,2}%\s*(?:off|diskon)?\b/i', ' ', $text);
        $text = preg_replace('/\b(?:NEW|Best\s*Seller|Hot\s*Sale|Flash\s*Sale|Special\s*Offer|Terlaris|Promo(?:si)?|Diskon|Add\s*to\s*cart|Tambah(?:kan)?\s*ke\s*keranjang|Lihat\s*Keranjang|View\s*Cart|Beli\s*Sekarang|Buy\s*Now|Checkout|Beli|Buy|Quick\s*View|Lihat\s*Detail|View\s*Details?)\b/i', ' ', $text);
        $text = preg_replace('/^\[?\s*(?:NEW|LAUNCH|NEW\s*LAUNCH|HOT|PROMO)\s*\]?\s*/i', '', $text);
        $text = preg_replace('/^[!\[\]\s\-]+/', '', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return $text;
    }

    /**
     * Normalisasi format URL
     */
    protected function normalizeUrl(string $url): string
    {
        $url = trim($url);
        $url = str_replace('\\', '/', $url);

        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    /**
     * Validasi keamanan URL untuk mencegah SSRF (hanya http/https, tolak localhost dan IP privat)
     */
    protected function isValidSafeUrl(string $url, ?string &$errorMsg = null): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            $errorMsg = 'URL tidak memiliki format yang valid.';
            return false;
        }

        $scheme = strtolower($parsed['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            $errorMsg = 'Hanya protokol HTTP dan HTTPS yang diizinkan.';
            return false;
        }

        $host = strtolower($parsed['host']);

        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            $errorMsg = 'Akses ke localhost atau domain lokal ditolak.';
            return false;
        }

        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            $errorMsg = 'Gagal melakukan DNS lookup untuk domain: ' . $host;
            return false;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $errorMsg = 'Akses ke alamat IP privat atau reserved (' . $ip . ') ditolak.';
            return false;
        }

        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '127.') || str_starts_with($ip, '10.') || str_starts_with($ip, '192.168.') || str_starts_with($ip, '169.254.')) {
            $errorMsg = 'Akses ke alamat IP loopback/lokal ditolak.';
            return false;
        }

        return true;
    }

    /**
     * Memeriksa apakah dua host berada pada domain yang sama
     */
    protected function isSameDomain(string $host1, string $host2): bool
    {
        $clean1 = preg_replace('/^www\./i', '', strtolower(explode(':', $host1)[0]));
        $clean2 = preg_replace('/^www\./i', '', strtolower(explode(':', $host2)[0]));

        if ($clean1 === $clean2) return true;
        if (str_ends_with($clean1, '.' . $clean2) || str_ends_with($clean2, '.' . $clean1)) return true;

        return false;
    }

    /**
     * Mengambil HTML menggunakan cURL dengan batasan ukuran 2 MB, timeout 15 detik,
     * serta pencegahan pengalihan (redirect) ke domain luar.
     */
    protected function fetchHtml(string $url, string $originalHost): array
    {
        $currentUrl = $url;
        $maxRedirects = 4;
        $redirectCount = 0;

        while ($redirectCount <= $maxRedirects) {
            $ch = curl_init();
            $body = '';
            $maxBytes = $this->maxBytesPerPage;

            curl_setopt_array($ch, [
                CURLOPT_URL            => $currentUrl,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_USERAGENT      => $this->userAgent,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_ENCODING       => '',
                CURLOPT_HEADER         => true,
                CURLOPT_HTTPHEADER     => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Cache-Control: no-cache',
                ],
                CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) use (&$body, $maxBytes) {
                    $len = strlen($chunk);
                    if (strlen($body) + $len > $maxBytes) {
                        $remaining = $maxBytes - strlen($body);
                        if ($remaining > 0) {
                            $body .= substr($chunk, 0, $remaining);
                        }
                        return -1;
                    }
                    $body .= $chunk;
                    return $len;
                },
            ]);

            $execSuccess = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $error = curl_error($ch);
            curl_close($ch);

            $rawHeaders = substr($body, 0, $headerSize);
            $html = substr($body, $headerSize);

            if ($httpCode >= 300 && $httpCode < 400) {
                $location = '';
                if (preg_match('/^Location:\s*(.*?)$/mi', $rawHeaders, $matches)) {
                    $location = trim($matches[1]);
                }

                if (empty($location)) {
                    return [
                        'success' => false,
                        'error'   => 'Respons pengalihan (HTTP ' . $httpCode . ') tanpa header Location.',
                    ];
                }

                $nextUrl = $this->resolveRelativeUrl($location, $currentUrl);
                $nextHost = strtolower((string) parse_url($nextUrl, PHP_URL_HOST));

                if (!$this->isSameDomain($nextHost, $originalHost)) {
                    return [
                        'success' => false,
                        'error'   => 'Pengalihan ke domain lain (' . $nextHost . ') ditolak demi keamanan.',
                    ];
                }

                $secErr = null;
                if (!$this->isValidSafeUrl($nextUrl, $secErr)) {
                    return [
                        'success' => false,
                        'error'   => 'Tujuan pengalihan tidak aman: ' . $secErr,
                    ];
                }

                $currentUrl = $nextUrl;
                $redirectCount++;
                continue;
            }

            if ($html === false || (!empty($error) && !str_contains($error, 'Failed writing received data'))) {
                return [
                    'success' => false,
                    'error'   => 'Koneksi gagal (' . $error . ')',
                ];
            }

            if ($httpCode >= 400) {
                return [
                    'success' => false,
                    'error'   => 'Status HTTP error: ' . $httpCode,
                ];
            }

            return [
                'success' => true,
                'html'    => $html,
                'url'     => $currentUrl,
            ];
        }

        return [
            'success' => false,
            'error'   => 'Terlalu banyak pengalihan (melebihi batas ' . $maxRedirects . ').',
        ];
    }

    /**
     * Resolusi URL relatif menjadi URL absolut
     */
    protected function resolveRelativeUrl(string $rel, string $base): string
    {
        if (parse_url($rel, PHP_URL_SCHEME) !== null) {
            return $rel;
        }

        if (str_starts_with($rel, '//')) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $rel;
        }

        $baseParsed = parse_url($base);
        $scheme = $baseParsed['scheme'] ?? 'https';
        $host   = $baseParsed['host'] ?? '';
        $port   = isset($baseParsed['port']) ? ':' . $baseParsed['port'] : '';

        if (str_starts_with($rel, '/')) {
            return $scheme . '://' . $host . $port . $rel;
        }

        $path = $baseParsed['path'] ?? '/';
        $dir = preg_replace('#/[^/]*$#', '', $path);
        return $scheme . '://' . $host . $port . $dir . '/' . $rel;
    }

    /**
     * Membersihkan tag HTML dan menyisakan teks produk/kategori.
     */
    protected function cleanHtmlContent(string $html): string
    {
        $stripTagsPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<style\b[^>]*>(.*?)<\/style>/is',
            '/<noscript\b[^>]*>(.*?)<\/noscript>/is',
            '/<svg\b[^>]*>(.*?)<\/svg>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/<!--(.*?)-->/is',
        ];
        $html = preg_replace($stripTagsPatterns, ' ', $html);
        $html = preg_replace('/\.css-[a-zA-Z0-9_-]+\s*\{[^}]*\}/s', ' ', $html);

        $nonProductWords = 'login|masuk|sign\s*in|register|daftar|keranjang|cart|checkout|tentang\s*kami|tentang|about\s*us|about|karier|karir|career|careers|kontak|kontak\s*kami|contact\s*us|contact|blog|news|berita|kebijakan\s*privasi|privacy\s*policy|terms\s*of\s*service|syarat\s*dan\s*ketentuan|terms\s*&\s*conditions|faq|bantuan|help|bahasa|language|pilih\s*bahasa';
        $html = preg_replace('/<(?:li|a|button)\b[^>]*>\s*(?:' . $nonProductWords . ')\s*<\/(?:li|a|button)>/iu', ' ', $html);

        // Buang harga mata uang
        $html = preg_replace('/(?:Rp\.?|IDR|USD|\$|EUR|£)\s*[0-9\.,]+/i', ' ', $html);
        $html = preg_replace('/\b[0-9]{1,3}(?:\.[0-9]{3})+(?:,[0-9]{2})?\s*(?:IDR|rupiah)?\b/i', ' ', $html);
        $html = preg_replace('/\b(?:disc(?:ount)?|diskon)?\s*[0-9]{1,2}%\s*(?:off|diskon)?\b/i', ' ', $html);
        $html = preg_replace('/\b(?:NEW|Best\s*Seller|Hot\s*Sale|Flash\s*Sale|Special\s*Offer|Terlaris|Promo(?:si)?|Diskon)\b/i', ' ', $html);
        $html = preg_replace('/\b(?:Add\s*to\s*cart|Tambah(?:kan)?\s*ke\s*keranjang|Lihat\s*Keranjang|View\s*Cart|Beli\s*Sekarang|Buy\s*Now|Checkout|Beli|Buy|Quick\s*View|Lihat\s*Detail|View\s*Details?)\b/i', ' ', $html);

        $html = preg_replace('/<(?:p|div|h[1-6]|li|tr|section|article|dt|dd)\b[^>]*>/i', "\n", $html);
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $lines = explode("\n", $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (mb_strlen($line) >= 3) {
                if (!preg_match('/^[0-9\W]+$/u', $line)) {
                    $cleanLines[] = $line;
                }
            }
        }

        return implode("\n", $cleanLines);
    }
}
