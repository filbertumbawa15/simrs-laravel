@extends('layouts.app')

@section('title', $tagihan->no_tagihan)
@section('page-header', true)
@section('page-title', $tagihan->no_tagihan)
@section('page-subtitle', 'Tagihan untuk: '.$tagihan->kunjungan->pasien->nama)

@section('page-actions')
    @if ($tagihan->status->value === 'DRAFT')
        @can('billing.finalize')
            <form method="POST" action="{{ route('billing.finalize', $tagihan) }}" class="inline">
                @csrf
                <button class="btn-warning"
                        onclick="return confirm('Finalisasi tagihan? Setelah ini tidak bisa diubah lagi.')">
                    🔒 Finalize Tagihan
                </button>
            </form>
        @endcan
    @endif
    @if (in_array($tagihan->status->value, ['BELUM_LUNAS', 'CICILAN']) && (float)$tagihan->sisa > 0)
        @can('billing.payment')
            <a href="{{ route('billing.bayar.form', $tagihan) }}" class="btn-success">
                💵 Catat Pembayaran
            </a>
        @endcan
    @endif
    <button onclick="window.print()" class="btn-secondary no-print">🖨 Cetak</button>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Rincian (2/3) --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="card">
            <div class="card-header"><h3 class="font-semibold">Rincian Tagihan</h3></div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $byKategori = $tagihan->details->groupBy('kategori'); @endphp
                        @foreach ($byKategori as $kategori => $items)
                            <tr class="bg-gray-50">
                                <td colspan="5" class="font-semibold text-xs uppercase tracking-wider text-gray-600">
                                    {{ $kategori }}
                                </td>
                            </tr>
                            @foreach ($items as $d)
                                <tr>
                                    <td></td>
                                    <td>{{ $d->deskripsi }}</td>
                                    <td class="text-right">{{ $d->qty }}</td>
                                    <td class="text-right text-xs">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                    <td class="text-right font-medium">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="text-right">Subtotal</td>
                            <td class="text-right">Rp {{ number_format($tagihan->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if ((float)$tagihan->diskon > 0)
                            <tr>
                                <td colspan="4" class="text-right">Diskon</td>
                                <td class="text-right text-red-600">- Rp {{ number_format($tagihan->diskon, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        @if ((float)$tagihan->ppn > 0)
                            <tr>
                                <td colspan="4" class="text-right">PPN</td>
                                <td class="text-right">Rp {{ number_format($tagihan->ppn, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr class="text-base font-bold">
                            <td colspan="4" class="text-right">TOTAL</td>
                            <td class="text-right text-primary-700">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="text-sm">
                            <td colspan="4" class="text-right text-green-700">Dibayar</td>
                            <td class="text-right text-green-700">Rp {{ number_format($tagihan->dibayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="text-sm font-semibold">
                            <td colspan="4" class="text-right text-red-700">Sisa Tagihan</td>
                            <td class="text-right text-red-700">Rp {{ number_format($tagihan->sisa, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- History pembayaran --}}
        @if ($tagihan->pembayaran->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Riwayat Pembayaran</h3></div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr><th>No.</th><th>Tanggal</th><th>Metode</th><th>Referensi</th><th>Kasir</th><th class="text-right">Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($tagihan->pembayaran as $p)
                                <tr>
                                    <td class="font-mono text-xs">{{ $p->no_pembayaran }}</td>
                                    <td class="text-xs">{{ $p->tgl_bayar->format('d M Y H:i') }}</td>
                                    <td><span class="badge badge-gray">{{ $p->metode->label() }}</span></td>
                                    <td class="text-xs font-mono">{{ $p->referensi_eksternal ?: '—' }}</td>
                                    <td class="text-xs">{{ $p->kasir->name }}</td>
                                    <td class="text-right font-semibold text-green-700">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar (1/3) --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Status</h3></div>
            <div class="card-body text-sm">
                @php
                    $statusColor = match($tagihan->status->value) {
                        'DRAFT' => 'gray', 'BELUM_LUNAS' => 'yellow', 'CICILAN' => 'blue',
                        'LUNAS' => 'green', 'KLAIM' => 'purple', 'VOID' => 'red',
                    };
                @endphp
                <span class="badge badge-{{ $statusColor }} text-base">{{ $tagihan->status->label() }}</span>

                @if ($tagihan->finalized_at)
                    <div class="text-xs text-gray-500 mt-3">
                        Difinalisasi: {{ $tagihan->finalized_at->format('d M Y H:i') }}
                        @if ($tagihan->finalizedBy)
                            <div>oleh {{ $tagihan->finalizedBy->name }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Pasien & Kunjungan</h3></div>
            <div class="card-body text-sm space-y-2">
                <div>
                    <div class="font-semibold">{{ $tagihan->kunjungan->pasien->nama }}</div>
                    <div class="text-xs text-gray-500">{{ $tagihan->kunjungan->pasien->no_rm }}</div>
                </div>
                <div class="border-t pt-2 text-xs">
                    <div><span class="text-gray-500">Kunjungan:</span>
                        <a href="{{ route('kunjungan.show', $tagihan->kunjungan) }}" class="font-mono text-primary-600 hover:underline">
                            {{ $tagihan->kunjungan->no_kunjungan }}
                        </a>
                    </div>
                    <div><span class="text-gray-500">Tipe:</span> {{ $tagihan->kunjungan->tipe->label() }}</div>
                    <div><span class="text-gray-500">Penjamin:</span> {{ $tagihan->kunjungan->penjamin->label() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
