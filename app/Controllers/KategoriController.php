<?php

namespace App\Controllers;

use App\Models\KategoriTemplateEmailModel;

class KategoriController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new KategoriTemplateEmailModel();
    }

    /* =========================
     * LIST DATA
     * ========================= */
    public function index()
    {
        $data = [
            'title'    => 'Data Kategori Template Email',
            'kategori' => $this->model->orderBy('id', 'DESC')->findAll(),
        ];

        return view('kategori_template_email/index', $data);
    }

    /* =========================
     * SIMPAN (CREATE)
     * ========================= */
    public function store()
    {
        $rules = [
            'nama_kategori' => 'required|min_length[3]|max_length[100]|is_unique[kategori_template_email.nama_kategori]',
            'status'        => 'required|in_list[aktif,nonaktif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'nama_kategori'   => $this->request->getPost('nama_kategori'),
            'deskripsi'       => $this->request->getPost('deskripsi'),
            'status'          => $this->request->getPost('status'),
            'dibuat_pada'     => date('Y-m-d H:i:s'),
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('kategori-template-email'))
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /* =========================
     * UPDATE
     * ========================= */
    public function update($id)
    {
        $kategori = $this->model->find($id);
        if (!$kategori) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama_kategori' => "required|min_length[3]|max_length[100]|is_unique[kategori_template_email.nama_kategori,id,{$id}]",
            'status'        => 'required|in_list[aktif,nonaktif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'nama_kategori'   => $this->request->getPost('nama_kategori'),
            'deskripsi'       => $this->request->getPost('deskripsi'),
            'status'          => $this->request->getPost('status'),
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('kategori-template-email'))
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /* =========================
     * DELETE
     * ========================= */
    public function delete($id)
    {
        $kategori = $this->model->find($id);
        if (!$kategori) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $this->model->delete($id);

        return redirect()->to(base_url('kategori-template-email'))
            ->with('success', 'Kategori berhasil dihapus.');
    }
}