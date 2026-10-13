<?php

namespace App\Controllers;

use App\Models\KontakModel;

class KontakController extends BaseController
{
    protected KontakModel $model;

    public function __construct()
    {
        $this->model = new KontakModel();
    }

    // Read: tampilkan semua data (tambah & edit memakai modal di halaman yang sama)
    public function index()
    {
        return view('kontak_email/index', [
            'perusahaan' => $this->model->semua(),
        ]);
    }

    // Create
    public function store()
    {
        $data = $this->request->getPost(['nama_perusahaan', 'email', 'domisili']);

        if (! $this->model->tambah($data)) {
            return redirect()->to('/kontak')
                ->withInput()
                ->with('errors', $this->model->errors())
                ->with('modal', 'tambah');
        }

        return redirect()->to('/kontak')->with('success', 'Data berhasil ditambahkan.');
    }

    // Update
    public function update(int $id)
    {
        if (! $this->model->cari($id)) {
            return redirect()->to('/kontak')->with('error', 'Data tidak ditemukan.');
        }

        $data = $this->request->getPost(['nama_perusahaan', 'email', 'domisili']);
        $data['id'] = $id; // dipakai placeholder {id} pada aturan is_unique

        if (! $this->model->ubah($id, $data)) {
            return redirect()->to('/kontak')
                ->withInput()
                ->with('errors', $this->model->errors())
                ->with('modal', 'edit')
                ->with('edit_id', $id);
        }

        return redirect()->to('/kontak')->with('success', 'Data berhasil diubah.');
    }

    // Delete
    public function delete(int $id)
    {
        if (! $this->model->cari($id)) {
            return redirect()->to('/kontak')->with('error', 'Data tidak ditemukan.');
        }

        $this->model->hapus($id);

        return redirect()->to('/kontak')->with('success', 'Data berhasil dihapus.');
    }
}