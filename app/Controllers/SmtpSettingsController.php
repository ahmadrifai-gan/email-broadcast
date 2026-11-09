<?php

namespace App\Controllers;

use App\Libraries\SmtpConfigurator;
use App\Models\SmtpSettingModel;

class SmtpSettingsController extends BaseController
{
    protected SmtpSettingModel $model;
    protected SmtpConfigurator $configurator;

    public function __construct()
    {
        $this->model        = new SmtpSettingModel();
        $this->configurator = new SmtpConfigurator();
    }

    /**
     * Aturan validasi di controller (bukan di model) agar update() bisa
     * melonggarkan password tanpa menyentuh protected $model->validationRules.
     */
    private function aturan(bool $wajibPassword): array
    {
        return [
            'nama'       => 'required|min_length[3]|max_length[100]',
            'host'       => 'required|max_length[150]',
            'username'   => 'required|max_length[150]',
            'password'   => $wajibPassword ? 'required|max_length[500]' : 'permit_empty|max_length[500]',
            'port'       => 'required|integer|greater_than[0]|less_than[65536]',
            'crypto'     => 'required|in_list[tls,ssl,none]',
            'from_email' => 'required|valid_email|max_length[150]',
            'from_name'  => 'required|max_length[100]',
        ];
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

        if (! $this->validate($this->aturan(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->model->where('nama', $post['nama'])->first()) {
            return redirect()->back()->withInput()->with('error', 'Nama konfigurasi sudah digunakan.');
        }

        try {
            $data = [
                'nama'       => $post['nama'],
                'host'       => $post['host'],
                'username'   => $post['username'],
                'password'   => $this->configurator->encryptPassword((string) $post['password']),
                'port'       => (int) $post['port'],
                'crypto'     => $post['crypto'],
                'from_email' => $post['from_email'],
                'from_name'  => $post['from_name'],
                'is_active'  => ! empty($post['is_active']) ? 1 : 0,
            ];
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal mengenkripsi password: ' . $e->getMessage());
        }

        $id = $this->model->insert($data);
        if (! $id) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan konfigurasi: ' . implode(' ', $this->model->errors()));
        }

        if ($data['is_active'] === 1) {
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

        if (! $this->validate($this->aturan(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dup = $this->model->where('nama', $post['nama'])->where('id !=', $id)->first();
        if ($dup) {
            return redirect()->back()->withInput()->with('error', 'Nama konfigurasi sudah digunakan.');
        }

        $data = [
            'nama'       => $post['nama'],
            'host'       => $post['host'],
            'username'   => $post['username'],
            'port'       => (int) $post['port'],
            'crypto'     => $post['crypto'],
            'from_email' => $post['from_email'],
            'from_name'  => $post['from_name'],
            'is_active'  => ! empty($post['is_active']) ? 1 : 0,
        ];

        // Password hanya di-update bila diisi (kosong = tidak diubah).
        if (! empty($post['password'])) {
            try {
                $data['password'] = $this->configurator->encryptPassword($post['password']);
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()
                    ->with('error', 'Gagal mengenkripsi password: ' . $e->getMessage());
            }
        }

        $this->model->update($id, $data);

        if ($data['is_active'] === 1) {
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

    /**
     * TEST SMTP — INI BAGIAN YANG DIPERBAIKI.
     *
     * Sebelumnya: $mailer->initialize($cfg) dengan asumsi $cfg array.
     * Sekarang  : $cfg dari applyById() adalah Config\Email → langsung
     *             dipakai sebagai argumen pertama Services::email().
     */
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
            /** @var \Config\Email|null $cfg */
            $cfg = $this->configurator->applyById((int) $id);
            if (! $cfg) {
                return redirect()->to(base_url('smtp'))->with('error', 'Konfigurasi tidak bisa dibaca.');
            }

            // Non-shared Email instance dengan config SMTP hasil resolve.
            $mailer = \Config\Services::email($cfg, false);
            $mailer->clear(true);
            $mailer->setMailType('html');
            $mailer->setFrom(
                $cfg->fromEmail ?: $item['from_email'],
                $cfg->fromName  ?: 'email-broadcast'
            );
            $mailer->setTo($tujuan);
            $mailer->setSubject('Test SMTP - ' . $item['nama']);
            $mailer->setMessage('<p>Ini email percobaan dari konfigurasi <b>' . esc($item['nama']) . '</b>.</p>');

            if ($mailer->send(false)) {
                return redirect()->to(base_url('smtp'))
                    ->with('success', 'Test email berhasil dikirim ke ' . esc($tujuan));
            }

            $debug = strip_tags($mailer->printDebugger(['headers', 'subject']));
            return redirect()->to(base_url('smtp'))->with('error', 'Gagal mengirim: ' . $debug);
        } catch (\Throwable $e) {
            return redirect()->to(base_url('smtp'))->with('error', 'Error: ' . $e->getMessage());
        }
    }
}