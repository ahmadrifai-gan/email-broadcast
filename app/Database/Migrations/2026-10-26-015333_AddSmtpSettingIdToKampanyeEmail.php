<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSmtpSettingIdToKampanyeEmail extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kampanye_email', [
            'smtp_setting_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'template_email_id',
                'comment'    => 'NULL = pakai SMTP aktif default',
            ],
        ]);

        $this->db->query('ALTER TABLE `kampanye_email` ADD INDEX `idx_smtp_setting` (`smtp_setting_id`)');
        $this->db->query(
            'ALTER TABLE `kampanye_email`
             ADD CONSTRAINT `kampanye_email_smtp_fk`
             FOREIGN KEY (`smtp_setting_id`) REFERENCES `smtp_settings`(`id`)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `kampanye_email` DROP FOREIGN KEY `kampanye_email_smtp_fk`');
        $this->forge->dropColumn('kampanye_email', 'smtp_setting_id');
    }
}