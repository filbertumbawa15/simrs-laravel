<?php

namespace Tests\Feature;

use App\Enums\MetodePembayaran;
use App\Enums\Penjamin;
use App\Exports\PasienExport;
use App\Exports\RekapTagihanExport;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\RawatJalan;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['SUPER_ADMIN', 'MANAGER', 'DIREKSI', 'AUDITOR', 'KASIR_SUPERVISOR', 'DOKTER', 'KASIR'] as $r) {
            Role::findOrCreate($r);
        }
    }

    public function test_export_pasien_hanya_bisa_manager_dan_atasan(): void
    {
        Excel::fake();

        $manager = User::factory()->create();
        $manager->assignRole('MANAGER');

        Pasien::factory()->count(3)->create();

        $this->actingAs($manager)->get('/export/pasien')->assertOk();
        Excel::matchByRegex();
        Excel::assertDownloaded('/pasien_\d{8}_\d{6}\.xlsx/');
    }

    public function test_export_pasien_ditolak_untuk_role_biasa(): void
    {
        $dokter = User::factory()->create();
        $dokter->assignRole('DOKTER');

        $this->actingAs($dokter)->get('/export/pasien')->assertForbidden();
    }

    public function test_export_rekap_tagihan_bisa_diakses_kasir_supervisor(): void
    {
        Excel::fake();

        $spv = User::factory()->create();
        $spv->assignRole('KASIR_SUPERVISOR');

        $this->actingAs($spv)->get('/export/tagihan')->assertOk();
        Excel::matchByRegex();
        Excel::assertDownloaded('/rekap_tagihan_\d{8}_\d{8}\.xlsx/');
    }

    public function test_export_rekap_tagihan_terima_filter_tanggal(): void
    {
        Excel::fake();

        $mgr = User::factory()->create();
        $mgr->assignRole('MANAGER');

        $res = $this->actingAs($mgr)->get('/export/tagihan?dari=2026-01-01&sampai=2026-01-31&status=LUNAS');
        $res->assertOk();
    }

    public function test_kasir_biasa_tidak_bisa_export_tagihan(): void
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('KASIR');

        $this->actingAs($kasir)->get('/export/tagihan')->assertForbidden();
    }

    public function test_pasien_export_query_apply_search_filter(): void
    {
        Pasien::factory()->create(['nama' => 'Budi Santoso']);
        Pasien::factory()->create(['nama' => 'Ahmad Fauzi']);

        $export = new PasienExport(search: 'Budi');
        $results = $export->query()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Budi Santoso', $results->first()->nama);
    }

    public function test_rekap_tagihan_export_filter_by_tanggal(): void
    {
        // Buat 2 tagihan: 1 di Jan, 1 di Feb
        $this->buatTagihanPada('2026-01-15');
        $this->buatTagihanPada('2026-02-15');

        $export = new RekapTagihanExport(
            \Carbon\Carbon::parse('2026-01-01'),
            \Carbon\Carbon::parse('2026-01-31'),
        );

        $this->assertCount(1, $export->query()->get());
    }

    private function buatTagihanPada(string $tanggal): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = Kunjungan::factory()->rj()->create(['penjamin' => Penjamin::Umum]);
        RawatJalan::factory()->create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => Poli::factory(),
            'dokter_id' => $dokter->id,
        ]);
        $t = app(BillingService::class)->generateTagihan($kunjungan);
        $t->update(['tgl_tagihan' => $tanggal]);
    }
}
