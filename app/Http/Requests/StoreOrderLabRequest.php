<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderLabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lab.order') ?? false;
    }

    public function rules(): array
    {
        return [
            'kunjungan_id' => ['required', 'uuid', 'exists:kunjungan,id'],
            'dokter_id' => ['required', 'uuid', 'exists:dokter,id'],
            'prioritas' => ['required', Rule::in(['RUTIN', 'CITO'])],
            'catatan_klinis' => ['nullable', 'string', 'max:1000'],
            'diagnosa_kerja' => ['nullable', 'string', 'max:500'],
            'parameter_ids' => ['required', 'array', 'min:1'],
            'parameter_ids.*' => ['uuid', 'exists:parameter_lab,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'parameter_ids.required' => 'Pilih minimal 1 parameter pemeriksaan.',
            'parameter_ids.min' => 'Pilih minimal 1 parameter pemeriksaan.',
        ];
    }
}
