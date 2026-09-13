<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Test rotation logic dari sihrs:backup.
 * mysqldump dijalankan via Process — kita tidak test itu di sini (butuh mysql server).
 * Fokus: pastikan file lama dihapus, file baru dipertahankan.
 */
class BackupDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private string $backupPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupPath = storage_path('app/backups-test-' . uniqid());
        File::makeDirectory($this->backupPath, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->backupPath)) {
            File::deleteDirectory($this->backupPath);
        }
        parent::tearDown();
    }

    public function test_rotate_hapus_file_lebih_lama_dari_keep_days(): void
    {
        $oldFile = $this->backupPath . '/sihrs_old.sql.gz';
        $newFile = $this->backupPath . '/sihrs_new.sql.gz';

        File::put($oldFile, 'dummy');
        File::put($newFile, 'dummy');
        touch($oldFile, now()->subDays(45)->getTimestamp());  // 45 hari lalu
        touch($newFile, now()->subDays(5)->getTimestamp());   // 5 hari lalu

        // Panggil rotate via reflection karena method protected
        $cmd = new \App\Console\Commands\BackupDatabase();
        $method = new \ReflectionMethod($cmd, 'rotate');
        $method->setAccessible(true);
        $deleted = $method->invoke($cmd, $this->backupPath, 30);

        $this->assertEquals(1, $deleted, 'Harus hapus 1 file (yang > 30 hari)');
        $this->assertFileDoesNotExist($oldFile);
        $this->assertFileExists($newFile);
    }

    public function test_rotate_skip_file_yang_bukan_sql_gz(): void
    {
        $sqlFile = $this->backupPath . '/sihrs.sql.gz';
        $otherFile = $this->backupPath . '/notes.txt';

        File::put($sqlFile, 'dummy');
        File::put($otherFile, 'dummy');
        touch($sqlFile, now()->subDays(60)->getTimestamp());
        touch($otherFile, now()->subDays(60)->getTimestamp());

        $cmd = new \App\Console\Commands\BackupDatabase();
        $method = new \ReflectionMethod($cmd, 'rotate');
        $method->setAccessible(true);
        $method->invoke($cmd, $this->backupPath, 30);

        $this->assertFileDoesNotExist($sqlFile);
        $this->assertFileExists($otherFile, 'File non-backup tidak boleh dihapus');
    }

    public function test_rotate_zero_ketika_semua_file_masih_baru(): void
    {
        File::put($this->backupPath . '/a.sql.gz', 'dummy');
        File::put($this->backupPath . '/b.sql.gz', 'dummy');

        $cmd = new \App\Console\Commands\BackupDatabase();
        $method = new \ReflectionMethod($cmd, 'rotate');
        $method->setAccessible(true);
        $deleted = $method->invoke($cmd, $this->backupPath, 30);

        $this->assertEquals(0, $deleted);
    }

    public function test_command_gagal_kalau_driver_bukan_mysql(): void
    {
        // Test env sudah pakai sqlite (dari phpunit.xml)
        $this->artisan('sihrs:backup')
            ->expectsOutput("Driver database 'sqlite' belum di-support. Sementara hanya mysql/mariadb.")
            ->assertFailed();
    }
}
