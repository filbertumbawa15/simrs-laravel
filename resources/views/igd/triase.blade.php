@extends('layouts.app')

@section('title', 'Triase IGD')
@section('page-header', true)
@section('page-title', 'Triase IGD')
@section('page-subtitle', $kunjungan->pasien->nama.' • Tiba: '.$kunjungan->tgl_masuk->format('H:i').' ('.$kunjungan->tgl_masuk->diffForHumans(null, true).' yang lalu)')

@section('content')

<form method="POST" action="{{ route('igd.triase.store', $kunjungan) }}" class="space-y-6">
    @csrf

    {{-- Kategori triase --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Kategori Triase</h3></div>
        <div class="card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([
                    'MERAH'  => [
                        'icon' => '🔴', 'label' => 'MERAH', 'desc' => 'Resusitasi - Henti napas/jantung, syok, trauma berat',
                        'active_border' => 'peer-checked:border-red-600 peer-checked:ring-2 peer-checked:ring-red-200',
                        'active_bg' => 'peer-checked:bg-red-50'
                    ],
                    'KUNING' => [
                        'icon' => '🟡', 'label' => 'KUNING', 'desc' => 'Emergent - Sesak, nyeri dada, perdarahan aktif',
                        'active_border' => 'peer-checked:border-yellow-500 peer-checked:ring-2 peer-checked:ring-yellow-200',
                        'active_bg' => 'peer-checked:bg-yellow-50'
                    ],
                    'HIJAU'  => [
                        'icon' => '🟢', 'label' => 'HIJAU', 'desc' => 'Urgent - Demam, luka ringan, sakit sedang',
                        'active_border' => 'peer-checked:border-green-600 peer-checked:ring-2 peer-checked:ring-green-200',
                        'active_bg' => 'peer-checked:bg-green-50'
                    ],
                    'HITAM'  => [
                        'icon' => '⚫', 'label' => 'HITAM', 'desc' => 'DOA - Death on Arrival, tidak ada tanda kehidupan',
                        'active_border' => 'peer-checked:border-gray-800 peer-checked:ring-2 peer-checked:ring-gray-200',
                        'active_bg' => 'peer-checked:bg-gray-100'
                    ],
                ] as $val => $cfg)
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="kategori" value="{{ $val }}" required class="sr-only peer">
                        <div class="border-2 border-gray-200 rounded-xl p-4 h-full transition-all duration-200 {{ $cfg['active_border'] }} {{ $cfg['active_bg'] }} group-hover:border-gray-300 shadow-sm">
                            <div class="flex justify-between items-start">
                                <div class="text-3xl">{{ $cfg['icon'] }}</div>
                                <div class="hidden peer-checked:block">
                                    <svg class="w-6 h-6 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="font-bold text-lg mt-2 tracking-wide">{{ $cfg['label'] }}</div>
                            <div class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $cfg['desc'] }}</div>
                        </div>
                        {{-- Overlay ring for focus/selected --}}
                        <div class="absolute inset-0 rounded-xl peer-focus:ring-2 peer-focus:ring-offset-2 peer-focus:ring-blue-500 pointer-events-none"></div>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Keluhan utama --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Keluhan Utama <span class="text-red-500">*</span></h3></div>
        <div class="card-body">
            <textarea name="keluhan_utama" rows="3" required class="textarea"
                      placeholder="Apa yang membawa pasien ke IGD? Onset, durasi, severity…">{{ old('keluhan_utama') }}</textarea>
        </div>
    </div>

    {{-- Tanda vital --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Tanda Vital</h3></div>
        <div class="card-body grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
            <div><label class="label text-xs">TD Sistol</label><input name="tanda_vital[td_sistol]" type="number" class="input" placeholder="mmHg"></div>
            <div><label class="label text-xs">TD Diastol</label><input name="tanda_vital[td_diastol]" type="number" class="input" placeholder="mmHg"></div>
            <div><label class="label text-xs">Nadi</label><input name="tanda_vital[nadi]" type="number" class="input" placeholder="x/m"></div>
            <div><label class="label text-xs">Respirasi</label><input name="tanda_vital[respirasi]" type="number" class="input" placeholder="x/m"></div>
            <div><label class="label text-xs">Suhu</label><input name="tanda_vital[suhu]" type="number" step="0.1" class="input" placeholder="°C"></div>
            <div><label class="label text-xs">SpO₂</label><input name="tanda_vital[spo2]" type="number" class="input" placeholder="%"></div>
            <div class="col-span-2"><label class="label text-xs">GCS (E-M-V)</label><input name="tanda_vital[gcs]" type="text" class="input" placeholder="E4M6V5 = 15"></div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('igd.board') }}" class="btn-secondary">Batal</a>
        <button class="btn-warning btn-lg">🚨 Simpan Triase</button>
    </div>
</form>

@endsection
