<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UbahEmailKontakJadiNullable extends Migration
{
    public function up()
    {
        // 1. Kolom email boleh NULL
        $this->forge->modifyColumn('kontak_email', [
            'email' => [
                'name'       => 'email',
                'type'       => 'VARCHAR',
                'constraint' => 254,
                'null'       => true,
            ],
        ]);

        // 2. Ubah data lama yang berisi '' menjadi NULL
        $this->db->table('kontak_email')
            ->where('email', '')
            ->update(['email' => null]);
    }

    public function down()
    {
        // Kembalikan '' dulu agar tidak ada NULL saat kolom dibuat NOT NULL
        $this->db->table('kontak_email')
            ->where('email', null)
            ->update(['email' => '']);

        $this->forge->modifyColumn('kontak_email', [
            'email' => [
                'name'       => 'email',
                'type'       => 'VARCHAR',
                'constraint' => 254,
                'null'       => false,
            ],
        ]);
    }
}
