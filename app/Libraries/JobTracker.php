<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Menyiapkan baris pelacakan per-batch & per-kontak untuk sebuah job
 * (tabel extract_job_batches dan extract_job_items).
 */
class JobTracker
{
    /** Total token yang dipakai HARI INI oleh semua proses (reset otomatis tiap 00.00). */
    public static function tokensToday(BaseConnection $db): int
    {
        try {
            $row = $db->table('extract_job_items')
                ->selectSum('tokens_used', 'n')
                ->where('processed_at >=', date('Y-m-d') . ' 00:00:00')
                ->get()->getRowArray();
            return (int) ($row['n'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function hasRows(BaseConnection $db, int $jobId): bool
    {
        return $db->table('extract_job_batches')->where('job_id', $jobId)->countAllResults() > 0;
    }

    /**
     * @param int[] $ids   kontak sesuai urutan antrean
     * @param int[] $plan  ukuran tiap batch, mis. [10, 12, 9]
     */
    public static function seed(BaseConnection $db, int $jobId, array $ids, array $plan): void
    {
        $batches = [];
        $items   = [];
        $pos     = 0;
        $count   = count($ids);

        foreach ($plan as $n => $size) {
            $no  = $n + 1;
            $cnt = 0;
            for ($k = 0; $k < $size && $pos < $count; $k++) {
                $items[] = [
                    'job_id' => $jobId, 'kontak_id' => (int) $ids[$pos], 'batch_no' => $no,
                    'pos' => $pos, 'status' => 'pending',
                ];
                $pos++;
                $cnt++;
            }
            if ($cnt === 0) {
                break;
            }
            $batches[] = [
                'job_id' => $jobId, 'batch_no' => $no, 'size' => $cnt, 'status' => 'pending',
            ];
        }

        foreach (array_chunk($batches, 500) as $chunk) {
            $db->table('extract_job_batches')->ignore(true)->insertBatch($chunk);
        }
        foreach (array_chunk($items, 500) as $chunk) {
            $db->table('extract_job_items')->ignore(true)->insertBatch($chunk);
        }
    }
}