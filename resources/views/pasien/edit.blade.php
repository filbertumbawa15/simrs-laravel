@extends('layouts.app')

@section('title', 'Edit Pasien')
@section('page-header', true)
@section('page-title', 'Edit Pasien — '.$pasien->nama)
@section('page-subtitle', 'No. RM: '.$pasien->no_rm.' (tidak dapat diubah)')

@section('content')

<form method="POST" action="{{ route('pasien.update', $pasien) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Identitas</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="nama" class="label">Nama Lengkap</label>
                <input id="nama" name="nama" type="text" required value="{{ old('nama', $pasien->nama) }}" class="input">
            </div>
            <div>
                <label class="label">NIK</label>
                <input name="nik" type="text" maxlength="16" value="{{ old('nik', $pasien->nik) }}" class="input font-mono">
            </div>
            <div>
                <label class="label">Jenis Kelamin</label>
                <select name="jenis_kelamin" required class="select">
                    <option value="L" @selected(old('jenis_kelamin', $pasien->jenis_kelamin->value) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $pasien->jenis_kelamin->value) === 'P')>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="label">Tempat Lahir</label>
                <input name="tempat_lahir" type="text" value="{{ old('tempat_lahir', $pasien->tempat_lahir) }}" class="input">
            </div>
            <div>
                <label class="label">Tanggal Lahir</label>
                <input name="tgl_lahir" type="date" required
                    value="{{ old('tgl_lahir', $pasien->tgl_lahir->format('Y-m-d')) }}" class="input">
            </div>
            <div>
                <label class="label">Telepon</label>
                <input name="telp" type="tel" value="{{ old('telp', $pasien->telp) }}" class="input">
            </div>
            <div>
                <label class="label">Email</label>
                <input name="email" type="email" value="{{ old('email', $pasien->email) }}" class="input">
            </div>
            <div class="md:col-span-2">
                <label class="label">Alamat</label>
                <textarea name="alamat" rows="2" required class="textarea">{{ old('alamat', $pasien->alamat) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('pasien.show', $pasien) }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary">Simpan Perubahan</button>
    </div>
</form>

@endsection