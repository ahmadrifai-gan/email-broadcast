<?php

namespace App\Libraries;

class SystemSpamChecker
{
    protected $spamWords = ['gratis', 'free', 'promo', 'diskon', 'klik sekarang', 'act now', '100%', 'jaminan', 'uang kembali'];

    public function check(string $subjek, string $isiHtml, string $isiTeks = ''): array
    {
        $textOnly = strip_tags($isiHtml);
        $finalTeks = !empty(trim($isiTeks)) ? $isiTeks : $textOnly;
        $textLower = strtolower($subjek . ' ' . $finalTeks);

        $score = 0;
        $alasan = [];

        // 1. Cek Kata Pemicu
        foreach ($this->spamWords as $word) {
            if (strpos($textLower, $word) !== false) {
                $score += 2;
                $alasan[] = "Mengandung kata pemicu spam: '" . strtoupper($word) . "'.";
            }
        }

        // 2. Cek Link Mencurigakan
        if (preg_match('/(bit\.ly|tinyurl|t\.co|goo\.gl)/i', $isiHtml)) {
            $score += 3;
            $alasan[] = "Terdapat link pemendek (shortlink) yang mencurigakan.";
        }

        // 3. Cek Link Unsubscribe
        if (stripos($textLower, 'unsubscribe') === false && stripos($textLower, 'berhenti berlangganan') === false) {
            $score += 2;
            $alasan[] = "Tidak ditemukan link berhenti berlangganan (unsubscribe).";
        }

        // 4. Cek Tanda Seru Berlebihan
        if (substr_count($subjek, '!') > 2 || substr_count($finalTeks, '!') > 3) {
            $score += 1;
            $alasan[] = "Penggunaan tanda seru (!) berlebihan.";
        }

        // 5. Cek Huruf Kapital Berlebihan di Subjek
        $letters = preg_replace('/[^a-zA-Z]/', '', $subjek);
        if (strlen($letters) > 5) {
            $caps = preg_replace('/[^A-Z]/', '', $subjek);
            if ((strlen($caps) / strlen($letters)) > 0.5) {
                $score += 1;
                $alasan[] = "Subjek email menggunakan huruf kapital berlebihan.";
            }
        }

        return [
            'success' => true,
            'status'  => ($score >= 3) ? 'spam' : 'inbox',
            'alasan'  => array_values(array_unique($alasan)),
        ];
    }
}