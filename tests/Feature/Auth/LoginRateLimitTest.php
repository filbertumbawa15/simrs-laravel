<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
    }

    public function test_login_diblokir_setelah_5_kali_gagal_dalam_1_menit(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->post('/login', [
                'username' => 'attacker',
                'password' => 'wrong',
            ]);
            $this->assertNotEquals(429, $res->getStatusCode(), "Percobaan ke-{$i} seharusnya tidak ke-throttle");
        }

        // Percobaan ke-6 harus di-throttle
        $blocked = $this->post('/login', [
            'username' => 'attacker',
            'password' => 'wrong',
        ]);

        $this->assertEquals(429, $blocked->getStatusCode(),
            'Setelah 5x gagal, request ke-6 harus mendapat HTTP 429');
    }

    public function test_rate_limit_terpisah_per_kombinasi_username_ip(): void
    {
        // Attacker 1 (username1) — 5x gagal, ke-throttle
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['username' => 'user1', 'password' => 'x']);
        }

        // Attacker 2 (username2) dari IP yang sama — masih boleh karena kombinasi berbeda
        $res = $this->post('/login', ['username' => 'user2', 'password' => 'x']);
        $this->assertNotEquals(429, $res->getStatusCode());
    }
}
