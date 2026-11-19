<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameRiwatatAnalisisTable extends Migration
{
    public function up()
    {
        $this->db->query("
            RENAME TABLE riwatat_analisis TO riwayat_analisis
        ");
    }

    public function down()
    {
        $this->db->query("
            RENAME TABLE riwayat_analisis TO riwatat_analisis
        ");
    }
}
