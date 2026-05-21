@extends('layouts.app')

@section('title', 'Pulangkan Pasien')
@section('page-header', true)
@section('page-title', 'Pulangkan Pasien')
@section('page-subtitle', $ri->kunjungan->pasien->nama.' • Lama dirawat: '.$ri->lama_inap.' hari')

@section('content')

<form method="POST" action="{{ route('ri.pulang.store', $ri) }}" class="space-y-6">
    @csrf

    <div class="alert alert-warning">
        <strong>⚠ Perhatian:</strong> Resume medis WAJIB diisi minimal 50 karakter sebelum pasien
        bisa dipulangkan (sesuai standar JCI/KARS untuk continuity of care).
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Cara Pulang</h3></div>
        <div class="card-body">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach ([
                    'SEMBUH' => ['label' => 'Sembuh', 'icon' => '✅'],
                    'MEMBAIK' => ['label' => 'Membaik', 'icon' => '👍'],
                    'BELUM_SEMBUH' => ['label' => 'Belum Sembuh', 'icon' => '⏳'],
                    'APS' => ['label' => 'APS (Atas Permintaan Sendiri)', 'icon' => '✋'],
                    'RUJUK' => ['label' => 'Rujuk ke RS lain', 'icon' => '🔄'],
                    'MENINGGAL' => ['label' => 'Meninggal', 'icon' => '🕊'],
                ] as $val => $cfg)
                    <label class="cursor-pointer">
                        <input type="radio" name="cara_pulang" value="{{ $val }}" required class="sr-only peer">
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center
                                    peer-checked:border-primary-600 peer-checked:bg-primary-50">
                            <div class="text-2xl">{{ $cfg['icon'] }}</div>
                            <div class="text-sm font-medium">{{ $cfg['label'] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Resume Medis <span class="text-red-500">*</span></h3></div>
        <div class="card-body">
            <textarea name="resume_medis" rows="6" required minlength="50" class="textarea"
                      placeholder="Ringkasan perjalanan penyakit, hasil pemeriksaan signifikan, tindakan yang diberikan, evolusi klinis, kondisi saat pulang…">{{ old('resume_medis') }}</textarea>
            <p class="help">Format standar: subjective awal → assessment → intervensi → respon → kondisi akhir</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold">Instruksi Pulang</h3></div>
        <div class="card-body">
            <textarea name="instruksi_pulang" rows="4" class="textarea"
                      placeholder="Obat lanjutan, diet, aktivitas, kontrol ulang, kapan ke IGD jika ada warning sign…">{{ old('instruksi_pulang') }}</textarea>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('ri.show', $ri) }}" class="btn-secondary">Batal</a>
        <button class="btn-success btn-lg"
                onclick="return confirm('Konfirmasi: Pulangkan pasien? Setelah ini kunjungan akan masuk ke billing.')">
            🏠 Pulangkan Pasien
        </button>
    </div>
</form>

@endsection
