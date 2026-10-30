<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJadwalKampanyeAndTipePengiriman extends Migration
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $kolomBaru = [
        'tipe_pengiriman' => [
            'type'       => 'VARCHAR',
            'constraint' => 30,
            'null'       => false,
            'default'    => 'sekarang',
        ],
        'interval_nilai' => [
            'type'       => 'INT',
            'constraint' => 10,
            'unsigned'   => true,
            'null'       => true,
        ],
        'interval_satuan' => [
            'type'       => 'VARCHAR',
            'constraint' => 10,
            'null'       => true,
        ],
        'berulang_sampai' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
        'maks_pengulangan' => [
            'type'       => 'INT',
            'constraint' => 10,
            'unsigned'   => true,
            'null'       => true,
        ],
        'jumlah_putaran' => [
            'type'       => 'INT',
            'constraint' => 10,
            'unsigned'   => true,
            'null'       => false,
            'default'    => 0,
        ],
        'cara_pengulangan' => [
            'type'       => 'VARCHAR',
            'constraint' => 20,
            'null'       => true,
        ],
        'total_putaran' => [
            'type'       => 'INT',
            'constraint' => 10,
            'unsigned'   => true,
            'null'       => false,
            'default'    => 1,
        ],
    ];

    // =================================================================
    // UP
    // =================================================================
    public function up()
    {
        $db    = db_connect();
        $forge = \Config\Database::forge();

        // -------------------------------------------------------------
        // 1. Tambah kolom baru ke kampanye_email
        // -------------------------------------------------------------
        $kolomAda = $db->getFieldNames('kampanye_email');

        $tambah      = [];
        $perluModify = [];

        foreach ($this->kolomBaru as $nama => $definisi) {
            if (in_array($nama, $kolomAda, true)) {
                $perluModify[$nama] = $definisi;
            } else {
                $tambah[$nama] = $definisi;
            }
        }

        if ($tambah !== []) {
            $forge->addColumn('kampanye_email', $tambah);
        }

        if (isset($perluModify['tipe_pengiriman'])) {
            $forge->modifyColumn('kampanye_email', [
                'tipe_pengiriman' => $this->kolomBaru['tipe_pengiriman'],
            ]);
        }

        // -------------------------------------------------------------
        // 2. Buat tabel jadwal_kampanye
        // -------------------------------------------------------------
        $forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kampanye_email_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'putaran_ke' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'waktu_kirim' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'menunggu',
            ],
            'dimulai_pada'     => ['type' => 'DATETIME', 'null' => true],
            'selesai_pada'     => ['type' => 'DATETIME', 'null' => true],
            'jumlah_berhasil'  => [
                'type' => 'INT', 'constraint' => 10, 'unsigned' => true,
                'null' => false, 'default' => 0,
            ],
            'jumlah_gagal' => [
                'type' => 'INT', 'constraint' => 10, 'unsigned' => true,
                'null' => false, 'default' => 0,
            ],
            'dibuat_pada'     => ['type' => 'DATETIME', 'null' => true],
            'diperbarui_pada' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $forge->addKey('id', true);
        $forge->addUniqueKey(['kampanye_email_id', 'putaran_ke'], 'kampanye_putaran');
        $forge->addKey(['status', 'waktu_kirim'], false, false, 'status_waktu');

        $forge->addForeignKey(
            'kampanye_email_id',   // field di tabel ini
            'kampanye_email',      // tabel referensi
            'id',                  // field referensi
            'CASCADE',
            'CASCADE',
            'jadwal_kampanye_kampanye_fk'
        );

        $forge->createTable('jadwal_kampanye', true);

        // -------------------------------------------------------------
        // 3. Migrasi data lama (idempoten)
        // -------------------------------------------------------------
        $tKampanye = $db->prefixTable('kampanye_email');
        $tJadwal   = $db->prefixTable('jadwal_kampanye');

        $db->query("
            UPDATE `{$tKampanye}`
            SET `tipe_pengiriman` = 'terjadwal_berulang'
            WHERE `tipe_pengiriman` = 'berulang'
        ");

        $db->query("
            UPDATE `{$tKampanye}`
            SET `tipe_pengiriman` = 'terjadwal'
            WHERE `tipe_pengiriman` = 'sekarang'
              AND `jadwal_pengiriman` IS NOT NULL
        ");

        $db->query("
            INSERT IGNORE INTO `{$tJadwal}`
                (`kampanye_email_id`, `putaran_ke`, `waktu_kirim`, `status`,
                 `dimulai_pada`, `selesai_pada`,
                 `jumlah_berhasil`, `jumlah_gagal`,
                 `dibuat_pada`, `diperbarui_pada`)
            SELECT
                `id`, 1,
                COALESCE(`jadwal_pengiriman`, `mulai_dikirim_pada`, `dibuat_pada`, NOW()),
                CASE `status`
                    WHEN 'selesai'        THEN 'selesai'
                    WHEN 'sedang_dikirim' THEN 'berjalan'
                    ELSE 'menunggu'
                END,
                `mulai_dikirim_pada`,
                `selesai_dikirim_pada`,
                `jumlah_berhasil`,
                `jumlah_gagal`,
                NOW(), NOW()
            FROM `{$tKampanye}`
        ");

        $db->query("
            UPDATE `{$tKampanye}`
            SET `jumlah_putaran` = 1
            WHERE `status` = 'selesai'
              AND `jumlah_putaran` = 0
        ");
    }

    // =================================================================
    // DOWN
    // =================================================================
    public function down()
    {
        $db    = db_connect();
        $forge = \Config\Database::forge();

        // 1. Drop tabel jadwal dulu (karena ada FK ke kampanye_email)
        $forge->dropTable('jadwal_kampanye', true);

        // 2. Kalau tabel kampanye_email tidak ada → tidak ada yang perlu di-drop
        if (! $db->tableExists('kampanye_email')) {
            return;
        }

        // 3. Ambil kolom yang benar-benar ada
        $kolomAda = $db->getFieldNames('kampanye_email');

        // 4. Irisan: kolom baru ∩ kolom yang ada
        $toDrop = array_values(
            array_intersect(array_keys($this->kolomBaru), $kolomAda)
        );

        // 5. Drop hanya kalau ada
        if ($toDrop !== []) {
            $forge->dropColumn('kampanye_email', $toDrop);
        }
    }
}