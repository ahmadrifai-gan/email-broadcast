<?php

namespace App\Libraries;

/**
 * Error dari Gemini / koneksi yang punya "alasan" jelas,
 * supaya worker tahu harus: ganti model, menunggu, atau menghentikan job.
 */
class QuotaException extends \RuntimeException
{
    /** Model tidak ada / tidak tersedia untuk API key ini -> coba model cadangan */
    public const MODEL_MISSING = 'model_missing';
    /** Kuota harian model habis -> coba model cadangan, kalau semua habis job berhenti */
    public const DAILY = 'daily';
    /** Limit per menit -> tunggu lalu coba lagi */
    public const RATE = 'rate';
    /** API key salah / ditolak -> job berhenti */
    public const AUTH = 'auth';
    /** Sertifikat SSL server bermasalah -> job berhenti */
    public const SSL = 'ssl';
    /** Gemini sibuk / gangguan (5xx, timeout) walau sudah dicoba ulang -> coba model lain, lalu regex */
    public const UNAVAILABLE = 'unavailable';
    public const OTHER = 'other';

    private string $reason;

    public function __construct(string $message = '', string $reason = self::OTHER, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->reason = $reason;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}