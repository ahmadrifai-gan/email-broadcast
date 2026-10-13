<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenerimaKampanye extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kampanye_email_id' => ['type' => 'INT', 'unsigned' => true],
            'kontak_email_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'alamat_email_penerima' => ['type' => 'VARCHAR', 'constraint' => 254],
            'nama_penerima' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'status_pengiriman' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'menunggu'],
            'jumlah_percobaan' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'id_pesan_penyedia' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'terkirim_pada' => ['type' => 'DATETIME', 'null' => true],
            'pesan_kesalahan' => ['type' => 'TEXT', 'null' => true],
            'dibuat_pada' => ['type' => 'DATETIME', 'null' => true],
            'diperbarui_pada' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('kampanye_email_id');
        $this->forge->addKey('kontak_email_id');
        $this->forge->addKey('status_pengiriman');
        $this->forge->addUniqueKey(['kampanye_email_id', 'alamat_email_penerima']);
        $this->forge->addForeignKey('kampanye_email_id', 'kampanye_email', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('kontak_email_id', 'kontak_email', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('penerima_kampanye', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('penerima_kampanye', true);
    }
}
