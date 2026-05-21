@extends('layouts.app')

@section('title', 'Pindah Kamar')
@section('page-header', true)
@section('page-title', 'Pindah Kamar')
@section('page-subtitle', $ri->kunjungan->pasien->nama)

@section('content')

<form method="POST" action="{{ route('ri.pindah.store', $ri) }}" class="space-y-6">
    @csrf

    @php $kamarSaatIni = $ri->kamarInap->whereNull('keluar')->first(); @endphp
    @if ($kamarSaatIni)
        <div class="alert alert-info">
            <strong>Kamar saat ini:</strong> {{ $kamarSaatIni->kamar->no_kamar }} (Kelas {{ $kamarSaatIni->kamar->kelas->nama }})
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Pilih Kamar Tujuan</h3></div>
        <div class="card-body space-y-4">
            @forelse ($kamarTersedia as $kelasNama => $kamarList)
                <div>
                    <h4 class="text-xs uppercase tracking-wider font-semibold text-gray-600 mb-2 border-b pb-1">
                        Kelas {{ $kelasNama }}
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                        @foreach ($kamarList as $k)
                            <label class="cursor-pointer">
                                <input type="radio" name="kamar_baru_id" value="{{ $k->id }}" class="sr-only peer">
                                <div class="border-2 border-gray-200 rounded-lg p-3 text-center
                                            peer-checked:border-primary-600 peer-checked:bg-primary-50">
                                    <div class="font-bold">{{ $k->no_kamar }}</div>
                                    <div class="text-xs text-gray-500">{{ $k->lokasi }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-red-600 py-6">Tidak ada kamar tersedia.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Alasan Pindah</h3></div>
        <div class="card-body">
            <textarea name="alasan" rows="2" class="textarea"
                      placeholder="Permintaan keluarga, upgrade kelas, kondisi medis, dll."></textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('ri.show', $ri) }}" class="btn-secondary">Batal</a>
        <button class="btn-primary btn-lg">↔ Pindahkan</button>
    </div>
</form>

@endsection
