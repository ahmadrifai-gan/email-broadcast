<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRekomendasiTemplate extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pemeriksaan_template_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'kategori_rekomendasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'saran_perbaikan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'prioritas' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'sedang',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'belum_ditindaklanjuti',
            ],
            'dibuat_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);

        // Index
        $this->forge->addKey('pemeriksaan_template_id');
        $this->forge->addKey('status');

        // Foreign key ke tabel pemeriksaan_template
        $this->forge->addForeignKey(
            'pemeriksaan_template_id',
            'pemeriksaan_template',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Buat tabel
        $this->forge->createTable(
            'rekomendasi_template',
            true,
            ['ENGINE' => 'InnoDB']
        );
    }

    public function down()
    {
        $this->forge->dropTable('rekomendasi_template', true);
    }
}