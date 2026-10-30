<?php

namespace App\Controllers;

use App\Models\SmtpSettingModel;
use App\Libraries\SmtpConfigurator;

class SmtpSettingsController extends BaseController
{
    protected SmtpSettingModel $model;
    protected SmtpConfigurator $configurator;

    public function __construct()
    {
        $this->model        = new SmtpSettingModel();
        $this->configurator = new SmtpConfigurator();
    }

    public function index()
    {
        return view('smtp_settings/index', [
            'title' => 'Konfigurasi SMTP',
            'items' => $this->model->orderBy('is_active', 'DESC')->orderBy('id', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('smtp_settings/form', [
            'title'  => 'Tambah Konfigurasi SMTP',
            'item'   => null,
            'action' => base_url('smtp'),
        ]);
    }

    public function store()
    {
        $post = $this->request->getPost();

        if (! $this->model->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        if ($this->model->where('nama', $post['nama'])->first()) {
            return redirect()->back()->withInput()->with('error', 'Nama konfigurasi sudah digunakan.');
        }

        try {
            $post['password'] = $this->configurator->encryptPassword((string) ($post['password'] ?? ''));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        $post['is_active'] = ! empty($post['is_active']) ? 1 : 0;

        $id = $this->model->insert($post);
        if (! $id) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan konfigurasi.');
        }

        if ($post['is_active'] === 1) {
            $this->model->setActive((int) $id);
        }

        return redirect()->to(base_url('smtp'))->with('success', 'Konfigurasi SMTP berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(base_url('smtp'))->with('error', 'Data tidak ditemukan.');
        }

        return view('smtp_settings/form', [
            'title'  => 'Edit Konfigurasi SMTP',
            'item'   => $item,
            'action' => base_url('smtp/' . $id),
        ]);
    }

    public function update($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(base_url('smtp'))->with('error', 'Data tidak ditemukan.');
        }

        $post = $this->request->getPost();

        $rules = $this->model->validationRules;
        if (empty($post['password'])) {
            $rules['password'] = 'permit_empty';
            unset($post['password']);
        }

        if (! $this->model->setValidationRules($rules)->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        $dup = $this->model->where('nama', $post['nama'])->where('id !=', $id)->first();
        if ($dup) {
            return redirect()->back()->withInput()->with('error', 'Nama konfigurasi sudah digunakan.');
        }

        if (! empty($post['password'])) {
            try {
                $post['password'] = $this->configurator->encryptPassword($post['password']);
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $post['is_active'] = ! empty($post['is_active']) ? 1 : 0;
        $this->model->update($id, $post);

        if ($post['is_active'] === 1) {
            $this->model->setActive((int) $id);
        }

        return redirect()->to(base_url('smtp'))->with('success', 'Konfigurasi SMTP berhasil diperbarui.');
    }

    public function activate($id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to(base_url('smtp'))->with('error', 'Data tidak ditemukan.');
        }

        $this->model->setActive((int) $id);

        return redirect()->to(base_url('smtp'))->with('success', 'Konfigurasi SMTP diaktifkan.');
    }

    public function delete($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(base_url('smtp'))->with('error', 'Data tidak ditemukan.');
        }

        if ((int) $item['is_active'] === 1) {
            return redirect()->to(base_url('smtp'))
                ->with('error', 'Tidak bisa menghapus konfigurasi yang sedang aktif. Aktifkan konfigurasi lain dulu.');
        }

        $this->model->delete($id);

        return redirect()->to(base_url('smtp'))->with('success', 'Konfigurasi SMTP dihapus.');
    }

    /** Kirim email percobaan untuk konfigurasi tertentu. */
    public function test($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(base_url('smtp'))->with('error', 'Data tidak ditemukan.');
        }

        $tujuan = $this->request->getPost('test_email') ?: $item['from_email'];
        if (! filter_var($tujuan, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to(base_url('smtp'))->with('error', 'Email tujuan test tidak valid.');
        }

        try {
            $cfg = $this->configurator->applyById((int) $id);
            if (! $cfg) {
                return redirect()->to(base_url('smtp'))->with('error', 'Konfigurasi tidak bisa dibaca.');
            }

            $mailer = \Config\Services::email($cfg, false);
            $mailer->setTo($tujuan);
            $mailer->setSubject('Test SMTP - ' . $item['nama']);
            $mailer->setMessage('<p>Ini email percobaan dari konfigurasi <b>' . esc($item['nama']) . '</b>.</p>');

            if ($mailer->send()) {
                return redirect()->to(base_url('smtp'))->with('success', 'Test email berhasil dikirim ke ' . esc($tujuan));
            }

            return redirect()->to(base_url('smtp'))->with('error', 'Gagal mengirim: ' . $mailer->printDebugger(['headers']));
        } catch (\Throwable $e) {
            return redirect()->to(base_url('smtp'))->with('error', 'Error: ' . $e->getMessage());
        }
    }
}