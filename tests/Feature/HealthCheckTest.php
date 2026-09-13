<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_up_endpoint_return_200(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_health_endpoint_return_json_dengan_status_semua_komponen(): void
    {
        $res = $this->getJson('/up/health');

        $res->assertOk();
        $res->assertJsonStructure([
            'status',
            'app',
            'environment',
            'timestamp',
            'checks' => [
                'database' => ['status'],
                'cache' => ['status'],
                'storage' => ['status'],
                'disk_space' => ['status'],
                'backup' => ['status'],
            ],
        ]);
    }

    public function test_health_check_database_ok_saat_db_tersedia(): void
    {
        $res = $this->getJson('/up/health');

        $res->assertJsonPath('checks.database.status', 'ok');
        $this->assertIsNumeric($res->json('checks.database.latency_ms'));
    }

    public function test_health_check_tidak_perlu_auth(): void
    {
        // Monitoring tool tidak login → endpoint harus public
        $this->getJson('/up/health')->assertOk();
    }
}
