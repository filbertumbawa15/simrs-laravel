<?php

namespace Tests\Feature;

use App\Enums\MetodePembayaran;
use App\Enums\Penjamin;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Poli;
use App\Models\RawatJalan;
use App\Models\StokObat;
use App\Models\User;
use App\Services\BillingService;
use App\Services\FarmasiService;
use App\Services\PdfService;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_service_generate_svg_data_uri(): void
    {
        $svc = new QrCodeService();
        $uri = $svc->svgDataUri('https://example.com/verify/kuitansi/xxx');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);

        $svg = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_qr_service_verify_url_pakai_config(): void
    {
        config(['sihrs.verify_base_url' => 'https://rs.example.com']);
        $svc = new QrCodeService();

        $this->assertEquals(
            'https://rs.example.com/verify/kuitansi/abc-123',
            $svc->verifyUrl('kuitansi', 'abc-123')
        );
    }

    public function test_pdf_kuitansi_generate_tanpa_error(): void
    {
        $pembayaran = $this->kuitansiFixture();

        /** @var PdfService $svc */
        $svc = app(PdfService::class);
        $response = $svc->kuitansi($pembayaran);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertGreaterThan(1000, strlen($response->getContent()), 'PDF harus punya konten minimal 1KB');
    }

    public function test_pdf_resep_generate_tanpa_error(): void
    {
        $kunjungan = Kunjungan::factory()->rj()->create();
        $dokter = Dokter::factory()->create();
        $obat = Obat::factory()->create();
        StokObat::factory()->forObat($obat)->withBatch('B1', 100, now()->addYear()->toDateString())->create();

        $resep = app(FarmasiService::class)->buatResep($kunjungan->id, $dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => 10, 'signa' => '3x1'],
        ]);

        $response = app(PdfService::class)->resep($resep);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_verify_endpoint_kuitansi_return_valid(): void
    {
        $pembayaran = $this->kuitansiFixture();

        $res = $this->get("/verify/kuitansi/{$pembayaran->id}");
        $res->assertOk();
        $res->assertSee('DOKUMEN SAH');
        $res->assertSee($pembayaran->no_pembayaran);
    }

    public function test_verify_endpoint_kuitansi_void_return_tidak_berlaku(): void
    {
        $pembayaran = $this->kuitansiFixture();
        $pembayaran->update(['is_void' => true]);

        $res = $this->get("/verify/kuitansi/{$pembayaran->id}");
        $res->assertOk();
        $res->assertSee('TIDAK BERLAKU');
    }

    public function test_verify_endpoint_return_404_kalau_dokumen_tidak_ada(): void
    {
        $res = $this->get('/verify/kuitansi/00000000-0000-0000-0000-000000000000');
        $res->assertStatus(404);
        $res->assertSee('Tidak Ditemukan');
    }

    public function test_verify_endpoint_return_404_kalau_doc_type_tidak_dikenal(): void
    {
        $res = $this->get('/verify/foo/anything');
        $res->assertStatus(404);
    }

    public function test_verify_endpoint_tidak_perlu_auth(): void
    {
        $pembayaran = $this->kuitansiFixture();

        // Tanpa auth setup
        $res = $this->get("/verify/kuitansi/{$pembayaran->id}");
        $res->assertOk();
    }

    // ============================================================
    // Fixtures
    // ============================================================

    private function kuitansiFixture(): Pembayaran
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = Kunjungan::factory()->rj()->create(['penjamin' => Penjamin::Umum]);
        RawatJalan::factory()->create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => Poli::factory(),
            'dokter_id' => $dokter->id,
        ]);

        $billing = app(BillingService::class);
        $tagihan = $billing->generateTagihan($kunjungan);
        $billing->finalize($tagihan, User::factory()->create()->id);

        return $billing->catatPembayaran(
            $tagihan->fresh(),
            MetodePembayaran::Tunai,
            100000,
            User::factory()->create()->id,
        );
    }
}
