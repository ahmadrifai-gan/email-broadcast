<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontakEmail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 150],
            'alamat_email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'aktif'],
            'status_langganan' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'belum_dikonfirmasi'],
            'sumber_data' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'catatan' => ['type' => 'TEXT', 'null' => true],
            'dibuat_pada' => ['type' => 'DATETIME', 'null' => true],
            'diperbarui_pada' => ['type' => 'DATETIME', 'null' => true],
            'dihapus_pada' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('alamat_email');
        $this->forge->addKey('status');
        $this->forge->addKey('status_langganan');
        $this->forge->createTable('kontak_email', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('kontak_email', true);
    }
}
