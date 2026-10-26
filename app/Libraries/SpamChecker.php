<?php

namespace App\Libraries;

use App\Services\GeminiService;
use RuntimeException;

class SpamChecker
{
  protected $gemini;

  public function __construct()
  {
    $this->gemini = new GeminiService();
  }

  public function check(string $subjek, string $isiHtml, string $isiTeks = ''): array
  {
    $textOnly = strip_tags($isiHtml);
    $finalTeks = !empty(trim($isiTeks)) ? $isiTeks : $textOnly;
    $finalTeks = substr($finalTeks, 0, 1000);

    $prompt = "Anda adalah ahli email marketing. Tentukan apakah email ini masuk SPAM atau INBOX.
Subjek: {$subjek}
Isi: {$finalTeks}

Balas HANYA dengan format JSON murni (tanpa teks lain, tanpa markdown):
{\"status\": \"spam\" atau \"inbox\", \"alasan\": [\"alasan 1\", \"alasan 2\"]}";

    // DAFTAR MODEL YANG AKAN DICOBA BERURUTAN
    // Jika satu gagal, akan coba model berikutnya
    // DAFTAR MODEL YANG DIJAMIN ADA OLEH GOOGLE SAAT INI
    $modelsToTry = [
      'gemini-3.8-flash',   // Model utama (paling pintar)
      'gemini-2.5-flash',   // Model cadangan (stabil)
      'gemini-3.6-flash'    // Model cadangan (ringan)
    ];

    $lastError = '';

    foreach ($modelsToTry as $model) {
      try {
        $response = $this->gemini->sendRequest($prompt, $model);

        if ($response === null) {
          $lastError = "API Key limit / sibuk untuk model {$model}";
          continue; // Coba model berikutnya
        }

        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!is_string($text) || trim($text) === '') {
          $lastError = "Format balasan AI kosong untuk model {$model}";
          continue;
        }

        // Bersihkan markdown jika AI nakal
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $result = json_decode(trim($text), true);

        if (!is_array($result) || !isset($result['status'])) {
          $lastError = "Hasil analisis tidak sesuai format JSON untuk model {$model}";
          continue;
        }

        // KALAU BERHASIL, LANGSUNG KEMBALIKAN HASILNYA
        return [
          'success' => true,
          'status'  => strtolower(trim($result['status'])),
          'alasan'  => $result['alasan'] ?? [],
        ];
      } catch (\Throwable $e) {
        // Simpan error terakhir dan coba model berikutnya
        $lastError = $e->getMessage();
        continue;
      }
    }

    // Jika semua model gagal
    throw new RuntimeException('Gagal terhubung ke AI. Detail: ' . $lastError);
  }
}
