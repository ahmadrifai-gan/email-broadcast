<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPdfColumnsToTemplateEmail extends Migration
{
    public function up()
    {
        // Tambahkan 2 kolom baru ke tabel template_email
        $fields = [
            'attachment_pdf_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'status_pemeriksaan', // Diletakkan setelah kolom ini
            ],
            'tipe_pdf' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'attachment_pdf_url',
            ],
        ];

        $this->forge->addColumn('template_email', $fields);
    }

    public function down()
    {
        // Jika migration di-rollback, hapus 2 kolom ini
        $this->forge->dropColumn('template_email', ['attachment_pdf_url', 'tipe_pdf']);
    }
}