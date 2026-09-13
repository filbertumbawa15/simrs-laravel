<?php

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_selalu_punya_security_headers_dasar(): void
    {
        $res = $this->get('/login');

        $res->assertHeader('X-Frame-Options', 'DENY');
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $res->assertHeader('Content-Security-Policy');
        $res->assertHeader('Permissions-Policy');
    }

    public function test_csp_membatasi_frame_ancestors_dan_default_src(): void
    {
        $res = $this->get('/login');

        $csp = $res->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
    }

    public function test_hsts_tidak_kirim_di_http(): void
    {
        $res = $this->get('/login');

        $this->assertNull($res->headers->get('Strict-Transport-Security'),
            'HSTS hanya dikirim di HTTPS untuk hindari lock user di dev');
    }

    public function test_hsts_dikirim_di_https(): void
    {
        $res = $this->get('https://localhost/login');

        $res->assertHeader('Strict-Transport-Security');
        $this->assertStringContainsString('max-age=', $res->headers->get('Strict-Transport-Security'));
    }

    public function test_x_powered_by_dihilangkan(): void
    {
        $res = $this->get('/login');

        $this->assertNull($res->headers->get('X-Powered-By'));
    }
}
