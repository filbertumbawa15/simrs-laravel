@extends('pdf.layout')
@section('content')
<div class="title">Resume Medis Pasien Pulang</div>

<table style="margin-bottom:10px">
<tr>
    <td style="width:50%">
        <div class="row"><span class="label-r">Nama</span>: <strong>{{ $ri->kunjungan->pasien->nama }}</strong></div>
        <div class="row"><span class="label-r">No. RM</span>: {{ $ri->kunjungan->pasien->no_rm }}</div>
        <div class="row"><span class="label-r">NIK</span>: {{ $ri->kunjungan->pasien->nik ?: '-' }}</div>
        <div class="row"><span class="label-r">Tgl Lahir / JK</span>: {{ $ri->kunjungan->pasien->tgl_lahir->format('d/m/Y') }} / {{ $ri->kunjungan->pasien->jenis_kelamin->label() }}</div>
        <div class="row"><span class="label-r">Alamat</span>: {{ Str::limit($ri->kunjungan->pasien->alamat, 60) }}</div>
    </td>
    <td style="width:50%; vertical-align:top">
        <div class="row"><span class="label-r">No. Kunjungan</span>: {{ $ri->kunjungan->no_kunjungan }}</div>
        <div class="row"><span class="label-r">Tgl Masuk RI</span>: {{ $ri->tgl_masuk_ri->format('d M Y H:i') }}</div>
        <div class="row"><span class="label-r">Tgl Pulang</span>: {{ $ri->tgl_pulang->format('d M Y H:i') }}</div>
        <div class="row"><span class="label-r">Lama Dirawat</span>: <strong>{{ $ri->lama_inap }} hari</strong></div>
        <div class="row"><span class="label-r">DPJP</span>: {{ $ri->dpjp->nama_lengkap }}</div>
        <div class="row"><span class="label-r">Cara Pulang</span>: <strong>{{ $ri->cara_pulang }}</strong></div>
    </td>
</tr>
</table>

<div class="box">
<strong>Alasan Masuk:</strong><br>
{{ $ri->alasan_masuk }}
</div>

@if($ri->kunjungan->diagnosa->isNotEmpty())
<div style="margin-top:10px"><strong>Diagnosa (ICD-10):</strong></div>
<table class="bordered">
<thead><tr><th style="width:10%">Tipe</th><th style="width:15%">Kode</th><th>Nama Diagnosa</th></tr></thead>
<tbody>
@foreach($ri->kunjungan->diagnosa as $dx)
<tr><td>{{ $dx->tipe }}</td><td class="bold">{{ $dx->icd10_kode }}</td><td>{{ $dx->icd10->nama }}</td></tr>
@endforeach
</tbody>
</table>
@endif

<div style="margin-top:10px"><strong>Resume Medis:</strong></div>
<div class="box">{!! nl2br(e($ri->resume_medis)) !!}</div>

@if($ri->instruksi_pulang)
<div style="margin-top:10px"><strong>Instruksi Pulang:</strong></div>
<div class="box">{!! nl2br(e($ri->instruksi_pulang)) !!}</div>
@endif

<div class="ttd">
    <div class="ttd-grid">
        <div class="ttd-cell">
            Pasien/Keluarga,
            <div style="height:60px"></div>
            <strong>(.......................................)</strong>
        </div>
        <div class="ttd-cell">
            {{ config('app.rs.alamat') }}, {{ $ri->resume_finalized_at->translatedFormat('d F Y') }}<br>
            DPJP,
            <div style="height:55px"></div>
            <strong>{{ $ri->dpjp->nama_lengkap }}</strong><br>
            <span class="muted">SIP: {{ $ri->dpjp->sip }}</span>
        </div>
    </div>
</div>

<hr>
<div class="muted text-center">Resume difinalisasi: {{ $ri->resume_finalized_at->format('d M Y H:i') }} · Dicetak {{ now()->format('d M Y H:i') }}</div>
@endsection
