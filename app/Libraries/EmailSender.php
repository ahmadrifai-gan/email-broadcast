<?php

namespace App\Libraries;

class EmailSender
{
    /**
     * Kirim email ke 1 penerima
     * @return array ['sukses'=>bool, 'pesan'=>string, 'id_pesan'=>string|null]
     */
    public function kirimSatu(array $penerima, array $template): array
    {
        $subjek  = $this->gantiPlaceholder($template['subjek_email'] ?? '', $penerima);
        $isiHtml = $this->gantiPlaceholder($template['isi_html'] ?? '', $penerima);
        $isiTeks = $this->gantiPlaceholder((string) ($template['isi_teks'] ?? ''), $penerima);

        try {
            $config = config('Email');
            $email  = \Config\Services::email();
            $email->clear(true);
            $email->setMailType('html'); // WAJIB: default CI4 = text, sehingga HTML tampil sebagai kode
            $email->setFrom(
                $config->fromEmail ?: 'noreply@example.com',
                $config->fromName  ?: 'Aplikasi Email'
            );
            $email->setTo($penerima['alamat_email_penerima']);
            $email->setSubject($subjek);
            $email->setMessage($isiHtml);
            if (! empty($isiTeks)) {
                $email->setAltMessage($isiTeks);
            }

            if ($email->send(false)) {
                $idPesan = 'MSG-' . date('YmdHis')
                    . '-' . $penerima['id']
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
        $map = [
            '{nama}'       => (string) ($penerima['nama_penerima'] ?? ''),
            '{perusahaan}' => (string) ($penerima['nama_perusahaan'] ?? ''),
            '{email}'      => (string) ($penerima['alamat_email_penerima'] ?? ''),
            '{domisili}'   => (string) ($penerima['domisili'] ?? ''),
        ];
        return str_replace(array_keys($map), array_values($map), $teks);
    }
}