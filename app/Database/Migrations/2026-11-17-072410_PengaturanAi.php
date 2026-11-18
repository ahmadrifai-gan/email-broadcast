<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PengaturanAi extends Migration
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
            'api_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nama_model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'gpt-4o-mini',
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'openai',
            ],
            'instruksi_ai' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'temperature' => [
                'type'    => 'FLOAT',
                'default' => 0.2,
            ],
            'max_tokens' => [
                'type'    => 'INT',
                'constraint' => 11,
                'default' => 1000,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
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

        $this->forge->addKey('id', true);
        // Jika kamu ingin nama tabelnya lain, ubah 'pengaturan_ai' di bawah ini
        $this->forge->createTable('pengaturan_ai'); 
    }

    public function down()
    {
        // Hapus tabel jika di-rollback
        $this->forge->dropTable('pengaturan_ai');
    }
}
