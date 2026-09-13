<?php

namespace Tests\Feature\Services;

use App\Enums\MetodePembayaran;
use App\Enums\Penjamin;
use App\Enums\StatusKunjungan;
use App\Enums\StatusTagihan;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Poli;
use App\Models\RawatJalan;
use App\Models\StokObat;
use App\Models\Tindakan;
use App\Models\TindakanKunjungan;
use App\Models\User;
use App\Services\BillingService;
use App\Services\FarmasiService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BillingService $service;
    private FarmasiService $farmasi;
    private User $kasir;
    private User $finalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BillingService();
        $this->farmasi = new FarmasiService();
        $this->kasir = User::factory()->create();
        $this->finalizer = User::factory()->create();
    }

    // ============================================================
    // generateTagihan — aggregation
    // ============================================================

    public function test_generate_tagihan_agregat_konsultasi_dari_dokter_rj(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 150000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);

        $tagihan = $this->service->generateTagihan($kunjungan);

        $konsul = $tagihan->details->firstWhere('kategori', 'KONSULTASI');
        $this->assertNotNull($konsul);
        $this->assertEquals(150000, (float) $konsul->subtotal);
        $this->assertEquals(150000, (float) $tagihan->subtotal);
        $this->assertEquals(150000, (float) $tagihan->total);
    }

    public function test_generate_tagihan_skip_konsultasi_kalau_jasa_konsul_nol(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 0]);
        $kunjungan = $this->kunjunganRjDengan($dokter);

        $tagihan = $this->service->generateTagihan($kunjungan);

        $this->assertNull($tagihan->details->firstWhere('kategori', 'KONSULTASI'));
    }

    public function test_generate_tagihan_agregat_tindakan(): void
    {
        $kunjungan = Kunjungan::factory()->rj()->create();
        $tindakan = Tindakan::factory()->create(['nama' => 'EKG']);
        TindakanKunjungan::factory()->create([
            'kunjungan_id' => $kunjungan->id,
            'tindakan_id' => $tindakan->id,
            'petugas_id' => User::factory(),
            'tarif' => 80000,
            'subtotal' => 80000,
        ]);

        $tagihan = $this->service->generateTagihan($kunjungan);

        $item = $tagihan->details->firstWhere('kategori', 'TINDAKAN');
        $this->assertNotNull($item);
        $this->assertEquals(80000, (float) $item->subtotal);
        $this->assertStringContainsString('EKG', $item->deskripsi);
    }

    public function test_generate_tagihan_agregat_obat_dari_resep_diserahkan(): void
    {
        $kunjungan = Kunjungan::factory()->rj()->create();
        $obat = Obat::factory()->create(['harga_jual' => 2000, 'nama' => 'Paracetamol']);
        StokObat::factory()->forObat($obat)->withBatch('B1', 100, now()->addYear()->toDateString())->create();

        $dokter = Dokter::factory()->create();
        $penyerah = User::factory()->create();

        $resep = $this->farmasi->buatResep($kunjungan->id, $dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => 10, 'signa' => '3x1'],
        ]);
        $this->farmasi->serahkanObat($resep, $penyerah->id);

        $tagihan = $this->service->generateTagihan($kunjungan);

        $item = $tagihan->details->firstWhere('kategori', 'FARMASI');
        $this->assertNotNull($item);
        $this->assertEquals(20000, (float) $item->subtotal);
        $this->assertStringContainsString('Paracetamol', $item->deskripsi);
    }

    public function test_generate_tagihan_skip_resep_yang_belum_diserahkan(): void
    {
        $kunjungan = Kunjungan::factory()->rj()->create();
        $obat = Obat::factory()->create(['harga_jual' => 2000]);
        $dokter = Dokter::factory()->create();

        // Buat resep tapi TIDAK diserahkan (status masih BARU)
        $this->farmasi->buatResep($kunjungan->id, $dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => 10, 'signa' => '3x1'],
        ]);

        $tagihan = $this->service->generateTagihan($kunjungan);

        $this->assertNull($tagihan->details->firstWhere('kategori', 'FARMASI'),
            'Resep BARU (belum diserahkan) tidak boleh masuk tagihan');
    }

    public function test_generate_tagihan_hitung_total_akumulasi_semua_kategori(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);

        // Tambah tindakan
        TindakanKunjungan::factory()->create([
            'kunjungan_id' => $kunjungan->id,
            'petugas_id' => User::factory(),
            'tarif' => 50000,
            'subtotal' => 50000,
        ]);

        $tagihan = $this->service->generateTagihan($kunjungan);

        $this->assertEquals(150000, (float) $tagihan->subtotal);
        $this->assertEquals(150000, (float) $tagihan->total);
        $this->assertEquals(150000, (float) $tagihan->sisa);
        $this->assertEquals(0, (float) $tagihan->dibayar);
    }

    // ============================================================
    // Idempotency
    // ============================================================

    public function test_generate_tagihan_idempotent_return_tagihan_yang_sama(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);

        $t1 = $this->service->generateTagihan($kunjungan);
        $t2 = $this->service->generateTagihan($kunjungan);

        $this->assertEquals($t1->id, $t2->id, 'Panggilan kedua harus return tagihan yang sama');
        $this->assertEquals($t1->no_tagihan, $t2->no_tagihan);
    }

    public function test_generate_tagihan_refresh_details_saat_dipanggil_ulang(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);

        $t1 = $this->service->generateTagihan($kunjungan);
        $this->assertCount(1, $t1->details);

        // Tambah tindakan setelah generate pertama
        TindakanKunjungan::factory()->create([
            'kunjungan_id' => $kunjungan->id,
            'petugas_id' => User::factory(),
            'tarif' => 50000,
            'subtotal' => 50000,
        ]);

        $t2 = $this->service->generateTagihan($kunjungan);

        $this->assertCount(2, $t2->details, 'Details harus di-refresh saat generate ulang');
        $this->assertEquals(150000, (float) $t2->subtotal);
    }

    // ============================================================
    // finalize
    // ============================================================

    public function test_finalize_umum_ubah_status_ke_belum_lunas(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);
        $tagihan = $this->service->generateTagihan($kunjungan);

        $finalized = $this->service->finalize($tagihan, $this->finalizer->id);

        $this->assertEquals(StatusTagihan::BelumLunas, $finalized->status);
        $this->assertEquals($this->finalizer->id, $finalized->finalized_by);
        $this->assertNotNull($finalized->finalized_at);
        $this->assertEquals(StatusKunjungan::MenungguPembayaran, $kunjungan->fresh()->status);
    }

    public function test_finalize_bpjs_ubah_status_ke_klaim(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter, penjamin: Penjamin::BPJS);
        $tagihan = $this->service->generateTagihan($kunjungan);

        $finalized = $this->service->finalize($tagihan, $this->finalizer->id);

        $this->assertEquals(StatusTagihan::Klaim, $finalized->status);
    }

    public function test_finalize_throw_kalau_status_bukan_draft(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);
        $tagihan = $this->service->generateTagihan($kunjungan);
        $this->service->finalize($tagihan, $this->finalizer->id);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('draft');
        $this->service->finalize($tagihan->fresh(), $this->finalizer->id);
    }

    // ============================================================
    // catatPembayaran — partial & full
    // ============================================================

    public function test_bayar_penuh_langsung_ubah_status_ke_lunas_dan_kunjungan_selesai(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);
        $tagihan = $this->service->generateTagihan($kunjungan);
        $this->service->finalize($tagihan, $this->finalizer->id);

        $this->service->catatPembayaran(
            $tagihan->fresh(),
            MetodePembayaran::Tunai,
            100000,
            $this->kasir->id,
        );

        $tagihan->refresh();
        $this->assertEquals(StatusTagihan::Lunas, $tagihan->status);
        $this->assertEquals(0, (float) $tagihan->sisa);
        $this->assertEquals(100000, (float) $tagihan->dibayar);

        $kunjungan->refresh();
        $this->assertEquals(StatusKunjungan::Selesai, $kunjungan->status);
        $this->assertNotNull($kunjungan->tgl_keluar);
    }

    public function test_bayar_partial_ubah_status_ke_cicilan(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);
        $tagihan = $this->service->generateTagihan($kunjungan);
        $this->service->finalize($tagihan, $this->finalizer->id);

        $this->service->catatPembayaran($tagihan->fresh(), MetodePembayaran::Tunai, 40000, $this->kasir->id);

        $tagihan->refresh();
        $this->assertEquals(StatusTagihan::Cicilan, $tagihan->status);
        $this->assertEquals(60000, (float) $tagihan->sisa);
        $this->assertEquals(40000, (float) $tagihan->dibayar);
    }

    public function test_bayar_cicilan_kedua_yang_menutup_langsung_lunas(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);
        $tagihan = $this->service->generateTagihan($kunjungan);
        $this->service->finalize($tagihan, $this->finalizer->id);

        // Bayar 60% dulu
        $this->service->catatPembayaran($tagihan->fresh(), MetodePembayaran::Tunai, 60000, $this->kasir->id);
        $this->assertEquals(StatusTagihan::Cicilan, $tagihan->fresh()->status);

        // Bayar sisa 40%
        $this->service->catatPembayaran($tagihan->fresh(), MetodePembayaran::Transfer, 40000, $this->kasir->id);

        $tagihan->refresh();
        $this->assertEquals(StatusTagihan::Lunas, $tagihan->status);
        $this->assertEquals(0, (float) $tagihan->sisa);
        $this->assertEquals(100000, (float) $tagihan->dibayar);
        $this->assertEquals(2, Pembayaran::where('tagihan_id', $tagihan->id)->count());
    }

    public function test_bayar_tagihan_lunas_ditolak(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);
        $tagihan = $this->service->generateTagihan($kunjungan);
        $this->service->finalize($tagihan, $this->finalizer->id);
        $this->service->catatPembayaran($tagihan->fresh(), MetodePembayaran::Tunai, 100000, $this->kasir->id);

        $this->expectException(DomainException::class);
        $this->service->catatPembayaran($tagihan->fresh(), MetodePembayaran::Tunai, 50000, $this->kasir->id);
    }

    public function test_generate_no_pembayaran_unik(): void
    {
        $dokter = Dokter::factory()->create(['jasa_konsul' => 100000]);
        $kunjungan = $this->kunjunganRjDengan($dokter);
        $tagihan = $this->service->generateTagihan($kunjungan);
        $this->service->finalize($tagihan, $this->finalizer->id);

        $p1 = $this->service->catatPembayaran($tagihan->fresh(), MetodePembayaran::Tunai, 30000, $this->kasir->id);
        $p2 = $this->service->catatPembayaran($tagihan->fresh(), MetodePembayaran::Tunai, 30000, $this->kasir->id);

        $this->assertNotEquals($p1->no_pembayaran, $p2->no_pembayaran);
        $prefix = 'PB/' . now()->format('Y') . '/' . now()->format('m');
        $this->assertStringStartsWith($prefix, $p1->no_pembayaran);
        $this->assertStringStartsWith($prefix, $p2->no_pembayaran);
    }

    // ============================================================
    // Helpers
    // ============================================================

    private function kunjunganRjDengan(Dokter $dokter, Penjamin $penjamin = Penjamin::Umum): Kunjungan
    {
        $kunjungan = Kunjungan::factory()->rj()->create(['penjamin' => $penjamin]);
        RawatJalan::factory()->create([
            'kunjungan_id' => $kunjungan->id,
            'poli_id' => Poli::factory(),
            'dokter_id' => $dokter->id,
        ]);

        return $kunjungan;
    }
}
