<?php

namespace App\Controllers;

use App\Libraries\EmailExtractor;
use App\Models\JobModel;
use App\Models\KontakModel;
use CodeIgniter\Controller;

class Extractor extends Controller
{
    private KontakModel $kontak;
    private JobModel $jobs;

    // Daftar model yang diizinkan (Fallback Model)
    private array $availableModels = [
        'gemini-3.5-flash-lite', 
        'gemini-3.8-flash',      
        'gemini-3.1-flash-lite', 
    ];

    public function __construct()
    {
        $this->kontak = new KontakModel();
        $this->jobs   = new JobModel();
    }

    public function index()
    {
        return view('extractor/cari_email');
    }

    public function kontak()
    {
        try {
            return $this->response->setJSON($this->kontak->tanpaEmail());
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'DB Error: ' . $e->getMessage()]);
        }
    }

    public function hasil()
    {
        try {
            return $this->response->setJSON($this->kontak->hasilEmail());
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'DB Error: ' . $e->getMessage()]);
        }
    }

    public function start()
    {
        try {
            $json = $this->request->getJSON(true) ?: [];
            $ids  = array_values(array_unique(array_map('intval', $json['ids'] ?? [])));
            
            if (! $ids) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Tidak ada kontak dipilih']);
            }

            $valid = [];
            $skipped = 0;
            $rows = $this->kontak->whereIn('id', $ids)->findAll();
            foreach ($rows as $r) {
                $hasEmail = trim((string) ($r['email'] ?? '')) !== '';
                $hasWeb   = EmailExtractor::normalizeWebsite($r['website'] ?? null) !== null;
                if (! $hasEmail && $hasWeb) {
                    $valid[] = (int) $r['id'];
                } else {
                    $skipped++;
                }
            }
            if (! $valid) {
                return $this->response->setStatusCode(422)->setJSON([
                    'error'   => 'Semua kontak terpilih tidak valid (sudah punya email atau website kosong)',
                    'skipped' => $skipped,
                ]);
            }

            // Hentikan job lama yang masih berjalan agar tidak bentrok
            $this->jobs->whereIn('status', ['queued', 'running'])
                ->set(['status' => 'stopped', 'stop_requested' => 1, 'message' => 'Digantikan job baru', 'api_key' => null])
                ->update();

            $requestedModel = trim((string) ($json['model'] ?? ''));
            $modelToUse = $requestedModel ?: $this->availableModels[0];

            // Karena di frontend token dihilangkan (Read-Only), kita paksa limit 1 Juta
            $tokenLimit = 1000000;

            // Insert job baru
            $this->jobs->insert([
                'status'          => 'queued',
                'api_key'         => trim((string) ($json['key'] ?? '')),
                'model'           => $modelToUse,
                'rate'            => max(0.05, (float) ($json['rate'] ?? 5)),
                'hours'           => min(24, max(0.01, (float) ($json['hours'] ?? 24))),
                'token_limit'     => $tokenLimit,
                'token_sisa'      => $tokenLimit, // Kolom baru untuk realtime sisa token
                'auto_fill'       => (int) ($json['auto_fill'] ?? 1) === 1 ? 1 : 0,
                'site_ids'        => json_encode($valid),
                'processed_count' => 0,
                'found_count'     => 0,
                'tokens_used'     => 0,
                'message'         => 'Menunggu worker',
            ]);

            return $this->response->setJSON([
                'queued'  => count($valid),
                'skipped' => $skipped,
                'ai'      => trim((string) ($json['key'] ?? '')) !== '',
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Gagal start: ' . $e->getMessage() . ' di ' . $e->getFile() . ':' . $e->getLine()
            ]);
        }
    }

    public function stop()
    {
        try {
            $this->jobs->whereIn('status', ['queued', 'running'])
                ->set(['stop_requested' => 1])
                ->update();
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function status()
    {
        try {
            $job = $this->jobs->orderBy('id', 'DESC')->first();
            if ($job) {
                $job['total'] = count(json_decode((string) $job['site_ids'], true) ?: []);
                
                // Hitung sisa token secara realtime
                $used = (int)($job['tokens_used'] ?? 0);
                $limit = (int)($job['token_limit'] ?? 1000000);
                $job['token_sisa'] = max(0, $limit - $used);
            }
            return $this->response->setJSON([
                'job'    => $job,
                'worker' => (bool) cache('extractor_heartbeat'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function test()
    {
        $json = $this->request->getJSON(true) ?: [];
        $key  = trim((string) ($json['key'] ?? '')) ?: env('GEMINI_API_KEY', '');
        
        if ($key === '') return $this->response->setStatusCode(422)->setJSON(['error' => 'API key kosong']);

        $lastError = '';
        foreach ($this->availableModels as $modelId) {
            try {
                $ext = new EmailExtractor($key, $modelId);
                $result = $ext->ping();
                return $this->response->setJSON([
                    'success' => true, 
                    'model'   => $modelId, 
                    'reply'   => $result['reply'], 
                    'tokens'  => $result['tokens'],
                ]);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                // Jika error karena kuota habis, langsung hentikan loop
                if (stripos($lastError, 'quota') !== false) break;
                continue;
            }
        }
        return $this->response->setStatusCode(500)->setJSON(['error' => 'Semua model gagal: ' . $lastError]);
    }

    public function models()
    {
        $json = $this->request->getJSON(true) ?: [];
        $key  = trim((string) ($json['key'] ?? '')) ?: env('GEMINI_API_KEY', '');
        if ($key === '') return $this->response->setStatusCode(422)->setJSON(['error' => 'API key kosong']);
        
        try {
            $ext = new EmailExtractor($key);
            return $this->response->setJSON(['models' => $ext->listModels()]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function pakai()
    {
        $json = $this->request->getJSON(true) ?: [];
        $id   = (int) ($json['id'] ?? 0);
        if ($id <= 0) return $this->response->setStatusCode(422)->setJSON(['error' => 'ID tidak valid']);
        
        try {
            $row = $this->kontak->db->table('kontak_email_hasil')->where('id', $id)->get()->getRowArray();
            if (! $row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Hasil tidak ditemukan']);
            
            if (! $this->kontak->isiEmail((int) $row['kontak_id'], (string) $row['email'])) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Email tidak bisa dipakai (kontak sudah punya email / bentrok)']);
            }
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
}