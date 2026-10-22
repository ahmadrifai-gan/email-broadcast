<?php

namespace App\Controllers;

use App\Controllers\BaseController;
// Panggil semua model yang dibutuhkan
use App\Models\KontakModel;
use App\Models\TemplateEmailModel;
use App\Models\KategoriTemplateEmailModel;
use App\Models\KampanyeEmailModel; // Sesuaikan dengan nama model kampanye Anda

class DashboardController extends BaseController
{
    public function index()
    {
        // Inisialisasi Model
        $kontakModel   = new KontakModel();
        $templateModel = new TemplateEmailModel();
        $kategoriModel = new KategoriTemplateEmailModel();
        $kampanyeModel = new KampanyeEmailModel();

        // Hitung total data dari masing-masing tabel
        $data = [
            'title'         => 'Dashboard',
            'totalKontak'   => $kontakModel->countAllResults(),
            'totalTemplate' => $templateModel->countAllResults(),
            'totalKategori' => $kategoriModel->countAllResults(),
            'totalKampanye' => $kampanyeModel->countAllResults(),
        ];

        // Kirim data ke view
        return view('dashboard/index', $data);
    }
}