<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKunjunganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kunjungan.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'pasien_id' => ['required', 'uuid', 'exists:pasien,id'],
            'tipe' => ['required', Rule::in(['RJ', 'RI', 'IGD'])],
            'penjamin' => ['required', Rule::in(['UMUM', 'BPJS', 'ASURANSI'])],
            'asuransi_pasien_id' => ['nullable', 'uuid', 'exists:asuransi_pasien,id'],
            'no_rujukan' => ['nullable', 'string', 'max:50'],
            'no_sep' => ['nullable', 'string', 'max:50', 'required_if:penjamin,BPJS'],

            // Untuk langsung assign ke poli (RJ)
            'poli_id' => ['nullable', 'uuid', 'exists:poli,id', 'required_if:tipe,RJ'],
            'dokter_id' => ['nullable', 'uuid', 'exists:dokter,id', 'required_if:tipe,RJ'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_sep.required_if' => 'Nomor SEP wajib diisi untuk pasien BPJS.',
            'poli_id.required_if' => 'Poli wajib dipilih untuk kunjungan rawat jalan.',
            'dokter_id.required_if' => 'Dokter wajib dipilih untuk kunjungan rawat jalan.',
        ];
    }
}
