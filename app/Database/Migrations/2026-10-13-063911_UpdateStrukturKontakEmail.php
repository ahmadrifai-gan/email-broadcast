<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateStrukturKontakEmail extends Migration
{
    public function up()
    {
        // Hapus kolom yang tidak diperlukan
        $this->forge->dropColumn('kontak_email', [
            'status',
            'status_langganan',
            'sumber_data',
            'catatan',
            'dibuat_pada',
            'diperbarui_pada',
            'dihapus_pada',
        ]);

        // Ganti nama kolom
        $this->forge->modifyColumn('kontak_email', [
            'nama' => [
                'name'       => 'nama_perusahaan',
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'alamat_email' => [
                'name'       => 'email',
                'type'       => 'VARCHAR',
                'constraint' => 254,
            ],
        ]);

        // Tambahkan kolom domisili
        $this->forge->addColumn('kontak_email', [
            'domisili' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Hapus kolom domisili
        $this->forge->dropColumn('kontak_email', 'domisili');

        // Kembalikan nama kolom sebelumnya
        $this->forge->modifyColumn('kontak_email', [
            'nama_perusahaan' => [
                'name'       => 'nama',
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'email' => [
                'name'       => 'alamat_email',
                'type'       => 'VARCHAR',
                'constraint' => 254,
            ],
        ]);

        // Kembalikan kolom yang sebelumnya dihapus
        $this->forge->addColumn('kontak_email', [
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'aktif',
            ],
            'status_langganan' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'belum_dikonfirmasi',
            ],
            'sumber_data' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dibuat_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'diperbarui_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'dihapus_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }
}