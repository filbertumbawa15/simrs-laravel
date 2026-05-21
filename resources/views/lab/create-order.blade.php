@extends('layouts.app')

@section('title', 'Order Lab')
@section('page-header', true)
@section('page-title', 'Buat Order Lab')
@section('page-subtitle', 'Untuk: '.$kunjungan->pasien->nama)

@section('content')

<form method="POST" action="{{ route('lab.store') }}" class="space-y-6"
      x-data="{ selected: [], total: 0 }">
    @csrf
    <input type="hidden" name="kunjungan_id" value="{{ $kunjungan->id }}">
    <input type="hidden" name="dokter_id" value="{{ $kunjungan->rawatJalan?->dokter_id ?? '' }}">

    {{-- Prioritas & catatan --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Detail Order</h3></div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Prioritas <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach (['RUTIN' => 'Rutin', 'CITO' => '⚡ CITO'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="prioritas" value="{{ $val }}"
                                   {{ $val === 'RUTIN' ? 'checked' : '' }} class="sr-only peer">
                            <div class="border-2 border-gray-200 rounded-lg p-2 text-center
                                        peer-checked:border-primary-600 peer-checked:bg-primary-50">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="label">Diagnosa Kerja</label>
                <input name="diagnosa_kerja" type="text" class="input" placeholder="Misal: Suspek DBD">
            </div>
            <div class="md:col-span-2">
                <label class="label">Catatan Klinis</label>
                <textarea name="catatan_klinis" rows="2" class="textarea"
                          placeholder="Info untuk analis lab — misal: pasien puasa, kondisi sampel khusus, dll."></textarea>
            </div>
        </div>
    </div>

    {{-- Pilih parameter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Parameter Pemeriksaan</h3>
            <div class="text-sm">
                <span x-text="selected.length"></span> parameter dipilih •
                Estimasi: Rp <span x-text="total.toLocaleString('id-ID')"></span>
            </div>
        </div>
        <div class="card-body space-y-4">
            @foreach ($parameters as $kategori => $params)
                <div>
                    <h4 class="text-xs uppercase tracking-wider font-semibold text-gray-600 mb-2 border-b pb-1">
                        {{ $kategori }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($params as $p)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="parameter_ids[]" value="{{ $p->id }}"
                                       data-tarif="{{ $p->tarif }}"
                                       @change="
                                           if ($event.target.checked) {
                                               selected.push($event.target.value);
                                               total += {{ $p->tarif }};
                                           } else {
                                               selected = selected.filter(id => id !== $event.target.value);
                                               total -= {{ $p->tarif }};
                                           }
                                       "
                                       class="peer sr-only">
                                <div class="border border-gray-200 rounded-lg p-2 hover:border-primary-300
                                            peer-checked:border-primary-600 peer-checked:bg-primary-50 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium">{{ $p->nama }}</div>
                                            <div class="text-xs text-gray-500">{{ $p->satuan }}</div>
                                        </div>
                                        <div class="text-xs text-gray-600">Rp {{ number_format($p->tarif, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('kunjungan.show', $kunjungan) }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary btn-lg" :disabled="selected.length === 0">
            Kirim Order ke Lab
        </button>
    </div>
</form>

@endsection
