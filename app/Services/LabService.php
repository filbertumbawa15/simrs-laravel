<?php

namespace App\Services;

use App\Enums\FlagHasilLab;
use App\Enums\PrioritasOrder;
use App\Enums\StatusKunjungan;
use App\Enums\StatusOrderLab;
use App\Models\HasilLab;
use App\Models\Kunjungan;
use App\Models\OrderLab;
use App\Models\OrderLabDetail;
use App\Models\ParameterLab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LabService
{
    /**
     * Buat order lab dari dokter. Otomatis snapshot tarif saat order
     * (kalau tarif berubah nanti, billing tetap pakai harga saat order).
     *
     * @param  array  $parameterIds  list UUID parameter_lab
     */
    public function buatOrder(
        string $kunjunganId,
        string $dokterId,
        array $parameterIds,
        PrioritasOrder $prioritas = PrioritasOrder::Rutin,
        ?string $catatanKlinis = null,
        ?string $diagnosaKerja = null,
    ): OrderLab {
        if (empty($parameterIds)) {
            throw new \InvalidArgumentException('Order lab harus berisi minimal 1 parameter.');
        }

        return DB::transaction(function () use (
            $kunjunganId, $dokterId, $parameterIds, $prioritas, $catatanKlinis, $diagnosaKerja
        ) {
            $order = OrderLab::create([
                'no_order' => OrderLab::generateNoOrder(),
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'tgl_order' => now(),
                'prioritas' => $prioritas,
                'status' => StatusOrderLab::Diorder,
                'catatan_klinis' => $catatanKlinis,
                'diagnosa_kerja' => $diagnosaKerja,
            ]);

            // Snapshot tarif per parameter (FIFO)
            $parameters = ParameterLab::whereIn('id', $parameterIds)->get();
            foreach ($parameters as $param) {
                OrderLabDetail::create([
                    'order_id' => $order->id,
                    'parameter_id' => $param->id,
                    'tarif' => $param->tarif,
                ]);
            }

            // Update status kunjungan jika belum ada
            if ($order->kunjungan->status === StatusKunjungan::DalamPemeriksaan) {
                $order->kunjungan->update(['status' => StatusKunjungan::MenungguHasilLab]);
            }

            return $order->load('details.parameter');
        });
    }

    /**
     * Analis lab menandai sampel sudah diambil dari pasien.
     */
    public function tandaiSampelDiambil(OrderLab $order, string $userId): OrderLab
    {
        if ($order->status !== StatusOrderLab::Diorder) {
            throw new \DomainException(
                "Order ini sudah {$order->status->label()}, tidak bisa di-sampling ulang."
            );
        }

        $order->update([
            'status' => StatusOrderLab::SampelDiambil,
            'sampling_oleh' => $userId,
            'sampling_at' => now(),
        ]);

        return $order->fresh();
    }

    /**
     * Mulai proses pemeriksaan (sampel di-load ke analyzer / di-test manual).
     */
    public function mulaiProses(OrderLab $order): OrderLab
    {
        $order->update(['status' => StatusOrderLab::Diproses]);

        return $order->fresh();
    }

    /**
     * Input hasil per parameter (bulk). Auto-flag berdasarkan nilai rujukan.
     *
     * @param  array  $hasilArray  ['parameter_id' => ['hasil' => '...', 'catatan' => '...'], ...]
     */
    public function inputHasil(OrderLab $order, array $hasilArray, string $userId): OrderLab
    {
        return DB::transaction(function () use ($order, $hasilArray, $userId) {
            foreach ($hasilArray as $parameterId => $data) {
                if (empty($data['hasil'])) {
                    continue;
                }

                $parameter = ParameterLab::findOrFail($parameterId);
                $hasilNumerik = is_numeric($data['hasil']) ? (float) $data['hasil'] : null;
                $flag = $this->evaluasiFlag($parameter, $data['hasil'], $hasilNumerik);

                HasilLab::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'parameter_id' => $parameter->id,
                    ],
                    [
                        'hasil' => $data['hasil'],
                        'hasil_numerik' => $hasilNumerik,
                        'satuan' => $parameter->satuan,
                        'nilai_rujukan' => $parameter->rujukan_normal,
                        'flag' => $flag,
                        'catatan' => $data['catatan'] ?? null,
                        'input_oleh' => $userId,
                    ]
                );
            }

            // Update status order → menunggu validasi dokter PK
            if ($order->status === StatusOrderLab::Diproses) {
                $order->update(['status' => StatusOrderLab::Validasi]);
            }

            return $order->fresh('hasil');
        });
    }

    /**
     * Dokter Patologi Klinik (PK) validasi hasil. Setelah ini hasil
     * resmi rilis dan trigger notifikasi nilai kritis.
     */
    public function validasiHasil(OrderLab $order, string $dokterPkId): OrderLab
    {
        return DB::transaction(function () use ($order, $dokterPkId) {
            if ($order->hasil->isEmpty()) {
                throw new \DomainException('Tidak ada hasil yang bisa divalidasi.');
            }

            // Tandai semua hasil sebagai validated
            $order->hasil()->update([
                'validator_id' => $dokterPkId,
                'validated_at' => now(),
            ]);

            $order->update([
                'status' => StatusOrderLab::Selesai,
                'validator_id' => $dokterPkId,
                'validated_at' => now(),
            ]);

            // Notifikasi nilai kritis ke DPJP
            $this->notifyKritis($order);

            // Update status kunjungan kalau semua order lab sudah selesai
            $kunjungan = $order->kunjungan;
            $adaOrderBelum = $kunjungan->orderLab()
                ->whereNotIn('status', [StatusOrderLab::Selesai, StatusOrderLab::Batal])
                ->exists();

            if (! $adaOrderBelum && $kunjungan->status === StatusKunjungan::MenungguHasilLab) {
                $adaResepBelumDiserahkan = $kunjungan->resep()
                    ->where('status', '!=', 'DISERAHKAN')->exists();
                $kunjungan->update([
                    'status' => $adaResepBelumDiserahkan
                        ? StatusKunjungan::MenungguObat
                        : StatusKunjungan::MenungguPembayaran,
                ]);
            }

            return $order->fresh(['hasil.parameter', 'validator']);
        });
    }

    /**
     * Evaluasi flag berdasarkan range rujukan.
     */
    protected function evaluasiFlag(ParameterLab $param, string $hasilRaw, ?float $hasilNumerik): FlagHasilLab
    {
        // Untuk hasil kualitatif (Positif/Negatif/Normal)
        if ($param->tipe_hasil === 'KUALITATIF' || $hasilNumerik === null) {
            $hasilLower = strtolower(trim($hasilRaw));
            if (in_array($hasilLower, ['negatif', 'negative', 'normal', '-'])) {
                return FlagHasilLab::Normal;
            }
            if (str_contains($hasilLower, 'positif') || str_contains($hasilLower, 'positive')
                || str_contains($hasilLower, '+')) {
                return FlagHasilLab::Abnormal;
            }
            return FlagHasilLab::Normal;
        }

        // Numerik → pakai range parameter
        return $param->evaluateFlag($hasilNumerik);
    }

    /**
     * Trigger notifikasi nilai kritis ke DPJP / dokter perujuk.
     * Production: kirim WA, SMS, atau push notif.
     */
    protected function notifyKritis(OrderLab $order): void
    {
        $kritis = $order->hasil()->whereIn('flag', ['LL', 'HH'])->get();

        foreach ($kritis as $hasil) {
            // Tandai sudah dinotifikasi (idempotent)
            if ($hasil->critical_notified) {
                continue;
            }

            $hasil->update([
                'critical_notified' => true,
                'critical_notified_at' => now(),
            ]);

            // Log untuk audit
            Log::channel('audit')->warning('Nilai kritis lab', [
                'order' => $order->no_order,
                'pasien' => $order->kunjungan->pasien->nama,
                'no_rm' => $order->kunjungan->pasien->no_rm,
                'parameter' => $hasil->parameter->nama,
                'hasil' => $hasil->hasil.' '.$hasil->satuan,
                'flag' => $hasil->flag->value,
                'dpjp' => $order->dokter->nama_lengkap,
            ]);

            // TODO production: dispatch job kirim WA/SMS ke dokter
            // NotifyKritisJob::dispatch($hasil)->onQueue('notifications');
        }
    }
}
