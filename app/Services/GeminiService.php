<?php

namespace App\Services;

use App\Models\ApiUsageModel;

class GeminiService
{
    private $apiKeys = [];
    private $currentIndex = 0;
    private $cacheService;
    private $requestDelay = 2;
    private $lastRequestTime = 0;
    private $keyStatus = [];
    private $cooldownTime = 300;
    private $maxRequestsPerDay = 15;
    private $apiUsageModel;

    public function __construct()
    {
        $this->cacheService = new CacheService();
        $this->apiUsageModel = new ApiUsageModel();

        for ($i = 1; $i <= 10; $i++) {
            $key = getenv('GEMINI_API_KEY_' . $i);
            if (!empty($key)) {
                $this->apiKeys[] = trim($key);
                $this->keyStatus[] = [
                    'active' => true,
                    'cooldown_until' => 0,
                    'error_count' => 0,
                    'last_used' => 0,
                    'limit_reached' => false,
                    'limit_message' => ''
                ];
            }
        }

        $this->requestDelay = (int)getenv('GEMINI_REQUEST_DELAY') ?: 2;
        $this->cooldownTime = (int)getenv('GEMINI_COOLDOWN_TIME') ?: 300;
        $this->maxRequestsPerDay = (int)getenv('GEMINI_MAX_REQUESTS_PER_DAY') ?: 15;

        $this->loadUsageFromDatabase();
    }

    private function loadUsageFromDatabase(): void
    {
        $allUsage = $this->apiUsageModel->getAllTodayUsage();
        
        foreach ($allUsage as $usage) {
            $index = array_search($usage['api_key'], $this->apiKeys);
            if ($index !== false) {
                if ($usage['is_limit']) {
                    $this->keyStatus[$index]['limit_reached'] = true;
                    $this->keyStatus[$index]['limit_message'] = $usage['limit_message'];
                    $this->keyStatus[$index]['active'] = false;
                }
            }
        }
    }

    public function sendRequest(string $prompt, string $model = 'gemini-3.8-flash'): ?array
    {
        $cacheKey = md5($prompt . $model);
        $cached = $this->cacheService->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $now = microtime(true);
        $timeSinceLast = $now - $this->lastRequestTime;
        if ($timeSinceLast < $this->requestDelay) {
            $waitTime = $this->requestDelay - $timeSinceLast;
            usleep($waitTime * 1000000);
        }

        $keyData = $this->getAvailableKey();
        if ($keyData === null) {
            return null;
        }

        $index = $keyData['index'];
        $apiKey = $keyData['key'];

        try {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent';

            $data = [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->lastRequestTime = microtime(true);
            $this->keyStatus[$index]['last_used'] = time();

            // KITA HANYA MEMBLOKIR KEY JIKA BENAR-BENAR 429 / 403
            if ($httpCode === 429 || $httpCode === 403) {
                $this->handleLimit($index, $apiKey, "Limit exceeded ({$httpCode})");
                return $this->sendRequest($prompt, $model);
            }

            // JIKA ERROR LAIN (404, 400, 500), KITA LEMPAR PESANNYA KE LAYAR
            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMessage = $errorData['error']['message'] ?? 'Unknown Error';
                throw new \RuntimeException("Google Error ({$httpCode}): " . $errorMessage);
            }

            $this->keyStatus[$index]['error_count'] = 0;
            $this->keyStatus[$index]['limit_reached'] = false;
            $this->apiUsageModel->incrementUsage($apiKey, $index);
            
            $result = json_decode($response, true);
            $this->cacheService->set($cacheKey, $result);
            
            $this->currentIndex = ($index + 1) % count($this->apiKeys);
            return $result;

        } catch (\Throwable $e) {
            // Jika errornya kita lempar dari atas, jangan ditelan
            throw $e; 
        }
    }

    private function getAvailableKey(): ?array
    {
        $totalKeys = count($this->apiKeys);
        $now = time();

        for ($attempt = 0; $attempt < $totalKeys; $attempt++) {
            $index = ($this->currentIndex + $attempt) % $totalKeys;
            $status = $this->keyStatus[$index];
            
            if ($status['cooldown_until'] > $now || !$status['active']) continue;

            $apiKey = $this->apiKeys[$index];
            $usage = $this->apiUsageModel->getTodayUsage($apiKey);
            
            if ($usage['is_limit'] || $usage['used_today'] >= $this->maxRequestsPerDay) {
                $this->handleLimit($index, $apiKey, 'Daily quota reached');
                continue;
            }

            $this->currentIndex = ($index + 1) % $totalKeys;
            return ['index' => $index, 'key' => $apiKey];
        }
        return null;
    }

    private function handleLimit(int $index, string $apiKey, string $message): void
    {
        $this->keyStatus[$index]['active'] = false;
        $this->keyStatus[$index]['cooldown_until'] = time() + $this->cooldownTime;
        $this->keyStatus[$index]['limit_reached'] = true;
        $this->keyStatus[$index]['limit_message'] = $message;
        $this->apiUsageModel->markLimit($apiKey, $message);
    }
}