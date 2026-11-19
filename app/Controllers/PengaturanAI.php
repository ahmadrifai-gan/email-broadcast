<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\AIService;
use App\Models\PengaturanAiModel;

class PengaturanAI extends BaseController
{
    /**
     * Menampilkan halaman pengaturan AI
     */
    public function index()
{
    $pengaturanModel = new PengaturanAiModel();

    $config = $pengaturanModel->getActiveConfig() ?? [];

    $aiService = new AIService();
    $hasApiKey = $aiService->hasApiKey();

    unset($config['api_key']);

    $data = [
        'title'         => 'Pengaturan AI',
        'active_menu'   => 'pengaturan',
        'config'        => $config,
        'hasApiKey'     => $hasApiKey,
        'defaultPrompt' => AIService::PROMPT_TEMPLATE,
    ];

    return view('pengaturan/index', $data);
}

    /**
     * Menyimpan pengaturan AI
     */
    public function save()
    {
        $pengaturanModel = new PengaturanAiModel();

        $current = $pengaturanModel
            ->orderBy('id', 'ASC')
            ->first();

        $provider = trim(
            $this->request->getPost('provider') ?: 'gemini'
        );

        $namaModel = trim(
            $this->request->getPost('nama_model') ?: 'gemini-3.5-flash-lite'
        );

        $instruksiAi = trim(
            $this->request->getPost('instruksi_ai')
            ?: AIService::PROMPT_TEMPLATE
        );

        $temperature = (float) (
            $this->request->getPost('temperature') ?? 0
        );

        $maxTokens = (int) (
            $this->request->getPost('max_tokens') ?: 8192
        );

        $status = $this->request->getPost('status') ?: 'aktif';

        // Validasi input
        if (
            $temperature < 0 ||
            $temperature > 2 ||
            $maxTokens < 1
        ) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nilai temperature atau max tokens tidak valid.');
        }

        $data = [
            'provider'     => $provider,
            'nama_model'   => $namaModel,
            'instruksi_ai' => $instruksiAi,
            'temperature'  => $temperature,
            'max_tokens'   => $maxTokens,
            'status'       => $status,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($current && isset($current['id'])) {

            // Update konfigurasi yang sudah ada
            $pengaturanModel->update($current['id'], $data);

        } else {

            // Tambahkan konfigurasi baru
            $data['created_at'] = date('Y-m-d H:i:s');

            $pengaturanModel->insert($data);
        }

        return redirect()->to(
            base_url('/pengaturan')
        )->with(
            'success',
            'Pengaturan AI berhasil diperbarui. API Key tetap dikelola melalui file .env.'
        );
    }

    /**
     * Menguji koneksi Google Gemini API
     */
    public function testConnection()
    {
        $aiService = new AIService();

        $result = $aiService->testConnection();

        return $this->response->setJSON($result);
    }
}
