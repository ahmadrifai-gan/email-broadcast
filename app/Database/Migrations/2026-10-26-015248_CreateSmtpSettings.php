<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSmtpSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'host'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'username'   => ['type' => 'VARCHAR', 'constraint' => 150],
            'password'   => ['type' => 'TEXT', 'comment' => 'Terenkripsi'],
            'port'       => ['type' => 'INT', 'constraint' => 6, 'default' => 587],
            'crypto'     => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'tls'],
            'from_email' => ['type' => 'VARCHAR', 'constraint' => 150],
            'from_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('is_active');
        $this->forge->createTable('smtp_settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('smtp_settings', true);
    }
}