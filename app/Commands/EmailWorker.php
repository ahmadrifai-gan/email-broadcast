<?php

namespace App\Commands;

use App\Libraries\EmailExtractor;
use App\Libraries\QuotaException;
use App\Models\JobModel;
use App\Models\KontakModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EmailWorker extends BaseCommand
{
    protected $group       = 'Extractor';
    protected $name        = 'emails:work';
    protected $description = 'Worker pencari email: mengambil job dari database dan memproses website kontak.';

    public function run(array $params)
    {
        set_time_limit(0);
        $db   = db_connect();
        $jobs = new JobModel();

        // ---------- Pemulihan saat worker baru dijalankan ----------
        // Jangan hapus api_key! Job yang terhenti mendadak bisa dilanjutkan / diaudit.
        try {
            $db->table('extract_jobs')
                ->where('status', 'running')
                ->update([
                    'status'  => 'stopped',
                    'message' => 'Worker terhenti mendadak (dipulihkan saat start)',
                ]);
        } catch (\Throwable $e) {
            CLI::error('Gagal memulihkan job running: ' . $e->getMessage());
        }

        // Kembalikan kontak yang statusnya "processing" tapi tidak ada job running ke "pending"
        try {
            $running = $jobs->where('status', 'running')->findAll();
            if (! $running) {
                $db->table('kontak_ekstraksi')
                    ->where('status', 'processing')
                    ->update(['status' => 'pending', 'note' => 'Direset saat worker start']);
            }
        } catch (\Throwable $e) {
            CLI::error('Gagal reset kontak processing: ' . $e->getMessage());
        }

        CLI::write('Worker aktif. Menunggu job... (Ctrl+C untuk keluar)', 'green');

        while (true) {
            $this->beat();

            try {
                $db->reconnect();
                $job = $jobs->where('status', 'queued')->orderBy('id', 'ASC')->first();
                if (! $job) {
                    sleep(2);
                    continue;
                }
                $this->runJob($job, $db, $jobs);
            } catch (\Throwable $e) {
                CLI::error('Worker error: ' . $e->getMessage());

                // Jangan hapus api_key — biarkan job tetap bisa dilacak
                try {
                    $db->reconnect();
                    $db->table('extract_jobs')
                        ->where('status', 'running')
                        ->update([
                            'status'  => 'stopped',
                            'message' => 'Error: ' . mb_substr($e->getMessage(), 0, 200),
                        ]);
                } catch (\Throwable $e2) {
                    // abaikan
                }
                sleep(5);
            }
        }
    }

    private function runJob(array $job, $db, JobModel $jobs): void
    {
        $jid = (int) $job['id'];

        // ---------- Validasi & normalisasi site_ids ----------
        $rawIds = json_decode((string) $job['site_ids'], true);
        $ids    = [];
        if (is_array($rawIds)) {
            foreach ($rawIds as $v) {
                $v = (int) $v;
                if ($v > 0) {
                    $ids[] = $v;
                }
            }
        }
        $ids = array_values(array_unique($ids));

        if (! $ids) {
            $jobs->update($jid, [
                'status'  => 'stopped',
                'message' => 'Tidak ada kontak valid di job ini',
            ]);
            CLI::write("Job #{$jid}: tidak ada kontak valid, dilewati.", 'red');
            return;
        }

        $interval   = 60 / max(0.05, (float) $job['rate']);
        $deadline   = time() + (int) ((float) $job['hours'] * 3600);
        $tokenLimit = (int) ($job['token_limit'] ?: 1000000);
        $autoFill   = (int) ($job['auto_fill'] ?? 1) === 1;
        $kontak     = new KontakModel();
        $apiKey     = (string) ($job['api_key'] ?? '');
        $model      = (string) ($job['model'] ?? '');
        $ext        = new EmailExtractor($apiKey, $model);

        $jobs->update($jid, [
            'status'         => 'running',
            'message'        => 'Berjalan',
            'started_at'     => date('Y-m-d H:i:s'),
            'deadline_at'    => date('Y-m-d H:i:s', $deadline),
            'stop_requested' => 0, // safety: reset flag
            'current_url'    => null,
        ]);

        CLI::write(
            "Job #{$jid} mulai: " . count($ids) . " kontak, {$job['rate']}/menit, "
            . "maks {$job['hours']} jam, limit token {$tokenLimit}"
            . ($apiKey === '' ? ' [regex-only, tanpa AI]' : ''),
            'yellow'
        );

        $processed = $found = $tokens = 0;
        $final     = ['finished', 'Selesai, semua kontak terpilih sudah diproses'];
        $last      = count($ids) - 1;

        try {
            foreach ($ids as $n => $kid) {
                $db->reconnect();
                $this->beat();

                // ---------- Cek sinyal stop / deadline / token ----------
                try {
                    $cur = $jobs->find($jid);
                } catch (\Throwable $e) {
                    $cur = null;
                }

                if (! $cur) {
                    $final = ['stopped', 'Job hilang dari database'];
                    break;
                }
                if (! empty($cur['stop_requested'])) {
                    $final = ['stopped', 'Dihentikan manual'];
                    break;
                }
                if (time() >= $deadline) {
                    $final = ['expired', 'Batas durasi tercapai'];
                    break;
                }
                if ($tokens >= $tokenLimit) {
                    $final = ['quota', 'Batas limit token tercapai'];
                    break;
                }

                // ---------- Ambil kontak ----------
                $c = $db->query(
                    'SELECT id, nama, nama_perusahaan, email, website FROM kontak_email WHERE id = ?',
                    [$kid]
                )->getRowArray();

                if (! $c) {
                    // Kontak sudah dihapus
                    continue;
                }

                if (trim((string) ($c['email'] ?? '')) !== '') {
                    // Sudah punya email (mis. dari proses lain / manual)
                    $processed++;
                    $jobs->update($jid, ['processed_count' => $processed]);
                    continue;
                }

                $url = EmailExtractor::normalizeWebsite($c['website'] ?? null);
                if ($url === null) {
                    $this->track($db, $kid, [
                        'status'       => 'error',
                        'note'         => 'Website kosong / tidak valid',
                        'found_count'  => 0,
                        'processed_at' => date('Y-m-d H:i:s'),
                    ]);
                    $processed++;
                    $jobs->update($jid, ['processed_count' => $processed]);
                    continue;
                }

                // ---------- Proses ekstraksi ----------
                $t0 = microtime(true);
                $this->track($db, $kid, ['status' => 'processing', 'note' => null]);
                $jobs->update($jid, ['current_url' => mb_substr($url, 0, 255)]);
                CLI::write('  -> ' . ($c['nama_perusahaan'] ?: $c['nama'] ?: '#' . $kid) . ' | ' . $url);

                try {
                    $r       = $ext->extract($url);
                    $tokens += (int) ($r['tokens'] ?? 0);
                    $db->reconnect();

                    // ---------- Simpan hasil email ----------
                    $saved = 0;
                    foreach ($r['emails'] ?? [] as $row) {
                        // Format: [email, label]
                        $email = is_array($row) ? (string) ($row[0] ?? '') : (string) $row;
                        $label = is_array($row) ? (string) ($row[1] ?? '') : '';
                        if ($email === '') {
                            continue;
                        }

                        $exists = $db->table('kontak_email_hasil')
                            ->where('kontak_id', $kid)
                            ->where('email', $email)
                            ->countAllResults();

                        if ($exists === 0) {
                            $db->table('kontak_email_hasil')->insert([
                                'kontak_id'  => $kid,
                                'email'      => $email,
                                'label'      => mb_substr($label, 0, 255),
                                'dipakai'    => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                            $saved++;
                        }
                    }
                    $found += $saved;

                    // ---------- Auto-fill email ke kontak ----------
                    $assigned = null;
                    if ($autoFill && ! empty($r['emails'])) {
                        foreach (EmailExtractor::rank($r['emails'], $r['url'] ?? $url) as $cand) {
                            if ($kontak->isiEmail($kid, $cand)) {
                                $assigned = $cand;
                                break;
                            }
                        }
                    }

                    // ---------- Tentukan status & catatan ----------
                    $countEmails = count($r['emails'] ?? []);
                    if ($countEmails > 0) {
                        if ($assigned) {
                            $note = "Email diisi: {$assigned}";
                        } elseif ($autoFill) {
                            $note = "{$countEmails} email ditemukan, semuanya sudah dipakai kontak lain";
                        } else {
                            $note = "{$countEmails} email ditemukan (isi otomatis nonaktif)";
                        }
                        $status = 'done';
                    } else {
                        $note   = 'Tidak ada email di website';
                        $status = 'empty';
                    }

                    if (! empty($r['note'])) {
                        $note .= ' · ' . $r['note'];
                    }

                    $this->track($db, $kid, [
                        'status'       => $status,
                        'note'         => mb_substr($note, 0, 250),
                        'found_count'  => $countEmails,
                        'processed_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (QuotaException $e) {
                    // Fatal: worker harus berhenti, kontak dikembalikan ke pending
                    $this->track($db, $kid, [
                        'status' => 'pending',
                        'note'   => 'Menunggu: ' . mb_substr($e->getMessage(), 0, 200),
                    ]);
                    $final = ['quota', 'Berhenti: ' . mb_substr($e->getMessage(), 0, 230)];
                    break;
                } catch (\Throwable $e) {
                    $db->reconnect();
                    $this->track($db, $kid, [
                        'status'       => 'error',
                        'note'         => mb_substr($e->getMessage(), 0, 250),
                        'found_count'  => 0,
                        'processed_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                $processed++;
                $jobs->update($jid, [
                    'processed_count' => $processed,
                    'found_count'     => $found,
                    'tokens_used'     => $tokens,
                ]);

                // ---------- Atur kecepatan (kontak per menit) ----------
                while ($n < $last && microtime(true) - $t0 < $interval) {
                    sleep(1);
                    $this->beat();

                    try {
                        $db->reconnect();
                        $cur = $jobs->find($jid);
                    } catch (\Throwable $e) {
                        $cur = null;
                    }

                    if (! $cur || ! empty($cur['stop_requested'])) {
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            $final = ['stopped', 'Berhenti karena error: ' . mb_substr($e->getMessage(), 0, 220)];
            CLI::error($e->getMessage());
        }

        // ---------- Finalisasi job ----------
        try {
            $db->reconnect();
            $jobs->update($jid, [
                'status'          => $final[0],
                'message'         => mb_substr($final[1], 0, 255),
                'current_url'     => null,
                'processed_count' => $processed,
                'found_count'     => $found,
                'tokens_used'     => $tokens,
            ]);
        } catch (\Throwable $e) {
            CLI::error('Gagal finalisasi job #' . $jid . ': ' . $e->getMessage());
        }

        CLI::write("Job #{$jid}: {$final[1]}", 'yellow');
    }

    private function beat(): void
    {
        try {
            cache()->save('extractor_heartbeat', time(), 120);
        } catch (\Throwable $e) {
            // abaikan — heartbeat bukan hal kritis
        }
    }

    /**
     * Simpan status proses sebuah kontak (insert bila belum ada, update bila sudah).
     */
    private function track($db, int $kid, array $data): void
    {
        $now = date('Y-m-d H:i:s');
        try {
            $exists = $db->table('kontak_ekstraksi')
                ->where('kontak_id', $kid)
                ->countAllResults();

            if ($exists > 0) {
                $db->table('kontak_ekstraksi')
                    ->where('kontak_id', $kid)
                    ->update($data + ['updated_at' => $now]);
            } else {
                $db->table('kontak_ekstraksi')->insert(
                    $data + [
                        'kontak_id'  => $kid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        } catch (\Throwable $e) {
            CLI::error("track() gagal untuk kontak #{$kid}: " . $e->getMessage());
        }
    }
}