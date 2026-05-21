@extends('layouts.app')

@section('title', 'Admisi Rawat Inap')
@section('page-header', true)
@section('page-title', 'Admisi Pasien Rawat Inap')
@section('page-subtitle', 'Untuk: '.$kunjungan->pasien->nama)

@section('content')

<form method="POST" action="{{ route('ri.admisi.store') }}" class="space-y-6">
    @csrf
    <input type="hidden" name="kunjungan_id" value="{{ $kunjungan->id }}">

    {{-- DPJP --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Dokter Penanggung Jawab (DPJP)</h3></div>
        <div class="card-body">
            <select name="dpjp_id" required class="select">
                <option value="">— Pilih DPJP —</option>
                @foreach ($dokter as $d)
                    <option value="{{ $d->id }}">{{ $d->nama_lengkap }} ({{ $d->spesialisasi }})</option>
                @endforeach
            </select>
            <p class="help">DPJP bertanggung jawab atas perawatan & resume medis pasien selama dirawat.</p>
        </div>
    </div>

    {{-- Pilih kamar --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Pilih Kamar</h3>
            <p class="text-xs text-gray-500">Hanya kamar yang TERSEDIA & aktif yang ditampilkan</p>
        </div>
        <div class="card-body space-y-4">
            @forelse ($kamarTersedia as $kelasNama => $kamarList)
                <div>
                    <h4 class="text-xs uppercase tracking-wider font-semibold text-gray-600 mb-2 border-b pb-1">
                        Kelas {{ $kelasNama }}
                        <span class="text-primary-600 ml-2">Rp {{ number_format($kamarList->first()->kelas->tarif_per_hari, 0, ',', '.') }}/hari</span>
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                        @foreach ($kamarList as $k)
                            <label class="cursor-pointer">
                                <input type="radio" name="kamar_id" value="{{ $k->id }}" class="sr-only peer">
                                <div class="border-2 border-gray-200 rounded-lg p-3 text-center
                                            peer-checked:border-primary-600 peer-checked:bg-primary-50 transition">
                                    <div class="font-bold">{{ $k->no_kamar }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $k->lokasi }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-red-600 py-6">⚠ Tidak ada kamar tersedia. Hubungi housekeeping atau tunggu.</p>
            @endforelse
        </div>
    </div>

    {{-- Alasan masuk --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Alasan Masuk</h3></div>
        <div class="card-body">
            <textarea name="alasan_masuk" rows="3" required class="textarea"
                      placeholder="Indikasi rawat inap, kondisi klinis saat masuk…">{{ old('alasan_masuk') }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('kunjungan.show', $kunjungan) }}" class="btn-secondary">Batal</a>
        <button class="btn-primary btn-lg">✓ Admisi Pasien</button>
    </div>
</form>

@endsection
