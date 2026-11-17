<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Simpan di: app/Database/Migrations/2026-11-17-100000_AddKolomAnalisisToKontak.php
 *
 * Menambah ke tabel `kontak` kolom-kolom dari tabel profil perusahaan.
 * Kolom lama `produk_perusahaan` DIGANTI oleh `produk_yang_dijual_auto`:
 * isinya disalin dulu, lalu kolom lama dihapus.
 *
 * created_at / updated_at tidak ditambah (sudah diwakili dibuat_pada / diperbarui_pada).
 * Aman dijalankan ulang: kolom yang sudah ada dilewati.
 */
class AddKolomAnalisisToKontak extends Migration
{
    protected string $tabel = 'kontak_email';

    public function up()
    {
        $kolom = [
            'nomor_excel' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'main_categories' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sub_categories' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'company_profile' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'link_catalog' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'produk_yang_dijual_auto' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // Pilihan enum diambil dari gambar yang terpotong ('manua...'):
            // pastikan 'manual' sudah sesuai dengan tabel aslinya.
            'status_analisis' => [
                'type'       => 'ENUM',
                'constraint' => ['belum_dianalisis', 'sukses', 'gagal', 'manual'],
                'default'    => 'belum_dianalisis',
                'null'       => false,
            ],
            'terakhir_dianalisis' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        // Lewati kolom yang sudah ada
        $baru = [];
        foreach ($kolom as $nama => $definisi) {
            if (! $this->db->fieldExists($nama, $this->tabel)) {
                $baru[$nama] = $definisi;
            }
        }

        if ($baru !== []) {
            $this->forge->addColumn($this->tabel, $baru);
        }

        // Pindahkan isi produk_perusahaan ke produk_yang_dijual_auto, lalu hapus kolom lama
        if ($this->db->fieldExists('produk_perusahaan', $this->tabel)) {
            $this->db->table($this->tabel)
                ->set('produk_yang_dijual_auto', 'produk_perusahaan', false)
                ->where('produk_yang_dijual_auto IS NULL', null, false)
                ->update();

            $this->forge->dropColumn($this->tabel, 'produk_perusahaan');
        }
    }

    public function down()
    {
        // Kembalikan produk_perusahaan dan salin isinya dari kolom pengganti
        if (! $this->db->fieldExists('produk_perusahaan', $this->tabel)) {
            $this->forge->addColumn($this->tabel, [
                'produk_perusahaan' => ['type' => 'TEXT', 'null' => true],
            ]);

            if ($this->db->fieldExists('produk_yang_dijual_auto', $this->tabel)) {
                $this->db->table($this->tabel)
                    ->set('produk_perusahaan', 'produk_yang_dijual_auto', false)
                    ->update();
            }
        }

        $hapus = [];
        foreach ([
            'nomor_excel', 'main_categories', 'sub_categories', 'company_profile',
            'link_catalog', 'produk_yang_dijual_auto', 'status_analisis', 'terakhir_dianalisis',
        ] as $nama) {
            if ($this->db->fieldExists($nama, $this->tabel)) {
                $hapus[] = $nama;
            }
        }

        if ($hapus !== []) {
            $this->forge->dropColumn($this->tabel, $hapus);
        }
    }
}