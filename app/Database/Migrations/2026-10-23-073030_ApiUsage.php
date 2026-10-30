<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ApiUsage extends Migration
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
                'constraint' => '255',
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'used_today' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_limit' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'limit_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['api_key', 'date'], 'api_date'); // Mencegah duplikasi key di hari yang sama
        $this->forge->createTable('api_usage');
    }

    public function down()
    {
        $this->forge->dropTable('api_usage');
    }
}