<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Restrukturisasi:
 *  - penerima_kampanye.jadwal_kampanye_id : tiap penerima ditautkan ke 1 putaran
 *  - jadwal_kampanye.jumlah_penerima      : jumlah penerima di putaran tsb
 *  - kampanye_email.kecepatan_per_menit & kecepatan_maks : rentang acak email/menit
 *  - DROP kolom lama mode "pesan berulang":
 *    interval_nilai, interval_satuan, berulang_sampai, maks_pengulangan, cara_pengulangan
 *
 * Catatan: down() hanya membalik SKEMA. Data hasil migrasi TIDAK bisa dikembalikan 100%.
 */
class RestrukturisasiPenerimaJadwalKecepatan extends Migration
{
    /** @var string[] */
    private array $kolomLama = [
        'interval_nilai',
        'interval_satuan',
        'berulang_sampai',
        'maks_pengulangan',
        'cara_pengulangan',
    ];

    // =================================================================
    // Helper
    // =================================================================

    /** @return \CodeIgniter\Database\BaseConnection */
    private function koneksi()
    {
        return $this->db ?? db_connect();
    }

    /** @return \CodeIgniter\Database\Forge */
    private function forgeDb()
    {
        return $this->forge ?? \Config\Database::forge($this->koneksi());
    }

    /** Cek index dengan aman (toleran object/array & return false). */
    private function indexAda(string $table, string $namaIndex): bool
    {
        $db = $this->koneksi();
        try {
            $data = $db->getIndexData($table);
        } catch (\Throwable $e) {
            return false;
        }
        if (! is_array($data)) {
            return false;
        }
        foreach ($data as $idx) {
            $nama = is_object($idx) ? ($idx->name ?? '') : ($idx['name'] ?? '');
            if ($nama === $namaIndex) {
                return true;
            }
        }
        return false;
    }

    // =================================================================
    // UP
    // =================================================================
    public function up()
    {
        $db    = $this->koneksi();
        $forge = $this->forgeDb();

        // -------------------------------------------------------------
        // 1) penerima_kampanye: jadwal_kampanye_id + index
        // -------------------------------------------------------------
        if ($db->tableExists('penerima_kampanye')) {
            if (! in_array('jadwal_kampanye_id', $db->getFieldNames('penerima_kampanye'), true)) {
                $forge->addColumn('penerima_kampanye', [
                    'jadwal_kampanye_id' => [
                        'type'       => 'INT',
                        'constraint' => 10,
                        'unsigned'   => true,
                        'null'       => true,
                    ],
                ]);
            }

            if (! $this->indexAda('penerima_kampanye', 'idx_jadwal_kampanye')) {
                $t = $db->prefixTable('penerima_kampanye');
                $db->query("ALTER TABLE `{$t}` ADD INDEX `idx_jadwal_kampanye` (`jadwal_kampanye_id`)");
            }
        }

        // -------------------------------------------------------------
        // 2) jadwal_kampanye: jumlah_penerima
        // -------------------------------------------------------------
        if ($db->tableExists('jadwal_kampanye')
            && ! in_array('jumlah_penerima', $db->getFieldNames('jadwal_kampanye'), true)
        ) {
            $forge->addColumn('jadwal_kampanye', [
                'jumlah_penerima' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                ],
            ]);
        }

        // -------------------------------------------------------------
        // 3) kampanye_email: kecepatan_per_menit & kecepatan_maks
        // -------------------------------------------------------------
        if ($db->tableExists('kampanye_email')) {
            $kolom  = $db->getFieldNames('kampanye_email');
            $tambah = [];

            if (! in_array('kecepatan_per_menit', $kolom, true)) {
                $tambah['kecepatan_per_menit'] = [
                    'type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true,
                ];
            }
            if (! in_array('kecepatan_maks', $kolom, true)) {
                $tambah['kecepatan_maks'] = [
                    'type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true,
                ];
            }
            if ($tambah !== []) {
                $forge->addColumn('kampanye_email', $tambah);
            }
        }

        // -------------------------------------------------------------
        // 4) Migrasi data lama — hanya jika ketiga tabel ada
        // -------------------------------------------------------------
        if ($db->tableExists('penerima_kampanye')
            && $db->tableExists('jadwal_kampanye')
            && $db->tableExists('kampanye_email')
        ) {
            $tPenerima = $db->prefixTable('penerima_kampanye');
            $tJadwal   = $db->prefixTable('jadwal_kampanye');
            $tKampanye = $db->prefixTable('kampanye_email');

            $db->transStart();

            $db->query("
                UPDATE `{$tPenerima}` p
                  JOIN `{$tJadwal}`   j
                    ON j.`kampanye_email_id` = p.`kampanye_email_id`
                   AND j.`putaran_ke`        = 1
                   SET p.`jadwal_kampanye_id` = j.`id`
                 WHERE p.`jadwal_kampanye_id` IS NULL
            ");

            $db->query("
                UPDATE `{$tJadwal}` j
                   SET j.`jumlah_penerima` = (
                       SELECT COUNT(*)
                         FROM `{$tPenerima}` p
                        WHERE p.`jadwal_kampanye_id` = j.`id`
                   )
            ");

            $db->query("
                UPDATE `{$tJadwal}`
                   SET `status` = 'dilewati'
                 WHERE `status` = 'menunggu'
                   AND `jumlah_penerima` = 0
            ");

            $db->query("
                UPDATE `{$tKampanye}` k
                   SET k.`status` = 'selesai'
                 WHERE k.`status` = 'terjadwal'
                   AND NOT EXISTS (
                       SELECT 1 FROM `{$tJadwal}` j
                        WHERE j.`kampanye_email_id` = k.`id`
                          AND j.`status` = 'menunggu'
                   )
            ");

            $db->query("
                UPDATE `{$tKampanye}` k
                   SET k.`total_putaran` = GREATEST(1, (
                       SELECT COUNT(*) FROM `{$tJadwal}` j
                        WHERE j.`kampanye_email_id` = k.`id`
                          AND j.`jumlah_penerima` > 0
                   ))
            ");

            $db->transComplete();
        }

        // -------------------------------------------------------------
        // 5) DROP kolom lama dari kampanye_email
        // -------------------------------------------------------------
        if ($db->tableExists('kampanye_email')) {
            $db->resetDataCache(); // hindari cache lama setelah ALTER sebelumnya
            $kolomAda = $db->getFieldNames('kampanye_email');
            $toDrop   = array_values(array_intersect($this->kolomLama, $kolomAda));

            if ($toDrop !== []) {
                $forge->dropColumn('kampanye_email', $toDrop);
            }
        }
    }

    // =================================================================
    // DOWN (hanya skema)
    // =================================================================
    public function down()
    {
        $db    = $this->koneksi();
        $forge = $this->forgeDb();

        // -------------------------------------------------------------
        // 1) kampanye_email: hapus kecepatan_*, kembalikan kolom lama
        // -------------------------------------------------------------
        if ($db->tableExists('kampanye_email')) {
            $kolom = $db->getFieldNames('kampanye_email');

            // hapus kolom baru
            $toDrop = [];
            if (in_array('kecepatan_per_menit', $kolom, true)) $toDrop[] = 'kecepatan_per_menit';
            if (in_array('kecepatan_maks',      $kolom, true)) $toDrop[] = 'kecepatan_maks';
            if ($toDrop !== []) {
                $forge->dropColumn('kampanye_email', $toDrop);
            }

            // restore kolom lama — reset cache dulu agar getFieldNames() segar
            $db->resetDataCache();
            $kolom  = $db->getFieldNames('kampanye_email');
            $tambah = [];

            if (! in_array('interval_nilai', $kolom, true)) {
                $tambah['interval_nilai'] = [
                    'type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true,
                ];
            }
            if (! in_array('interval_satuan', $kolom, true)) {
                $tambah['interval_satuan'] = [
                    'type' => 'VARCHAR', 'constraint' => 10, 'null' => true,
                ];
            }
            if (! in_array('berulang_sampai', $kolom, true)) {
                $tambah['berulang_sampai'] = ['type' => 'DATETIME', 'null' => true];
            }
            if (! in_array('maks_pengulangan', $kolom, true)) {
                $tambah['maks_pengulangan'] = [
                    'type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true,
                ];
            }
            if (! in_array('cara_pengulangan', $kolom, true)) {
                $tambah['cara_pengulangan'] = [
                    'type' => 'VARCHAR', 'constraint' => 20, 'null' => true,
                ];
            }
            if ($tambah !== []) {
                $forge->addColumn('kampanye_email', $tambah);
            }
        }

        // -------------------------------------------------------------
        // 2) jadwal_kampanye: hapus jumlah_penerima
        // -------------------------------------------------------------
        if ($db->tableExists('jadwal_kampanye')
            && in_array('jumlah_penerima', $db->getFieldNames('jadwal_kampanye'), true)
        ) {
            $forge->dropColumn('jadwal_kampanye', 'jumlah_penerima');
        }

        // -------------------------------------------------------------
        // 3) penerima_kampanye: hapus index + kolom
        // -------------------------------------------------------------
        if ($db->tableExists('penerima_kampanye')) {
            if ($this->indexAda('penerima_kampanye', 'idx_jadwal_kampanye')) {
                $t = $db->prefixTable('penerima_kampanye');
                $db->query("ALTER TABLE `{$t}` DROP INDEX `idx_jadwal_kampanye`");
            }

            if (in_array('jadwal_kampanye_id', $db->getFieldNames('penerima_kampanye'), true)) {
                $forge->dropColumn('penerima_kampanye', 'jadwal_kampanye_id');
            }
        }
    }
}