<?php

namespace App\Libraries;

use Config\Email as EmailConfig;

class EmailSender
{
    /**
     * Kirim 1 email untuk 1 penerima.
     */
    public function kirimSatu(array $penerima, array $template, ?EmailConfig $smtpConfig = null): array
    {
        try {
            $data = [
                'nama'            => $this->pick($penerima, ['nama', 'nama_penerima'], ''),
                'nama_perusahaan' => $this->pick($penerima, ['nama_perusahaan', 'perusahaan'], ''),
                'no_hp'           => $this->pick($penerima, ['no_hp', 'telepon', 'telp'], ''),
                'email'           => $this->pick($penerima, ['email', 'alamat_email_penerima'], ''),
                'domisili'        => $this->pick($penerima, ['domisili', 'kota_kabupaten', 'kota'], ''),
            ];

            if ($data['email'] === '') {
                return ['sukses' => false, 'pesan' => 'Alamat email penerima kosong.', 'id_pesan' => null];
            }

            $subjek  = $this->gantiPlaceholder((string) ($template['subjek_email'] ?? ''), $data);
            $isiHtml = $this->gantiPlaceholder((string) ($template['isi_html']     ?? ''), $data);
            $isiTeks = $this->gantiPlaceholder((string) ($template['isi_teks']     ?? ''), $data);

            // =========================================================
            // Resolve SMTP
            // =========================================================
            if ($smtpConfig === null) {
                if (class_exists(SmtpConfigurator::class) && method_exists(SmtpConfigurator::class, 'apply')) {
                    try {
                        $cfg = (new SmtpConfigurator())->apply();
                        if ($cfg instanceof EmailConfig) {
                            $smtpConfig = $cfg;
                        }
                    } catch (\Throwable $e) {
                        log_message('debug', '[EmailSender] SmtpConfigurator gagal: ' . $e->getMessage());
                    }
                }
                if ($smtpConfig === null) {
                    $smtpConfig = config('Email');
                }
            }

            // ⚠️ KRITIS: paksa newline & CRLF "\r\n" di instance config
            //    agar base64 attachment di-chunk dengan CRLF (RFC 2045 compliant).
            //    Tanpa ini → PDF korup saat dibuka.
            $smtpConfig->newline = "\r\n";
            $smtpConfig->CRLF    = "\r\n";

            $email = \Config\Services::email($smtpConfig, false);
            $email->clear(true);
            $email->setMailType('html');

            // Force juga di instance (double protection)
            $email->setNewline("\r\n");
            $email->setCRLF("\r\n");

            $fromEmail = $smtpConfig->fromEmail ?: 'noreply@example.com';
            $fromName  = $smtpConfig->fromName  ?: 'Aplikasi Email';

            $email->setFrom($fromEmail, $fromName);
            $email->setTo($data['email']);
            $email->setSubject($subjek);

            // =========================================================
            // Embed gambar HTML → CID
            // =========================================================
            $isiHtml = $this->attachInlineImages($email, $isiHtml);

            // =========================================================
            // ATTACH PDF DULU (SEBELUM setMessage)
            // =========================================================
            $pdfNote     = '';
            $pdfUrl      = trim((string) ($template['attachment_pdf_url'] ?? ''));
            $tipePdf     = strtolower(trim((string) ($template['tipe_pdf'] ?? '')));

            if ($pdfUrl !== '' && in_array($tipePdf, ['attachment', 'attachments'], true)) {
                $result = $this->attachPdf($email, $pdfUrl);

                if ($result === true) {
                    $pdfNote = ' + PDF';
                } elseif ($result === 'too_large') {
                    $pdfNote = ' (PDF > 25MB, tidak dilampirkan)';
                } else {
                    $pdfNote = ' (PDF GAGAL DILAMPIRKAN)';
                    // Fallback: tambahkan link download ke body
                    $isiHtml .= '<hr style="margin:24px 0;border:0;border-top:1px solid #eee">'
                              . '<p style="font-size:13px;color:#666;">'
                              . '<strong>Lampiran PDF:</strong> '
                              . '<a href="' . esc($pdfUrl, 'attr') . '" target="_blank">Unduh dokumen PDF</a>'
                              . '</p>';
                }
            }

            // =========================================================
            // Set message TERAKHIR
            // =========================================================
            if (trim($isiTeks) !== '') {
                $email->setAltMessage($isiTeks);
            }
            $email->setMessage($isiHtml);

            // =========================================================
            // Kirim
            // =========================================================
            $sent = $email->send(false);

            if ($sent) {
                $idPesan = 'MSG-' . date('YmdHis')
                    . '-' . ($penerima['id'] ?? '0')
                    . '-' . bin2hex(random_bytes(4));

                log_message('info', '[EmailSender] OK → ' . $data['email'] . $pdfNote);
                return ['sukses' => true, 'pesan' => 'Terkirim' . $pdfNote, 'id_pesan' => $idPesan];
            }

            $errMsg = $this->ambilPesanError($email);
            log_message('error', '[EmailSender] GAGAL → ' . $data['email'] . ' | ' . $errMsg);
            return ['sukses' => false, 'pesan' => $errMsg, 'id_pesan' => null];
        } catch (\Throwable $e) {
            log_message('error', '[EmailSender] Exception: ' . $e->getMessage()
                . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return ['sukses' => false, 'pesan' => $e->getMessage(), 'id_pesan' => null];
        }
    }

    /* =========================================================
     * HELPERS
     * ========================================================= */

    protected function pick(array $arr, array $keys, $default = null)
    {
        foreach ($keys as $k) {
            if (isset($arr[$k]) && $arr[$k] !== '') return $arr[$k];
        }
        return $default;
    }

    protected function gantiPlaceholder(string $teks, array $data): string
    {
        return strtr($teks, [
            '{nama}'            => (string) $data['nama'],
            '{nama_perusahaan}' => (string) $data['nama_perusahaan'],
            '{perusahaan}'      => (string) $data['nama_perusahaan'],
            '{no_hp}'           => (string) $data['no_hp'],
            '{email}'           => (string) $data['email'],
            '{domisili}'        => (string) $data['domisili'],
            '{kota}'            => (string) $data['domisili'],
        ]);
    }

    /* =========================================================
     * PDF ATTACHMENT — WITH FULL VALIDATION
     * ========================================================= */

    /**
     * @return true|'too_large'|false
     */
    protected function attachPdf($email, string $pdfUrl)
    {
        $pdfUrl = trim($pdfUrl);
        if ($pdfUrl === '') {
            log_message('debug', '[PDF] URL kosong');
            return false;
        }

        // A. Cari file di filesystem
        $local = $this->resolvePdfPath($pdfUrl);
        if ($local === null) {
            log_message('warning', '[PDF] TIDAK ditemukan: ' . $pdfUrl
                . ' | FCPATH=' . FCPATH
                . ' | is_dir(FCPATH/uploads)=' . (is_dir(FCPATH . 'uploads/') ? 'YES' : 'NO'));
            $this->logDirListing(FCPATH . 'uploads/', 10);
            return false;
        }

        // B. Info file
        $size   = (int) @filesize($local);
        $sizeMb = round($size / 1048576, 2);
        $readOk = is_readable($local);
        $mime   = $this->detectMime($local);
        $hash   = @md5_file($local) ?: '?';

        log_message('info', sprintf(
            '[PDF] Found: %s | size=%.2fMB | readable=%s | mime=%s | md5=%s',
            $local, $sizeMb, $readOk ? 'YES' : 'NO', $mime, $hash
        ));

        if (!$readOk) {
            log_message('error', '[PDF] Tidak readable: ' . $local);
            return false;
        }

        // C. VALIDASI PDF LENGKAP — magic bytes + EOF marker
        if (!$this->isValidPdf($local)) {
            log_message('error', '[PDF] File bukan PDF valid (gagal magic bytes / EOF check): ' . $local);
            return false;
        }

        // D. Cek ukuran
        if ($sizeMb > 25) {
            log_message('error', '[PDF] ' . $sizeMb . 'MB > 25MB, tidak dilampirkan.');
            return 'too_large';
        }
        if ($sizeMb > 18) {
            log_message('warning', '[PDF] ' . $sizeMb . 'MB, berisiko ditolak SMTP (base64 overhead ~33%).');
        }

        // E. Deteksi file HTML yang di-rename jadi .pdf (penyebab paling umum "PDF korup")
        if (stripos($mime, 'html') !== false || stripos($mime, 'text/plain') !== false) {
            log_message('error', '[PDF] File ber-MIME "' . $mime . '" — bukan PDF asli (kemungkinan HTML 404 yang di-rename). Tidak dilampirkan: ' . $local);
            return false;
        }

        // F. Attach
        //    ⚠️ PENYEBAB PDF RUSAK SEBELUMNYA:
        //    CI4 Email::attach($file, $disposition, $newname, $mime):
        //    jika parameter $mime DIISI, CI4 menganggap $file adalah ISI file (buffer),
        //    BUKAN path. Akibatnya yang di-base64 hanyalah teks path-nya,
        //    sehingga PDF terkirim tapi isinya salah dan gagal dibuka.
        //    Solusi: baca isi file sendiri lalu kirim sebagai buffer + MIME + nama file.
        try {
            $content = @file_get_contents($local);
            if ($content === false || $content === '' || strncmp($content, '%PDF', 4) !== 0) {
                log_message('error', '[PDF] Gagal membaca isi file / bukan PDF: ' . $local);
                return false;
            }

            $namaFile = basename($local);
            $email->attach($content, 'attachment', $namaFile, 'application/pdf');
            unset($content);

            log_message('info', '[PDF] attach() OK (buffer): ' . $local);
            return true;
        } catch (\Throwable $e) {
            log_message('error', '[PDF] attach() EXCEPTION: ' . $e->getMessage() . ' | ' . $local);
            return false;
        }
    }

    /**
     * Cari file PDF di filesystem.
     */
    protected function resolvePdfPath(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;

        if (is_file($url) && $this->isValidPdf($url)) {
            return realpath($url) ?: $url;
        }

        $path     = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') $path = $url;

        $filename = rawurldecode(basename($path));
        $base     = preg_replace('/\.pdf$/i', '', $filename);

        $nameVariants = array_unique([
            $filename,
            $base . '.pdf',
            $base . '.PDF',
            $base . '.Pdf',
        ]);

        $relativePath = null;
        if (preg_match('#/uploads/(.+)$#i', $path, $m)) {
            $relativePath = rawurldecode($m[1]);
        }

        // Prioritas: public/uploads/
        $dirs = [
            FCPATH . 'uploads/',                        // ← utama
            FCPATH . 'uploads' . DIRECTORY_SEPARATOR,
            FCPATH,
            FCPATH . 'assets/uploads/',
            FCPATH . 'files/',
            WRITEPATH . 'uploads/',
            ROOTPATH . 'uploads/',
            ROOTPATH . 'public/uploads/',
            dirname(FCPATH) . '/uploads/',
            dirname(FCPATH) . '/public/uploads/',
        ];

        foreach ($dirs as $dir) {
            foreach ($nameVariants as $name) {
                $candidate = $dir . $name;
                if (is_file($candidate) && $this->isValidPdf($candidate)) {
                    return realpath($candidate) ?: $candidate;
                }
            }
            if ($relativePath) {
                $candidate = $dir . $relativePath;
                if (is_file($candidate) && $this->isValidPdf($candidate)) {
                    return realpath($candidate) ?: $candidate;
                }
            }
        }

        if (is_dir(FCPATH)) {
            foreach ($nameVariants as $name) {
                $found = $this->recursiveFind(FCPATH, $name, 4);
                if ($found !== null) return $found;
            }
        }

        log_message('debug', '[PDF] Gagal resolve. Dicoba: ' . implode(' | ', array_map(
            static fn($d) => $d . $filename, $dirs
        )));
        return null;
    }

    protected function recursiveFind(string $dir, string $filename, int $maxDepth = 4): ?string
    {
        if (!is_dir($dir)) return null;
        try {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST,
                \RecursiveIteratorIterator::CATCH_GET_CHILD
            );
            $it->setMaxDepth($maxDepth);
            foreach ($it as $file) {
                if (!$file->isFile()) continue;
                if (strcasecmp($file->getFilename(), $filename) !== 0) continue;
                $p = $file->getPathname();
                if ($this->isValidPdf($p)) return $p;
            }
        } catch (\Throwable $e) {
            log_message('debug', '[PDF] recursiveFind error: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Validasi PDF LENGKAP:
     *  1) magic bytes "%PDF" di awal
     *  2) EOF marker "%%EOF" di 2KB terakhir (deteksi truncation)
     *  3) ukuran ≥ 200 byte (PDF minimal ~200B)
     */
    protected function isValidPdf(string $path): bool
    {
        if (!is_file($path) || !is_readable($path)) return false;

        $size = (int) @filesize($path);
        if ($size < 200) return false;

        // Cek magic bytes di awal
        $fp = @fopen($path, 'rb');
        if (!$fp) return false;
        $head = fread($fp, 5);
        if (!is_string($head) || substr($head, 0, 4) !== '%PDF') {
            fclose($fp);
            return false;
        }

        // Cek EOF marker di akhir file (%%EOF atau %EOF atau \r\n%%EOF dll)
        $tailSize = min(2048, $size);
        fseek($fp, -$tailSize, SEEK_END);
        $tail = fread($fp, $tailSize);
        fclose($fp);

        // PDF valid selalu diakhiri %%EOF (dengan kemungkinan trailing whitespace)
        return is_string($tail) && preg_match('/%%EOF\s*$/s', $tail) === 1;
    }

    protected function detectMime(string $path): string
    {
        if (function_exists('finfo_open')) {
            $fi = @finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) {
                $mime = @finfo_file($fi, $path);
                finfo_close($fi);
                if (is_string($mime) && $mime !== '') return $mime;
            }
        }
        return 'application/octet-stream';
    }

    protected function logDirListing(string $dir, int $limit = 10): void
    {
        if (!is_dir($dir)) {
            log_message('debug', '[PDF] Dir tidak ada: ' . $dir);
            return;
        }
        try {
            $files = @scandir($dir) ?: [];
            $files = array_values(array_filter($files, static fn($f) => $f !== '.' && $f !== '..'));
            $list  = array_slice($files, 0, $limit);
            log_message('debug', '[PDF] Isi ' . $dir . ' (' . count($files) . ' item): '
                . implode(', ', $list) . (count($files) > $limit ? '...' : ''));
        } catch (\Throwable $e) {
            log_message('debug', '[PDF] scandir error: ' . $e->getMessage());
        }
    }

    /* =========================================================
     * IMAGE INLINE (CID)
     * ========================================================= */

    protected function urlToLocalPath(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;
        if (is_file($url)) return $url;

        $path     = parse_url($url, PHP_URL_PATH) ?: $url;
        $filename = rawurldecode(basename($path));

        $dirs = [
            FCPATH . 'uploads/',
            FCPATH,
            FCPATH . 'assets/uploads/',
            WRITEPATH . 'uploads/',
            ROOTPATH . 'uploads/',
        ];
        foreach ($dirs as $dir) {
            $p = $dir . $filename;
            if (is_file($p) && is_readable($p)) return $p;
        }
        if (is_dir(FCPATH)) {
            return $this->recursiveFind(FCPATH, $filename, 4);
        }
        return null;
    }

    protected function attachInlineImages($email, string $html): string
    {
        if ($html === '' || stripos($html, '<img') === false) return $html;

        return preg_replace_callback(
            '#<img\b([^>]*?)\bsrc\s*=\s*(["\'])(.*?)\2([^>]*)>#i',
            function ($m) use ($email) {
                $before = $m[1];
                $src    = trim($m[3]);
                $after  = $m[4];

                if ($src === '') return $m[0];
                if (stripos($src, 'data:') === 0 || stripos($src, 'cid:') === 0) return $m[0];

                $path = $this->urlToLocalPath($src);
                if (!$path) return $m[0];

                try {
                    // MIME dikosongkan agar CI4 membaca file dari path
                    $ok = $email->attach($path, 'inline', basename($path));
                    if ($ok === false) return $m[0];

                    // CID harus diminta dari CI4 (attach() tidak menerima parameter CID)
                    $cid = $email->setAttachmentCID($path);
                    if (!$cid) return $m[0];

                    return '<img' . $before . 'src="cid:' . $cid . '"' . $after . '>';
                } catch (\Throwable $e) {
                    log_message('warning', '[IMG] attach gagal: ' . $e->getMessage());
                    return $m[0];
                }
            },
            $html
        );
    }

    /* =========================================================
     * ERROR EXTRACTION
     * ========================================================= */

    protected function ambilPesanError($email): string
    {
        try {
            $debug = (string) $email->printDebugger(['headers', 'subject']);
            $debug = trim(strip_tags($debug));
            if ($debug !== '') return mb_substr($debug, 0, 1500);
        } catch (\Throwable $e) { /* ignore */ }
        return 'Gagal mengirim email (tidak ada detail dari SMTP).';
    }
}