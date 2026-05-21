@extends('layouts.app')

@section('title', 'Resep '.$resep->no_resep)
@section('page-header', true)
@section('page-title', 'Resep '.$resep->no_resep)
@section('page-subtitle', $resep->tgl_resep->format('d M Y H:i').' • '.$resep->dokter->nama_lengkap)

@section('page-actions')
    @if ($resep->status === 'BARU')
        @can('farmasi.verify')
            <form method="POST" action="{{ route('resep.verifikasi', $resep) }}" class="inline">
                @csrf
                <button class="btn-warning"
                        onclick="return confirm('Verifikasi resep ini? Pastikan tidak ada kontraindikasi, interaksi obat, atau alergi.')">
                    ✓ Verifikasi
                </button>
            </form>
        @endcan
    @elseif ($resep->status === 'DIVERIFIKASI')
        @can('farmasi.dispense')
            <form method="POST" action="{{ route('resep.serahkan', $resep) }}" class="inline">
                @csrf
                <button class="btn-success"
                        onclick="return confirm('Serahkan obat ke pasien? Stok akan otomatis berkurang (FEFO).')">
                    📦 Serahkan ke Pasien
                </button>
            </form>
        @endcan
    @endif
@endsection

@section('content')

@if ($resep->kunjungan->pasien->rekamMedis?->alergi_obat)
    <div class="alert alert-error">
        <strong>⚠ ALERGI OBAT:</strong> {{ $resep->kunjungan->pasien->rekamMedis->alergi_obat }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="font-semibold">Detail Resep</h3>
                    @php
                        $color = match($resep->status) {
                            'BARU' => 'yellow', 'DIVERIFIKASI' => 'blue',
                            'DISERAHKAN' => 'green', default => 'gray',
                        };
                    @endphp
                </div>
                <span class="badge badge-{{ $color }}">{{ $resep->status }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Obat</th>
                            <th>Signa</th>
                            <th>Aturan Pakai</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resep->details as $d)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $d->obat->nama }}</div>
                                    <div class="text-xs text-gray-500">{{ $d->obat->kekuatan }} • {{ $d->obat->bentuk_sediaan }}</div>
                                    @if ($d->is_diserahkan && $d->batch_used)
                                        <div class="text-xs text-green-600 mt-1">
                                            ✓ Diserahkan dari batch:
                                            @foreach ($d->batch_used as $b)
                                                <span class="font-mono">{{ $b['batch'] }}</span> ({{ $b['qty'] }})
                                                @if (! $loop->last), @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="font-semibold">{{ $d->signa }}</td>
                                <td class="text-xs">{{ $d->aturan_pakai ?: '—' }}</td>
                                <td class="text-right">{{ $d->jumlah }} {{ $d->obat->satuan }}</td>
                                <td class="text-right text-xs">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-right font-semibold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td colspan="5" class="text-right">Total</td>
                            <td class="text-right text-primary-700">Rp {{ number_format($resep->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($resep->catatan)
                <div class="card-footer">
                    <div class="text-xs text-gray-500">Catatan dokter:</div>
                    <p class="text-sm">{{ $resep->catatan }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Pasien</h3></div>
            <div class="card-body text-sm">
                <div class="font-semibold">{{ $resep->kunjungan->pasien->nama }}</div>
                <div class="text-xs text-gray-500">{{ $resep->kunjungan->pasien->no_rm }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $resep->kunjungan->pasien->jenis_kelamin->label() }} • {{ $resep->kunjungan->pasien->umur }} thn
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Timeline Resep</h3></div>
            <div class="card-body text-xs space-y-2">
                <div><span class="text-gray-500">Dibuat:</span> {{ $resep->tgl_resep->format('d M Y H:i') }}</div>
                @if ($resep->verified_at)
                    <div>
                        <span class="text-gray-500">Diverifikasi:</span> {{ $resep->verified_at->format('d M Y H:i') }}
                        @if ($resep->apoteker)<div class="text-gray-500">oleh {{ $resep->apoteker->name }}</div>@endif
                    </div>
                @endif
                @if ($resep->diserahkan_at)
                    <div>
                        <span class="text-gray-500">Diserahkan:</span> {{ $resep->diserahkan_at->format('d M Y H:i') }}
                        @if ($resep->penyerah)<div class="text-gray-500">oleh {{ $resep->penyerah->name }}</div>@endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
