<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Temuan Radiologi Kritis</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif; color: #1f2937; line-height: 1.5; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .alert { background: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin-bottom: 20px; }
        .alert h1 { margin: 0; color: #991b1b; font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background: #f9fafb; }
        .footer { color: #6b7280; font-size: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="alert">
        <h1>🚨 TEMUAN KRITIS RADIOLOGI</h1>
        <p>Radiolog menandai temuan kritis pada pemeriksaan pasien Anda. Mohon segera evaluasi.</p>
    </div>

    <h2>Data Pasien</h2>
    <table>
        <tr><th>No. RM</th><td>{{ $pasien->no_rm }}</td></tr>
        <tr><th>Nama</th><td>{{ $pasien->nama }}</td></tr>
        <tr><th>Tanggal Lahir</th><td>{{ $pasien->tgl_lahir?->format('d M Y') }} ({{ $pasien->umur }} th)</td></tr>
        <tr><th>No. Kunjungan</th><td>{{ $order->kunjungan->no_kunjungan }}</td></tr>
    </table>

    <h2>Temuan Kritis</h2>
    <table>
        <thead>
        <tr>
            <th style="width:30%">Pemeriksaan</th>
            <th>Kesan / Temuan</th>
        </tr>
        </thead>
        <tbody>
        @foreach($hasilKritis as $h)
            <tr>
                <td>{{ $h->pemeriksaan->nama }}</td>
                <td>
                    <strong>{{ $h->kesan }}</strong>
                    @if($h->saran)
                        <br><em>Saran: {{ $h->saran }}</em>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Order Info</h2>
    <table>
        <tr><th>No. Order</th><td>{{ $order->no_order }}</td></tr>
        <tr><th>Divalidasi</th><td>{{ $order->validated_at?->format('d M Y H:i') }}</td></tr>
        <tr><th>Dokter Perujuk</th><td>{{ $dokter->nama_lengkap }}</td></tr>
    </table>

    <p style="margin-top: 20px;">
        <a href="{{ url("/rad/{$order->id}") }}" style="background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
            Buka Detail Order
        </a>
    </p>

    <div class="footer">
        Email otomatis dari {{ $rsNama }}. Jangan dibalas.
    </div>
</div>
</body>
</html>
