<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiwayatPengirimanEmail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'penerima_kampanye_id' => ['type' => 'INT', 'unsigned' => true],
            'jenis_aktivitas' => ['type' => 'VARCHAR', 'constraint' => 50],
            'penyedia_email' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'smtp'],
            'id_pesan_penyedia' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'detail_aktivitas' => ['type' => 'TEXT', 'null' => true],
            'terjadi_pada' => ['type' => 'DATETIME'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('penerima_kampanye_id');
        $this->forge->addKey('jenis_aktivitas');
        $this->forge->addKey('terjadi_pada');
        $this->forge->addForeignKey('penerima_kampanye_id', 'penerima_kampanye', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('riwayat_pengiriman_email', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('riwayat_pengiriman_email', true);
    }
}
