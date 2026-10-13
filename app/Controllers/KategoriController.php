<?php

namespace App\Controllers;

class KategoriController extends BaseController
{
    public function index()
    {

        return view('kategori_template_email/index');   // ✅ tambah /index
    }
}