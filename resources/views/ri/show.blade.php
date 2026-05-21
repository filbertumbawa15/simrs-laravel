@extends('layouts.app')

@section('title', 'Rawat Inap')
@section('page-header', true)
@section('page-title', $ri->kunjungan->pasien->nama)
@section('page-subtitle', 'Rawat Inap • Masuk: '.$ri->tgl_masuk_ri->format('d M Y H:i').' • '.$ri->lama_inap.' hari')

@section('page-actions')
    @if (! $ri->tgl_pulang)
        <a href="{{ route('ri.pindah.form', $ri) }}" class="btn-secondary">↔ Pindah Kamar</a>
        <a href="{{ route('ri.pulang.form', $ri) }}" class="btn-success">🏠 Pulangkan Pasien</a>
    @endif
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        {{-- Info utama --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold">Informasi Perawatan</h3></div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-500">DPJP</div>
                    <div class="font-medium">{{ $ri->dpjp->nama_lengkap }}</div>
                    <div class="text-xs text-gray-500">{{ $ri->dpjp->spesialisasi }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Kamar Saat Ini</div>
                    @php $kamarAktif = $ri->kamarInap->whereNull('keluar')->first(); @endphp
                    @if ($kamarAktif)
                        <div class="font-medium">{{ $kamarAktif->kamar->no_kamar }} (Kelas {{ $kamarAktif->kamar->kelas->nama }})</div>
                        <div class="text-xs text-gray-500">{{ $kamarAktif->kamar->lokasi }}</div>
                    @else
                        <div class="text-gray-400">—</div>
                    @endif
                </div>
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500">Alasan Masuk</div>
                    <p>{{ $ri->alasan_masuk }}</p>
                </div>
            </div>
        </div>

        {{-- History kamar --}}
        @if ($ri->kamarInap->count() > 1)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Riwayat Kamar</h3></div>
                <div class="card-body">
                    <ol class="relative border-l border-gray-200 ml-3 space-y-3">
                        @foreach ($ri->kamarInap->sortBy('masuk') as $ki)
                            <li class="ml-4 text-sm">
                                <div class="w-2 h-2 rounded-full {{ $ki->keluar ? 'bg-gray-400' : 'bg-primary-600' }} absolute -left-1 mt-1.5"></div>
                                <div class="font-medium">{{ $ki->kamar->no_kamar }} ({{ $ki->kamar->kelas->nama }})</div>
                                <div class="text-xs text-gray-500">
                                    {{ $ki->masuk->format('d M H:i') }}
                                    @if ($ki->keluar)
                                        → {{ $ki->keluar->format('d M H:i') }} ({{ $ki->durasi_hari }} hari)
                                    @else
                                        → sekarang (<strong>aktif</strong>)
                                    @endif
                                </div>
                                @if ($ki->alasan_pindah)
                                    <div class="text-xs text-gray-600 italic mt-1">{{ $ki->alasan_pindah }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif

        {{-- Diagnosa --}}
        @if ($ri->kunjungan->diagnosa->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Diagnosa (ICD-10)</h3></div>
                <div class="card-body space-y-2">
                    @foreach ($ri->kunjungan->diagnosa as $dx)
                        <div class="flex items-start gap-3 p-2 bg-gray-50 rounded text-sm">
                            <span class="badge badge-{{ $dx->tipe === 'PRIMER' ? 'teal' : 'gray' }}">{{ $dx->tipe }}</span>
                            <div class="flex-1">
                                <span class="font-mono font-semibold">{{ $dx->icd10_kode }}</span>
                                — {{ $dx->icd10->nama }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Resume medis (kalau sudah pulang) --}}
        @if ($ri->resume_medis)
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold">Resume Medis Pasien Pulang</h3>
                    @if ($ri->resume_finalized)
                        <span class="badge badge-green">✓ Finalized {{ $ri->resume_finalized_at->format('d M H:i') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="prose prose-sm max-w-none">
                        <h4 class="text-xs uppercase tracking-wider font-semibold text-gray-500">Cara Pulang</h4>
                        <p>{{ $ri->cara_pulang }}</p>

                        <h4 class="text-xs uppercase tracking-wider font-semibold text-gray-500 mt-3">Resume Medis</h4>
                        <p class="whitespace-pre-line">{{ $ri->resume_medis }}</p>

                        @if ($ri->instruksi_pulang)
                            <h4 class="text-xs uppercase tracking-wider font-semibold text-gray-500 mt-3">Instruksi Pulang</h4>
                            <p class="whitespace-pre-line">{{ $ri->instruksi_pulang }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Pasien</h3></div>
            <div class="card-body text-sm">
                <div class="font-semibold">{{ $ri->kunjungan->pasien->nama }}</div>
                <div class="text-xs text-gray-500">{{ $ri->kunjungan->pasien->no_rm }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $ri->kunjungan->pasien->jenis_kelamin->label() }} • {{ $ri->kunjungan->pasien->umur }} thn
                </div>
                @if ($ri->kunjungan->pasien->rekamMedis?->alergi_obat)
                    <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs">
                        <strong class="text-red-700">⚠ Alergi:</strong> {{ $ri->kunjungan->pasien->rekamMedis->alergi_obat }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Quick Links</h3></div>
            <div class="card-body space-y-2">
                <a href="{{ route('kunjungan.show', $ri->kunjungan) }}" class="btn-secondary btn-sm w-full justify-start">
                    📋 Lihat Kunjungan
                </a>
                <a href="{{ route('lab.create', ['kunjungan_id' => $ri->kunjungan_id]) }}" class="btn-secondary btn-sm w-full justify-start">
                    🧪 Order Lab
                </a>
                <a href="{{ route('resep.create', ['kunjungan_id' => $ri->kunjungan_id]) }}" class="btn-secondary btn-sm w-full justify-start">
                    💊 Buat Resep
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
