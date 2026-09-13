<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class WriteRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('write');
        RateLimiter::clear('search');
    }

    public function test_pasien_store_di_throttle_setelah_30_request(): void
    {
        $user = User::factory()->create();

        // 30 request pertama boleh (walau data invalid, tetap kena rate limiter dulu)
        for ($i = 1; $i <= 30; $i++) {
            $res = $this->actingAs($user)->post('/pasien', []);
            $this->assertNotEquals(429, $res->getStatusCode(), "Req ke-{$i} tidak boleh throttled");
        }

        // Request ke-31 harus di-throttle
        $blocked = $this->actingAs($user)->post('/pasien', []);
        $this->assertEquals(429, $blocked->getStatusCode(),
            'Setelah 30 request write, req ke-31 harus HTTP 429');
    }

    public function test_search_di_throttle_setelah_120_request(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 120; $i++) {
            $res = $this->actingAs($user)->get('/rj/icd/search?q=A00');
            $this->assertNotEquals(429, $res->getStatusCode(), "Search ke-{$i} tidak boleh throttled");
        }

        $blocked = $this->actingAs($user)->get('/rj/icd/search?q=A00');
        $this->assertEquals(429, $blocked->getStatusCode());
    }

    public function test_write_rate_limit_terpisah_per_user(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        // User 1 exhaust quota
        for ($i = 1; $i <= 30; $i++) {
            $this->actingAs($u1)->post('/pasien', []);
        }

        // User 2 masih boleh — quota independent
        $res = $this->actingAs($u2)->post('/pasien', []);
        $this->assertNotEquals(429, $res->getStatusCode());
    }
}
