<?php

namespace App\Libraries;

use App\Models\SmtpSettingModel;
use Config\Email as EmailConfig;
use Config\Services;
use CodeIgniter\Encryption\EncrypterInterface;

class SmtpConfigurator
{
    protected SmtpSettingModel $model;
    protected ?EncrypterInterface $encrypter = null;
    protected array $cache = []; // cache hasil decrypt per id agar tidak bolak-balik

    public function __construct()
    {
        $this->model = new SmtpSettingModel();
        try {
            $this->encrypter = Services::encrypter();
        } catch (\Throwable $e) {
            $this->encrypter = null; // biar error ditangani di method
        }
    }

    public function encryptPassword(string $plain): string
    {
        if ($this->encrypter === null) {
            throw new \RuntimeException(
                'Encryption key belum di-set. Jalankan: php spark key:generate'
            );
        }
        return base64_encode($this->encrypter->encrypt($plain));
    }

    public function decryptPassword(string $encrypted): string
    {
        if ($this->encrypter === null || $encrypted === '') {
            return '';
        }
        try {
            return $this->encrypter->decrypt(base64_decode($encrypted));
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** Terapkan konfigurasi SMTP berdasarkan ID tertentu. */
    public function applyById(?int $id, ?EmailConfig $config = null): ?EmailConfig
    {
        if (! $id) {
            return null;
        }

        $row = $this->model->find($id);
        if (! $row) {
            return null;
        }

        return $this->applyRow($row, $config);
    }

    /** Terapkan konfigurasi SMTP yang is_active = 1. */
    public function apply(?EmailConfig $config = null): ?EmailConfig
    {
        $row = $this->model->getActive();
        if (! $row) {
            return null;
        }
        return $this->applyRow($row, $config);
    }

    /**
     * Resolver utama: pilih SMTP untuk kampanye.
     * Prioritas: id kampanye → aktif global → .env
     */
    public function forKampanye(?int $smtpSettingId): EmailConfig
    {
        if ($cfg = $this->applyById($smtpSettingId)) {
            return $cfg;
        }
        if ($cfg = $this->apply()) {
            return $cfg;
        }
        return config('Email'); // fallback .env
    }

    protected function applyRow(array $row, ?EmailConfig $config = null): EmailConfig
    {
        $config ??= config('Email');

        $config->protocol   = 'smtp';
        $config->SMTPHost   = $row['host'];
        $config->SMTPUser   = $row['username'];
        $config->SMTPPass   = $this->decryptPassword($row['password']);
        $config->SMTPPort   = (int) $row['port'];
        $config->SMTPCrypto = $row['crypto'] === 'none' ? '' : $row['crypto'];
        $config->fromEmail  = $row['from_email'];
        $config->fromName   = $row['from_name'];
        $config->mailType   = 'html';

        return $config;
    }
}