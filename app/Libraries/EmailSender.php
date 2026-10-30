<?php

namespace App\Libraries;

use Config\Email as EmailConfig;

class EmailSender
{
    /**
     * Kirim 1 email untuk 1 penerima.
     *
     * @param array            $penerima   Baris penerima_kampanye + nama_perusahaan, domisili
     * @param array            $template   Baris template_email + subjek_email (override)
     * @param EmailConfig|null $smtpConfig Config SMTP hasil resolve per-kampanye.
     *                                     Null → pakai SMTP aktif global (fallback .env).
     *
     * @return array ['sukses' => bool, 'pesan' => string, 'id_pesan' => string|null]
     */
    public function kirimSatu(array $penerima, array $template, ?EmailConfig $smtpConfig = null): array
    {
        $subjek  = $this->gantiPlaceholder($template['subjek_email'] ?? '', $penerima);
        $isiHtml = $this->gantiPlaceholder($template['isi_html'] ?? '', $penerima);
        $isiTeks = $this->gantiPlaceholder((string) ($template['isi_teks'] ?? ''), $penerima);

        try {
            // ── Resolve SMTP: parameter → SMTP aktif global → .env ──
            if ($smtpConfig === null) {
                $cfg        = (new SmtpConfigurator())->apply();
                $smtpConfig = $cfg ?? config('Email');
            }

            // Non-shared instance (penting untuk batch — hindari state bocor antar email)
            $email = \Config\Services::email($smtpConfig, false);
            $email->clear(true);

            // WAJIB: default CI4 = text, sehingga HTML tampil sebagai kode
            $email->setMailType('html');

            $email->setFrom(
                $smtpConfig->fromEmail ?: 'noreply@example.com',
                $smtpConfig->fromName  ?: 'Aplikasi Email'
            );
            $email->setTo($penerima['alamat_email_penerima']);
            $email->setSubject($subjek);
            $email->setMessage($isiHtml);

            if (trim($isiTeks) !== '') {
                $email->setAltMessage($isiTeks);
            }

            if ($email->send(false)) {
                $idPesan = 'MSG-' . date('YmdHis')
                    . '-' . ($penerima['id'] ?? '0')
                    . '-' . bin2hex(random_bytes(4));

                return ['sukses' => true, 'pesan' => 'Terkirim', 'id_pesan' => $idPesan];
            }

            return [
                'sukses'   => false,
                'pesan'    => strip_tags($email->printDebugger(['headers', 'subject'])),
                'id_pesan' => null,
            ];
        } catch (\Throwable $e) {
            return ['sukses' => false, 'pesan' => $e->getMessage(), 'id_pesan' => null];
        }
    }

    protected function gantiPlaceholder(string $teks, array $penerima): string
    {
        // Di skema DB saat ini, nama_penerima = nama_perusahaan.
        // Kalau nanti ditambahkan nama individu, logika fallback ini tetap aman.
        $nama = (string) ($penerima['nama_penerima'] ?? '');
        if ($nama === '') {
            $nama = (string) ($penerima['nama_perusahaan'] ?? '');
        }

        $map = [
            '{nama}'       => $nama,
            '{perusahaan}' => (string) ($penerima['nama_perusahaan'] ?? ''),
            '{email}'      => (string) ($penerima['alamat_email_penerima'] ?? ''),
            '{domisili}'   => (string) ($penerima['domisili'] ?? ''),
        ];

        return str_replace(array_keys($map), array_values($map), $teks);
    }
}