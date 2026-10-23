<?php

namespace App\Services;

class CacheService
{
    private $cacheDir;
    private $ttl = 86400; // 24 jam

    public function __construct()
    {
        $this->cacheDir = WRITEPATH . 'cache/gemini/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get($key)
    {
        $file = $this->cacheDir . md5($key) . '.json';
        if (file_exists($file) && (time() - filemtime($file)) < $this->ttl) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }

    public function set($key, $data)
    {
        $file = $this->cacheDir . md5($key) . '.json';
        file_put_contents($file, json_encode($data));
    }

    public function clear()
    {
        $files = glob($this->cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    public function getStats()
    {
        $files = glob($this->cacheDir . '*.json');
        return ['total_cached' => count($files)];
    }
}