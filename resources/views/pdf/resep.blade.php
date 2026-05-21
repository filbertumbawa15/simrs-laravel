@extends('pdf.layout')
@section('content')
<div class="title">Resep Dokter</div>

<div class="row"><span class="label-r">No. Resep</span>: <strong>{{ $resep->no_resep }}</strong></div>
<div class="row"><span class="label-r">Tanggal</span>: {{ $resep->tgl_resep->format('d M Y H:i') }}</div>
<div class="row"><span class="label-r">Dokter</span>: {{ $resep->dokter->nama_lengkap }} (SIP: {{ $resep->dokter->sip }})</div>

<hr>

<div class="row"><span class="label-r">Nama Pasien</span>: <strong>{{ $resep->kunjungan->pasien->nama }}</strong></div>
<div class="row"><span class="label-r">No. RM</span>: {{ $resep->kunjungan->pasien->no_rm }}</div>
<div class="row"><span class="label-r">Tgl Lahir / JK</span>: {{ $resep->kunjungan->pasien->tgl_lahir->format('d/m/Y') }} ({{ $resep->kunjungan->pasien->umur }} thn) / {{ $resep->kunjungan->pasien->jenis_kelamin->label() }}</div>
@if($resep->kunjungan->pasien->rekamMedis?->alergi_obat)
<div class="row" style="color:#dc2626"><span class="label-r">⚠ Alergi</span>: {{ $resep->kunjungan->pasien->rekamMedis->alergi_obat }}</div>
@endif

<table class="bordered" style="margin-top:15px">
<thead><tr>
<th style="width:5%">No</th><th>Nama Obat</th><th style="width:12%">Jumlah</th><th style="width:15%">Signa</th><th>Aturan Pakai</th>
</tr></thead>
<tbody>
@foreach($resep->details as $i => $d)
<tr>
    <td>{{ $i+1 }}</td>
    <td><strong>{{ $d->obat->nama }}</strong> @if($d->obat->kekuatan)<span class="muted">({{ $d->obat->kekuatan }})</span>@endif</td>
    <td>{{ $d->jumlah }} {{ $d->obat->satuan }}</td>
    <td class="bold">{{ $d->signa }}</td>
    <td>{{ $d->aturan_pakai ?: '-' }}</td>
</tr>
@endforeach
</tbody>
</table>

@if($resep->catatan)
<div class="box"><strong>Catatan:</strong> {{ $resep->catatan }}</div>
@endif

<div class="ttd">
    <div class="ttd-grid">
        <div class="ttd-cell"></div>
        <div class="ttd-cell">
            {{ config('app.rs.alamat') }}, {{ now()->translatedFormat('d F Y') }}<br>
            Dokter,
            <div style="height:55px"></div>
            <strong>{{ $resep->dokter->nama_lengkap }}</strong><br>
            <span class="muted">SIP: {{ $resep->dokter->sip }}</span>
        </div>
    </div>
</div>

<hr>
<div class="muted text-center">Dokumen elektronik, sah tanpa tanda tangan basah · Dicetak {{ now()->format('d M Y H:i') }}</div>
@endsection
