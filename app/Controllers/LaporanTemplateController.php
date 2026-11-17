<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TemplateEmailModel;

class LaporanTemplateController extends BaseController
{
    public function index()
    {
        $templateModel = new TemplateEmailModel();
        $db = \Config\Database::connect();

        // Ambil semua template (termasuk yang sudah dihapus jika perlu)
        $dataTemplate = $templateModel->withDeleted()->findAll();

        $laporan = [];
        foreach ($dataTemplate as $t) {
            // 1. Hitung total berapa kali template ini dipakai di tabel kampanye_email
            $totalKampanye = $db->table('kampanye_email')
                                ->where('template_email_id', $t['id'])
                                ->countAllResults();

            // 2. Ambil data statistik dari tabel kampanye_email
            // Kita menggunakan query builder untuk menjumlahkan (SUM)
            
            // Hitung Total Berhasil Terkirim
            $suksesKirim = $db->table('kampanye_email')
                              ->selectSum('jumlah_berhasil')
                              ->where('template_email_id', $t['id'])
                              ->get()->getRow()->jumlah_berhasil ?? 0;

            // Hitung Total Gagal
            $gagalKirim = $db->table('kampanye_email')
                             ->selectSum('jumlah_gagal')
                             ->where('template_email_id', $t['id'])
                             ->get()->getRow()->jumlah_gagal ?? 0;
                             
            // Hitung Total Penerima (Termasuk yang masih pending)
            $totalPenerima = $db->table('kampanye_email')
                                ->selectSum('jumlah_penerima')
                                ->where('template_email_id', $t['id'])
                                ->get()->getRow()->jumlah_penerima ?? 0;

            $pendingKirim = $totalPenerima - ($suksesKirim + $gagalKirim);
            if ($pendingKirim < 0) $pendingKirim = 0;

            $laporan[] = [
                'id'             => $t['id'],
                'nama_template'  => $t['nama_template'],
                'status'         => $t['status'],
                'dibuat_pada'    => $t['dibuat_pada'],
                'total_kampanye' => $totalKampanye,
                'sukses_kirim'   => $suksesKirim,
                'pending_kirim'  => $pendingKirim,
                'gagal_kirim'    => $gagalKirim,
            ];
        }

        $data = [
            'title'   => 'Laporan Template Email',
            'laporan' => $laporan,
        ];

        return view('laporantemplate/index', $data);
    }
}