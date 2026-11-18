<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RiwayatAnalisis extends Migration
{
 public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // PERUBAHAN DI SINI: perusahaan_id menjadi kontak_email_id
            'kontak_email_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['sukses', 'gagal'], // Sesuaikan dengan isi enum di gambar
                'default'    => 'gagal', // Opsional, beri default agar aman
            ],
            'sumber_digunakan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'hasil_mentah' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'confidence' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'pesan_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'waktu_analisis' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // 1. Set Primary Key
        $this->forge->addKey('id', true);
        
        // 2. Set Foreign Key ke tabel kontak_emails
        // Parameter: (Nama Kolom di tabel ini, Nama Tabel Tujuan, Nama Kolom Tujuan, onDelete, onUpdate)
        $this->forge->addForeignKey('kontak_email_id', 'kontak_email', 'id', 'CASCADE', 'CASCADE');
        
        // 3. Buat tabel (Ganti 'nama_tabel' sesuai kebutuhanmu, misal: 'analisis_produk')
        $this->forge->createTable('riwatat_analisis'); // true = jika tabel sudah ada, jangan buat lagi
    }

    public function down()
    {
        // Gunakan pengecekan agar tidak error "Unknown table" saat rollback
        if ($this->db->tableExists('riwatat_analisis')) {
            $this->forge->dropTable('riwatat_analisis');
        }
    }
}
