<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KontakModel;
use App\Models\TemplateEmailModel;
use App\Models\KategoriTemplateEmailModel;
use App\Models\KampanyeEmailModel;

class DashboardController extends BaseController
{
    public function index()
    {
        try {
            // Inisialisasi Model
            $kontakModel   = new KontakModel();
            $templateModel = new TemplateEmailModel();
            $kategoriModel = new KategoriTemplateEmailModel();
            $kampanyeModel = new KampanyeEmailModel();

            // Hitung total data
            $data = [
                'title'         => 'Dashboard',
                'totalKontak'   => $kontakModel->countAllResults(),
                'totalTemplate' => $templateModel->countAllResults(),
                'totalKategori' => $kategoriModel->countAllResults(),
                'totalKampanye' => $kampanyeModel->countAllResults(),
            ];
        } catch (\Throwable $e) {
            // Fallback jika model belum siap/error
            $data = [
                'title'         => 'Dashboard',
                'totalKontak'   => 0,
                'totalTemplate' => 0,
                'totalKategori' => 0,
                'totalKampanye' => 0,
            ];
        }

        return view('dashboard/index', $data);
    }
}