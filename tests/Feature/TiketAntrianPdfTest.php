<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Poli;
use App\Models\RawatJalan;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TiketAntrianPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_tiket_antrian_generate_pdf_valid(): void
    {
        $rj = $this->buatRj();

        /** @var PdfService $svc */
        $svc = app(PdfService::class);
        $response = $svc->tiketAntrian($rj);

        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertGreaterThan(500, strlen($response->getContent()));
    }

    public function test_filename_tiket_tidak_mengandung_karakter_invalid(): void
    {
        $rj = $this->buatRj();

        $response = app(PdfService::class)->tiketAntrian($rj);

        $disposition = $response->headers->get('content-disposition');
        $this->assertStringNotContainsString('/', $disposition ?? '', 'Filename tidak boleh ada slash');
        $this->assertStringNotContainsString('\\', $disposition ?? '');
    }

    private function buatRj(): RawatJalan
    {
        $kunjungan = Kunjungan::factory()->rj()->create();
        return RawatJalan::factory()->create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => Poli::factory(),
            'dokter_id' => Dokter::factory(),
            'no_antrian' => 12,
        ]);
    }
}
