<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Simpan di: app/Database/Migrations/2026-11-17-120000_UrutkanKolomKontak.php
 *
 * Mengatur urutan kolom tabel kontak:
 *   - nomor_excel         -> urutan ke-2 (tepat setelah id)
 *   - dibuat_pada         -> kolom terakhir kedua
 *   - diperbarui_pada     -> kolom paling terakhir
 *
 * Hanya mengubah posisi kolom; data dan fungsi aplikasi tidak berubah.
 * Aman dijalankan meskipun migration UrutkanKolomNomorExcel sebelumnya sudah dijalankan.
 */
class UrutkanKolomKontak extends Migration
{
    // Samakan dengan nama tabel kontak di database
    protected string $tabel = 'kontak_email';

    public function up()
    {
        $t = $this->tabel;

        if ($this->db->fieldExists('nomor_excel', $t)) {
            $this->db->query("ALTER TABLE `{$t}` MODIFY COLUMN `nomor_excel` VARCHAR(50) NULL AFTER `id`");
        }

        // Kolom terakhir sebelum waktu dibuat/diperbarui
        $terakhir = $this->db->fieldExists('terakhir_dianalisis', $t) ? 'terakhir_dianalisis' : 'jumlah_tidak_terkirim';

        $this->db->query("ALTER TABLE `{$t}` MODIFY COLUMN `dibuat_pada` DATETIME NULL DEFAULT NULL AFTER `{$terakhir}`");
        $this->db->query("ALTER TABLE `{$t}` MODIFY COLUMN `diperbarui_pada` DATETIME NULL DEFAULT NULL AFTER `dibuat_pada`");
    }

    public function down()
    {
        $t = $this->tabel;

        // Kembalikan seperti semula: waktu dibuat/diperbarui setelah jumlah_tidak_terkirim,
        // nomor_excel setelah diperbarui_pada
        $this->db->query("ALTER TABLE `{$t}` MODIFY COLUMN `dibuat_pada` DATETIME NULL DEFAULT NULL AFTER `jumlah_tidak_terkirim`");
        $this->db->query("ALTER TABLE `{$t}` MODIFY COLUMN `diperbarui_pada` DATETIME NULL DEFAULT NULL AFTER `dibuat_pada`");

        if ($this->db->fieldExists('nomor_excel', $t)) {
            $this->db->query("ALTER TABLE `{$t}` MODIFY COLUMN `nomor_excel` VARCHAR(50) NULL AFTER `diperbarui_pada`");
        }
    }
}