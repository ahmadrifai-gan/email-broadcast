<?php

namespace App\Libraries;

use Exception;

class SpamChecker
{
  protected $apiKey;
  protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent';

  public function __construct()
  {
    $this->apiKey = env('GEMINI_API_KEY');

    if (empty($this->apiKey)) {
      throw new Exception('GEMINI_API_KEY belum di-set di file .env');
    }
  }

  /**
   * Cek apakah template email masuk spam atau inbox
   *
   * @param string $subjek Subjek email
   * @param string $isiHtml Isi HTML template
   * @param string $isiTeks Isi teks (opsional)
   * @return array ['status' => 'spam'|'inbox', 'alasan' => array]
   */
  public function check($subjek, $isiHtml, $isiTeks = '')
  {
    $prompt = $this->buildPrompt($subjek, $isiHtml, $isiTeks);

    try {
      $response = $this->callGemini($prompt);
      return $this->parseResponse($response);
    } catch (Exception $e) {
      return [
        'status'  => 'error',
        'alasan'  => ['Gagal menganalisis: ' . $e->getMessage()],
        'success' => false,
      ];
    }
  }

  /**
   * Build prompt untuk Gemini
   */
  protected function buildPrompt($subjek, $isiHtml, $isiTeks)
  {
    // Bersihkan HTML untuk analisis teks
    $textOnly = strip_tags($isiHtml);

    return <<<PROMPT
Anda adalah ahli email marketing yang berpengalaman menganalisis email spam.

Analisis template email berikut dan tentukan apakah email ini kemungkinan besar akan masuk ke **SPAM** atau **INBOX** penerima.

=== DATA EMAIL ===
SUBJEK: {$subjek}

ISI HTML:
{$isiHtml}

ISI TEKS:
{$isiTeks}

TEKS MURNI (tanpa HTML):
{$textOnly}
=== END DATA ===

Kriteria penilaian SPAM:
1. Kata-kata pemicu spam (GRATIS, KLIK SEKARANG, 100%, JAMINAN, ACT NOW, dll.)
2. Huruf kapital berlebihan
3. Tanda seru berlebihan (!!!)
4. Rasio gambar vs teks tidak seimbang (terlalu banyak gambar, sedikit teks)
5. Link mencurigakan atau terlalu banyak link
6. Tidak ada link unsubscribe
7. HTML rusak atau tidak valid
8. Subjek mencurigakan (misal pakai "RE:" padahal bukan reply)
9. Penggunaan font/warna mencolok berlebihan
10. Placeholder tidak wajar (misal {{nama}} tapi tidak ada konteks personalisasi)

Aturan output:
- Jika email AMAN (tidak ada masalah signifikan), status = "inbox", alasan = []
- Jika email BERISIKO SPAM, status = "spam", alasan = daftar masalah yang ditemukan (dalam bahasa Indonesia, singkat, jelas)

Balas HANYA dengan format JSON berikut, tanpa penjelasan tambahan:

{
  "status": "spam" atau "inbox",
  "alasan": ["alasan 1", "alasan 2", ...]
}

Jika status = "inbox", alasan HARUS array kosong [].
PROMPT;
  }

  /**
   * Panggil Gemini API
   */
  protected function callGemini($prompt)
  {
    $url = $this->apiUrl . '?key=' . $this->apiKey;

    $payload = [
      'contents' => [
        [
          'parts' => [
            ['text' => $prompt]
          ]
        ]
      ],
      'generationConfig' => [
        'temperature'      => 0.3,
        'maxOutputTokens'  => 500,
        'responseMimeType' => 'application/json',
      ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
      throw new Exception('cURL error: ' . $curlError);
    }

    if ($httpCode !== 200) {
      $decoded = json_decode($response, true);
      $errorMsg = $decoded['error']['message'] ?? 'HTTP ' . $httpCode;
      throw new Exception('API error: ' . $errorMsg);
    }

    return $response;
  }

  /**
   * Parse response dari Gemini
   */
  protected function parseResponse($rawResponse)
  {
    $decoded = json_decode($rawResponse, true);

    if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
      throw new Exception('Format response Gemini tidak valid');
    }

    $text = $decoded['candidates'][0]['content']['parts'][0]['text'];

    // Bersihkan jika ada markdown code block
    $text = trim($text);
    $text = preg_replace('/^```json\s*/i', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);
    $text = trim($text);

    $result = json_decode($text, true);

    if (!$result || !isset($result['status'])) {
      throw new Exception('Gagal parse JSON dari Gemini: ' . $text);
    }

    return [
      'status'  => $result['status'],           // 'spam' atau 'inbox'
      'alasan'  => $result['alasan'] ?? [],     // array
      'success' => true,
    ];
  }
}
