<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    /* Layout thermal 58mm — jarak minimal, font monospace-friendly */
    @page { margin: 4mm; }
    * { font-family: 'DejaVu Sans', 'Helvetica', sans-serif; box-sizing: border-box; }
    body { margin: 0; padding: 0; font-size: 9pt; color: #000; }
    .header { text-align: center; font-size: 10pt; font-weight: bold; margin-bottom: 2mm; }
    .subheader { text-align: center; font-size: 7pt; color: #333; margin-bottom: 3mm; }
    .divider { border-top: 1px dashed #000; margin: 3mm 0; }
    .queue-label { text-align: center; font-size: 7pt; text-transform: uppercase; letter-spacing: 2px; }
    .queue-no { text-align: center; font-size: 48pt; font-weight: bold; line-height: 1; margin: 2mm 0; letter-spacing: 3px; }
    .row { display: block; margin-bottom: 1.5mm; font-size: 8.5pt; }
    .label { color: #333; font-size: 7pt; }
    .value { font-weight: bold; }
    .footer { text-align: center; font-size: 7pt; margin-top: 4mm; color: #333; }
    .qr { text-align: center; margin-top: 3mm; }
    .qr img { width: 60px; height: 60px; }
</style>
</head>
<body>

<div class="header">{{ config('sihrs.rs_nama') }}</div>
<div class="subheader">
    {{ config('sihrs.rs_alamat') }}<br>
    Telp: {{ config('sihrs.rs_telp') }}
</div>

<div class="divider"></div>

<div class="queue-label">Nomor Antrian</div>
<div class="queue-no">{{ str_pad($rj->no_antrian, 3, '0', STR_PAD_LEFT) }}</div>

<div class="divider"></div>

<div class="row">
    <div class="label">Nama Pasien</div>
    <div class="value">{{ $rj->kunjungan->pasien->nama }}</div>
</div>
<div class="row">
    <div class="label">No. RM</div>
    <div class="value">{{ $rj->kunjungan->pasien->no_rm }}</div>
</div>
<div class="row">
    <div class="label">Poli</div>
    <div class="value">{{ $rj->poli->nama }}</div>
</div>
<div class="row">
    <div class="label">Dokter</div>
    <div class="value">{{ $rj->dokter->nama_lengkap }}</div>
</div>
<div class="row">
    <div class="label">Tgl Daftar</div>
    <div class="value">{{ $rj->created_at->format('d/m/Y H:i') }}</div>
</div>
<div class="row">
    <div class="label">Penjamin</div>
    <div class="value">{{ $rj->kunjungan->penjamin->label() }}</div>
</div>

@if(!empty($qrDataUri))
<div class="qr">
    <img src="{{ $qrDataUri }}" alt="QR">
</div>
@endif

<div class="divider"></div>

<div class="footer">
    Simpan tiket ini.<br>
    Silakan tunggu panggilan sesuai nomor.<br>
    Terima kasih.
</div>

</body>
</html>
