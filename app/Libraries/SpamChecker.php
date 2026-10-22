<?php

namespace App\Libraries;

use RuntimeException;

class SpamChecker
{
  private string $model;
  private string $apiUrl;

  public function __construct()
  {
    $this->model = trim((string) (getenv('GEMINI_MODEL') ?: 'gemini-3.8-flash'));
    $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent';
  }

  public function check(string $subjek, string $isiHtml, string $isiTeks = ''): array
  {
    // AMBIL KEDUA API KEY
    $keys = [
      trim((string) getenv('GEMINI_API_KEY_1')),
      trim((string) getenv('GEMINI_API_KEY_2')),
    ];
    
    // Bersihkan jika ada yang kosong
    $keys = array_values(array_unique(array_filter($keys)));

    if (empty($keys)) {
      throw new RuntimeException('Konfigurasi layanan analisis belum tersedia.');
    }

    $errorDetails = [];

    // LOOPING: Coba Key 1, kalau gagal coba Key 2
    foreach ($keys as $index => $apiKey) {
      $label = 'KEY_' . ($index + 1);
      try {
        // Jika berhasil, langsung kembalikan hasilnya
        return $this->callGemini($apiKey, $subjek, $isiHtml, $isiTeks);
      } catch (\Throwable $e) {
        // Jika error (limit/timeout), catat pesannya lalu lanjut ke Key berikutnya
        $errorDetails[] = "{$label}: " . $e->getMessage();
        continue; 
      }
    }

    // Jika Key 1 dan Key 2 gagal semua, tampilkan error
    throw new RuntimeException(implode(' | ', $errorDetails));
  }

  private function callGemini(string $apiKey, string $subjek, string $isiHtml, string $isiTeks): array
  {
    // Ambil teks murni tanpa tag HTML agar ringan
    $textOnly = strip_tags($isiHtml);
    
    // Gunakan isi teks jika ada, jika tidak gunakan teks dari HTML
    $finalTeks = !empty(trim($isiTeks)) ? $isiTeks : $textOnly;
    
    // Batasi panjang teks maksimal 500 karakter (anti-timeout)
    $finalTeks = substr($finalTeks, 0, 500);

    $prompt = "Tentukan SPAM atau INBOX. Subjek: {$subjek}. Isi: {$finalTeks}. Balas JSON: {\"status\": \"spam\" atau \"inbox\", \"alasan\": [\"alasan\"]}";

    $payload = [
      'contents' => [['parts' => [['text' => $prompt]]]],
      'generationConfig' => [
        'temperature' => 0.2,
        'responseMimeType' => 'application/json',
      ],
    ];

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $urlWithKey = $this->apiUrl . '?key=' . $apiKey;
    
    $ch = curl_init($urlWithKey);

    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_POSTFIELDS => $jsonPayload,
      CURLOPT_CONNECTTIMEOUT => 15, 
      CURLOPT_TIMEOUT => 30,        // 30 detik cukup karena teks sudah pendek
      CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
    ]);

    $response = curl_exec($ch);
    $curlErrno = curl_errno($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
      throw new RuntimeException('cURL error: ' . $curlError, $curlErrno);
    }

    $data = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
      $apiMessage = is_array($data) ? ($data['error']['message'] ?? 'Tidak ada detail') : 'Bukan JSON';
      throw new RuntimeException("HTTP {$httpCode}: {$apiMessage}", $httpCode);
    }

    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if (!is_string($text) || trim($text) === '') {
      throw new RuntimeException('Respons analisis kosong.');
    }

    $text = trim($text);
    $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);
    
    $result = json_decode(trim($text), true);

    if (!is_array($result) || !isset($result['status'])) {
      throw new RuntimeException('Hasil analisis tidak sesuai format JSON.');
    }

    return [
      'success' => true,
      'status'  => strtolower(trim($result['status'])),
      'alasan'  => $result['alasan'] ?? [],
    ];
  }
}