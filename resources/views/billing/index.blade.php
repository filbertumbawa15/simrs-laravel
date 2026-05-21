@extends('layouts.app')

@section('title', 'Billing')
@section('page-header', true)
@section('page-title', 'Daftar Tagihan')
@section('page-subtitle', 'Manajemen pembayaran & klaim')

@section('content')

<div class="card">
    <div class="card-body border-b border-gray-200">
        <form method="GET" class="flex gap-3">
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="input w-auto">
            <select name="status" class="select w-auto">
                <option value="">Semua status</option>
                @foreach (\App\Enums\StatusTagihan::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
            <button class="btn-primary">Filter</button>
            @if (request()->hasAny(['tanggal', 'status']))
                <a href="{{ route('billing.index') }}" class="btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Tagihan</th>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Dibayar</th>
                    <th class="text-right">Sisa</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tagihan as $t)
                    <tr>
                        <td class="font-mono text-xs">
                            <a href="{{ route('billing.show', $t) }}" class="text-primary-600 hover:underline">{{ $t->no_tagihan }}</a>
                        </td>
                        <td class="text-xs">{{ $t->tgl_tagihan->format('d M Y') }}</td>
                        <td>
                            <div class="font-medium">{{ $t->kunjungan->pasien->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $t->kunjungan->pasien->no_rm }}</div>
                        </td>
                        <td class="text-right font-semibold">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="text-right text-green-700">Rp {{ number_format($t->dibayar, 0, ',', '.') }}</td>
                        <td class="text-right text-red-700">Rp {{ number_format($t->sisa, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $statusColor = match($t->status->value) {
                                    'DRAFT' => 'gray', 'BELUM_LUNAS' => 'yellow', 'CICILAN' => 'blue',
                                    'LUNAS' => 'green', 'KLAIM' => 'purple', 'VOID' => 'red',
                                };
                            @endphp
                            <span class="badge badge-{{ $statusColor }}">{{ $t->status->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('billing.show', $t) }}" class="btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-12 text-gray-400">Tidak ada tagihan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($tagihan->hasPages())<div class="card-footer">{{ $tagihan->links() }}</div>@endif
</div>

@endsection
