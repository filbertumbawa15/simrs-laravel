<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRadiologiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rad.order') ?? false;
    }

    public function rules(): array
    {
        return [
            'kunjungan_id' => ['required', 'uuid', 'exists:kunjungan,id'],
            'dokter_id' => ['required', 'uuid', 'exists:dokter,id'],
            'prioritas' => ['required', Rule::in(['RUTIN', 'CITO'])],
            'klinis' => ['nullable', 'string', 'max:1000'],
            'diagnosa_kerja' => ['nullable', 'string', 'max:500'],
            'hamil' => ['nullable', 'boolean'],
            'persiapan_puasa' => ['nullable', 'boolean'],
            'pemeriksaan_ids' => ['required', 'array', 'min:1'],
            'pemeriksaan_ids.*' => ['uuid', 'exists:pemeriksaan_radiologi,id'],
        ];
    }
}
