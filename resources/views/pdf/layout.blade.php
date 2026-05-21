<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { font-family: 'Helvetica', sans-serif; box-sizing: border-box; }
body { margin: 0; padding: 20px; font-size: 11px; color: #1f2937; }
.kop { border-bottom: 2px solid #0d9488; padding-bottom: 10px; margin-bottom: 15px; }
.kop-rs { font-size: 16px; font-weight: bold; color: #0d9488; }
.kop-info { font-size: 9px; color: #6b7280; margin-top: 2px; }
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
</style>
</head>
<body>
<div class="kop">
    <div class="kop-rs">{{ config('app.rs.nama') }}</div>
    <div class="kop-info">
        {{ config('app.rs.alamat') }} · Telp: {{ config('app.rs.telp') }} · Kode RS: {{ config('app.rs.kode') }}
    </div>
</div>
@yield('content')
</body>
</html>
