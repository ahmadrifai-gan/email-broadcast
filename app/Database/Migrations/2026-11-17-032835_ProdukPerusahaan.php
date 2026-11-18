<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProdukPerusahaan extends Migration
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
            'kontak_email_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_produk_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nama_produk_en' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true); // Primary Key
        $this->forge->addForeignKey('kontak_email_id', 'kontak_email', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('produks');
    }

    public function down()
    {
        $this->forge->dropTable('produk_perusahaan');
    }
}