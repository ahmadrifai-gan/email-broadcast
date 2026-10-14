<?php

namespace App\Controllers;

use App\Models\TemplateEmailModel;

class TemplateEmailmodel extends BaseController
{
    protected $templateModel;

    public function __construct()
    {
        $this->templateModel = new TemplateEmailmode();
    }

    public function index()
    {
        $data = [
            'template_email' => $this->templateModel->getAllWithKategori(),
        ];

        return view('template_email/index', $data);
    }

    public function store()
    {
        $data = [
            'kategori_template_id' => $this->request->getPost('kategori_template_id'),
            'nama_template'        => $this->request->getPost('nama_template'),
            'subjek_email'         => $this->request->getPost('subjek_email'),
            'isi_html'             => $this->request->getPost('isi_html'),
            'isi_teks'             => $this->request->getPost('isi_teks'),
            'status'               => $this->request->getPost('status'),
            'versi'                => $this->request->getPost('versi') ?? 1,
            'status_pemeriksaan'   => $this->request->getPost('status_pemeriksaan') ?? 'belum_diperiksa',
        ];

        if (!$this->templateModel->save($data)) {
            return redirect()->back()
                ->with('errors', $this->templateModel->errors())
                ->withInput();
        }

        return redirect()->to('template-email')
            ->with('success', 'Template email berhasil ditambahkan.');
    }

    public function update($id)
    {
        $data = [
            'id'                   => $id,
            'kategori_template_id' => $this->request->getPost('kategori_template_id'),
            'nama_template'        => $this->request->getPost('nama_template'),
            'subjek_email'         => $this->request->getPost('subjek_email'),
            'isi_html'             => $this->request->getPost('isi_html'),
            'isi_teks'             => $this->request->getPost('isi_teks'),
            'status'               => $this->request->getPost('status'),
            'versi'                => $this->request->getPost('versi'),
            'status_pemeriksaan'   => $this->request->getPost('status_pemeriksaan'),
        ];

        if (!$this->templateModel->save($data)) {
            return redirect()->back()
                ->with('errors', $this->templateModel->errors())
                ->withInput();
        }

        return redirect()->to('template-email')
            ->with('success', 'Template email berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (!$this->templateModel->delete($id)) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus template email.');
        }

        return redirect()->to('template-email')
            ->with('success', 'Template email berhasil dihapus.');
    }
}