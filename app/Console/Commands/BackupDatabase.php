<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Backup database MySQL/MariaDB ke file .sql.gz.
 * Aman dijalankan via cron produksi setiap hari.
 *
 * Usage:
 *   php artisan sihrs:backup                          # backup + rotate default
 *   php artisan sihrs:backup --keep=60                # simpan 60 hari
 *   php artisan sihrs:backup --path=/backup/sihrs     # target folder lain
 *
 * Prasyarat: mysqldump + gzip terinstall & ada di PATH.
 */
class BackupDatabase extends Command
{
    protected $signature = 'sihrs:backup
        {--path= : Direktori tujuan backup (default: env BACKUP_PATH atau storage/app/backups)}
        {--keep= : Hari retensi (default: env BACKUP_KEEP_DAYS atau 30)}
        {--no-rotate : Skip pembersihan file lama}';

    protected $description = 'Backup database SIHRS ke file .sql.gz dan rotate file lama';

    public function handle(): int
    {
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if (! in_array($driver, ['mysql', 'mariadb'])) {
            $this->error("Driver database '{$driver}' belum di-support. Sementara hanya mysql/mariadb.");
            return self::FAILURE;
        }

        $path = $this->option('path') ?: env('BACKUP_PATH', storage_path('app/backups'));
        $keep = (int) ($this->option('keep') ?: env('BACKUP_KEEP_DAYS', 30));

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $dbName = config("database.connections.$connection.database");
        $filename = "sihrs_{$dbName}_{$timestamp}.sql.gz";
        $fullPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $filename;

        $this->info("Backup database '{$dbName}' → {$fullPath}");

        if (! $this->runDump($connection, $fullPath)) {
            return self::FAILURE;
        }

        $sizeMb = round(File::size($fullPath) / 1024 / 1024, 2);
        $this->info("✓ Backup selesai ({$sizeMb} MB)");

        if (! $this->option('no-rotate')) {
            $deleted = $this->rotate($path, $keep);
            $this->info("✓ Rotate: hapus {$deleted} file lama (> {$keep} hari)");
        }

        return self::SUCCESS;
    }

    protected function runDump(string $connection, string $outputPath): bool
    {
        $cfg = config("database.connections.$connection");
        $host = $cfg['host'];
        $port = (int) $cfg['port'];
        $user = $cfg['username'];
        $pass = $cfg['password'];
        $db = $cfg['database'];

        // Pipe: mysqldump | gzip > file.sql.gz
        // Windows-friendly: gunakan cmd /c untuk shell redirection.
        $isWin = PHP_OS_FAMILY === 'Windows';
        $mysqldump = sprintf(
            'mysqldump --host=%s --port=%d --user=%s --password=%s --single-transaction --routines --triggers --no-tablespaces %s',
            escapeshellarg($host),
            $port,
            escapeshellarg($user),
            escapeshellarg((string) $pass),
            escapeshellarg($db),
        );
        $cmd = $isWin
            ? "cmd /c \"{$mysqldump} | gzip > \"{$outputPath}\"\""
            : "{$mysqldump} | gzip > " . escapeshellarg($outputPath);

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(3600); // 1 jam max — DB besar butuh waktu

        $process->run(function ($type, $buffer) {
            // Log stderr saat proses jalan (mysqldump kadang kirim warning ke stderr)
            if ($type === Process::ERR) {
                $this->line(trim($buffer));
            }
        });

        if (! $process->isSuccessful()) {
            $this->error('Backup gagal:');
            $this->error($process->getErrorOutput());
            @unlink($outputPath); // hapus file setengah jadi
            return false;
        }

        if (! File::exists($outputPath) || File::size($outputPath) === 0) {
            $this->error('Backup gagal: file kosong / tidak dibuat.');
            @unlink($outputPath);
            return false;
        }

        return true;
    }

    /**
     * Hapus backup > $keepDays hari. Return jumlah file yang dihapus.
     */
    protected function rotate(string $path, int $keepDays): int
    {
        $cutoff = now()->subDays($keepDays)->getTimestamp();
        $deleted = 0;

        foreach (File::files($path) as $file) {
            if (! str_ends_with($file->getFilename(), '.sql.gz')) {
                continue;
            }
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        return $deleted;
    }
}
