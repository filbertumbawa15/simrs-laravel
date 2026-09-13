<?php

namespace Tests\Feature\Services;

use App\Enums\PrioritasOrder;
use App\Mail\HasilLabKritisMail;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\ParameterLab;
use App\Models\User;
use App\Services\LabService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LabServiceCriticalNotifTest extends TestCase
{
    use RefreshDatabase;

    private LabService $service;
    private Dokter $dpjp;
    private User $analis;
    private User $dokterPk;
    private Kunjungan $kunjungan;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->service = new LabService();
        $this->dpjp = Dokter::factory()->create(['email' => 'dpjp@sihrs.local']);
        $this->analis = User::factory()->create();
        $this->dokterPk = User::factory()->create();
        $this->kunjungan = Kunjungan::factory()->rj()->create();
    }

    public function test_email_kritis_dikirim_ke_dpjp_saat_hasil_flag_hh(): void
    {
        $paramGds = ParameterLab::factory()->create([
            'nama' => 'GDS',
            'nilai_rujukan_min' => 70,
            'nilai_rujukan_max' => 140,
            'nilai_kritis_high' => 400,
        ]);

        $order = $this->service->buatOrder(
            $this->kunjungan->id,
            $this->dpjp->id,
            [$paramGds->id],
            PrioritasOrder::Cito,
        );

        // Input hasil kritis: GDS 550 → flag HH (>= 400)
        $this->service->inputHasil($order, [
            $paramGds->id => ['hasil' => '550'],
        ], $this->analis->id);

        $this->service->validasiHasil($order->fresh(), $this->dokterPk->id);

        Mail::assertQueued(HasilLabKritisMail::class, function ($mail) use ($order) {
            return $mail->hasTo('dpjp@sihrs.local')
                && $mail->order->id === $order->id
                && $mail->hasilKritis->count() === 1;
        });
    }

    public function test_tidak_kirim_email_kalau_semua_hasil_normal(): void
    {
        $param = ParameterLab::factory()->create();

        $order = $this->service->buatOrder($this->kunjungan->id, $this->dpjp->id, [$param->id]);
        $this->service->inputHasil($order, [
            $param->id => ['hasil' => '100'], // normal
        ], $this->analis->id);
        $this->service->validasiHasil($order->fresh(), $this->dokterPk->id);

        Mail::assertNothingQueued();
    }

    public function test_hasil_kritis_di_mark_notified_supaya_idempotent(): void
    {
        $param = ParameterLab::factory()->create(['nilai_kritis_high' => 300]);

        $order = $this->service->buatOrder($this->kunjungan->id, $this->dpjp->id, [$param->id]);
        $this->service->inputHasil($order, [$param->id => ['hasil' => '500']], $this->analis->id);
        $this->service->validasiHasil($order->fresh(), $this->dokterPk->id);

        $hasil = $order->fresh('hasil')->hasil->first();
        $this->assertTrue($hasil->critical_notified);
        $this->assertNotNull($hasil->critical_notified_at);
    }

    public function test_tidak_kirim_email_kalau_dpjp_tidak_punya_email(): void
    {
        $dpjpNoEmail = Dokter::factory()->create(['email' => null]);
        $param = ParameterLab::factory()->create(['nilai_kritis_high' => 300]);

        $order = $this->service->buatOrder($this->kunjungan->id, $dpjpNoEmail->id, [$param->id]);
        $this->service->inputHasil($order, [$param->id => ['hasil' => '500']], $this->analis->id);
        $this->service->validasiHasil($order->fresh(), $this->dokterPk->id);

        Mail::assertNothingQueued();
    }

    public function test_kirim_cc_ke_alamat_di_config(): void
    {
        config(['sihrs.kritis_cc' => ['komdik@sihrs.local']]);

        $param = ParameterLab::factory()->create(['nilai_kritis_high' => 300]);
        $order = $this->service->buatOrder($this->kunjungan->id, $this->dpjp->id, [$param->id]);
        $this->service->inputHasil($order, [$param->id => ['hasil' => '500']], $this->analis->id);
        $this->service->validasiHasil($order->fresh(), $this->dokterPk->id);

        Mail::assertQueued(HasilLabKritisMail::class, function ($mail) {
            return $mail->hasCc('komdik@sihrs.local');
        });
    }

    public function test_hasil_normal_tidak_ditandai_critical_notified(): void
    {
        $param = ParameterLab::factory()->create();
        $order = $this->service->buatOrder($this->kunjungan->id, $this->dpjp->id, [$param->id]);
        $this->service->inputHasil($order, [$param->id => ['hasil' => '100']], $this->analis->id);
        $this->service->validasiHasil($order->fresh(), $this->dokterPk->id);

        $hasil = $order->fresh('hasil')->hasil->first();
        $this->assertFalse($hasil->critical_notified);
    }
}
