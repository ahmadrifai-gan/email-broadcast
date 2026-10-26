<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateKontakEmailTambahKategori extends Migration
{
    private string $tabel = 'kontak_email';
    private string $fk    = 'fk_kontak_email_kategori';

    /** Ambil daftar kolom langsung dari database (tanpa cache). */
    private function daftarKolom(): array
    {
        return array_column(
            $this->db->query('SHOW COLUMNS FROM `' . $this->tabel . '`')->getResultArray(),
            'Field'
        );
    }

    /** Cek apakah foreign key sudah ada. */
    private function fkAda(): bool
    {
        $hasil = $this->db->query(
            'SELECT COUNT(*) AS total
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\'',
            [$this->db->getDatabase(), $this->tabel, $this->fk]
        )->getRowArray();

        return (int) ($hasil['total'] ?? 0) > 0;
    }

    public function up()
    {
        $kolomAda = $this->daftarKolom();

        // 1. domisili -> kota_kabupaten (atau buat baru jika keduanya belum ada)
        if (in_array('domisili', $kolomAda, true) && ! in_array('kota_kabupaten', $kolomAda, true)) {
            $this->db->query('ALTER TABLE `' . $this->tabel . '` CHANGE `domisili` `kota_kabupaten` VARCHAR(100) NULL');
        } elseif (! in_array('kota_kabupaten', $kolomAda, true)) {
            $this->db->query('ALTER TABLE `' . $this->tabel . '` ADD `kota_kabupaten` VARCHAR(100) NULL');
        }

        // 2. Kolom baru (kota_kabupaten sudah ditangani di atas)
        $kolomBaru = [
            'kategori_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'kategori_id',
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'provinsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'produk_perusahaan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sumber_data' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'jumlah_tidak_terkirim' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'dibuat_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'diperbarui_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $kolomAda = $this->daftarKolom();

        foreach ($kolomBaru as $nama => $definisi) {
            if (! in_array($nama, $kolomAda, true)) {
                $this->forge->addColumn($this->tabel, [$nama => $definisi]);
            }
        }

        // 3. Foreign key ke kategori (hanya jika belum ada)
        if (! $this->fkAda()) {
            $this->db->query('
                ALTER TABLE `' . $this->tabel . '`
                ADD CONSTRAINT `' . $this->fk . '`
                FOREIGN KEY (`kategori_id`) REFERENCES `kategori`(`id`)
                ON UPDATE CASCADE
                ON DELETE SET NULL
            ');
        }
    }

    public function down()
    {
        // 1. Hapus foreign key dulu
        if ($this->fkAda()) {
            $this->db->query('ALTER TABLE `' . $this->tabel . '` DROP FOREIGN KEY `' . $this->fk . '`');
        }

        // 2. Hapus kolom yang ditambahkan migration ini (hanya yang ada)
        $kolomAda  = $this->daftarKolom();
        $kolomHapus = array_values(array_intersect([
            'kategori_id',
            'nama',
            'no_hp',
            'website',
            'provinsi',
            'produk_perusahaan',
            'sumber_data',
            'catatan',
            'jumlah_tidak_terkirim',
            'dibuat_pada',
            'diperbarui_pada',
        ], $kolomAda));

        if ($kolomHapus !== []) {
            $this->forge->dropColumn($this->tabel, $kolomHapus);
        }

        // 3. kota_kabupaten -> domisili
        if (in_array('kota_kabupaten', $kolomAda, true) && ! in_array('domisili', $kolomAda, true)) {
            $this->db->query('ALTER TABLE `' . $this->tabel . '` CHANGE `kota_kabupaten` `domisili` VARCHAR(150) NULL');
        }
    }
}