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

  // Import Excel
public function import()
{
    $file = $this->request->getFile('file_excel');

    if (! $file || ! $file->isValid() || ! in_array(strtolower($file->getExtension()), ['xlsx', 'xls'], true)) {
        return redirect()->to('/kontak')->with('error', 'File harus berformat .xlsx atau .xls.');
    }

    try {
        $rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName())
            ->getActiveSheet()
            ->toArray(null, true, true, false);
    } catch (\Throwable $e) {
        return redirect()->to('/kontak')->with('error', 'File Excel tidak bisa dibaca: ' . $e->getMessage());
    }

    array_shift($rows); // buang baris header

    $berhasil = 0;
    $gagal    = [];

    foreach ($rows as $i => $r) {
        $data = [
            'nama_perusahaan' => trim((string) ($r[0] ?? '')),
            'email'           => trim((string) ($r[1] ?? '')),
            'domisili'        => trim((string) ($r[2] ?? '')),
        ];

        // lewati baris yang benar-benar kosong
        if (implode('', $data) === '') {
            continue;
        }

        // string kosong -> NULL agar tidak bentrok dengan unique key email
        foreach ($data as $k => $v) {
            $data[$k] = ($v === '') ? null : $v;
        }

        try {
            $ok = $this->model->tambah($data);
        } catch (\Throwable $e) {
            $gagal[] = 'Baris ' . ($i + 2) . ': gagal disimpan (' . $e->getMessage() . ')';
            continue;
        }

        if ($ok) {
            $berhasil++;
        } else {
            $gagal[] = 'Baris ' . ($i + 2) . ': ' . implode(', ', $this->model->errors());
        }
    }

    $redirect = redirect()->to('/kontak')->with('success', "$berhasil data berhasil diimpor.");

    return $gagal ? $redirect->with('import_errors', $gagal) : $redirect;
}

}