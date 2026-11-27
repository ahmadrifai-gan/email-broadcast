<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tambahstatuskontak extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('status_analisis', 'kontak_email')) {
            return;
        }

        // Jaga-jaga jika enum pernah diubah manual ke nilai 'perlu_diperiksa':
        // kembalikan ke 'belum_dianalisis' agar tidak terpotong saat ALTER.
        $this->db->query("UPDATE kontak_email SET status_analisis = 'belum_dianalisis' WHERE status_analisis = 'perlu_diperiksa'");

        $this->db->query("
            ALTER TABLE kontak_email
            MODIFY status_analisis ENUM(
                'belum_dianalisis',
                'sukses',
                'gagal',
                'manual',
                'tanpa_produk',
                'sumber_tidak_ada'
            ) NOT NULL DEFAULT 'belum_dianalisis'
        ");
    }

    public function down()
    {
        if (! $this->db->fieldExists('status_analisis', 'kontak_email')) {
            return;
        }

        // Nilai yang akan dihapus dari enum dikembalikan ke 'belum_dianalisis'
        $this->db->query("UPDATE kontak_email SET status_analisis = 'belum_dianalisis' WHERE status_analisis IN ('tanpa_produk', 'sumber_tidak_ada')");

        $this->db->query("
            ALTER TABLE kontak_email
            MODIFY status_analisis ENUM(
                'belum_dianalisis',
                'sukses',
                'gagal',
                'manual'
            ) NOT NULL DEFAULT 'belum_dianalisis'
        ");
    }
}
