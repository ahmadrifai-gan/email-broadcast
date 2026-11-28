<?php

namespace App\Controllers;

use App\Models\DataWebsiteModel;
use App\Models\KontakModel;
use CodeIgniter\Controller;

/**
 * CRUD Website milik kontak/perusahaan.
 *   GET  data-kontak/website                 halaman (?kontak=ID untuk menyaring satu perusahaan)
 *   GET  data-kontak/website/data            daftar data JSON (?kontak_id=ID)
 *   POST data-kontak/website/simpan          tambah
 *   POST data-kontak/website/perbarui/(:num) ubah
 *   POST data-kontak/website/hapus/(:num)    hapus
 */
class DataWebsite extends Controller
{
    private DataWebsiteModel $model;

    public function __construct()
    {
        $this->model = new DataWebsiteModel();
    }

    /** Halaman daftar + form tambah/ubah/hapus. */
    public function index()
    {
        return view('data_kontak/website', [
            'kontakId' => (int) $this->request->getGet('kontak'),
            'opsi'     => (new KontakModel())->opsiKontak(),
        ]);
    }

    /** Data untuk tabel (JSON). */
    public function data()
    {
        try {
            return $this->response->setJSON($this->model->daftar((int) $this->request->getGet('kontak_id')));
        } catch (\Throwable $e) {
            return $this->gagal($e, 500);
        }
    }

    public function simpan()
    {
        try {
            return $this->response->setJSON(['ok' => true, 'data' => $this->model->tambah($this->input())]);
        } catch (\InvalidArgumentException $e) {
            return $this->gagal($e, 422);
        } catch (\Throwable $e) {
            return $this->gagal($e, 500);
        }
    }

    public function perbarui($id = 0)
    {
        try {
            return $this->response->setJSON(['ok' => true, 'data' => $this->model->ubah((int) $id, $this->input())]);
        } catch (\InvalidArgumentException $e) {
            return $this->gagal($e, 422);
        } catch (\Throwable $e) {
            return $this->gagal($e, 500);
        }
    }

    public function hapus($id = 0)
    {
        try {
            if (! $this->model->hapusData((int) $id)) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Data tidak ditemukan']);
            }
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->gagal($e, 500);
        }
    }

    /** Body JSON, atau form biasa sebagai cadangan. */
    private function input(): array
    {
        $json = $this->request->getJSON(true);
        return is_array($json) && $json ? $json : (array) $this->request->getPost();
    }

    private function gagal(\Throwable $e, int $code)
    {
        $msg = $code === 422 ? $e->getMessage() : 'Terjadi kesalahan: ' . $e->getMessage();
        return $this->response->setStatusCode($code)->setJSON(['error' => $msg]);
    }
}