<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed minimal roles yang dipakai test
        foreach (['AUDITOR', 'SUPER_ADMIN', 'DOKTER'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_auditor_bisa_akses_audit_log(): void
    {
        $user = User::factory()->create();
        $user->assignRole('AUDITOR');

        $res = $this->actingAs($user)->get('/audit-log');
        $res->assertOk();
        $res->assertSee('Audit Log');
    }

    public function test_super_admin_bisa_akses_audit_log(): void
    {
        $user = User::factory()->create();
        $user->assignRole('SUPER_ADMIN');

        $this->actingAs($user)->get('/audit-log')->assertOk();
    }

    public function test_role_lain_ditolak_akses_audit_log(): void
    {
        $user = User::factory()->create();
        $user->assignRole('DOKTER');

        $this->actingAs($user)->get('/audit-log')->assertForbidden();
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get('/audit-log')->assertRedirect('/login');
    }

    public function test_activity_log_menampilkan_perubahan_pasien(): void
    {
        // Setup users dulu SAAT logging masih disabled (default dari TestCase)
        $causer = User::factory()->create();
        $auditor = User::factory()->create();
        $auditor->assignRole('AUDITOR');
        $pasien = Pasien::factory()->create();

        // Baru enable logging + simulasi causer edit pasien
        $this->actingAs($causer);
        activity()->enableLogging();
        $pasien->update(['nama' => 'Nama Baru']);
        activity()->disableLogging();

        // Auditor view halaman
        $res = $this->actingAs($auditor)->get('/audit-log');
        $res->assertOk();
        $res->assertSee($causer->name);
    }
}
