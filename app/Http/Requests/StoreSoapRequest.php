<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rj.examine') ?? false;
    }

    public function rules(): array
    {
        return [
            'subjective' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'edukasi' => ['nullable', 'string'],

            'tanda_vital' => ['nullable', 'array'],
            'tanda_vital.td_sistol' => ['nullable', 'integer', 'between:50,300'],
            'tanda_vital.td_diastol' => ['nullable', 'integer', 'between:30,200'],
            'tanda_vital.nadi' => ['nullable', 'integer', 'between:30,250'],
            'tanda_vital.respirasi' => ['nullable', 'integer', 'between:5,80'],
            'tanda_vital.suhu' => ['nullable', 'numeric', 'between:25,45'],
            'tanda_vital.spo2' => ['nullable', 'integer', 'between:50,100'],
            'tanda_vital.bb' => ['nullable', 'numeric', 'between:0.5,500'],
            'tanda_vital.tb' => ['nullable', 'numeric', 'between:20,250'],

            'diagnosa' => ['nullable', 'array'],
            'diagnosa.*.icd10_kode' => ['required_with:diagnosa.*', 'exists:icd10,kode'],
            'diagnosa.*.tipe' => ['required_with:diagnosa.*', 'in:PRIMER,SEKUNDER,KOMPLIKASI'],
            'diagnosa.*.catatan' => ['nullable', 'string'],
        ];
    }
}
