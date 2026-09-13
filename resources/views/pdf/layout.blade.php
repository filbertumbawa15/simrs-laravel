<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { font-family: 'Helvetica', sans-serif; box-sizing: border-box; }
body { margin: 0; padding: 20px 20px 60px 20px; font-size: 11px; color: #1f2937; }
.kop { border-bottom: 2px solid #0d9488; padding-bottom: 10px; margin-bottom: 15px; }
.kop-rs { font-size: 16px; font-weight: bold; color: #0d9488; }
.kop-info { font-size: 9px; color: #6b7280; margin-top: 2px; }
.kop-izin { font-size: 8px; color: #9ca3af; margin-top: 1px; }
.title { font-size: 14px; font-weight: bold; text-align: center; margin: 12px 0; text-transform: uppercase; letter-spacing: 1px; }
.row { display: block; margin-bottom: 4px; }
.label-r { display: inline-block; width: 110px; color: #6b7280; font-size: 10px; }
table { width: 100%; border-collapse: collapse; margin: 8px 0; }
th, td { padding: 5px 7px; text-align: left; font-size: 10px; }
table.bordered th, table.bordered td { border: 1px solid #d1d5db; }
thead { background: #f3f4f6; }
th { font-weight: bold; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.bold { font-weight: bold; }
.muted { color: #6b7280; font-size: 9px; }
.box { border: 1px solid #d1d5db; padding: 8px; margin: 8px 0; border-radius: 4px; }
.ttd { margin-top: 30px; }
.ttd-grid { width: 100%; }
.ttd-cell { display: inline-block; width: 48%; text-align: center; vertical-align: top; }
hr { border: 0; border-top: 1px dashed #9ca3af; margin: 10px 0; }
.flag-LL, .flag-HH { color: #dc2626; font-weight: bold; }
.flag-L, .flag-H { color: #d97706; font-weight: bold; }
.flag-N { color: #059669; }

/* Watermark SALINAN untuk cetak ulang */
.watermark {
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 96px;
    color: rgba(220, 38, 38, 0.12);
    font-weight: bold;
    letter-spacing: 10px;
    z-index: -1;
    pointer-events: none;
}

/* Footer verifikasi QR */
.verify-footer {
    position: fixed;
    bottom: 12px;
    left: 20px;
    right: 20px;
    border-top: 1px solid #d1d5db;
    padding-top: 6px;
    font-size: 8px;
    color: #6b7280;
}
.verify-footer table { margin: 0; }
.verify-footer td { padding: 2px 4px; vertical-align: middle; }
.verify-qr { width: 45px; height: 45px; }
</style>
</head>
<body>

@if($isSalinan ?? false)
    <div class="watermark">SALINAN</div>
@endif

<div class="kop">
    <table style="margin:0">
        <tr>
            <td style="width:70%">
                <div class="kop-rs">{{ config('sihrs.rs_nama') }}</div>
                <div class="kop-info">
                    {{ config('sihrs.rs_alamat') }}<br>
                    Telp: {{ config('sihrs.rs_telp') }}
                    @if(config('sihrs.rs_email')) · {{ config('sihrs.rs_email') }} @endif
                </div>
                <div class="kop-izin">
                    Kode RS: {{ config('sihrs.rs_kode') }}
                    @if(config('sihrs.rs_kelas')) · Kelas {{ config('sihrs.rs_kelas') }} @endif
                    @if(config('sihrs.rs_izin')) · Izin: {{ config('sihrs.rs_izin') }} @endif
                </div>
            </td>
            <td style="width:30%; text-align:right; color:#6b7280; font-size:9px">
                @if(!empty($docNumber))
                    <div>{{ $docNumber }}</div>
                @endif
                <div>Dicetak: {{ now()->format('d M Y H:i') }}</div>
            </td>
        </tr>
    </table>
</div>

@yield('content')

@if(!empty($qrDataUri))
<div class="verify-footer">
    <table>
        <tr>
            <td style="width:50px"><img src="{{ $qrDataUri }}" class="verify-qr" alt="QR"></td>
            <td>
                <strong>Verifikasi dokumen ini:</strong><br>
                Scan QR atau kunjungi:<br>
                <span style="font-family:monospace">{{ $verifyUrl ?? '' }}</span>
            </td>
            <td class="text-right" style="width:35%">
                Dokumen ini di-generate otomatis oleh SIHRS.<br>
                Sah tanpa tanda tangan basah untuk RME (Permenkes 24/2022).
            </td>
        </tr>
    </table>
</div>
@endif

</body>
</html>
