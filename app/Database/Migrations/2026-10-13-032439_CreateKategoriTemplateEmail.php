<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKategoriTemplateEmail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_kategori' => ['type' => 'VARCHAR', 'constraint' => 100],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'aktif', 'null' => false,],
            'dibuat_pada' => ['type' => 'DATETIME', 'null' => true],
            'diperbarui_pada' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nama_kategori');
        $this->forge->createTable('kategori_template_email', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('kategori_template_email', true);
    }
}
