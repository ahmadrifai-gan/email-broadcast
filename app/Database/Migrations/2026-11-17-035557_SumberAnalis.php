<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SumberAnalis extends Migration
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
            // Asumsi isi ENUM dari gambar yang terpotong
            'jenis_sumber' => [
                'type'       => 'ENUM',
                'constraint' => ['company', 'website', 'catalog'], // Silakan sesuaikan isi ENUM-nya
                'null'       => true, // Opsional
            ],
            'url_sumber' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // Asumsi isi ENUM dari gambar yang terpotong
            'status_pengambilan' => [
                'type'       => 'ENUM',
                'constraint' => ['sukses', 'gagal',], // Silakan sesuaikan isi ENUM-nya
                'null'       => true,
            ],
            'isi_sumber' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'pesan_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // 1. Set Primary Key
        $this->forge->addKey('id', true);
        
        // 2. Set Foreign Key ke tabel kontak_emails
        // (Nama kolom di sini, Tabel tujuan, Kolom tujuan, onDelete, onUpdate)
        $this->forge->addForeignKey('kontak_email_id', 'kontak_email', 'id', 'CASCADE', 'CASCADE');
        
        // 3. Buat tabel (Ganti 'nama_tabel_kamu' dengan nama tabel yang diinginkan)
        $this->forge->createTable('sumber_analisis'); 
    }

    public function down()
    {
        // Pengecekan agar tidak error saat rollback jika tabel sudah tidak ada
        if ($this->db->tableExists('sumber_analisis')) {
            $this->forge->dropTable('sumber_analisis');
        }
    }
}
