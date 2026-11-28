<?php

namespace App\Controllers;

use App\Libraries\ContactStore;
use App\Libraries\EmailExtractor;
use App\Libraries\JobTracker;
use App\Libraries\QuotaException;
use App\Models\JobModel;
use App\Models\KontakModel;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class Extractor extends Controller
{
    private KontakModel $kontak;
    private JobModel $jobs;

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

    /* ==================================================================
       HALAMAN
       ================================================================== */

    /** Halaman utama: data & hasil pencarian email + riwayat proses. */
    public function index()
    {
        return view('extractor/index');
    }

    /** Halaman setup: koneksi AI, pilih kontak, pengaturan, jalankan. */
    public function cari()
    {
        return view('extractor/cari_email');
    }

    /** Halaman DETAIL sebuah proses: progres, token per batch, hasil per kontak. */
    public function detail(int $id)
    {
        if (! $this->jobs->find($id)) {
            throw PageNotFoundException::forPageNotFound('Proses pencarian tidak ditemukan');
        }
        // ?kontak=ID -> halaman detail langsung menyorot kontak tersebut
        return view('extractor/detail', ['jobId' => $id, 'kontakId' => (int) $this->request->getGet('kontak')]);
    }

    /* ==================================================================
       API DATA
       ================================================================== */

    public function ringkasan()
    {
        try {
            $job = $this->latestJob();
            return $this->response->setJSON([
                'stats'          => $this->kontak->ringkasanEmail(),
                'job'            => $job,
                'worker'         => (bool) cache('extractor_heartbeat'),
                // total token SEMUA proses hari ini (reset otomatis tiap 00.00)
                'token_hari_ini' => JobTracker::tokensToday($this->jobs->db),
                'daily_limit'    => (int) ($job['daily_limit'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function kontak()
    {
        try {
            $check = (string) $this->request->getGet('check');   // all | fresh (<30 hari) | stale
            return $this->response->setJSON($this->kontak->tanpaEmail(in_array($check, ['fresh', 'stale'], true) ? $check : 'all'));
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

    public function riwayat()
    {
        try {
            return $this->response->setJSON($this->kontak->riwayatEkstraksi());
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'DB Error: ' . $e->getMessage()]);
        }
    }

    /** Daftar proses (job) terbaru untuk halaman index. */
    public function daftarJob()
    {
        try {
            $rows = $this->jobs
                ->select('id, status, model, token_limit, tokens_used, processed_count, found_count, message, started_at, created_at, updated_at, site_ids, config')
                ->orderBy('id', 'DESC')->limit(50)->findAll();

            foreach ($rows as &$r) {
                $ids  = json_decode((string) $r['site_ids'], true) ?: [];
                $cfg  = json_decode((string) ($r['config'] ?? ''), true);
                $r['total']      = count($ids);
                $r['batches']    = is_array($cfg) && ! empty($cfg['plan']) ? count($cfg['plan']) : 0;
                $r['token_mode'] = (is_array($cfg) && ($cfg['token_mode'] ?? '') === 'per_batch') ? 'per_batch' : 'total';
                $r['daily_limit'] = is_array($cfg) ? (int) ($cfg['daily_limit'] ?? 0) : 0;
                unset($r['site_ids'], $r['config']);
            }
            unset($r);

            return $this->response->setJSON($rows);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Data halaman detail.
     *   /detail/{id}          -> job + batch (ringan, dipakai polling)
     *   /detail/{id}?items=1  -> ditambah daftar kontak + email hasilnya
     */
    public function detailData(int $id)
    {
        try {
            $job = $this->jobs->find($id);
            if (! $job) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Proses tidak ditemukan']);
            }
            $active = in_array($job['status'], ['queued', 'running'], true);

            $out = [
                'job'     => $this->shapeJob($job),
                'worker'  => (bool) cache('extractor_heartbeat'),
                'batches' => $this->detailBatches($job, $active),
            ];
            if ($this->request->getGet('items') === '1') {
                $out['items'] = $this->detailItems($job);
            }
            return $this->response->setJSON($out);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    private function detailBatches(array $job, bool $active): array
    {
        $rows = $this->jobs->db->table('extract_job_batches')
            ->where('job_id', (int) $job['id'])->orderBy('batch_no', 'ASC')->get()->getResultArray();

        if ($rows) {
            foreach ($rows as &$b) {
                foreach (['batch_no', 'size', 'processed', 'found_count', 'tokens_used', 'token_awal', 'token_sisa'] as $f) {
                    $b[$f] = (int) $b[$f];
                }
                if ($b['status'] === 'running' && ! $active) {
                    $b['status'] = 'stopped'; // job sudah berhenti sebelum batch selesai
                }
            }
            unset($b);
            return $rows;
        }

        // Job lama (dibuat sebelum ada tabel batch): bentuk dari rencana, tanpa data token
        $out  = [];
        $done = (int) $job['processed_count'];
        $acc  = 0;
        foreach ($this->planOf($job) as $n => $size) {
            $proc  = max(0, min($size, $done - $acc));
            $acc  += $size;
            $out[] = [
                'batch_no' => $n + 1, 'size' => $size, 'processed' => $proc, 'found_count' => 0,
                'tokens_used' => 0, 'token_awal' => 0, 'token_sisa' => 0,
                'status' => $proc >= $size ? 'done' : ($proc > 0 && $active ? 'running' : 'pending'),
                'started_at' => null, 'finished_at' => null,
            ];
        }
        return $out;
    }

    private function detailItems(array $job): array
    {
        $jobId = (int) $job['id'];
        $rows  = $this->jobs->db->table('extract_job_items i')
            ->select('i.kontak_id AS id, i.pos, i.batch_no AS batch, i.status, i.found_count, i.tokens_used, i.model, i.note, i.emails, i.processed_at, k.nama, k.nama_perusahaan, k.kota_kabupaten, k.website, k.email AS email_kontak')
            ->join('tb_perusahaan k', 'k.id_perusahaan = i.kontak_id', 'left')
            ->where('i.job_id', $jobId)->orderBy('i.pos', 'ASC')
            ->get()->getResultArray();

        if ($rows) {
            foreach ($rows as &$r) {
                $list        = json_decode((string) ($r['emails'] ?? ''), true);
                $r['emails'] = is_array($list) ? $list : [];
                $r['id']     = (int) $r['id'];
                $r['batch']  = (int) $r['batch'];
                $r['found_count']  = (int) $r['found_count'];
                $r['tokens_used']  = (int) $r['tokens_used'];
            }
            unset($r);
            return $rows;
        }

        // Job lama: pakai data kontak + status ekstraksi terakhir
        $ids = array_values(array_map('intval', json_decode((string) $job['site_ids'], true) ?: []));
        $map = $this->batchMap($this->planOf($job), count($ids), max(1, (int) ($job['batch_size'] ?: 10)));
        $out = [];
        foreach ($this->kontak->itemsByIds($ids) as $it) {
            $out[] = [
                'id' => (int) $it['id'], 'pos' => (int) $it['pos'], 'batch' => $map[(int) $it['pos']] ?? 1,
                'status' => $it['status'], 'found_count' => (int) ($it['found_count'] ?? 0), 'tokens_used' => 0,
                'model' => null, 'note' => $it['note'] ?? '', 'emails' => [], 'processed_at' => null,
                'nama' => $it['nama'], 'nama_perusahaan' => $it['nama_perusahaan'],
                'kota_kabupaten' => $it['kota_kabupaten'], 'website' => $it['website'], 'email_kontak' => $it['email'],
            ];
        }
        return $out;
    }

    /** Kontak milik job terakhir (dipertahankan untuk kompatibilitas). */
    public function items()
    {
        try {
            $job = $this->jobs->orderBy('id', 'DESC')->first();
            if (! $job) {
                return $this->response->setJSON([]);
            }
            return $this->response->setJSON($this->detailItems($job));
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function status()
    {
        try {
            return $this->response->setJSON([
                'job'            => $this->latestJob(),
                'worker'         => (bool) cache('extractor_heartbeat'),
                'token_hari_ini' => JobTracker::tokensToday($this->jobs->db),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /* ==================================================================
       HELPER PENGATURAN (TETAP / ACAK)
       Setiap parameter berbentuk {mode: tetap|acak, val, min, max}
       ================================================================== */

    private function normSpec($raw, float $lo, float $hi, float $def): array
    {
        $raw  = is_array($raw) ? $raw : [];
        $c    = static fn ($v, $d) => is_numeric($v) ? max($lo, min($hi, (float) $v)) : $d;
        $mode = (($raw['mode'] ?? 'tetap') === 'acak') ? 'acak' : 'tetap';
        $val  = $c($raw['val'] ?? null, $def);
        $min  = $c($raw['min'] ?? null, $val);
        $max  = $c($raw['max'] ?? null, $val);
        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }
        return ['mode' => $mode, 'val' => $val, 'min' => $min, 'max' => $max];
    }

    private function resolve(array $s): float
    {
        if ($s['mode'] === 'acak') {
            return $s['min'] + (mt_rand() / mt_getrandmax()) * ($s['max'] - $s['min']);
        }
        return $s['val'];
    }

    private function avgSpec(array $s): float
    {
        return $s['mode'] === 'acak' ? ($s['min'] + $s['max']) / 2 : $s['val'];
    }

    /** Rencana ukuran batch tersimpan di config.plan, mis. [10, 12, 9, ...] */
    private function planOf(array $job): array
    {
        $cfg = json_decode((string) ($job['config'] ?? ''), true);
        if (! is_array($cfg) || empty($cfg['plan'])) {
            return [];
        }
        return array_values(array_filter(array_map('intval', $cfg['plan']), static fn ($x) => $x > 0));
    }

    /** posisi kontak (0-based) => nomor batch */
    private function batchMap(array $plan, int $count, int $fallbackSize): array
    {
        $map = [];
        $pos = 0;
        foreach ($plan as $n => $size) {
            for ($k = 0; $k < $size && $pos < $count; $k++) {
                $map[$pos++] = $n + 1;
            }
        }
        while ($pos < $count) {
            $map[$pos] = intdiv($pos, $fallbackSize) + 1;
            $pos++;
        }
        return $map;
    }

    private function latestJob(): ?array
    {
        $job = $this->jobs->orderBy('id', 'DESC')->first();
        return $job ? $this->shapeJob($job) : null;
    }

    /** Bentuk job untuk browser (tanpa API key) + angka turunan. */
    private function shapeJob(array $job): array
    {
        unset($job['api_key']); // jangan pernah kirim API key ke browser

        $cfg  = json_decode((string) ($job['config'] ?? ''), true);
        $cfg  = is_array($cfg) ? $cfg : [];
        $mode = (($cfg['token_mode'] ?? '') === 'per_batch') ? 'per_batch' : 'total';

        $plan  = $this->planOf($job);
        $ids   = json_decode((string) $job['site_ids'], true) ?: [];
        $total = count($ids);
        $size  = max(1, (int) ($job['batch_size'] ?: 10));
        $done  = (int) $job['processed_count'];
        $used  = (int) ($job['tokens_used'] ?? 0);
        $limit = (int) ($job['token_limit'] ?? 1000000);
        $idx   = $total ? min($done, $total - 1) : 0;

        if ($plan) {
            $totalBatches = count($plan);
            $cur          = $totalBatches;
            $acc          = 0;
            foreach ($plan as $n => $sz) {
                $acc += $sz;
                if ($idx < $acc) { $cur = $n + 1; break; }
            }
        } else {
            $totalBatches = (int) ceil($total / $size);
            $cur          = intdiv($idx, $size) + 1;
        }
        $cur = $total ? $cur : 0;

        // Sisa token: akumulatif = limit - total terpakai; per batch = limit - terpakai di batch berjalan
        $batchUsed = 0;
        if ($mode === 'per_batch' && $cur > 0) {
            try {
                $b = $this->jobs->db->table('extract_job_batches')->select('tokens_used')
                    ->where('job_id', (int) $job['id'])->where('batch_no', $cur)->get()->getRowArray();
                $batchUsed = (int) ($b['tokens_used'] ?? 0);
            } catch (\Throwable $e) {
                $batchUsed = 0;
            }
        }

        unset($job['config'], $job['site_ids']);

        $job['total']            = $total;
        $job['token_mode']       = $mode;
        $job['token_batch_used'] = $batchUsed;
        $job['token_sisa']       = max(0, $limit - ($mode === 'per_batch' ? $batchUsed : $used));
        $job['total_batches']    = $totalBatches;
        $job['current_batch']    = $cur;
        $job['waiting_next']     = ! empty($job['next_run_at']);
        $job['next_batch_at']    = $job['next_run_at'];
        $job['hour_limit']       = (int) ($job['hour_limit_now'] ?? 0);

        // token harian (semua proses, reset 00.00)
        $dailyLimit              = (int) ($cfg['daily_limit'] ?? 0);
        $dailyUsed               = JobTracker::tokensToday($this->jobs->db);
        $job['daily_limit']      = $dailyLimit;
        $job['token_hari_ini']   = $dailyUsed;
        $job['daily_sisa']       = $dailyLimit > 0 ? max(0, $dailyLimit - $dailyUsed) : null;

        if (! empty($job['started_at'])) {
            $end = in_array($job['status'], ['queued', 'running'], true)
                ? time()
                : strtotime((string) ($job['updated_at'] ?? 'now'));
            $job['elapsed_seconds'] = max(0, $end - strtotime((string) $job['started_at']));
        }

        // jendela jam yang sudah lewat -> hitungan jam ini dianggap 0
        if (! empty($job['hour_window_start']) && time() - strtotime((string) $job['hour_window_start']) >= 3600) {
            $job['hour_count'] = 0;
        }
        return $job;
    }

    /* ==================================================================
       START / STOP
       ================================================================== */

    public function start()
    {
        try {
            $json = $this->request->getJSON(true) ?: [];

            $ids = [];
            foreach ((array) ($json['ids'] ?? []) as $i) {
                $i = (int) $i;
                if ($i > 0 && ! in_array($i, $ids, true)) $ids[] = $i;
            }
            if (! $ids) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Tidak ada kontak dipilih']);
            }

            // Hanya kontak tanpa email & berwebsite valid, urutan mengikuti pilihan
            $byId = [];
            foreach ($this->kontak->whereIn('id_perusahaan', $ids)->findAll() as $r) {
                $byId[(int) $r['id_perusahaan']] = $r;
            }
            $valid   = [];
            $skipped = 0;
            $recent  = 0;
            $force   = ! empty($json['force']);   // true = tetap cari ulang walau dicek < 30 hari lalu
            foreach ($ids as $id) {
                $r = $byId[$id] ?? null;
                if (! $r) { $skipped++; continue; }
                $hasEmail = trim((string) ($r['email'] ?? '')) !== '';
                $hasWeb   = EmailExtractor::normalizeWebsite($r['website'] ?? null) !== null;
                if ($hasEmail || ! $hasWeb) { $skipped++; continue; }
                if (! $force && ContactStore::isFresh($r['last_check'] ?? null)) { $recent++; $skipped++; continue; }
                $valid[] = $id;
            }
            if (! $valid) {
                return $this->response->setStatusCode(422)->setJSON([
                    'error'   => 'Semua kontak terpilih dilewati (sudah punya email, website kosong, atau baru dicek < ' . ContactStore::STALE_DAYS . ' hari lalu)',
                    'recent'  => $recent,
                    'skipped' => $skipped,
                ]);
            }

            // --- pengaturan: tiap parameter bisa TETAP atau ACAK ---
            $S = (array) ($json['settings'] ?? []);
            $sHours    = $this->normSpec($S['hours']    ?? null, 0.1, 168, 24);
            $sTotal    = $this->normSpec($S['total']    ?? null, 1, 1000000, 100);
            $sBatch    = $this->normSpec($S['batch']    ?? null, 1, 1000, 10);
            $sPerHour  = $this->normSpec($S['perhour']  ?? null, 0, 100000, 0);
            $sDelay    = $this->normSpec($S['delay']    ?? null, 2, 3600, 5);
            $sInterval = $this->normSpec($S['interval'] ?? null, 0.05, 1440, 5);

            // durasi & total ditentukan SEKALI saat job dimulai
            $hours      = round($this->resolve($sHours), 2);
            $totalLimit = max(1, (int) round($this->resolve($sTotal)));
            $trimmed    = 0;
            if (count($valid) > $totalLimit) {
                $trimmed = count($valid) - $totalLimit;
                $valid   = array_slice($valid, 0, $totalLimit);
            }

            // rencana ukuran batch (acak -> tiap batch ukurannya berbeda)
            $plan = [];
            $left = count($valid);
            while ($left > 0) {
                $sz     = min($left, max(1, (int) round($this->resolve($sBatch))));
                $plan[] = $sz;
                $left  -= $sz;
            }

            $tokenLimit = (int) max(1000, min(2000000000, is_numeric($json['token_limit'] ?? null) ? (float) $json['token_limit'] : 1000000));
            // total     = token terus berkurang sepanjang proses (TIDAK reset)
            // per_batch = tiap batch mulai lagi dengan jatah token penuh (RESET)
            $tokenMode  = (($json['token_mode'] ?? 'total') === 'per_batch') ? 'per_batch' : 'total';
            // batas token HARIAN untuk semua proses (reset 00.00). 0 = tanpa batas harian.
            $dailyLimit = (int) max(0, min(2000000000, is_numeric($json['daily_limit'] ?? null) ? (float) $json['daily_limit'] : 0));

            $requestedModel = trim((string) ($json['model'] ?? ''));
            $modelToUse     = $requestedModel ?: $this->availableModels[0];
            $key            = trim((string) ($json['key'] ?? ''));

            // hentikan job lama
            $this->jobs->builder()->whereIn('status', ['queued', 'running'])->update([
                'status'         => 'stopped',
                'stop_requested' => 1,
                'message'        => 'Digantikan job baru',
                'api_key'        => null,
                'next_run_at'    => null,
                'wait_reason'    => null,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            $this->kontak->resetEkstraksi($valid);

            $db = $this->jobs->db;
            $db->transBegin();
            try {
                $jobId = (int) $this->jobs->insert([
                    'status'          => 'queued',
                    'api_key'         => JobModel::packKey($key),
                    'model'           => $modelToUse,
                    // kolom lama diisi nilai rata-rata supaya tetap kompatibel
                    'rate'            => round($this->avgSpec($sInterval), 2),
                    'rate_min'        => $sInterval['min'],
                    'rate_max'        => $sInterval['max'],
                    'mode'            => $sInterval['mode'] === 'acak' ? 'acak' : 'manual',
                    'hours'           => $hours,
                    'batch_size'      => max(1, (int) round($this->avgSpec($sBatch))),
                    'total_limit'     => $totalLimit,
                    'per_hour_limit'  => (int) round($this->avgSpec($sPerHour)),
                    'delay_account'   => max(2, (int) round($this->avgSpec($sDelay))),
                    'config'          => json_encode([
                        'batch'      => $sBatch,
                        'perhour'    => $sPerHour,
                        'delay'      => $sDelay,
                        'interval'   => $sInterval,
                        'plan'       => $plan,
                        'token_mode' => $tokenMode,
                        'daily_limit' => $dailyLimit,
                    ]),
                    'hour_limit_now'  => 0,
                    'token_limit'     => $tokenLimit,
                    'token_sisa'      => $tokenLimit,
                    'auto_fill'       => (int) ($json['auto_fill'] ?? 1) === 1 ? 1 : 0,
                    'site_ids'        => json_encode($valid),
                    'stop_requested'  => 0,
                    'processed_count' => 0,
                    'found_count'     => 0,
                    'tokens_used'     => 0,
                    'hour_count'      => 0,
                    'message'         => 'Menunggu worker',
                ]);

                // baris pelacakan per batch & per kontak (untuk halaman detail)
                JobTracker::seed($db, $jobId, $valid, $plan);
                $db->transCommit();
            } catch (\Throwable $e) {
                $db->transRollback();
                throw $e;
            }

            return $this->response->setJSON([
                'job_id'      => $jobId,
                'queued'      => count($valid),
                'skipped'     => $skipped,
                'trimmed'     => $trimmed,
                'batches'     => count($plan),
                'hours'       => $hours,
                'total_limit' => $totalLimit,
                'token_mode'  => $tokenMode,
                'daily_limit' => $dailyLimit,
                'daily_used'  => JobTracker::tokensToday($this->jobs->db),
                'ai'          => $key !== '' || env('GEMINI_API_KEY', '') !== '',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Gagal start: ' . $e->getMessage()]);
        }
    }

    public function stop()
    {
        try {
            $now = date('Y-m-d H:i:s');
            $this->jobs->builder()->where('status', 'queued')->update([
                'status' => 'stopped', 'stop_requested' => 1, 'message' => 'Dihentikan oleh pengguna', 'api_key' => null, 'updated_at' => $now,
            ]);
            $this->jobs->builder()->where('status', 'running')->update([
                'stop_requested' => 1, 'message' => 'Menghentikan...', 'updated_at' => $now,
            ]);
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /* ==================================================================
       KONEKSI AI
       ================================================================== */

    public function test()
    {
        $json = $this->request->getJSON(true) ?: [];
        $key  = trim((string) ($json['key'] ?? '')) ?: env('GEMINI_API_KEY', '');

        if ($key === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'API key kosong']);
        }

        $requested = trim((string) ($json['model'] ?? ''));
        $models    = array_values(array_unique(array_filter(array_merge([$requested], $this->availableModels))));

        $lastError = '';
        foreach ($models as $modelId) {
            try {
                $ext    = new EmailExtractor($key, $modelId);
                $result = $ext->ping();
                return $this->response->setJSON([
                    'success' => true,
                    'model'   => $modelId,
                    'reply'   => $result['reply'],
                    'tokens'  => $result['tokens'],
                ]);
            } catch (QuotaException $e) {
                $lastError = $e->getMessage();
                if (in_array($e->getReason(), [QuotaException::MODEL_MISSING, QuotaException::DAILY, QuotaException::RATE, QuotaException::UNAVAILABLE], true)) {
                    continue;
                }
                break;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                continue;
            }
        }
        return $this->response->setStatusCode(500)->setJSON(['error' => 'Koneksi gagal: ' . $lastError]);
    }

    public function models()
    {
        $json = $this->request->getJSON(true) ?: [];
        $key  = trim((string) ($json['key'] ?? '')) ?: env('GEMINI_API_KEY', '');
        if ($key === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'API key kosong']);
        }

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
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'ID tidak valid']);
        }

        try {
            $row = $this->kontak->db->table('tb_email_perusahaan')->where('id_email', $id)->get()->getRowArray();
            if (! $row) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Hasil tidak ditemukan']);
            }
            if (! $this->kontak->isiEmail((int) $row['id_perusahaan'], (string) $row['email'])) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Email tidak bisa dipakai (kontak sudah punya email / email sudah dipakai kontak lain)']);
            }
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /** Semua email, no HP, dan website satu kontak (one-to-many). */
    public function kontakDetail($id = 0)
    {
        try {
            $row = $this->kontak->detailKontak((int) $id);
            if (! $row) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Kontak tidak ditemukan']);
            }
            return $this->response->setJSON($row);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
}