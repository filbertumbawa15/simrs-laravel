@extends('pdf.layout')
@section('content')
<div class="title">Hasil Pemeriksaan Laboratorium</div>

<table style="margin-bottom:10px">
<tr>
    <td style="width:50%">
        <div class="row"><span class="label-r">Nama</span>: <strong>{{ $order->kunjungan->pasien->nama }}</strong></div>
        <div class="row"><span class="label-r">No. RM</span>: {{ $order->kunjungan->pasien->no_rm }}</div>
        <div class="row"><span class="label-r">Umur / JK</span>: {{ $order->kunjungan->pasien->umur }} thn / {{ $order->kunjungan->pasien->jenis_kelamin->label() }}</div>
    </td>
    <td style="width:50%; vertical-align:top">
        <div class="row"><span class="label-r">No. Order</span>: {{ $order->no_order }}</div>
        <div class="row"><span class="label-r">Tgl Order</span>: {{ $order->tgl_order->format('d M Y H:i') }}</div>
        <div class="row"><span class="label-r">Prioritas</span>: {{ $order->prioritas->label() }}</div>
        <div class="row"><span class="label-r">Dokter Perujuk</span>: {{ $order->dokter->nama_lengkap }}</div>
    </td>
</tr>
</table>

@if($order->diagnosa_kerja)
<div class="box"><strong>Diagnosa Kerja:</strong> {{ $order->diagnosa_kerja }}</div>
@endif

@php $byKategori = $order->hasil->groupBy('parameter.kategori'); @endphp

@foreach($byKategori as $kategori => $hasilList)
<div style="margin-top:12px; font-weight:bold; color:#0d9488; text-transform:uppercase; font-size:11px">{{ $kategori }}</div>
<table class="bordered">
<thead><tr>
<th style="width:30%">Parameter</th>
<th style="width:15%" class="text-center">Hasil</th>
<th style="width:10%" class="text-center">Satuan</th>
<th style="width:25%">Nilai Rujukan</th>
<th style="width:10%" class="text-center">Flag</th>
<th>Catatan</th>
</tr></thead>
<tbody>
@foreach($hasilList as $h)
<tr>
    <td>{{ $h->parameter->nama }}</td>
    <td class="text-center bold flag-{{ $h->flag->value }}">{{ $h->hasil }}</td>
    <td class="text-center">{{ $h->satuan }}</td>
    <td>{{ $h->nilai_rujukan }}</td>
    <td class="text-center flag-{{ $h->flag->value }}">{{ $h->flag->value }}</td>
    <td>{{ $h->catatan ?: '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endforeach

<div style="margin-top:10px" class="muted">
Keterangan flag: <span class="flag-N">N</span>=Normal · <span class="flag-L">L</span>=Low · <span class="flag-H">H</span>=High · <span class="flag-LL">LL</span>=Critical Low · <span class="flag-HH">HH</span>=Critical High
</div>

<div class="ttd">
    <div class="ttd-grid">
        <div class="ttd-cell"></div>
        <div class="ttd-cell">
            {{ config('app.rs.alamat') }}, {{ $order->validated_at->translatedFormat('d F Y') }}<br>
            Divalidasi oleh,
            <div style="height:55px"></div>
            <strong>{{ $order->validator->name }}</strong><br>
            <span class="muted">{{ $order->validated_at->format('d M Y H:i') }}</span>
        </div>
    </div>
</div>

<hr>
<div class="muted text-center">Hasil ini adalah dokumen resmi laboratorium · Dicetak {{ now()->format('d M Y H:i') }}</div>
@endsection
