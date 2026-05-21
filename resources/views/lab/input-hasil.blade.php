@extends('layouts.app')

@section('title', 'Input Hasil Lab')
@section('page-header', true)
@section('page-title', 'Input Hasil — '.$order->no_order)
@section('page-subtitle', 'Pasien: '.$order->kunjungan->pasien->nama)

@section('content')

<form method="POST" action="{{ route('lab.input.store', $order) }}" class="space-y-6">
    @csrf

    <div class="alert alert-info">
        <strong>Petunjuk:</strong> Masukkan hasil per parameter. Flag akan otomatis dihitung
        berdasarkan nilai rujukan. Nilai kritis (LL/HH) akan memicu notifikasi ke dokter perujuk
        setelah validasi.
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Hasil Pemeriksaan</h3>
        </div>
        <div class="card-body space-y-3">
            @foreach ($order->details as $d)
                @php $h = $order->hasil->firstWhere('parameter_id', $d->parameter_id); @endphp
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-4">
                            <div class="font-semibold">{{ $d->parameter->nama }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $d->parameter->kategori }} • Rujukan: {{ $d->parameter->rujukan_normal }}
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="label text-xs">Hasil <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="text" name="hasil[{{ $d->parameter_id }}][hasil]"
                                       value="{{ $h?->hasil }}"
                                       class="input @if ($h && $h->flag->isCritical()) input-error @endif"
                                       placeholder="...">
                                <span class="self-center text-xs text-gray-500 whitespace-nowrap">{{ $d->parameter->satuan }}</span>
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="label text-xs">Catatan</label>
                            <input type="text" name="hasil[{{ $d->parameter_id }}][catatan]"
                                   value="{{ $h?->catatan }}" class="input" placeholder="Opsional...">
                        </div>
                        <div class="md:col-span-1 text-center">
                            @if ($h)
                                <span class="badge badge-{{ $h->flag->color() }} font-bold">{{ $h->flag->value }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('lab.show', $order) }}" class="btn-secondary">← Kembali</a>
        <button type="submit" class="btn-primary btn-lg">💾 Simpan & Kirim ke Validasi</button>
    </div>
</form>

@endsection
