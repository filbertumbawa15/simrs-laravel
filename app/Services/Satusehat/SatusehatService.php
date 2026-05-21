<?php

namespace App\Services\Satusehat;

use App\Models\Diagnosa;
use App\Models\HasilLab;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Resep;

/**
 * High-level wrapper untuk push FHIR resource ke SATUSEHAT.
 *
 * Resource utama yang RS wajib push:
 * - Patient    → registrasi pasien baru (dapat IHS ID)
 * - Encounter  → setiap kunjungan
 * - Condition  → diagnosa (per ICD-10)
 * - Observation → tanda vital, hasil lab
 * - MedicationRequest → resep
 *
 * Catatan: kode obat butuh mapping ke KFA (Kamus Farmasi Alkes Kemenkes),
 * kode lab butuh mapping ke LOINC. Sudah disiapkan field-nya di master.
 */
class SatusehatService
{
    public function __construct(protected SatusehatClient $client) {}

    public static function make(): self
    {
        return new self(SatusehatClient::make());
    }

    /**
     * Registrasi pasien baru ke SATUSEHAT → dapat IHS ID.
     * Wajib: NIK valid 16 digit.
     */
    public function pushPatient(Pasien $pasien): string
    {
        if (! $pasien->nik || strlen($pasien->nik) !== 16) {
            throw new SatusehatException('Pasien tidak punya NIK 16 digit, tidak bisa register SATUSEHAT', 400);
        }

        // Idempotent: kalau sudah ada IHS ID, return existing
        if ($pasien->ihs_id) {
            return $pasien->ihs_id;
        }

        // Cari dulu by NIK
        $existing = $this->client->get('Patient', [
            'identifier' => "https://fhir.kemkes.go.id/id/nik|{$pasien->nik}",
        ]);

        if (($existing['total'] ?? 0) > 0) {
            $ihsId = $existing['entry'][0]['resource']['id'];
            $pasien->update(['ihs_id' => $ihsId]);
            return $ihsId;
        }

        // Belum ada — POST resource Patient
        $body = $this->buildPatientResource($pasien);
        $response = $this->client->post('Patient', $body);

        $ihsId = $response['id'];
        $pasien->update(['ihs_id' => $ihsId]);

        return $ihsId;
    }

    /**
     * Push Encounter (kunjungan) ke SATUSEHAT.
     */
    public function pushEncounter(Kunjungan $kunjungan): string
    {
        $pasien = $kunjungan->pasien;
        if (! $pasien->ihs_id) {
            $this->pushPatient($pasien);
            $pasien->refresh();
        }

        $body = $this->buildEncounterResource($kunjungan);
        $response = $this->client->post('Encounter', $body);

        return $response['id'];
    }

    /**
     * Push Condition (diagnosa) ke SATUSEHAT.
     */
    public function pushCondition(Diagnosa $diagnosa, string $encounterId): string
    {
        $body = [
            'resourceType' => 'Condition',
            'clinicalStatus' => [
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                    'code' => 'active',
                ]],
            ],
            'category' => [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                    'code' => 'encounter-diagnosis',
                ]],
            ]],
            'code' => [
                'coding' => [[
                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                    'code' => $diagnosa->icd10_kode,
                    'display' => $diagnosa->icd10->nama,
                ]],
            ],
            'subject' => [
                'reference' => "Patient/{$diagnosa->kunjungan->pasien->ihs_id}",
            ],
            'encounter' => [
                'reference' => "Encounter/{$encounterId}",
            ],
            'recordedDate' => $diagnosa->created_at->toIso8601String(),
        ];

        $response = $this->client->post('Condition', $body);
        return $response['id'];
    }

    /**
     * Push Observation hasil lab.
     */
    public function pushObservationLab(HasilLab $hasil, string $encounterId): string
    {
        $param = $hasil->parameter;
        $pasien = $hasil->order->kunjungan->pasien;

        $body = [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => 'laboratory',
                ]],
            ]],
            'code' => [
                'coding' => array_filter([
                    $param->loinc_code ? [
                        'system' => 'http://loinc.org',
                        'code' => $param->loinc_code,
                        'display' => $param->nama,
                    ] : null,
                ]),
                'text' => $param->nama,
            ],
            'subject' => ['reference' => "Patient/{$pasien->ihs_id}"],
            'encounter' => ['reference' => "Encounter/{$encounterId}"],
            'effectiveDateTime' => $hasil->validated_at?->toIso8601String(),
            'issued' => $hasil->validated_at?->toIso8601String(),
        ];

        // Hasil numerik vs kualitatif
        if ($hasil->hasil_numerik !== null) {
            $body['valueQuantity'] = [
                'value' => (float) $hasil->hasil_numerik,
                'unit' => $hasil->satuan,
            ];
        } else {
            $body['valueString'] = $hasil->hasil;
        }

        if (in_array($hasil->flag->value, ['L', 'H', 'LL', 'HH'])) {
            $body['interpretation'] = [[
                'coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
                    'code' => $hasil->flag->value,
                ]],
            ]];
        }

        $response = $this->client->post('Observation', $body);
        return $response['id'];
    }

    /**
     * Push MedicationRequest (resep).
     */
    public function pushMedicationRequest(Resep $resep, string $encounterId): array
    {
        $ids = [];
        foreach ($resep->details as $detail) {
            $body = [
                'resourceType' => 'MedicationRequest',
                'status' => 'active',
                'intent' => 'order',
                'medicationCodeableConcept' => [
                    'coding' => array_filter([
                        $detail->obat->kode_kfa ? [
                            'system' => 'http://sys-ids.kemkes.go.id/kfa',
                            'code' => $detail->obat->kode_kfa,
                            'display' => $detail->obat->nama,
                        ] : null,
                    ]),
                    'text' => $detail->obat->nama,
                ],
                'subject' => ['reference' => "Patient/{$resep->kunjungan->pasien->ihs_id}"],
                'encounter' => ['reference' => "Encounter/{$encounterId}"],
                'authoredOn' => $resep->tgl_resep->toIso8601String(),
                'requester' => [
                    'reference' => "Practitioner/{$resep->dokter->ihs_id}",
                    'display' => $resep->dokter->nama_lengkap,
                ],
                'dosageInstruction' => [[
                    'text' => "{$detail->signa} - {$detail->aturan_pakai}",
                ]],
                'dispenseRequest' => [
                    'quantity' => [
                        'value' => $detail->jumlah,
                        'unit' => $detail->obat->satuan,
                    ],
                ],
            ];

            $response = $this->client->post('MedicationRequest', $body);
            $ids[] = $response['id'];
        }

        return $ids;
    }

    // ============================================================
    // Resource builders
    // ============================================================

    protected function buildPatientResource(Pasien $p): array
    {
        return [
            'resourceType' => 'Patient',
            'identifier' => [
                [
                    'use' => 'official',
                    'system' => 'https://fhir.kemkes.go.id/id/nik',
                    'value' => $p->nik,
                ],
            ],
            'active' => true,
            'name' => [['use' => 'official', 'text' => $p->nama]],
            'telecom' => array_values(array_filter([
                $p->telp ? ['system' => 'phone', 'value' => $p->telp, 'use' => 'mobile'] : null,
                $p->email ? ['system' => 'email', 'value' => $p->email] : null,
            ])),
            'gender' => $p->jenis_kelamin->value === 'L' ? 'male' : 'female',
            'birthDate' => $p->tgl_lahir->format('Y-m-d'),
            'address' => [[
                'use' => 'home',
                'line' => [$p->alamat],
                'city' => $p->kabupaten,
                'postalCode' => $p->kode_pos,
                'country' => 'ID',
            ]],
        ];
    }

    protected function buildEncounterResource(Kunjungan $k): array
    {
        $class = $k->tipe->value === 'RJ' ? 'AMB' : ($k->tipe->value === 'IGD' ? 'EMER' : 'IMP');

        return [
            'resourceType' => 'Encounter',
            'status' => $k->tgl_keluar ? 'finished' : 'in-progress',
            'class' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => $class,
                'display' => match($class) { 'AMB' => 'ambulatory', 'EMER' => 'emergency', 'IMP' => 'inpatient' },
            ],
            'subject' => [
                'reference' => "Patient/{$k->pasien->ihs_id}",
                'display' => $k->pasien->nama,
            ],
            'period' => array_filter([
                'start' => $k->tgl_masuk->toIso8601String(),
                'end' => $k->tgl_keluar?->toIso8601String(),
            ]),
            'serviceProvider' => [
                'reference' => "Organization/{$this->client->organizationId()}",
            ],
            'identifier' => [[
                'system' => 'http://sys-ids.kemkes.go.id/encounter/'.$this->client->organizationId(),
                'value' => $k->no_kunjungan,
            ]],
        ];
    }
}
