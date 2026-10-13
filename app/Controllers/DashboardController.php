<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        $data = [
            'title'        => 'Dashboard | Email Blast',
            'pageTitle'    => 'Dashboard',
            'pageSubtitle' => 'Ringkasan',
        ];

        return view('dashboard/index', $data);   // ✅ tambah /index
    }
}