<?php

namespace App\Commands;

use App\Libraries\ContactStore;
use App\Libraries\EmailExtractor;
use App\Libraries\JobTracker;
use App\Libraries\QuotaException;
use App\Models\KontakModel;
use App\Models\JobModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Worker antrean pencarian email.
 *
 * Alur satu job:
 *   - kontak diproses satu per satu, dikelompokkan per BATCH
 *   - ukuran batch, jeda antar kontak (detik), jeda antar batch (menit) dan batas
 *     per jam masing-masing bisa TETAP atau ACAK (lihat kolom extract_jobs.config)
 *   - token dicatat PER BATCH (tabel extract_job_batches) dan PER KONTAK (extract_job_items)
 *   - mode token (config.token_mode):
 *       total     -> batas token berlaku untuk seluruh job, TIDAK reset tiap batch
 *       per_batch -> tiap batch mulai lagi dengan jatah token penuh (RESET).
 *                    Bila jatah batch habis, sisa kontak di batch itu dilewati
 *                    (status "skipped") dan proses lanjut ke batch berikutnya.
 *   - batas token HARIAN (config.daily_limit, 0 = nonaktif): dijumlahkan dari SEMUA proses hari ini.
 *     Bila tercapai, proses MENUNGGU sampai pukul 00.00 lalu lanjut sendiri (selama belum lewat batas durasi).
 *   - berhenti jika: semua selesai | token habis (mode total) | kuota harian habis | durasi habis | dihentikan
 *
 * Jalankan:  php spark emails:work
 */
class EmailWorker extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'emails:work';
    protected $description = 'Worker memproses antrean pencarian email via Gemini AI + Regex.';
    protected $usage       = 'emails:work';

    private const TERMINAL       = ['stopped', 'finished', 'expired', 'quota'];
    private const RATE_RETRY_MAX = 3;

    private string $fatalMessage = '';

    // konteks job yang sedang berjalan
    private JobModel $jobs;
    private KontakModel $kontak;
    private int $jobId        = 0;
    private int $batchNo      = 0;
    private int $tokenLimit   = 0;
    private string $tokenMode = 'total';

    private function fallbackModels(): array
    {
        $env = trim((string) env('GEMINI_FALLBACK_MODELS', ''));
        if ($env !== '') {
            $list = array_values(array_filter(array_map('trim', explode(',', $env))));
            if ($list) return $list;
        }
        return ['gemini-3.5-flash-lite', 'gemini-3.8-flash', 'gemini-3.1-flash-lite'];
    }

    public function run(array $params)
    {
        set_time_limit(0);

        $this->jobs   = new JobModel();
        $this->kontak = new KontakModel();

        CLI::write('Email worker aktif. Menunggu job...', 'green');

        while (true) {
            $this->beat();

            try {
                $job = $this->pickJob($this->jobs);
                if ($job === null) {
                    sleep(3);
                    continue;
                }
                $this->processJob($job);
            } catch (\Throwable $e) {
                CLI::error('[' . date('H:i:s') . '] Worker error: ' . $e->getMessage());
                try {
                    $this->jobs->db->reconnect();
                } catch (\Throwable $e2) {
                }
                sleep(5);
            }
        }
    }

    /** Detak jantung worker (dibaca halaman untuk menampilkan "Worker belum aktif"). */
    private function beat(): void
    {
        cache()->save('extractor_heartbeat', true, 300);
    }

    private function pickJob(JobModel $jobs): ?array
    {
        foreach ($jobs->where('status', 'running')->where('stop_requested', 1)->findAll() as $r) {
            $jobs->update($r['id'], [
                'status'      => 'stopped',
                'message'     => 'Dihentikan oleh pengguna',
                'api_key'     => null,
                'next_run_at' => null,
                'wait_reason' => null,
            ]);
        }

        return $jobs->whereIn('status', ['queued', 'running'])
            ->where('stop_requested', 0)
            ->orderBy('id', 'ASC')
            ->first();
    }

    /* ==================================================================
       PROSES SATU JOB
       ================================================================== */

    private function processJob(array $job): void
    {
        $jobs  = $this->jobs;
        $jobId = (int) $job['id'];
        $this->jobId = $jobId;

        if ($job['status'] === 'queued') {
            $hours      = max(0.1, (float) ($job['hours'] ?: 24));
            $deadlineAt = date('Y-m-d H:i:s', time() + (int) round($hours * 3600));
            $jobs->update($jobId, [
                'status'            => 'running',
                'started_at'        => date('Y-m-d H:i:s'),
                'deadline_at'       => $deadlineAt,
                'message'           => 'Berjalan',
                'hour_window_start' => null,
                'hour_count'        => 0,
                'hour_limit_now'    => 0,
            ]);
            $job['status']      = 'running';
            $job['deadline_at'] = $deadlineAt;
        }

        $ids       = array_values(array_map('intval', json_decode((string) $job['site_ids'], true) ?: []));
        $total     = count($ids);
        $processed = (int) $job['processed_count'];

        $deadline   = $job['deadline_at'] ? strtotime((string) $job['deadline_at']) : (time() + 86400);
        $tokenLimit = max(1, (int) $job['token_limit']);

        $apiKey = JobModel::unpackKey($job['api_key'] ?? '');
        if ($apiKey === '') {
            $apiKey = (string) env('GEMINI_API_KEY', '');
        }
        if ($apiKey === '') {
            CLI::write('PERINGATAN: API key kosong (form maupun .env GEMINI_API_KEY). AI tidak dipanggil, hanya regex.', 'yellow');
        }
        $fallback = $this->fallbackModels();
        $primary  = trim((string) ($job['model'] ?? '')) ?: $fallback[0];
        $models   = array_values(array_unique(array_merge([$primary], $fallback)));

        $autoFill = (int) ($job['auto_fill'] ?? 1) === 1;

        // Pengaturan (tiap parameter: tetap / acak) + rencana ukuran batch
        $cfg     = $this->readConfig($job, $total);
        $plan    = $cfg['plan'];
        $batchOf = [];                // posisi (0-based) => nomor batch
        $endIdx  = [];                // posisi akhir batch (1-based) => nomor batch
        $acc     = 0;
        foreach ($plan as $n => $size) {
            for ($k = 0; $k < $size && count($batchOf) < $total; $k++) {
                $batchOf[$acc + $k] = $n + 1;
            }
            $acc += $size;
            $endIdx[$acc] = $n + 1;
        }
        $totalBatches = count($plan);

        $this->tokenLimit = $tokenLimit;
        $this->tokenMode  = (($cfg['token_mode'] ?? '') === 'per_batch') ? 'per_batch' : 'total';
        // batas total hanya berlaku di mode "total"; di mode "per_batch" dicek per batch
        $jobTokenCap = $this->tokenMode === 'total' ? $tokenLimit : PHP_INT_MAX;
        $dailyLimit  = max(0, (int) ($cfg['daily_limit'] ?? 0));

        // Pastikan baris pelacakan per batch / kontak ada (job lama / tabel baru)
        try {
            if (! JobTracker::hasRows($jobs->db, $jobId)) {
                JobTracker::seed($jobs->db, $jobId, $ids, $plan);
            }
        } catch (\Throwable $e) {
            CLI::error('Tabel pelacakan belum ada: ' . $e->getMessage());
            $jobs->update($jobId, [
                'status'      => 'quota',
                'message'     => 'Tabel extract_job_batches / extract_job_items belum dibuat. Jalankan migrasi.sql',
                'api_key'     => null,
                'next_run_at' => null,
                'wait_reason' => null,
            ]);
            return;
        }

        for ($i = $processed; $i < $total; $i++) {
            if ($this->halted($jobs, $jobId, $deadline, $jobTokenCap)) return;
            if (! $this->hourGate($jobs, $jobId, $cfg['perhour'], $deadline)) return;
            if (! $this->dailyGate($jobs, $jobId, $dailyLimit, $deadline)) return;
            if ($this->halted($jobs, $jobId, $deadline, $jobTokenCap)) return;
            $this->beat();

            $this->batchNo = $batchOf[$i] ?? (int) (end($endIdx) ?: 1);
            $isBatchEnd    = isset($endIdx[$i + 1]) || $i === $total - 1;
            $this->startBatch($this->batchNo);

            if ($this->tokenMode === 'per_batch' && $this->batchTokens($this->batchNo) >= $tokenLimit) {
                // jatah token batch ini habis -> lewati kontak ini
                $outcome = 'skip';
                $this->finish($ids[$i], 'skipped', 'Dilewati: jatah token batch ini habis');
            } else {
                // --- proses 1 kontak (dengan percobaan ulang bila terkena limit per menit) ---
                $attempt = 0;
                while (true) {
                    $attempt++;
                    $final = $attempt >= self::RATE_RETRY_MAX;
                    try {
                        $outcome = $this->processOne($ids[$i], $models, $apiKey, $autoFill, $final, $i + 1, $total);
                    } catch (\Throwable $e) {
                        CLI::error("  Kontak #{$ids[$i]}: " . $e->getMessage());
                        $this->finish($ids[$i], 'error', 'Sistem error: ' . $e->getMessage());
                        $outcome = 'done';
                    }

                    if ($outcome !== 'rate') break;

                    $wait = 60 * $attempt;
                    if (! $this->wait($wait, $jobs, $jobId, 'rate', 'Limit per menit Gemini, menunggu lalu mencoba lagi', $deadline)) return;
                    if ($this->halted($jobs, $jobId, $deadline, $jobTokenCap)) return;
                }
            }

            if ($outcome === 'fatal') {
                $jobs->update($jobId, [
                    'status'      => 'quota',
                    'message'     => mb_substr('Berhenti: ' . $this->fatalMessage, 0, 255),
                    'api_key'     => null,
                    'next_run_at' => null,
                    'wait_reason' => null,
                ]);
                return;
            }

            if ($isBatchEnd) {
                $this->finishBatch($this->batchNo);
            }

            // --- jeda sebelum kontak berikutnya ---
            if ($i < $total - 1) {
                if (isset($endIdx[$i + 1])) {
                    // akhir batch -> jeda antar batch (tetap / acak, menit)
                    $batchNo = $endIdx[$i + 1];
                    $sec     = max(5, (int) round($this->pick($cfg['interval']) * 60));
                    $msg     = "Batch {$batchNo}/{$totalBatches} selesai" . $this->batchSummaryText($batchNo) . ', jeda antar batch';
                    if (! $this->wait($sec, $jobs, $jobId, 'batch', $msg, $deadline)) return;
                } elseif ($outcome !== 'skip') {
                    // masih dalam batch -> jeda antar kontak (tetap / acak, detik)
                    $sec = max(2, (int) round($this->pick($cfg['delay'])));
                    if (! $this->wait($sec, $jobs, $jobId, null, null, $deadline)) return;
                }
            }
        }

        $jobs->update($jobId, [
            'status'      => 'finished',
            'message'     => 'Selesai, semua kontak terpilih sudah diproses',
            'api_key'     => null,
            'next_run_at' => null,
            'wait_reason' => null,
        ]);
    }

    /** true = job harus berhenti sekarang (status sudah diperbarui bila perlu). */
    private function halted(JobModel $jobs, int $jobId, int $deadline, int $tokenCap): bool
    {
        $fresh = $jobs->find($jobId);
        if (! $fresh || in_array($fresh['status'], self::TERMINAL, true)) {
            return true;
        }
        if ((int) $fresh['stop_requested'] === 1) {
            $jobs->update($jobId, ['status' => 'stopped', 'message' => 'Dihentikan oleh pengguna', 'api_key' => null, 'next_run_at' => null, 'wait_reason' => null]);
            return true;
        }
        if (time() >= $deadline) {
            $jobs->update($jobId, ['status' => 'expired', 'message' => 'Batas waktu tercapai', 'api_key' => null, 'next_run_at' => null, 'wait_reason' => null]);
            return true;
        }
        if ((int) $fresh['tokens_used'] >= $tokenCap) {
            $jobs->update($jobId, ['status' => 'quota', 'message' => 'Batas token tercapai', 'api_key' => null, 'next_run_at' => null, 'wait_reason' => null]);
            return true;
        }
        return false;
    }

    /**
     * Batas kontak per jam (tetap / acak). Batas baru dipilih setiap jendela 60 menit dimulai.
     * false = job berhenti (dihentikan / waktu tidak cukup).
     */
    private function hourGate(JobModel $jobs, int $jobId, array $spec, int $deadline): bool
    {
        $mode = $spec['mode'] ?? 'tetap';
        if (($mode === 'acak' && (float) $spec['max'] <= 0) || ($mode !== 'acak' && (float) $spec['val'] <= 0)) {
            return true; // tanpa batas per jam
        }

        while (true) {
            $job = $jobs->find($jobId);
            if (! $job || in_array($job['status'], self::TERMINAL, true) || (int) $job['stop_requested'] === 1) {
                return false;
            }
            $start = $job['hour_window_start'] ? strtotime((string) $job['hour_window_start']) : 0;

            if (! $start || time() - $start >= 3600) {
                $jobs->update($jobId, [
                    'hour_window_start' => date('Y-m-d H:i:s'),
                    'hour_count'        => 0,
                    'hour_limit_now'    => max(0, (int) round($this->pick($spec))),
                ]);
                return true;
            }

            $limit = (int) $job['hour_limit_now'];
            if ($limit <= 0 || (int) $job['hour_count'] < $limit) {
                return true;
            }

            $sec = max(1, ($start + 3600) - time());
            if (! $this->wait($sec, $jobs, $jobId, 'hour', "Batas {$limit} kontak/jam tercapai", $deadline)) {
                return false;
            }
        }
    }

    /**
     * Batas token HARIAN (semua proses). Bila hari ini sudah mencapai batas, tunggu sampai 00.00.
     * false = job berhenti (dihentikan / batas durasi habis sebelum besok).
     */
    private function dailyGate(JobModel $jobs, int $jobId, int $dailyLimit, int $deadline): bool
    {
        if ($dailyLimit <= 0) {
            return true;
        }
        while (true) {
            $used = JobTracker::tokensToday($jobs->db);
            if ($used < $dailyLimit) {
                return true;
            }
            $sec = max(1, strtotime('tomorrow') - time());
            $msg = 'Batas token harian ' . number_format($dailyLimit, 0, ',', '.') . ' tercapai (terpakai ' . number_format($used, 0, ',', '.') . '), menunggu reset 00.00';
            if (! $this->wait($sec, $jobs, $jobId, 'daily', $msg, $deadline)) {
                return false;
            }
        }
    }

    /**
     * Tunggu $seconds detik sambil menjaga heartbeat & memantau tombol Hentikan.
     * $reason != null  -> jeda "besar": dicatat ke job supaya tampil di halaman.
     * Return false bila job harus berhenti.
     */
    private function wait(int $seconds, JobModel $jobs, int $jobId, ?string $reason, ?string $message, int $deadline = 0): bool
    {
        $until = time() + $seconds;

        if ($reason !== null && $deadline > 0 && $until >= $deadline) {
            $jobs->update($jobId, [
                'status'      => 'expired',
                'message'     => 'Batas waktu tercapai sebelum semua kontak selesai',
                'api_key'     => null,
                'next_run_at' => null,
                'wait_reason' => null,
            ]);
            return false;
        }

        if ($reason !== null) {
            $jobs->update($jobId, [
                'next_run_at' => date('Y-m-d H:i:s', $until),
                'wait_reason' => $reason,
                'message'     => mb_substr(($message ?? 'Menunggu') . ' - lanjut ' . date('H:i:s', $until), 0, 255),
            ]);
        }

        $lastBeat = 0;
        while (time() < $until) {
            sleep(1);
            if (time() - $lastBeat >= 5) {
                $this->beat();
                $lastBeat = time();
            }
            $fresh = $jobs->find($jobId);
            if (! $fresh || (int) $fresh['stop_requested'] === 1 || in_array($fresh['status'], self::TERMINAL, true)) {
                return false;
            }
        }

        if ($reason !== null) {
            $jobs->update($jobId, ['next_run_at' => null, 'wait_reason' => null]);
        }
        return true;
    }

    /** Ambil nilai dari parameter {mode, val, min, max}: tetap -> val, acak -> acak di antara min..max. */
    private function pick(array $s): float
    {
        if (($s['mode'] ?? 'tetap') === 'acak') {
            $min = (float) $s['min'];
            $max = max($min, (float) $s['max']);
            return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
        }
        return (float) ($s['val'] ?? 0);
    }

    private function okSpec($s): ?array
    {
        return (is_array($s) && isset($s['mode'], $s['val'], $s['min'], $s['max'])) ? $s : null;
    }

    /** Baca config job; job lama (tanpa config) memakai kolom lama sebagai nilai tetap. */
    private function readConfig(array $job, int $total): array
    {
        $cfg   = json_decode((string) ($job['config'] ?? ''), true);
        $cfg   = is_array($cfg) ? $cfg : [];
        $fixed = static fn ($v) => ['mode' => 'tetap', 'val' => $v, 'min' => $v, 'max' => $v];

        $batchSize = max(1, (int) ($job['batch_size'] ?: 10));
        $rate      = max(0.05, (float) ($job['rate'] ?? 5));

        $cfg['batch']    = $this->okSpec($cfg['batch'] ?? null)   ?? $fixed($batchSize);
        $cfg['perhour']  = $this->okSpec($cfg['perhour'] ?? null) ?? $fixed(max(0, (int) ($job['per_hour_limit'] ?? 0)));
        $cfg['delay']    = $this->okSpec($cfg['delay'] ?? null)   ?? $fixed(max(2, (int) ($job['delay_account'] ?: 5)));
        $cfg['interval'] = $this->okSpec($cfg['interval'] ?? null) ?? [
            'mode' => ($job['mode'] ?? 'manual') === 'acak' ? 'acak' : 'tetap',
            'val'  => $rate,
            'min'  => max(0.05, (float) ($job['rate_min'] ?? $rate)),
            'max'  => max(0.05, (float) ($job['rate_max'] ?? $rate)),
        ];

        $plan = array_values(array_filter(array_map('intval', (array) ($cfg['plan'] ?? [])), static fn ($x) => $x > 0));
        $left = $total - array_sum($plan);
        if ($left > 0) { // job lama / rencana kurang: lengkapi dengan ukuran rata-rata
            $sz = max(1, (int) round($cfg['batch']['mode'] === 'acak' ? ($cfg['batch']['min'] + $cfg['batch']['max']) / 2 : $cfg['batch']['val']));
            while ($left > 0) {
                $plan[] = min($sz, $left);
                $left  -= $sz;
            }
        }
        $cfg['plan'] = $plan;
        return $cfg;
    }

    /* ==================================================================
       PELACAKAN PER BATCH
       ================================================================== */

    private function batchRow(int $no): ?array
    {
        return $this->jobs->db->table('extract_job_batches')
            ->where('job_id', $this->jobId)->where('batch_no', $no)->get()->getRowArray() ?: null;
    }

    private function batchTokens(int $no): int
    {
        $b = $this->batchRow($no);
        return $b ? (int) $b['tokens_used'] : 0;
    }

    /** Tandai batch mulai (sekali). Mode per_batch: jatah token job di-RESET ke batas penuh. */
    private function startBatch(int $no): void
    {
        $b = $this->batchRow($no);
        if (! $b || ! empty($b['started_at'])) {
            return;
        }

        if ($this->tokenMode === 'per_batch') {
            $awal = $this->tokenLimit;
        } else {
            $fresh = $this->jobs->find($this->jobId);
            $awal  = max(0, $this->tokenLimit - (int) ($fresh['tokens_used'] ?? 0));
        }

        $this->jobs->db->table('extract_job_batches')
            ->where('job_id', $this->jobId)->where('batch_no', $no)
            ->update(['status' => 'running', 'started_at' => date('Y-m-d H:i:s'), 'token_awal' => $awal, 'token_sisa' => $awal]);

        if ($this->tokenMode === 'per_batch') {
            $this->jobs->update($this->jobId, ['token_sisa' => $this->tokenLimit]);
        }
    }

    private function finishBatch(int $no): void
    {
        $this->jobs->db->table('extract_job_batches')
            ->where('job_id', $this->jobId)->where('batch_no', $no)
            ->update(['status' => 'done', 'finished_at' => date('Y-m-d H:i:s')]);
    }

    /** Ringkasan token batch untuk pesan status, mis. " (terpakai 12.345 token, sisa 987.655)". */
    private function batchSummaryText(int $no): string
    {
        // $no = nomor batch yang baru selesai
        $b = $this->batchRow($no);
        if (! $b) {
            return '';
        }
        $f = static fn ($n) => number_format((int) $n, 0, ',', '.');
        if ($this->tokenMode === 'per_batch') {
            return " (terpakai {$f($b['tokens_used'])} token; token direset ke {$f($this->tokenLimit)} untuk batch berikutnya)";
        }
        return " (terpakai {$f($b['tokens_used'])} token; sisa total {$f($b['token_sisa'])})";
    }

    /* ==================================================================
       PROSES SATU KONTAK
       Return: 'done'  -> kontak selesai (sudah dihitung)
               'rate'  -> terkena limit per menit, ulangi kontak yang sama
               'fatal' -> job harus berhenti (kuota harian / API key / SSL / model semua hilang)
       ================================================================== */

    private function processOne(int $kontakId, array $models, string $apiKey, bool $autoFill, bool $final, int $nomor, int $total): string
    {
        $kontak = $this->kontak;
        $jobs   = $this->jobs;
        $jobId  = $this->jobId;

        $row = $kontak->find($kontakId);
        if (! $row) {
            $this->finish($kontakId, 'error', 'Kontak tidak ditemukan');
            return 'done';
        }

        $hasEmail = trim((string) ($row['email'] ?? '')) !== '';
        $website  = EmailExtractor::normalizeWebsite($row['website'] ?? null);

        if ($hasEmail) {
            $this->finish($kontakId, 'done', 'Sudah punya email');
            return 'done';
        }
        if ($website === null) {
            $this->finish($kontakId, 'error', 'Website tidak valid');
            return 'done';
        }

        $nama = trim((string) ($row['nama_perusahaan'] ?: $row['nama'])) ?: "#{$kontakId}";
        CLI::write("[{$nomor}/{$total}] (batch {$this->batchNo}) {$nama} - {$website}");

        $this->setEkstraksi($kontak, $kontakId, 'processing', 'Memproses...', 0);
        $this->itemSet($kontakId, 'processing', 'Memproses...');
        $jobs->update($jobId, [
            'current_url' => mb_substr($website, 0, 255),
            'message'     => mb_substr("Memproses {$nomor}/{$total}: {$nama}", 0, 255),
        ]);
        $jobs->builder()->where('id', $jobId)->set('hour_count', 'hour_count + 1', false)->update();

        // 1) Unduh website SEKALI (tanpa AI)
        $this->beat();
        try {
            $bundle = (new EmailExtractor($apiKey, $models[0]))->collect($website);
        } catch (\Throwable $e) {
            $this->finish($kontakId, 'error', $e->getMessage());
            return 'done';
        }

        // 2) Analisis dengan Gemini, ganti model cadangan bila perlu
        $result    = null;
        $usedModel = $models[0];
        $lastError = '';
        $fatal     = null;
        $daily     = false;
        $rate      = false;
        $missing   = 0;
        $unavail   = 0;

        foreach ($models as $m) {
            $this->beat();
            try {
                $result    = (new EmailExtractor($apiKey, $m))->analyze($bundle);
                $usedModel = $m;
                break;
            } catch (QuotaException $e) {
                $lastError = $e->getMessage();
                switch ($e->getReason()) {
                    case QuotaException::MODEL_MISSING:
                        $missing++;
                        continue 2;
                    case QuotaException::DAILY:
                        $daily = true;
                        continue 2;
                    case QuotaException::RATE:
                        $rate = true;
                        continue 2;
                    case QuotaException::UNAVAILABLE:
                        $unavail++;
                        continue 2;
                    default: // AUTH, SSL, OTHER
                        $fatal = $lastError;
                        break 2;
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                break;
            }
        }

        if ($result === null) {
            if ($fatal === null && $missing === count($models)) {
                $fatal = 'Semua model Gemini tidak tersedia untuk API key ini. Ganti model di pengaturan.';
            }
            if ($fatal === null && $daily && ! $rate && $unavail === 0) {
                $fatal = 'Kuota harian Gemini habis untuk semua model.';
            }
            if ($fatal !== null) {
                $this->fatalMessage = $fatal;
                $this->setEkstraksi($kontak, $kontakId, 'pending', 'Dihentikan: ' . $fatal, 0);
                $this->itemSet($kontakId, 'pending', 'Dihentikan: ' . $fatal);
                return 'fatal';
            }
            if ($rate && ! $final) {
                // limit per menit: tunggu lalu ulangi kontak yang sama
                $this->setEkstraksi($kontak, $kontakId, 'pending', 'Menunggu limit API...', 0);
                $this->itemSet($kontakId, 'pending', 'Menunggu limit API...');
                return 'rate';
            }
            if ($rate || $daily || $unavail > 0) {
                // Gemini sibuk / limit belum pulih: email tetap diambil lewat regex supaya kontak tidak gagal
                $why       = $rate ? 'Limit API Gemini belum pulih' : ($unavail > 0 ? 'Gemini sedang gangguan' : 'Kuota Gemini habis');
                $result    = (new EmailExtractor('', $models[0]))->analyze($bundle, false, $why . ', memakai regex');
                $usedModel = null;   // AI tidak dipakai -> jangan menimpa nama model job
            } else {
                $this->finish($kontakId, 'error', $lastError ?: 'Gagal mengambil data');
                return 'done';
            }
        }

        // 3) Simpan hasil
        $emails = $result['emails'] ?? [];
        $tokens = (int) ($result['tokens'] ?? 0);
        $note   = (string) ($result['note'] ?? '');

        // SEMUA email yang ditemukan disimpan satu baris per email (one-to-many), bukan hanya satu
        $now   = date('Y-m-d H:i:s');
        $store = ContactStore::saveEmails($kontak->db, $kontakId, $emails, $now, (string) $website);
        $list  = $store['list'];
        $count = $store['count'];

        // No HP & website juga disimpan semuanya
        $phoneN = ContactStore::savePhones($kontak->db, $kontakId, $result['phones'] ?? [], $now, (string) $website);
        ContactStore::saveWebsites($kontak->db, $kontakId, (array) ($result['websites'] ?? []), $website, $now);

        if ($count > 0) {
            $status   = 'done';
            $noteText = "Ditemukan {$count} email" . ($note ? " ({$note})" : '');
            if ($autoFill) {
                $filled = null;
                foreach ($list as $it) {
                    if ($kontak->isiEmail($kontakId, $it['email'])) {
                        $filled = $it['email'];
                        break;
                    }
                }
                $noteText = $filled
                    ? "Email diisi: {$filled} - " . $noteText
                    : $noteText . ' [auto-fill gagal: email sudah dipakai kontak lain]';
            }
        } else {
            $status   = 'empty';
            $noteText = 'Tidak ada email ditemukan' . ($note ? " ({$note})" : '');
        }

        if ($phoneN > 0) {
            $noteText .= " | {$phoneN} no HP";
        }

        $this->finish($kontakId, $status, $noteText, $count, $tokens, $usedModel, $list);
        return 'done';
    }

    private function setEkstraksi(KontakModel $kontak, int $kontakId, string $status, string $note, int $foundCount): void
    {
        $db       = $kontak->db;
        $now      = date('Y-m-d H:i:s');
        $existing = $db->table('kontak_ekstraksi')->where('kontak_id', $kontakId)->get()->getRowArray();

        $data = [
            'status'       => $status,
            'note'         => mb_substr($note, 0, 255),
            'found_count'  => $foundCount,
            'processed_at' => $now,
            'updated_at'   => $now,
        ];

        if ($existing) {
            $db->table('kontak_ekstraksi')->where('id', $existing['id'])->update($data);
        } else {
            $data['kontak_id']  = $kontakId;
            $data['created_at'] = $now;
            $db->table('kontak_ekstraksi')->insert($data);
        }
    }

    /** Ubah status / catatan baris kontak di job ini (tanpa menghitung sebagai selesai). */
    private function itemSet(int $kontakId, string $status, string $note): void
    {
        try {
            $this->jobs->db->table('extract_job_items')
                ->where('job_id', $this->jobId)->where('kontak_id', $kontakId)
                ->update(['status' => $status, 'note' => mb_substr($note, 0, 255)]);
        } catch (\Throwable $e) {
        }
    }

    /**
     * Kontak SELESAI diproses: catat status, hasil & token ke
     *   - kontak_ekstraksi    (riwayat per kontak)
     *   - extract_job_items   (detail per kontak di job ini)
     *   - extract_job_batches (total per batch + token awal/terpakai/sisa)
     *   - extract_jobs        (total job)
     */
    private function finish(int $kontakId, string $status, string $note, int $found = 0, int $tokens = 0, ?string $model = null, array $emails = []): void
    {
        $db  = $this->jobs->db;
        $now = date('Y-m-d H:i:s');

        // status "skipped" hanya ada di tabel job; riwayat kontak tetap "pending"
        $this->setEkstraksi($this->kontak, $kontakId, $status === 'skipped' ? 'pending' : $status, $note, $found);

        // last_check hanya dicatat bila pengecekan benar-benar selesai (bukan error / dihentikan)
        if (in_array($status, ['done', 'empty'], true)) {
            ContactStore::touch($db, $kontakId, $now);
        }

        $db->table('extract_job_items')
            ->where('job_id', $this->jobId)->where('kontak_id', $kontakId)
            ->update([
                'status'       => $status,
                'found_count'  => $found,
                'tokens_used'  => $tokens,
                'model'        => $model,
                'note'         => mb_substr($note, 0, 255),
                'emails'       => $emails ? json_encode($emails, JSON_UNESCAPED_UNICODE) : null,
                'processed_at' => $now,
            ]);

        $db->table('extract_job_batches')
            ->where('job_id', $this->jobId)->where('batch_no', $this->batchNo)
            ->set('processed', 'processed + 1', false)
            ->set('found_count', 'found_count + ' . (int) $found, false)
            ->set('tokens_used', 'tokens_used + ' . (int) $tokens, false)
            ->update();

        $batchTokens = $this->batchTokens($this->batchNo);
        $sisa        = $this->bumpProcessed($found, $tokens, $model, $batchTokens);

        $db->table('extract_job_batches')
            ->where('job_id', $this->jobId)->where('batch_no', $this->batchNo)
            ->update(['token_sisa' => $sisa]);
    }

    /**
     * Tambah counter job. $addFound / $addTokens adalah TAMBAHAN, bukan total.
     * Return sisa token: mode total = batas - total terpakai, mode per_batch = batas - terpakai batch ini.
     */
    private function bumpProcessed(int $addFound, int $addTokens, ?string $model, int $batchTokens): int
    {
        $fresh = $this->jobs->find($this->jobId);
        if (! $fresh) return 0;

        $done  = (int) $fresh['processed_count'] + 1;
        $total = count(json_decode((string) $fresh['site_ids'], true) ?: []);
        $used  = (int) $fresh['tokens_used'] + $addTokens;
        $sisa  = max(0, (int) $fresh['token_limit'] - ($this->tokenMode === 'per_batch' ? $batchTokens : $used));

        $data = [
            'processed_count' => $done,
            'found_count'     => (int) $fresh['found_count'] + $addFound,
            'tokens_used'     => $used,
            'token_sisa'      => $sisa,
            'message'         => "Diproses {$done}/{$total}",
        ];
        if ($model !== null && $model !== '') $data['model'] = $model;

        $this->jobs->update($this->jobId, $data);
        return $sisa;
    }
}