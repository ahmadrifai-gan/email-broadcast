<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKampanyeEmail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'template_email_id' => ['type' => 'INT', 'unsigned' => true],
            'nama_kampanye' => ['type' => 'VARCHAR', 'constraint' => 150],
            'subjek_email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draf'],
            'jadwal_pengiriman' => ['type' => 'DATETIME', 'null' => true],
            'mulai_dikirim_pada' => ['type' => 'DATETIME', 'null' => true],
            'selesai_dikirim_pada' => ['type' => 'DATETIME', 'null' => true],
            'jumlah_penerima' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'jumlah_berhasil' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'jumlah_gagal' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'dibuat_pada' => ['type' => 'DATETIME', 'null' => true],
            'diperbarui_pada' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('template_email_id');
        $this->forge->addKey('status');
        $this->forge->addKey('jadwal_pengiriman');
        $this->forge->addForeignKey('template_email_id', 'template_email', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('kampanye_email', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('kampanye_email', true);
    }
}
