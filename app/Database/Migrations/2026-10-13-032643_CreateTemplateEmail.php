<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTemplateEmail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kategori_template_id' => ['type' => 'INT', 'unsigned' => true],
            'nama_template' => ['type' => 'VARCHAR', 'constraint' => 150],
            'subjek_email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'isi_html' => ['type' => 'LONGTEXT'],
            'isi_teks' => ['type' => 'LONGTEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draf'],
            'versi' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'status_pemeriksaan' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'belum_diperiksa'],
            'diperiksa_pada' => ['type' => 'DATETIME', 'null' => true],
            'dibuat_pada' => ['type' => 'DATETIME', 'null' => true],
            'diperbarui_pada' => ['type' => 'DATETIME', 'null' => true],
            'dihapus_pada' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('kategori_template_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('kategori_template_id', 'kategori_template_email', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('template_email', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('template_email', true);
    }
}
