<?php

namespace App\Controllers;

class TemplateController extends BaseController
{
    public function index()
    {
        return view('template_email/index');
    }
    public function tambah()
    {
        return view('template_email/tambah');
    }
    public function edit()
    {
        return view('template_email/edit');
    }
}