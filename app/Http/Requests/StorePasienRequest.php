<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePasienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pasien.create') ?? false;
    }

    public function rules(): array
    {
        $pasienId = $this->route('pasien')?->id;

        return [
            'nik' => ['nullable', 'digits:16', Rule::unique('pasien', 'nik')->ignore($pasienId)->whereNull('deleted_at')],
            'nama' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tgl_lahir' => ['required', 'date', 'before:tomorrow'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'status_pernikahan' => ['nullable', Rule::in(['BELUM_KAWIN', 'KAWIN', 'CERAI_HIDUP', 'CERAI_MATI'])],
            'agama' => ['nullable', 'string', 'max:30'],
            'pendidikan' => ['nullable', 'string', 'max:30'],
            'pekerjaan' => ['nullable', 'string', 'max:50'],
            'alamat' => ['required', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'kelurahan' => ['nullable', 'string', 'max:50'],
            'kecamatan' => ['nullable', 'string', 'max:50'],
            'kabupaten' => ['nullable', 'string', 'max:50'],
            'provinsi' => ['nullable', 'string', 'max:50'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'telp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gol_darah' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'kontak_darurat_nama' => ['nullable', 'string', 'max:255'],
            'kontak_darurat_hubungan' => ['nullable', 'string', 'max:30'],
            'kontak_darurat_telp' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar atas nama pasien lain.',
            'tgl_lahir.before' => 'Tanggal lahir tidak boleh di masa depan.',
        ];
    }
}
