<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveTravelColumnsToKontakEmail extends Migration
{
    public function up()
    {
        // Menambahkan SEMUA kolom dari tabel travel ke tabel kontak_email
        // (Kecuali kolom 'id' karena tabel kontak_email sudah punya 'id' sendiri)
        $fields = [
            'keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'alamat' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'nomor' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'maps_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
            ],
            'wa_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'wa_checked_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'website_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'website_checked_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'unique_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('kontak_email', $fields);
    }

    public function down()
    {
        // Hapus kolom jika di-rollback
        $this->forge->dropColumn('kontak_email', [
            'keyword', 'alamat', 'nomor', 'maps_url', 'wa_status', 
            'wa_checked_at', 'website_status', 'website_checked_at', 
            'unique_hash', 'created_at', 'updated_at'
        ]);
    }
}