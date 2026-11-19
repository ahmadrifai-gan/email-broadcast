<?php

namespace App\Libraries;

use Exception;
use Smalot\PdfParser\Parser;

class CatalogExtractor
{
    protected int $timeout = 60; // Timeout 60 detik (PERUBAHAN 2)
    protected int $maxBytes = 41943040; // 40 MB (PERUBAHAN 2)
    protected string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /**
     * Mengekstrak teks dari tautan katalog (PDF atau Google Drive)
     *
     * @param string|null $catalogUrl
     * @return array [success => bool, content => string, url => string, error => ?string]
     */
    public function extractText(?string $catalogUrl): array
    {
        $rawUrl = trim($catalogUrl ?? '');
        if (empty($rawUrl) || $rawUrl === '-' || $rawUrl === 'n/a') {
            return [
                'success' => false,
                'content' => '',
                'url'     => '',
                'error'   => 'Link katalog tidak tersedia.',
            ];
        }

        // Cek apakah Google Drive Folder
        if (preg_match('#drive\.google\.com/drive/folders/([a-zA-Z0-9_-]+)#i', $rawUrl)) {
            return [
                'success' => false,
                'content' => '',
                'url'     => $rawUrl,
                'error'   => 'Tautan berupa Folder Google Drive (tidak dapat diekstrak otomatis sebagai berkas tunggal).',
            ];
        }

        // Ubah Google Drive File view link menjadi direct download link
        $downloadUrl = $this->transformUrl($rawUrl);

        // Unduh berkas
        $downloadResult = $this->downloadFile($downloadUrl);
        if (!$downloadResult['success']) {
            return [
                'success' => false,
                'content' => '',
                'url'     => $downloadUrl,
                'error'   => $downloadResult['error'],
            ];
        }

        $content = $downloadResult['data'];

        // Cek apakah konten diawali dengan penanda PDF (%PDF)
        if (substr($content, 0, 4) !== '%PDF') {
            // Coba periksa apakah respons HTML Google Drive virus warning atau preview
            if (stripos($content, 'Google Drive - Virus scan warning') !== false || stripos($content, 'confirm=') !== false) {
                // Ekstrak confirm code jika ada
                if (preg_match('/confirm=([0-9A-Za-z_-]+)/', $content, $m)) {
                    $confirmUrl = $downloadUrl . '&confirm=' . $m[1];
                    $retry = $this->downloadFile($confirmUrl);
                    if ($retry['success'] && substr($retry['data'], 0, 4) === '%PDF') {
                        $content = $retry['data'];
                    }
                }
            }
        }

        if (substr($content, 0, 4) !== '%PDF') {
            return [
                'success' => false,
                'content' => '',
                'url'     => $downloadUrl,
                'error'   => 'Berkas yang diunduh bukan format PDF yang valid (mungkin halaman web atau akses dibatasi).',
            ];
        }

        // Ekstraksi teks dari PDF menggunakan Smalot\PdfParser
        try {
            $parser = new Parser();
            $pdf = $parser->parseContent($content);
            $text = $pdf->getText();

            // Bersihkan teks
            $text = trim(preg_replace('/\s+/', ' ', $text));

            if (empty($text) || mb_strlen($text) < 10) {
                return [
                    'success' => false,
                    'content' => '',
                    'url'     => $downloadUrl,
                    'error'   => 'PDF berhasil diunduh namun berupa gambar hasil scan (tidak ada teks tertanam).',
                ];
            }

            // Teks katalog diambil seluruhnya tanpa batas karakter (PERUBAHAN 2)
            $cleanText = $text;

            return [
                'success' => true,
                'content' => $cleanText,
                'url'     => $downloadUrl,
                'error'   => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'content' => '',
                'url'     => $downloadUrl,
                'error'   => 'Gagal mengekstrak teks PDF: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengubah URL Google Drive menjadi tautan direct download
     */
    protected function transformUrl(string $url): string
    {
        // Format: https://drive.google.com/file/d/{ID}/view
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#i', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }

        // Format: https://drive.google.com/open?id={ID}
        if (preg_match('#drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)#i', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }

        return $url;
    }

    /**
     * Download berkas dengan batasan ukuran 15 MB.
     * Jika ukuran berkas diketahui lebih dari 15 MB sebelum diunduh, lewati tanpa mengunduh.
     */
    protected function downloadFile(string $url): array
    {
        $ch = curl_init();
        $body = '';
        $maxBytes = $this->maxBytes;
        $tooLarge = false;

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 6,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT      => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING       => '',
            // Periksa ukuran berkas dari header Content-Length sebelum atau saat mulai unduh
            CURLOPT_HEADERFUNCTION => function ($ch, $headerLine) use ($maxBytes, &$tooLarge) {
                if (preg_match('/^Content-Length:\s*(\d+)/i', trim($headerLine), $m)) {
                    $length = (int) $m[1];
                    if ($length > $maxBytes) {
                        $tooLarge = true;
                        return -1; // Batalkan transfer cURL seketika
                    }
                }
                return strlen($headerLine);
            },
            CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) use (&$body, $maxBytes, &$tooLarge) {
                $len = strlen($chunk);
                if (strlen($body) + $len > $maxBytes) {
                    $tooLarge = true;
                    return -1; // Batalkan unduhan jika melebihi batas 15 MB
                }
                $body .= $chunk;
                return $len;
            },
        ]);

        $execResult = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($tooLarge) {
            return [
                'success' => false,
                'error'   => 'Ukuran berkas katalog lebih dari 15 MB, dilewati tanpa diunduh.',
            ];
        }

        if ($execResult === false || !empty($error)) {
            return [
                'success' => false,
                'error'   => 'Gagal mengunduh katalog (' . $error . ')',
            ];
        }

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'error'   => 'Gagal mengunduh berkas katalog (HTTP ' . $httpCode . ')',
            ];
        }

        return [
            'success' => true,
            'data'    => $body,
        ];
    }
}

