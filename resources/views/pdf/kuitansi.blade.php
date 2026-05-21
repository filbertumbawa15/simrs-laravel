@extends('pdf.layout')
@section('content')
<div class="title">Kuitansi Pembayaran</div>

<div class="row"><span class="label-r">No. Pembayaran</span>: <strong>{{ $pembayaran->no_pembayaran }}</strong></div>
<div class="row"><span class="label-r">No. Tagihan</span>: {{ $pembayaran->tagihan->no_tagihan }}</div>
<div class="row"><span class="label-r">Tanggal Bayar</span>: {{ $pembayaran->tgl_bayar->format('d M Y H:i') }}</div>
<div class="row"><span class="label-r">Metode</span>: {{ $pembayaran->metode->label() }}
    @if($pembayaran->referensi_eksternal)<span class="muted">(Ref: {{ $pembayaran->referensi_eksternal }})</span>@endif
</div>
<div class="row"><span class="label-r">Kasir</span>: {{ $pembayaran->kasir->name }}</div>

<hr>

<div class="row"><span class="label-r">Diterima dari</span>: <strong>{{ $pembayaran->tagihan->kunjungan->pasien->nama }}</strong></div>
<div class="row"><span class="label-r">No. RM</span>: {{ $pembayaran->tagihan->kunjungan->pasien->no_rm }}</div>

<table class="bordered" style="margin-top:15px">
<thead><tr><th>Kategori</th><th>Deskripsi</th><th class="text-right">Qty</th><th class="text-right">Subtotal</th></tr></thead>
<tbody>
@foreach($pembayaran->tagihan->details as $d)
<tr>
    <td>{{ $d->kategori }}</td>
    <td>{{ $d->deskripsi }}</td>
    <td class="text-right">{{ $d->qty }}</td>
    <td class="text-right">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
<tfoot>
<tr><td colspan="3" class="text-right bold">TOTAL TAGIHAN</td><td class="text-right bold">Rp {{ number_format($pembayaran->tagihan->total, 0, ',', '.') }}</td></tr>
<tr><td colspan="3" class="text-right">Dibayar pada transaksi ini</td><td class="text-right bold" style="color:#059669">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td></tr>
<tr><td colspan="3" class="text-right">Sisa Tagihan</td><td class="text-right bold" style="color:#dc2626">Rp {{ number_format($pembayaran->tagihan->sisa, 0, ',', '.') }}</td></tr>
</tfoot>
</table>

<div class="box" style="margin-top:15px">
<div style="font-size:13px">Terbilang: <em>{{ ucwords(\App\Helpers\Terbilang::convert($pembayaran->jumlah)) }} rupiah</em></div>
</div>

<div class="ttd">
    <div class="ttd-grid">
        <div class="ttd-cell"></div>
        <div class="ttd-cell">
            {{ config('app.rs.alamat') }}, {{ $pembayaran->tgl_bayar->translatedFormat('d F Y') }}<br>
            Kasir,
            <div style="height:55px"></div>
            <strong>{{ $pembayaran->kasir->name }}</strong>
        </div>
    </div>
</div>

<hr>
<div class="muted text-center">Dicetak {{ now()->format('d M Y H:i') }} · Simpan kuitansi ini sebagai bukti pembayaran sah</div>
@endsection
