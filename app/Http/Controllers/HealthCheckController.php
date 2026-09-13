<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Endpoint monitoring untuk uptime checker (UptimeRobot, Pingdom, Grafana, dll).
 *
 * GET /up/health   → JSON dengan status per komponen. 200 kalau semua OK, 503 kalau ada critical fail.
 * GET /up          → default Laravel, cepat, cukup untuk basic liveness.
 */
class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorageWritable(),
            'disk_space' => $this->checkDiskSpace(),
            'backup' => $this->checkLastBackup(),
        ];

        // Status keseluruhan = fail kalau ada check yang critical fail
        $overallOk = collect($checks)->every(fn ($c) => $c['status'] !== 'critical');

        return response()->json([
            'status' => $overallOk ? 'ok' : 'unhealthy',
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $overallOk ? 200 : 503);
    }

    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return ['status' => 'ok', 'latency_ms' => $latencyMs];
        } catch (Throwable $e) {
            return ['status' => 'critical', 'error' => 'DB unreachable: ' . $e->getMessage()];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = 'healthcheck_' . now()->timestamp;
            Cache::put($key, 'ping', 5);
            $value = Cache::get($key);
            Cache::forget($key);

            return $value === 'ping'
                ? ['status' => 'ok']
                : ['status' => 'warning', 'error' => 'Cache read mismatch'];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'error' => 'Cache error: ' . $e->getMessage()];
        }
    }

    protected function checkStorageWritable(): array
    {
        try {
            $disk = Storage::disk(config('filesystems.default'));
            $testFile = 'healthcheck-' . now()->timestamp . '.tmp';
            $disk->put($testFile, 'ok');
            $disk->delete($testFile);

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'critical', 'error' => 'Storage not writable: ' . $e->getMessage()];
        }
    }

    protected function checkDiskSpace(): array
    {
        try {
            $free = disk_free_space(storage_path());
            $total = disk_total_space(storage_path());

            if (! $free || ! $total) {
                return ['status' => 'warning', 'error' => 'Cannot read disk stats'];
            }

            $freeGb = round($free / 1024 / 1024 / 1024, 2);
            $usedPct = round(($total - $free) / $total * 100, 1);

            // Warning kalau usage > 85%, critical kalau > 95%
            $status = match (true) {
                $usedPct > 95 => 'critical',
                $usedPct > 85 => 'warning',
                default => 'ok',
            };

            return [
                'status' => $status,
                'used_percent' => $usedPct,
                'free_gb' => $freeGb,
            ];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'error' => $e->getMessage()];
        }
    }

    protected function checkLastBackup(): array
    {
        try {
            $path = env('BACKUP_PATH', storage_path('app/backups'));
            if (! File::isDirectory($path)) {
                return ['status' => 'warning', 'error' => 'Backup directory belum ada'];
            }

            $files = collect(File::files($path))
                ->filter(fn ($f) => str_ends_with($f->getFilename(), '.sql.gz'))
                ->sortByDesc(fn ($f) => $f->getMTime());

            if ($files->isEmpty()) {
                return ['status' => 'warning', 'error' => 'Belum ada backup'];
            }

            $latest = $files->first();
            $ageHours = round((time() - $latest->getMTime()) / 3600, 1);

            // Warning kalau backup terakhir > 30 jam (harusnya harian)
            $status = $ageHours > 30 ? 'warning' : 'ok';

            return [
                'status' => $status,
                'last_backup' => $latest->getFilename(),
                'age_hours' => $ageHours,
                'size_mb' => round($latest->getSize() / 1024 / 1024, 2),
            ];
        } catch (Throwable $e) {
            return ['status' => 'warning', 'error' => $e->getMessage()];
        }
    }
}
