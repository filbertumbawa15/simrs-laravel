@extends('layouts.app')

@section('title', 'Pasien Baru')
@section('page-header', true)
@section('page-title', 'Pendaftaran Pasien Baru')
@section('page-subtitle', 'Nomor RM akan di-generate otomatis')

@section('content')

<form method="POST" action="{{ route('pasien.store') }}" class="space-y-6">
    @csrf

    {{-- Identitas --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Identitas Pasien</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="nama" class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                <input id="nama" name="nama" type="text" required value="{{ old('nama') }}"
                    class="input @error('nama') input-error @enderror">
                @error('nama') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nik" class="label">NIK <span class="text-xs text-gray-400">(16 digit)</span></label>
                <input id="nik" name="nik" type="text" maxlength="16" value="{{ old('nik') }}"
                    class="input font-mono @error('nik') input-error @enderror">
                @error('nik') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="jenis_kelamin" class="label">Jenis Kelamin <span class="text-red-500">*</span></label>
                <select id="jenis_kelamin" name="jenis_kelamin" required class="select">
                    <option value="">— Pilih —</option>
                    <option value="L" @selected(old('jenis_kelamin')==='L' )>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin')==='P' )>Perempuan</option>
                </select>
            </div>

            <div>
                <label for="tempat_lahir" class="label">Tempat Lahir</label>
                <input id="tempat_lahir" name="tempat_lahir" type="text" value="{{ old('tempat_lahir') }}" class="input">
            </div>

            <div>
                <label for="tgl_lahir" class="label">Tanggal Lahir <span class="text-red-500">*</span></label>
                <input id="tgl_lahir" name="tgl_lahir" type="date" required value="{{ old('tgl_lahir') }}"
                    max="{{ now()->format('Y-m-d') }}"
                    class="input @error('tgl_lahir') input-error @enderror">
                @error('tgl_lahir') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="gol_darah" class="label">Golongan Darah</label>
                <select id="gol_darah" name="gol_darah" class="select">
                    <option value="">— Tidak diketahui —</option>
                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gd)
                    <option value="{{ $gd }}" @selected(old('gol_darah')===$gd)>{{ $gd }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status_pernikahan" class="label">Status Pernikahan</label>
                <select id="status_pernikahan" name="status_pernikahan" class="select">
                    <option value="">—</option>
                    <option value="BELUM_KAWIN" @selected(old('status_pernikahan')==='BELUM_KAWIN' )>Belum Kawin</option>
                    <option value="KAWIN" @selected(old('status_pernikahan')==='KAWIN' )>Kawin</option>
                    <option value="CERAI_HIDUP" @selected(old('status_pernikahan')==='CERAI_HIDUP' )>Cerai Hidup</option>
                    <option value="CERAI_MATI" @selected(old('status_pernikahan')==='CERAI_MATI' )>Cerai Mati</option>
                </select>
            </div>

            <div>
                <label for="agama" class="label">Agama</label>
                <select id="agama" name="agama" class="select">
                    <option value="">—</option>
                    @foreach (['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $ag)
                    <option value="{{ $ag }}" @selected(old('agama')===$ag)>{{ $ag }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="pekerjaan" class="label">Pekerjaan</label>
                <input id="pekerjaan" name="pekerjaan" type="text" value="{{ old('pekerjaan') }}" class="input">
            </div>
        </div>
    </div>

    {{-- Kontak & Alamat --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Kontak & Alamat</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="telp" class="label">No. Telepon / HP</label>
                <input id="telp" name="telp" type="tel" value="{{ old('telp') }}" class="input">
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    class="input @error('email') input-error @enderror">
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="alamat" class="label">Alamat <span class="text-red-500">*</span></label>
                <textarea id="alamat" name="alamat" rows="2" required
                    class="textarea @error('alamat') input-error @enderror">{{ old('alamat') }}</textarea>
                @error('alamat') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="rt" class="label">RT</label>
                    <input id="rt" name="rt" type="text" maxlength="3" value="{{ old('rt') }}" class="input">
                </div>
                <div>
                    <label for="rw" class="label">RW</label>
                    <input id="rw" name="rw" type="text" maxlength="3" value="{{ old('rw') }}" class="input">
                </div>
            </div>

            <div>
                <label for="kelurahan" class="label">Kelurahan / Desa</label>
                <input id="kelurahan" name="kelurahan" type="text" value="{{ old('kelurahan') }}" class="input">
            </div>

            <div>
                <label for="kecamatan" class="label">Kecamatan</label>
                <input id="kecamatan" name="kecamatan" type="text" value="{{ old('kecamatan') }}" class="input">
            </div>

            <div>
                <label for="kabupaten" class="label">Kabupaten / Kota</label>
                <input id="kabupaten" name="kabupaten" type="text" value="{{ old('kabupaten', 'Medan') }}" class="input">
            </div>

            <div>
                <label for="provinsi" class="label">Provinsi</label>
                <input id="provinsi" name="provinsi" type="text" value="{{ old('provinsi', 'Sumatera Utara') }}" class="input">
            </div>

            <div>
                <label for="kode_pos" class="label">Kode Pos</label>
                <input id="kode_pos" name="kode_pos" type="text" maxlength="5" value="{{ old('kode_pos') }}" class="input">
            </div>
        </div>
    </div>

    {{-- Kontak Darurat --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Kontak Darurat</h3>
            <p class="text-xs text-gray-500">Untuk notifikasi keluarga jika diperlukan</p>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="kontak_darurat_nama" class="label">Nama</label>
                <input id="kontak_darurat_nama" name="kontak_darurat_nama" type="text"
                    value="{{ old('kontak_darurat_nama') }}" class="input">
            </div>
            <div>
                <label for="kontak_darurat_hubungan" class="label">Hubungan</label>
                <select id="kontak_darurat_hubungan" name="kontak_darurat_hubungan" class="select">
                    <option value="">—</option>
                    @foreach (['Suami', 'Istri', 'Anak', 'Orang Tua', 'Saudara', 'Lainnya'] as $hub)
                    <option value="{{ $hub }}" @selected(old('kontak_darurat_hubungan')===$hub)>{{ $hub }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kontak_darurat_telp" class="label">No. Telepon</label>
                <input id="kontak_darurat_telp" name="kontak_darurat_telp" type="tel"
                    value="{{ old('kontak_darurat_telp') }}" class="input">
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('pasien.index') }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary btn-lg">
            Simpan & Buat Rekam Medis
        </button>
    </div>
</form>

@endsection