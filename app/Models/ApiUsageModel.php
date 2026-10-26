<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiUsageModel extends Model
{
    protected $table = 'api_usage';
    protected $primaryKey = 'id';
    protected $allowedFields = ['api_key', 'date', 'used_today', 'is_limit', 'limit_message', 'last_used_at'];
    protected $useTimestamps = false;

    public function getTodayUsage($apiKey)
    {
        $today = date('Y-m-d');
        $usage = $this->where(['api_key' => $apiKey, 'date' => $today])->first();

        if (!$usage) {
            $this->insert([
                'api_key' => $apiKey,
                'date' => $today,
                'used_today' => 0,
                'is_limit' => 0,
                'limit_message' => ''
            ]);
            return ['used_today' => 0, 'is_limit' => 0, 'limit_message' => '', 'last_used_at' => null];
        }
        return $usage;
    }

    public function getAllTodayUsage()
    {
        return $this->where('date', date('Y-m-d'))->findAll();
    }

    public function incrementUsage($apiKey, $index)
    {
        $today = date('Y-m-d');
        $usage = $this->where(['api_key' => $apiKey, 'date' => $today])->first();

        if ($usage) {
            $this->update($usage['id'], [
                'used_today' => $usage['used_today'] + 1,
                'last_used_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->insert([
                'api_key' => $apiKey,
                'date' => $today,
                'used_today' => 1,
                'last_used_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function markLimit($apiKey, $message)
    {
        $today = date('Y-m-d');
        $usage = $this->where(['api_key' => $apiKey, 'date' => $today])->first();

        if ($usage) {
            $this->update($usage['id'], [
                'is_limit' => 1,
                'limit_message' => $message
            ]);
        } else {
            $this->insert([
                'api_key' => $apiKey,
                'date' => $today,
                'used_today' => 0,
                'is_limit' => 1,
                'limit_message' => $message
            ]);
        }
    }

    public function resetToday()
    {
        $this->where('date', date('Y-m-d'))->delete();
    }
}