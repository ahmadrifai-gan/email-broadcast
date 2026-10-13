<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePemeriksaanTemplate extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'template_email_id' => ['type' => 'INT', 'unsigned' => true],
            'versi_template' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'jenis_pemeriksaan' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'otomatis'],
            'status_pemeriksaan' => ['type' => 'VARCHAR', 'constraint' => 20],
            'skor' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'ringkasan' => ['type' => 'TEXT', 'null' => true],
            'hasil_pemeriksaan' => ['type' => 'JSON', 'null' => true],
            'diperiksa_pada' => ['type' => 'DATETIME'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('template_email_id');
        $this->forge->addKey('status_pemeriksaan');
        $this->forge->addForeignKey('template_email_id', 'template_email', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pemeriksaan_template', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('pemeriksaan_template', true);
    }
}
