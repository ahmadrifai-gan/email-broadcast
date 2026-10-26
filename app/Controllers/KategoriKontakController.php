<?php

namespace App\Controllers;

use App\Models\KategoriKontakModel;

class KategoriKontakController extends BaseController
{
    protected KategoriKontakModel $model;

    public function __construct()
    {
        $this->model = new KategoriKontakModel();
    }

    private function aturan(?int $id = null): array
    {
        $unik = $id ? "is_unique[kategori.nama,id,{$id}]" : 'is_unique[kategori.nama]';

        return [
            'nama' => [
                'label'  => 'Nama kategori',
                'rules'  => "required|max_length[100]|{$unik}",
                'errors' => [
                    'required'   => 'Nama kategori wajib diisi.',
                    'max_length' => 'Nama kategori maksimal 100 karakter.',
                    'is_unique'  => 'Nama kategori sudah dipakai.',
                ],
            ],
        ];
    }

    // READ: daftar + pencarian + pagination
    public function index()
    {
        $cari = trim((string) $this->request->getGet('cari'));

        $builder = $this->model->orderBy('nama', 'ASC');
        if ($cari !== '') {
            $builder->like('nama', $cari);
        }

        return view('kategori/index', [
            'judul'    => 'Kategori',
            'kategori' => $builder->paginate(10),
            'pager'    => $this->model->pager,
            'cari'     => $cari,
        ]);
    }

    // CREATE: simpan data (form ada di modal)
    public function simpan()
    {
        if (! $this->validate($this->aturan())) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('modal_aksi', site_url('kategori/simpan'));
        }

        $this->model->insert(['nama' => trim((string) $this->request->getPost('nama'))]);

        return redirect()->to('/kategori')->with('sukses', 'Kategori berhasil ditambahkan.');
    }

    // UPDATE: simpan perubahan (form ada di modal)
    public function perbarui(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/kategori')->with('gagal', 'Kategori tidak ditemukan.');
        }

        if (! $this->validate($this->aturan($id))) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('modal_aksi', site_url('kategori/perbarui/' . $id));
        }

        $this->model->update($id, ['nama' => trim((string) $this->request->getPost('nama'))]);

        return redirect()->to('/kategori')->with('sukses', 'Kategori berhasil diperbarui.');
    }

    // DELETE
    public function hapus(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/kategori')->with('gagal', 'Kategori tidak ditemukan.');
        }

        // Opsional: tolak jika kategori masih dipakai kontak.
        // Hapus blok ini jika ingin kontaknya otomatis menjadi tanpa kategori (ON DELETE SET NULL).
        $dipakai = db_connect()->table('kontak_email')->where('kategori_id', $id)->countAllResults();
        if ($dipakai > 0) {
            return redirect()->to('/kategori')
                ->with('gagal', "Kategori tidak bisa dihapus karena masih dipakai oleh {$dipakai} kontak.");
        }

        $this->model->delete($id);

        return redirect()->to('/kategori')->with('sukses', 'Kategori berhasil dihapus.');
    }
}