@extends('layouts.app')

@section('title', 'Pendaftaran Kunjungan')
@section('page-header', true)
@section('page-title', 'Pendaftaran Kunjungan')
@section('page-subtitle', $pasien ? 'Untuk pasien: '.$pasien->nama.' ('.$pasien->no_rm.')' : 'Pilih pasien dulu')

@section('content')

@if (! $pasien)
<div class="alert alert-warning">
    Silakan pilih pasien dulu dari <a href="{{ route('pasien.index') }}" class="underline">daftar pasien</a>.
</div>
@else

<form method="POST" action="{{ route('kunjungan.store') }}" class="space-y-6"
    x-data="{ tipe: '{{ old('tipe', 'RJ') }}' }">
    @csrf
    <input type="hidden" name="pasien_id" value="{{ $pasien->id }}">

    {{-- Ringkas data pasien --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-lg font-semibold">
                    {{ strtoupper(substr($pasien->nama, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-gray-900">{{ $pasien->nama }}</div>
                    <div class="text-sm text-gray-500">
                        {{ $pasien->no_rm }} • {{ $pasien->jenis_kelamin->label() }} • {{ $pasien->umur }} tahun
                    </div>
                </div>
                @if ($pasien->rekamMedis?->alergi_obat)
                <div class="badge badge-red">⚠ Alergi: {{ $pasien->rekamMedis->alergi_obat }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tipe & Penjamin --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Tipe & Penjamin</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Tipe Kunjungan <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                    'RJ' => ['label' => 'Rawat Jalan', 'color' => 'blue'],
                    'IGD' => ['label' => 'IGD', 'color' => 'red'],
                    'RI' => ['label' => 'Rawat Inap', 'color' => 'purple'],
                    ] as $val => $cfg)
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe" value="{{ $val }}" x-model="tipe" class="sr-only peer">
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center
                                        peer-checked:border-primary-600 peer-checked:bg-primary-50 transition">
                            <div class="font-semibold">{{ $cfg['label'] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="penjamin" class="label">Penjamin <span class="text-red-500">*</span></label>
                <select id="penjamin" name="penjamin" required class="select">
                    <option value="UMUM" @selected(old('penjamin')==='UMUM' )>Umum</option>
                    <option value="BPJS" @selected(old('penjamin', 'BPJS' )==='BPJS' )>BPJS Kesehatan</option>
                    <option value="ASURANSI" @selected(old('penjamin')==='ASURANSI' )>Asuransi Swasta</option>
                </select>
            </div>

            <div x-show="document.getElementById('penjamin').value === 'BPJS'">
                <label class="label">No. SEP <span class="text-red-500">*</span></label>
                <input name="no_sep" type="text" value="{{ old('no_sep') }}" class="input font-mono"
                    placeholder="Generate dari V-Claim BPJS">
                <p class="help">Wajib diisi untuk pasien BPJS. Diperoleh dari aplikasi V-Claim.</p>
            </div>

            <div>
                <label class="label">No. Rujukan (opsional)</label>
                <input name="no_rujukan" type="text" value="{{ old('no_rujukan') }}" class="input">
            </div>
        </div>
    </div>

    {{-- Pilih Poli (RJ only) --}}
    <div class="card" x-show="tipe === 'RJ'" x-transition>
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Tujuan Poli</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Poli <span class="text-red-500">*</span></label>
                <select name="poli_id" class="select">
                    <option value="">— Pilih poli —</option>
                    @foreach ($poli as $p)
                    <option value="{{ $p->id }}" @selected(old('poli_id')===$p->id)>
                        {{ $p->nama }} ({{ $p->lokasi }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Dokter <span class="text-red-500">*</span></label>
                <select name="dokter_id" class="select">
                    <option value="">— Pilih dokter —</option>
                    @foreach ($dokter as $d)
                    <option value="{{ $d->id }}" @selected(old('dokter_id')===$d->id)>
                        {{ $d->nama_lengkap }} ({{ $d->spesialisasi }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('pasien.show', $pasien) }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary btn-lg">Daftarkan Kunjungan</button>
    </div>
</form>

@endif
@endsection