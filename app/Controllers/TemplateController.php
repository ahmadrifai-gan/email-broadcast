<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TemplateEmailModel;
use App\Models\KategoriTemplateEmailModel;
use App\Libraries\SpamChecker;

class TemplateController extends BaseController
{
    protected $templateModel;
    protected $kategoriModel;

    public function __construct()
    {
        $this->templateModel = new TemplateEmailModel();
        $this->kategoriModel = new KategoriTemplateEmailModel();
    }

    public function index()
    {
        $data = [
            'template_email' => $this->templateModel->getAllWithKategori(),
        ];

        return view('template_email/index', $data);
    }

    public function tambah()
    {
        $data = [
            'kategori' => $this->kategoriModel->findAll(),
        ];

        return view('template_email/tambah', $data);
    }

    public function store()
    {
        // Validasi input
        if (!$this->validate([
            'kategori_template_id' => 'required|numeric',
            'nama_template'        => 'required|max_length[150]',
            'subjek_email'         => 'required|max_length[255]',
            'isi_html'             => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kategori_template_id' => $this->request->getPost('kategori_template_id'),
            'nama_template'        => $this->request->getPost('nama_template'),
            'subjek_email'         => $this->request->getPost('subjek_email'),
            'isi_html'             => $this->request->getPost('isi_html'),
            'isi_teks'             => $this->request->getPost('isi_teks'),
            'status'               => $this->request->getPost('status') ?? 'draf',
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

    public function edit($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('template-email')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'template' => $template,
            'kategori' => $this->kategoriModel->findAll(),
        ];

        return view('template_email/edit', $data);
    }

    public function update($id)
    {
        if (!$this->validate([
            'kategori_template_id' => 'required|numeric',
            'nama_template'        => 'required|max_length[150]',
            'subjek_email'         => 'required|max_length[255]',
            'isi_html'             => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

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
            return redirect()->back()->with('error', 'Gagal menghapus template email.');
        }

        return redirect()->to('template-email')->with('success', 'Template email berhasil dihapus.');
    }


    public function cekSpam()
    {
        // KITA HAPUS PENGECEKAN isAJAX AGAR TIDAK DI-BLOCK
        $subjek = trim((string) $this->request->getPost('subjek_email'));
        $isiHtml = trim((string) $this->request->getPost('isi_html'));
        $isiTeks = trim((string) $this->request->getPost('isi_teks'));

        if ($subjek === '' || $isiHtml === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Subjek email dan isi HTML wajib diisi.',
            ]);
        }

        try {
            $spamChecker = new SpamChecker();
            $hasil = $spamChecker->check($subjek, $isiHtml, $isiTeks);

            return $this->response->setJSON([
                'success' => true,
                'status'  => $hasil['status'],
                'alasan'  => $hasil['alasan'],
            ]);
        } catch (\Throwable $e) {
            // TAMPILKAN ERROR ASLI KE LAYAR
            return $this->response
                ->setStatusCode(502)
                ->setJSON([
                    'success' => false,
                    'message' => 'Detail Error: ' . $e->getMessage(),
                ]);
        }
    }
}
