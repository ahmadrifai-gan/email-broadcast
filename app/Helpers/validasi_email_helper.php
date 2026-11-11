<?php

/**
 * Simpan di: app/Helpers/validasi_email_helper.php
 * Panggil dengan: helper('validasi_email');
 */

if (! function_exists('cek_email')) {
    /**
     * Cek email lewat dua lapis:
     *  1) format penulisan
     *  2) domain punya server email (DNS MX / A)
     *
     * @return string|null null jika lolos, atau pesan alasan jika gagal
     */
    function cek_email(?string $email): ?string
    {
        // Cache per domain, supaya gmail.com yang muncul ribuan kali hanya dicek sekali
        static $cacheDomain = [];

        $email = trim((string) $email);

        // Lapis 1: format
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'format email tidak valid';
        }

        // Lapis 2: domain
        $domain = strtolower(substr(strrchr($email, '@'), 1));

        // Titik di akhir mencegah DNS mencoba menambahkan suffix jaringan lokal
        $cacheDomain[$domain] ??= (checkdnsrr($domain . '.', 'MX') || checkdnsrr($domain . '.', 'A'));

        return $cacheDomain[$domain] ? null : "domain {$domain} tidak ditemukan";
    }
}