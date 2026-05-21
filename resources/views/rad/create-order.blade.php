@extends('layouts.app')

@section('title', 'Order Radiologi')
@section('page-header', true)
@section('page-title', 'Buat Order Radiologi')
@section('page-subtitle', 'Untuk: '.$kunjungan->pasien->nama)

@section('content')

<form method="POST" action="{{ route('rad.store') }}" class="space-y-6"
      x-data="{ selected: [], total: 0, hamil: false }">
    @csrf
    <input type="hidden" name="kunjungan_id" value="{{ $kunjungan->id }}">
    <input type="hidden" name="dokter_id" value="{{ $kunjungan->rawatJalan?->dokter_id ?? '' }}">

    {{-- Warning hamil --}}
    @if ($kunjungan->pasien->jenis_kelamin->value === 'P' && $kunjungan->pasien->umur >= 12 && $kunjungan->pasien->umur <= 55)
        <div class="alert alert-warning">
            <strong>⚠ Pasien wanita usia subur</strong> — wajib konfirmasi status kehamilan sebelum X-ray/CT
            (radiasi berbahaya untuk janin). Centang "Pasien hamil" di bawah jika positif.
        </div>
    @endif

    {{-- Detail order --}}
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
                                        peer-checked:border-primary-600 peer-checked:bg-primary-50">{{ $label }}</div>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="label">Diagnosa Kerja</label>
                <input name="diagnosa_kerja" type="text" class="input" placeholder="Misal: Suspek pneumonia">
            </div>
            <div class="md:col-span-2">
                <label class="label">Keterangan Klinis untuk Radiolog</label>
                <textarea name="klinis" rows="2" class="textarea"
                          placeholder="Apa yang ingin dicari/dieksklusi? Tanda klinis, lokasi keluhan…"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="hamil" value="1" x-model="hamil"
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span class="text-sm">⚠ <strong>Pasien sedang hamil</strong> (kontraindikasi X-ray)</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="persiapan_puasa" value="1"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm">Perlu persiapan puasa</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Pilih pemeriksaan --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Pilih Pemeriksaan</h3>
            <div class="text-sm">
                <span x-text="selected.length"></span> dipilih ·
                Estimasi: Rp <span x-text="total.toLocaleString('id-ID')"></span>
            </div>
        </div>
        <div class="card-body space-y-4">
            @forelse ($pemeriksaans as $modalitas => $items)
                <div :class="hamil && '{{ $modalitas }}' !== 'USG' ? 'opacity-40 pointer-events-none' : ''">
                    <h4 class="text-xs uppercase tracking-wider font-semibold text-gray-600 mb-2 border-b pb-1 flex items-center justify-between">
                        <span>{{ \App\Enums\Modalitas::from($modalitas)->label() }}</span>
                        @if($modalitas !== 'USG')
                            <span class="text-red-500 text-[10px]" x-show="hamil">Tidak untuk ibu hamil</span>
                        @endif
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($items as $p)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="pemeriksaan_ids[]" value="{{ $p->id }}"
                                       @change="
                                           if ($event.target.checked) { selected.push($event.target.value); total += {{ $p->tarif_kelas3 }}; }
                                           else { selected = selected.filter(id => id !== $event.target.value); total -= {{ $p->tarif_kelas3 }}; }
                                       "
                                       class="peer sr-only">
                                <div class="border border-gray-200 rounded-lg p-2 hover:border-primary-300
                                            peer-checked:border-primary-600 peer-checked:bg-primary-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium">{{ $p->nama }}</div>
                                            <div class="text-xs text-gray-500">{{ $p->region }}</div>
                                        </div>
                                        <div class="text-xs text-gray-600">Rp {{ number_format($p->tarif_kelas3, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-400 py-6">Belum ada master pemeriksaan. Seed terlebih dahulu.</p>
            @endforelse
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('kunjungan.show', $kunjungan) }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary btn-lg" :disabled="selected.length === 0">
            Kirim Order ke Radiologi
        </button>
    </div>
</form>

@endsection
