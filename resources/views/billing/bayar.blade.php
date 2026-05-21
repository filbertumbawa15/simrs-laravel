@extends('layouts.app')

@section('title', 'Pembayaran')
@section('page-header', true)
@section('page-title', 'Catat Pembayaran')
@section('page-subtitle', 'Tagihan: '.$tagihan->no_tagihan)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Form pembayaran (2/3) --}}
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('billing.bayar', $tagihan) }}" class="space-y-6"
              x-data="{ metode: '{{ old('metode', 'TUNAI') }}', jumlah: {{ old('jumlah', $tagihan->sisa) }} }">
            @csrf

            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Metode Pembayaran</h3></div>
                <div class="card-body">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach ([
                            'TUNAI' => ['label' => 'Tunai', 'icon' => '💵'],
                            'DEBIT' => ['label' => 'Kartu Debit', 'icon' => '💳'],
                            'KREDIT' => ['label' => 'Kartu Kredit', 'icon' => '💳'],
                            'QRIS' => ['label' => 'QRIS', 'icon' => '📱'],
                            'TRANSFER' => ['label' => 'Transfer', 'icon' => '🏦'],
                            'BPJS' => ['label' => 'Klaim BPJS', 'icon' => '🏥'],
                        ] as $val => $cfg)
                            <label class="cursor-pointer">
                                <input type="radio" name="metode" value="{{ $val }}" x-model="metode" class="sr-only peer">
                                <div class="border-2 border-gray-200 rounded-lg p-3 text-center
                                            peer-checked:border-primary-600 peer-checked:bg-primary-50 transition">
                                    <div class="text-2xl">{{ $cfg['icon'] }}</div>
                                    <div class="text-sm font-medium">{{ $cfg['label'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Jumlah & Referensi</h3></div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="label">Jumlah Pembayaran <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" name="jumlah" x-model.number="jumlah" required
                                   min="1" max="{{ $tagihan->sisa }}"
                                   class="input pl-10 text-lg font-semibold">
                        </div>
                        <p class="help">
                            Sisa tagihan: Rp {{ number_format($tagihan->sisa, 0, ',', '.') }}
                            (boleh partial / cicilan)
                        </p>
                    </div>

                    <div x-show="['DEBIT', 'KREDIT', 'QRIS', 'TRANSFER'].includes(metode)">
                        <label class="label">No. Referensi / Approval Code</label>
                        <input name="referensi_eksternal" type="text" value="{{ old('referensi_eksternal') }}"
                               class="input font-mono">
                        <p class="help">Untuk EDC: ambil dari struk approval. Untuk transfer: 4 digit terakhir rekening.</p>
                    </div>

                    <div>
                        <label class="label">Catatan (opsional)</label>
                        <textarea name="catatan" rows="2" class="textarea">{{ old('catatan') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('billing.show', $tagihan) }}" class="btn-secondary">Batal</a>
                <button class="btn-success btn-lg">✓ Simpan Pembayaran</button>
            </div>
        </form>
    </div>

    {{-- Summary tagihan (1/3) --}}
    <div>
        <div class="card sticky top-6">
            <div class="card-header"><h3 class="font-semibold text-sm">Ringkasan Tagihan</h3></div>
            <div class="card-body space-y-2 text-sm">
                <div class="text-xs text-gray-500">Pasien</div>
                <div class="font-semibold">{{ $tagihan->kunjungan->pasien->nama }}</div>
                <div class="text-xs text-gray-500">{{ $tagihan->kunjungan->pasien->no_rm }}</div>

                <div class="border-t pt-3 mt-3 space-y-1.5">
                    <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>Rp {{ number_format($tagihan->subtotal, 0, ',', '.') }}</span></div>
                    @if ((float)$tagihan->diskon > 0)
                        <div class="flex justify-between text-red-600"><span>Diskon</span><span>- Rp {{ number_format($tagihan->diskon, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="flex justify-between font-bold text-base pt-2 border-t">
                        <span>TOTAL</span>
                        <span class="text-primary-700">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-green-700"><span>Dibayar</span><span>Rp {{ number_format($tagihan->dibayar, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between font-bold text-red-700 text-base">
                        <span>SISA</span>
                        <span>Rp {{ number_format($tagihan->sisa, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
