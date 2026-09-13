<?php

namespace Tests\Feature\Services;

use App\Enums\Penjamin;
use App\Enums\StatusKunjungan;
use App\Enums\TipeKunjungan;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\RekamMedis;
use App\Services\PendaftaranService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendaftaranServiceTest extends TestCase
{
    use RefreshDatabase;

    private PendaftaranService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PendaftaranService();
    }

    // ============================================================
    // daftarPasienBaru
    // ============================================================

    public function test_daftar_pasien_baru_generate_no_rm_pertama_100001(): void
    {
        $pasien = $this->service->daftarPasienBaru($this->dataPasien());

        $this->assertEquals('100001', $pasien->no_rm);
    }

    public function test_daftar_pasien_baru_no_rm_sequential(): void
    {
        $p1 = $this->service->daftarPasienBaru($this->dataPasien(['nama' => 'A']));
        $p2 = $this->service->daftarPasienBaru($this->dataPasien(['nama' => 'B']));
        $p3 = $this->service->daftarPasienBaru($this->dataPasien(['nama' => 'C']));

        $this->assertEquals('100001', $p1->no_rm);
        $this->assertEquals('100002', $p2->no_rm);
        $this->assertEquals('100003', $p3->no_rm);
    }

    public function test_daftar_pasien_baru_no_rm_lanjut_dari_pasien_existing(): void
    {
        // Simulasi ada pasien lama dengan no_rm 100050
        Pasien::factory()->create(['no_rm' => '100050']);

        $baru = $this->service->daftarPasienBaru($this->dataPasien());

        $this->assertEquals('100051', $baru->no_rm);
    }

    public function test_daftar_pasien_baru_no_rm_selalu_6_digit_padded(): void
    {
        $pasien = $this->service->daftarPasienBaru($this->dataPasien());

        $this->assertEquals(6, strlen($pasien->no_rm));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $pasien->no_rm);
    }

    public function test_daftar_pasien_baru_auto_create_rekam_medis(): void
    {
        $pasien = $this->service->daftarPasienBaru($this->dataPasien());

        $rm = RekamMedis::where('pasien_id', $pasien->id)->first();
        $this->assertNotNull($rm, 'Rekam medis harus otomatis dibuat 1:1 dengan pasien');
    }

    public function test_daftar_pasien_baru_hanya_1_rekam_medis_per_pasien(): void
    {
        $pasien = $this->service->daftarPasienBaru($this->dataPasien());

        $count = RekamMedis::where('pasien_id', $pasien->id)->count();
        $this->assertEquals(1, $count);
    }

    public function test_daftar_pasien_baru_simpan_data_demografis(): void
    {
        $pasien = $this->service->daftarPasienBaru($this->dataPasien([
            'nama' => 'Budi Santoso',
            'nik' => '1271010101010001',
            'jenis_kelamin' => 'L',
        ]));

        $this->assertEquals('Budi Santoso', $pasien->nama);
        $this->assertEquals('1271010101010001', $pasien->nik);
    }

    // ============================================================
    // buatKunjungan
    // ============================================================

    public function test_buat_kunjungan_generate_no_kunjungan_format_yang_benar(): void
    {
        $pasien = $this->service->daftarPasienBaru($this->dataPasien());
        $prefix = 'RJ/' . now()->format('Y') . '/' . now()->format('m');

        $kunjungan = $this->service->buatKunjungan($pasien, [
            'tipe' => 'RJ',
        ]);

        $this->assertStringStartsWith($prefix . '/', $kunjungan->no_kunjungan);
        $this->assertMatchesRegularExpression("#^{$prefix}/\d{5}$#", $kunjungan->no_kunjungan);
    }

    public function test_buat_kunjungan_prefix_sesuai_tipe(): void
    {
        $pasien = Pasien::factory()->create();
        $pasien2 = Pasien::factory()->create();
        $pasien3 = Pasien::factory()->create();

        $rj = $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);
        $ri = $this->service->buatKunjungan($pasien2, ['tipe' => 'RI']);
        $igd = $this->service->buatKunjungan($pasien3, ['tipe' => 'IGD']);

        $this->assertStringStartsWith('RJ/', $rj->no_kunjungan);
        $this->assertStringStartsWith('RI/', $ri->no_kunjungan);
        $this->assertStringStartsWith('IGD/', $igd->no_kunjungan);
    }

    public function test_buat_kunjungan_sequential_dalam_bulan_yang_sama(): void
    {
        $prefix = 'RJ/' . now()->format('Y') . '/' . now()->format('m');

        $k1 = $this->service->buatKunjungan(Pasien::factory()->create(), ['tipe' => 'RJ']);
        $k2 = $this->service->buatKunjungan(Pasien::factory()->create(), ['tipe' => 'RJ']);
        $k3 = $this->service->buatKunjungan(Pasien::factory()->create(), ['tipe' => 'RJ']);

        $this->assertEquals("{$prefix}/00001", $k1->no_kunjungan);
        $this->assertEquals("{$prefix}/00002", $k2->no_kunjungan);
        $this->assertEquals("{$prefix}/00003", $k3->no_kunjungan);
    }

    public function test_buat_kunjungan_default_status_terdaftar(): void
    {
        $pasien = Pasien::factory()->create();
        $kunjungan = $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);

        $this->assertEquals(StatusKunjungan::Terdaftar, $kunjungan->status);
    }

    public function test_buat_kunjungan_default_penjamin_umum(): void
    {
        $pasien = Pasien::factory()->create();
        $kunjungan = $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);

        $this->assertEquals(Penjamin::Umum, $kunjungan->penjamin);
    }

    public function test_buat_kunjungan_penjamin_bpjs_dengan_no_sep(): void
    {
        $pasien = Pasien::factory()->create();
        $kunjungan = $this->service->buatKunjungan($pasien, [
            'tipe' => 'RJ',
            'penjamin' => 'BPJS',
            'no_sep' => 'SEP-001-2026',
        ]);

        $this->assertEquals(Penjamin::BPJS, $kunjungan->penjamin);
        $this->assertEquals('SEP-001-2026', $kunjungan->no_sep);
    }

    public function test_buat_kunjungan_tipe_enum_di_cast_dengan_benar(): void
    {
        $pasien = Pasien::factory()->create();
        $kunjungan = $this->service->buatKunjungan($pasien, ['tipe' => 'IGD']);

        $this->assertEquals(TipeKunjungan::IGD, $kunjungan->tipe);
    }

    // ============================================================
    // Guard: kunjungan aktif duplicate
    // ============================================================

    public function test_buat_kunjungan_ditolak_kalau_pasien_masih_ada_kunjungan_aktif(): void
    {
        $pasien = Pasien::factory()->create();
        $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('kunjungan aktif');

        $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);
    }

    public function test_buat_kunjungan_bisa_kalau_kunjungan_sebelumnya_sudah_selesai(): void
    {
        $pasien = Pasien::factory()->create();
        $k1 = $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);
        $k1->update(['status' => StatusKunjungan::Selesai, 'tgl_keluar' => now()]);

        $k2 = $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);

        $this->assertNotNull($k2);
        $this->assertNotEquals($k1->no_kunjungan, $k2->no_kunjungan);
    }

    public function test_buat_kunjungan_bisa_kalau_kunjungan_sebelumnya_batal(): void
    {
        $pasien = Pasien::factory()->create();
        $k1 = $this->service->buatKunjungan($pasien, ['tipe' => 'RJ']);
        $k1->update(['status' => StatusKunjungan::Batal]);

        $k2 = $this->service->buatKunjungan($pasien, ['tipe' => 'IGD']);

        $this->assertNotNull($k2);
    }

    public function test_buat_kunjungan_pasien_lain_tidak_terpengaruh(): void
    {
        $p1 = Pasien::factory()->create();
        $p2 = Pasien::factory()->create();

        $this->service->buatKunjungan($p1, ['tipe' => 'RJ']);
        $k2 = $this->service->buatKunjungan($p2, ['tipe' => 'RJ']); // pasien lain, harus lolos

        $this->assertNotNull($k2);
        $this->assertEquals(1, Kunjungan::where('pasien_id', $p2->id)->count());
    }

    // ============================================================
    // Helpers
    // ============================================================

    private function dataPasien(array $override = []): array
    {
        return array_merge([
            'nik' => '1271' . fake()->unique()->numerify('############'),
            'nama' => 'Test Pasien',
            'tempat_lahir' => 'Medan',
            'tgl_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test No. 1',
        ], $override);
    }
}
