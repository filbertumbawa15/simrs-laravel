<?php

namespace Tests\Feature\Services;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\MutasiStokObat;
use App\Models\Obat;
use App\Models\Resep;
use App\Models\StokObat;
use App\Models\User;
use App\Services\FarmasiService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private FarmasiService $service;
    private Kunjungan $kunjungan;
    private Dokter $dokter;
    private User $apoteker;
    private User $penyerah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FarmasiService();
        $this->kunjungan = Kunjungan::factory()->rj()->create();
        $this->dokter = Dokter::factory()->create();
        $this->apoteker = User::factory()->create();
        $this->penyerah = User::factory()->create();
    }

    // ============================================================
    // buatResep
    // ============================================================

    public function test_buat_resep_snapshots_current_harga_jual(): void
    {
        $obat = Obat::factory()->create(['harga_jual' => 5000]);

        $resep = $this->service->buatResep($this->kunjungan->id, $this->dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => 10, 'signa' => '3x1'],
        ]);

        $detail = $resep->details->first();

        $this->assertEquals(5000, (float) $detail->harga_satuan);
        $this->assertEquals(50000, (float) $detail->subtotal);
        $this->assertEquals(10, $detail->jumlah);
        $this->assertEquals('3x1', $detail->signa);
    }

    public function test_buat_resep_snapshot_harga_tidak_ikut_perubahan_master(): void
    {
        $obat = Obat::factory()->create(['harga_jual' => 5000]);

        $resep = $this->service->buatResep($this->kunjungan->id, $this->dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => 2, 'signa' => '2x1'],
        ]);

        // Harga master naik setelah resep dibuat
        $obat->update(['harga_jual' => 12000]);

        $detail = $resep->fresh('details')->details->first();

        $this->assertEquals(5000, (float) $detail->harga_satuan, 'Harga snapshot tidak boleh berubah walau master di-update');
        $this->assertEquals(10000, (float) $detail->subtotal);
    }

    public function test_buat_resep_generates_no_resep_dengan_format_yang_benar(): void
    {
        $obat = Obat::factory()->create();
        $prefix = 'RX/' . now()->format('Y') . '/' . now()->format('m');

        $resep = $this->service->buatResep($this->kunjungan->id, $this->dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => 1, 'signa' => '1x1'],
        ]);

        $this->assertStringStartsWith($prefix . '/', $resep->no_resep);
        $this->assertMatchesRegularExpression("#^{$prefix}/\d{5}$#", $resep->no_resep);
    }

    public function test_buat_resep_default_status_baru(): void
    {
        $obat = Obat::factory()->create();

        $resep = $this->service->buatResep($this->kunjungan->id, $this->dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => 1, 'signa' => '1x1'],
        ]);

        $this->assertEquals('BARU', $resep->status);
    }

    // ============================================================
    // verifikasiResep
    // ============================================================

    public function test_verifikasi_resep_sets_status_and_apoteker_metadata(): void
    {
        $resep = $this->buatResepDengan1Obat();

        $verified = $this->service->verifikasiResep($resep, $this->apoteker->id);

        $this->assertEquals('DIVERIFIKASI', $verified->status);
        $this->assertEquals($this->apoteker->id, $verified->apoteker_verifikator_id);
        $this->assertNotNull($verified->verified_at);
    }

    // ============================================================
    // serahkanObat — FEFO logic
    // ============================================================

    public function test_serahkan_obat_pakai_batch_dengan_exp_paling_dekat_dulu(): void
    {
        $obat = Obat::factory()->create(['harga_jual' => 1000]);

        // Batch B expired lebih lama (harusnya tidak dipakai dulu)
        $batchLama = StokObat::factory()->forObat($obat)
            ->withBatch('BATCH-LAMA', 100, now()->addYears(2)->toDateString())
            ->create();

        // Batch A expired lebih cepat (harusnya dipakai duluan sesuai FEFO)
        $batchDekat = StokObat::factory()->forObat($obat)
            ->withBatch('BATCH-DEKAT', 100, now()->addMonths(3)->toDateString())
            ->create();

        $resep = $this->buatResepDengan1Obat($obat, jumlah: 20);
        $this->service->serahkanObat($resep, $this->penyerah->id);

        // Batch dengan exp lebih dekat harus berkurang, yang lama tidak berubah
        $this->assertEquals(80, $batchDekat->fresh()->jumlah_sisa, 'Batch exp lebih dekat harus dipakai duluan (FEFO)');
        $this->assertEquals(100, $batchLama->fresh()->jumlah_sisa, 'Batch exp lebih lama tidak boleh dipakai kalau yang dekat masih cukup');
    }

    public function test_serahkan_obat_split_ke_multiple_batches_kalau_batch_pertama_tidak_cukup(): void
    {
        $obat = Obat::factory()->create(['harga_jual' => 1000]);

        $batchDekat = StokObat::factory()->forObat($obat)
            ->withBatch('BATCH-DEKAT', 30, now()->addMonths(3)->toDateString())
            ->create();
        $batchLama = StokObat::factory()->forObat($obat)
            ->withBatch('BATCH-LAMA', 100, now()->addYears(1)->toDateString())
            ->create();

        // Butuh 50, batch pertama hanya 30 → harus split: 30 dari BATCH-DEKAT + 20 dari BATCH-LAMA
        $resep = $this->buatResepDengan1Obat($obat, jumlah: 50);
        $this->service->serahkanObat($resep, $this->penyerah->id);

        $this->assertEquals(0, $batchDekat->fresh()->jumlah_sisa, 'Batch pertama harus habis dipakai');
        $this->assertEquals(80, $batchLama->fresh()->jumlah_sisa, 'Sisa 20 unit diambil dari batch berikutnya');

        $detail = $resep->fresh('details')->details->first();
        $this->assertTrue($detail->is_diserahkan);
        $this->assertCount(2, $detail->batch_used, 'batch_used JSON harus mencatat 2 batch yang dipakai');
        $this->assertEquals(['BATCH-DEKAT', 'BATCH-LAMA'], array_column($detail->batch_used, 'batch'));
        $this->assertEquals([30, 20], array_column($detail->batch_used, 'qty'));
    }

    public function test_serahkan_obat_throws_kalau_total_stok_kurang(): void
    {
        $obat = Obat::factory()->create(['harga_jual' => 1000, 'nama' => 'Paracetamol']);
        StokObat::factory()->forObat($obat)->withBatch('B1', 10, now()->addYear()->toDateString())->create();

        $resep = $this->buatResepDengan1Obat($obat, jumlah: 50);

        try {
            $this->service->serahkanObat($resep, $this->penyerah->id);
            $this->fail('Expected DomainException tapi tidak dilempar');
        } catch (DomainException $e) {
            $this->assertStringContainsString('tidak cukup', $e->getMessage());
            $this->assertStringContainsString('Paracetamol', $e->getMessage());
        }

        // Rollback: stok tidak berubah, resep masih BARU, tidak ada mutasi
        $this->assertEquals(10, StokObat::first()->jumlah_sisa, 'Stok harus utuh setelah rollback');
        $this->assertEquals('BARU', $resep->fresh()->status);
        $this->assertEquals(0, MutasiStokObat::count(), 'Tidak boleh ada mutasi yang tercatat');
    }

    public function test_serahkan_obat_skip_batch_yang_sudah_expired(): void
    {
        $obat = Obat::factory()->create();

        // Batch A sudah expired — harus di-skip
        StokObat::factory()->forObat($obat)->expired()->withBatch(
            'BATCH-EXPIRED',
            100,
            now()->subDays(1)->toDateString()
        )->create();

        // Batch B masih valid
        $batchValid = StokObat::factory()->forObat($obat)
            ->withBatch('BATCH-VALID', 50, now()->addMonths(6)->toDateString())
            ->create();

        $resep = $this->buatResepDengan1Obat($obat, jumlah: 30);
        $this->service->serahkanObat($resep, $this->penyerah->id);

        $this->assertEquals(20, $batchValid->fresh()->jumlah_sisa);
        $this->assertEquals(100, StokObat::where('no_batch', 'BATCH-EXPIRED')->value('jumlah_sisa'),
            'Batch expired tidak boleh dipakai');
    }

    public function test_serahkan_obat_skip_batch_kosong(): void
    {
        $obat = Obat::factory()->create();

        StokObat::factory()->forObat($obat)->empty()
            ->withBatch('BATCH-KOSONG', 50, now()->addMonths(3)->toDateString())
            ->create(['jumlah_sisa' => 0]);

        $batchIsi = StokObat::factory()->forObat($obat)
            ->withBatch('BATCH-ISI', 40, now()->addYear()->toDateString())
            ->create();

        $resep = $this->buatResepDengan1Obat($obat, jumlah: 25);
        $this->service->serahkanObat($resep, $this->penyerah->id);

        $this->assertEquals(15, $batchIsi->fresh()->jumlah_sisa);
    }

    public function test_serahkan_obat_mencatat_mutasi_stok_per_batch(): void
    {
        $obat = Obat::factory()->create();
        $batch1 = StokObat::factory()->forObat($obat)
            ->withBatch('B1', 20, now()->addMonths(2)->toDateString())->create();
        $batch2 = StokObat::factory()->forObat($obat)
            ->withBatch('B2', 30, now()->addMonths(8)->toDateString())->create();

        $resep = $this->buatResepDengan1Obat($obat, jumlah: 35);
        $this->service->serahkanObat($resep, $this->penyerah->id);

        $mutasi = MutasiStokObat::orderBy('created_at')->get();
        $this->assertCount(2, $mutasi);

        // Mutasi pertama: 20 dari B1
        $this->assertEquals($batch1->id, $mutasi[0]->stok_id);
        $this->assertEquals(-20, $mutasi[0]->jumlah);
        $this->assertEquals(20, $mutasi[0]->saldo_sebelum);
        $this->assertEquals(0, $mutasi[0]->saldo_sesudah);
        $this->assertEquals('KELUAR', $mutasi[0]->jenis);
        $this->assertEquals($this->penyerah->id, $mutasi[0]->user_id);

        // Mutasi kedua: 15 dari B2
        $this->assertEquals($batch2->id, $mutasi[1]->stok_id);
        $this->assertEquals(-15, $mutasi[1]->jumlah);
        $this->assertEquals(30, $mutasi[1]->saldo_sebelum);
        $this->assertEquals(15, $mutasi[1]->saldo_sesudah);
    }

    public function test_serahkan_obat_update_resep_ke_status_diserahkan(): void
    {
        $obat = Obat::factory()->create();
        StokObat::factory()->forObat($obat)->withBatch('B1', 100, now()->addYear()->toDateString())->create();

        $resep = $this->buatResepDengan1Obat($obat, jumlah: 5);
        $result = $this->service->serahkanObat($resep, $this->penyerah->id);

        $this->assertEquals('DISERAHKAN', $result->status);
        $this->assertEquals($this->penyerah->id, $result->penyerah_id);
        $this->assertNotNull($result->diserahkan_at);
    }

    public function test_serahkan_obat_rollback_kalau_obat_kedua_gagal(): void
    {
        $obatA = Obat::factory()->create(['nama' => 'Obat A']);
        $obatB = Obat::factory()->create(['nama' => 'Obat B']);

        StokObat::factory()->forObat($obatA)->withBatch('BA', 100, now()->addYear()->toDateString())->create();
        StokObat::factory()->forObat($obatB)->withBatch('BB', 5, now()->addYear()->toDateString())->create();

        // Resep butuh 20 obat A (cukup) + 50 obat B (KURANG → harus rollback semuanya)
        $resep = $this->service->buatResep($this->kunjungan->id, $this->dokter->id, [
            ['obat_id' => $obatA->id, 'jumlah' => 20, 'signa' => '3x1'],
            ['obat_id' => $obatB->id, 'jumlah' => 50, 'signa' => '2x1'],
        ]);

        try {
            $this->service->serahkanObat($resep, $this->penyerah->id);
            $this->fail('Expected DomainException');
        } catch (DomainException) {
            // expected
        }

        $this->assertEquals(100, StokObat::where('no_batch', 'BA')->value('jumlah_sisa'),
            'Deduction obat A harus di-rollback karena obat B gagal');
        $this->assertEquals(5, StokObat::where('no_batch', 'BB')->value('jumlah_sisa'));
        $this->assertEquals(0, MutasiStokObat::count(), 'Tidak boleh ada mutasi yang tercatat');
        $this->assertEquals('BARU', $resep->fresh()->status);
    }

    public function test_serahkan_obat_batch_used_menyimpan_exp_date(): void
    {
        $obat = Obat::factory()->create();
        StokObat::factory()->forObat($obat)
            ->withBatch('B1', 100, '2027-06-15')
            ->create();

        $resep = $this->buatResepDengan1Obat($obat, jumlah: 5);
        $this->service->serahkanObat($resep, $this->penyerah->id);

        $detail = $resep->fresh('details')->details->first();
        $this->assertEquals('2027-06-15', $detail->batch_used[0]['exp_date']);
        $this->assertEquals('B1', $detail->batch_used[0]['batch']);
        $this->assertEquals(5, $detail->batch_used[0]['qty']);
    }

    // ============================================================
    // Helpers
    // ============================================================

    private function buatResepDengan1Obat(?Obat $obat = null, int $jumlah = 1): Resep
    {
        $obat ??= Obat::factory()->create();

        return $this->service->buatResep($this->kunjungan->id, $this->dokter->id, [
            ['obat_id' => $obat->id, 'jumlah' => $jumlah, 'signa' => '3x1'],
        ]);
    }
}
