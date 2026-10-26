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
            
            // TAMBAHKAN 2 BARIS INI
            'attachment_pdf_url'   => $this->request->getPost('attachment_pdf_url'),
            'tipe_pdf'             => $this->request->getPost('tipe_pdf') ?? 'link',
            
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
            
            // TAMBAHKAN 2 BARIS INI
            'attachment_pdf_url'   => $this->request->getPost('attachment_pdf_url'),
            'tipe_pdf'             => $this->request->getPost('tipe_pdf') ?? 'link',
            
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

    public function uploadGambar()
    {
        if ($this->request->isAJAX()) {
            $file = $this->request->getFile('file');

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $ext = strtolower($file->getClientExtension());
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

                if (in_array($ext, $allowedExts)) {
                    $newName = $file->getRandomName();
                    $type = ($ext === 'pdf') ? 'application/pdf' : 'image/' . $ext;

                    if ($file->move(FCPATH . 'uploads', $newName)) {
                        return $this->response->setJSON([
                            'success' => true,
                            'url'     => base_url('uploads/' . $newName),
                            'type'    => $type
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Gagal memindahkan file ke folder uploads.'
                        ]);
                    }
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau PDF.'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File tidak valid atau gagal diunggah.'
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Request tidak valid.'
        ]);
    }

    public function cekSpam()
    {
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
            return $this->response
                ->setStatusCode(502)
                ->setJSON([
                    'success' => false,
                    'message' => 'Detail Error: ' . $e->getMessage(),
                ]);
        }
    }
}