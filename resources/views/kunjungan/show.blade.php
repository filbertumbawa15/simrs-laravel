@extends('layouts.app')

@section('title', $kunjungan->no_kunjungan)
@section('page-header', true)
@section('page-title', 'Kunjungan '.$kunjungan->no_kunjungan)
@section('page-subtitle', $kunjungan->pasien->nama.' • '.$kunjungan->tgl_masuk->format('d M Y H:i'))

@section('page-actions')
<div class="flex items-center gap-2">
    <span class="badge {{ $kunjungan->tipe->badge() }}">{{ $kunjungan->tipe->label() }}</span>
    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
        @switch($kunjungan->status->value)
            @case('TERDAFTAR') bg-blue-100 text-blue-800 @break
            @case('DALAM_PEMERIKSAAN') bg-amber-100 text-amber-800 @break
            @case('MENUNGGU_HASIL_LAB') bg-purple-100 text-purple-800 @break
            @case('MENUNGGU_OBAT') bg-orange-100 text-orange-800 @break
            @case('MENUNGGU_PEMBAYARAN') bg-cyan-100 text-cyan-800 @break
            @case('SELESAI') bg-emerald-100 text-emerald-800 @break
            @case('BATAL') bg-red-100 text-red-800 @break
            @case('LANJUT_RI') bg-indigo-100 text-indigo-800 @break
        @endswitch
    ">
        <span class="w-1.5 h-1.5 rounded-full inline-block
            @switch($kunjungan->status->value)
                @case('TERDAFTAR') bg-blue-500 @break
                @case('DALAM_PEMERIKSAAN') bg-amber-500 @break
                @case('MENUNGGU_HASIL_LAB') bg-purple-500 @break
                @case('MENUNGGU_OBAT') bg-orange-500 @break
                @case('MENUNGGU_PEMBAYARAN') bg-cyan-500 @break
                @case('SELESAI') bg-emerald-500 @break
                @case('BATAL') bg-red-500 @break
                @case('LANJUT_RI') bg-indigo-500 @break
            @endswitch
        "></span>
        {{ $kunjungan->status->label() }}
    </span>
</div>
@endsection

@php
// ============================================================
// CONTEXTUAL FLAGS — biar @if di view lebih clean
// ============================================================
$tipe = $kunjungan->tipe->value;
$status = $kunjungan->status->value;
$isAktif = !in_array($status, ['SELESAI', 'BATAL']);

$isIgd = $tipe === 'IGD';
$isRj = $tipe === 'RJ';
$isRi = $tipe === 'RI';

$rj = $kunjungan->rawatJalan;
$ri = $kunjungan->rawatInap;
$triase = $kunjungan->triase ?? null;

$sudahDiagnosa = $kunjungan->diagnosa->isNotEmpty();
$adaTagihan = (bool) $kunjungan->tagihan;
@endphp

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- =====================================================
         KOLOM KIRI (2/3) — workflow utama
    ===================================================== --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ============================================
             ACTION PANEL — kontekstual per tipe & status
        ============================================ --}}
        @if ($isAktif)
        <div class="card bg-primary-50 ring-1 ring-primary-200">
            <div class="card-body">
                <h3 class="font-semibold text-primary-900 mb-3 flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Tindakan Berikutnya
                </h3>
                <div class="flex flex-wrap gap-2">

                    {{-- ========================================
                         RAWAT JALAN
                    ======================================== --}}
                    @if ($isRj && $rj)
                    @if ($status === 'TERDAFTAR')
                    @can('rj.examine')
                    <a href="{{ route('rj.periksa', $rj) }}" class="btn-primary">
                        🩺 Mulai Pemeriksaan
                    </a>
                    @endcan
                    @endif

                    @if ($status === 'DALAM_PEMERIKSAAN')
                    @can('rj.examine')
                    <a href="{{ route('rj.periksa', $rj) }}" class="btn-primary">
                        ↪ Lanjutkan Pemeriksaan
                    </a>
                    @endcan
                    @endif
                    @endif

                    {{-- ========================================
                         IGD — workflow paling kompleks
                    ======================================== --}}
                    @if ($isIgd)
                    @if (! $triase)
                    {{-- Belum triase --}}
                    @can('igd.triase')
                    <a href="{{ route('igd.triase.form', $kunjungan) }}" class="btn-danger">
                        🚨 Triase Sekarang
                    </a>
                    @endcan
                    @else
                    {{-- Sudah triase — tampilkan kategori --}}
                    <div class="w-full mb-2">
                        <span class="text-xs text-gray-600">Triase:</span>
                        <span class="badge badge-{{ strtolower($triase->kategori) }} ml-1">
                            {{ $triase->kategori }}
                        </span>
                        <span class="text-xs text-gray-500 ml-2">
                            oleh {{ $triase->petugas->name ?? '-' }}
                            pada {{ $triase->waktu_triase->format('H:i') }}
                        </span>
                    </div>

                    @if ($status === 'TERDAFTAR' || $status === 'DALAM_PEMERIKSAAN')
                    {{-- Periksa pasien IGD --}}
                    @can('igd.periksa')
                    <a href="{{ route('igd.periksa', $kunjungan) }}" class="btn-primary">
                        🩺 Periksa Pasien
                    </a>
                    @endcan

                    {{-- Order penunjang --}}
                    @can('lab.order')
                    <a href="{{ route('lab.create', ['kunjungan_id' => $kunjungan->id]) }}" class="btn-secondary">
                        🧪 Order Lab
                    </a>
                    @endcan

                    @can('radiologi.order')
                    <a href="{{ route('rad.create', ['kunjungan_id' => $kunjungan->id]) }}" class="btn-secondary">
                        📷 Order Radiologi
                    </a>
                    @endcan

                    {{-- Buat resep --}}
                    @can('resep.create')
                    <a href="{{ route('resep.create', ['kunjungan_id' => $kunjungan->id]) }}" class="btn-secondary">
                        💊 Buat Resep
                    </a>
                    @endcan
                    @endif

                    {{-- DISPOSISI — keputusan akhir IGD --}}
                    @if ($sudahDiagnosa && in_array($status, ['DALAM_PEMERIKSAAN', 'MENUNGGU_HASIL_LAB']))
                    <div class="w-full border-t border-primary-200 pt-3 mt-2">
                        <div class="text-xs font-semibold text-primary-900 uppercase mb-2">
                            Disposisi Pasien
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @can('ri.admisi')
                            <button type="button"
                                onclick="document.getElementById('modal-admisi').showModal()"
                                class="btn-warning">
                                🛏 Admisi Rawat Inap
                            </button>
                            @endcan

                            @can('igd.disposisi')
                            <button type="button"
                                onclick="document.getElementById('modal-rujuk').showModal()"
                                class="btn-secondary">
                                🚑 Rujuk ke RS Lain
                            </button>

                            <button type="button"
                                onclick="document.getElementById('modal-pulang-igd').showModal()"
                                class="btn-secondary">
                                🏠 Pulangkan dari IGD
                            </button>

                            <button type="button"
                                onclick="document.getElementById('modal-meninggal').showModal()"
                                class="btn-secondary border-gray-700 text-gray-700">
                                ⚱ Meninggal di IGD
                            </button>
                            @endcan
                        </div>
                    </div>
                    @elseif (! $sudahDiagnosa && $status === 'DALAM_PEMERIKSAAN')
                    <div class="w-full mt-2">
                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2">
                            ℹ Input diagnosa ICD-10 dulu sebelum bisa disposisi (admisi/rujuk/pulang).
                        </p>
                    </div>
                    @endif
                    @endif
                    @endif

                    {{-- ========================================
                         RAWAT INAP
                    ======================================== --}}
                    @if ($isRi && $ri)
                    @if (! $ri->tgl_pulang)
                    @can('ri.cppt')
                    <a href="{{ route('ri.cppt.index', $ri) }}" class="btn-primary">
                        📝 CPPT
                    </a>
                    @endcan

                    @can('ri.transfer')
                    <a href="{{ route('ri.transfer.form', $ri) }}" class="btn-secondary">
                        ↔ Pindah Kamar
                    </a>
                    @endcan

                    @can('lab.order')
                    <a href="{{ route('lab.create', ['kunjungan_id' => $kunjungan->id]) }}" class="btn-secondary">
                        🧪 Order Lab
                    </a>
                    @endcan

                    @can('radiologi.order')
                    <a href="{{ route('rad.create', ['kunjungan_id' => $kunjungan->id]) }}" class="btn-secondary">
                        📷 Order Radiologi
                    </a>
                    @endcan

                    @can('resep.create')
                    <a href="{{ route('resep.create', ['kunjungan_id' => $kunjungan->id]) }}" class="btn-secondary">
                        💊 Buat Resep
                    </a>
                    @endcan

                    @can('ri.discharge')
                    <a href="{{ route('ri.discharge.form', $ri) }}" class="btn-warning">
                        🏠 Pulangkan Pasien
                    </a>
                    @endcan
                    @endif
                    @endif

                    {{-- ========================================
                         BILLING — semua tipe
                    ======================================== --}}
                    @if ($status === 'MENUNGGU_PEMBAYARAN' && ! $adaTagihan)
                    @can('billing.generate')
                    <form method="POST" action="{{ route('billing.generate', $kunjungan) }}">
                        @csrf
                        <button class="btn-warning">💵 Generate Tagihan</button>
                    </form>
                    @endcan
                    @endif

                    @if ($adaTagihan)
                    <a href="{{ route('billing.show', $kunjungan->tagihan) }}" class="btn-secondary">
                        💰 Lihat Tagihan ({{ $kunjungan->tagihan->status->label() }})
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ============================================
             TRIASE IGD CARD
        ============================================ --}}
        @if ($isIgd && $triase)
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="font-semibold">Triase IGD</h3>
                <span class="badge badge-{{ strtolower($triase->kategori) }}">
                    {{ $triase->kategori }}
                </span>
            </div>
            <div class="card-body text-sm space-y-3">
                <div>
                    <div class="text-xs text-gray-500 mb-0.5">Keluhan Utama</div>
                    <p class="whitespace-pre-line">{{ $triase->keluhan_utama }}</p>
                </div>
                @if ($triase->tanda_vital)
                <div class="border-t pt-3">
                    <div class="text-xs font-medium text-gray-500 mb-2">Tanda Vital</div>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                        @foreach ($triase->tanda_vital as $key => $val)
                        <div class="bg-gray-50 rounded-lg px-2.5 py-2 text-center">
                            <div class="text-[10px] text-gray-400 uppercase">{{ str_replace('_', ' ', $key) }}</div>
                            <div class="text-sm font-semibold text-gray-800">{{ $val ?: '-' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="text-xs text-gray-500 border-t pt-2">
                    Triase oleh <b>{{ $triase->petugas->name ?? '—' }}</b>
                    pada {{ $triase->waktu_triase->format('d M Y H:i') }}
                </div>
            </div>
        </div>
        @endif

        {{-- ============================================
             RAWAT JALAN / PEMERIKSAAN IGD CARD
        ============================================ --}}
        @if ($rj)
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">
                    {{ $isIgd ? 'Pemeriksaan IGD' : 'Rawat Jalan' }}
                </h3>
                @if ($rj->no_antrian)
                <span class="badge badge-blue">Antrian #{{ $rj->no_antrian }}</span>
                @endif
            </div>
            <div class="card-body space-y-4 text-sm">
                {{-- Info Poli & Dokter --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-0.5">{{ $isIgd ? 'Unit' : 'Poli' }}</div>
                        <div class="font-medium">{{ $rj->poli->nama ?? 'IGD' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-0.5">Dokter Pemeriksa</div>
                        <div class="font-medium">{{ $rj->dokter->nama_lengkap ?? '—' }}</div>
                    </div>
                </div>

                {{-- Tanda Vital --}}
                @if ($rj->tanda_vital)
                <div class="border-t pt-3">
                    <div class="text-xs font-medium text-gray-500 mb-2">Tanda Vital</div>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                        @foreach ($rj->tanda_vital as $key => $val)
                        <div class="bg-gray-50 rounded-lg px-2.5 py-2 text-center">
                            <div class="text-[10px] text-gray-400 uppercase">{{ str_replace('_', ' ', $key) }}</div>
                            <div class="text-sm font-semibold text-gray-800">{{ $val ?: '-' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- SOAP Notes — layout vertikal agar mudah dibaca --}}
                @if ($rj->subjective || $rj->objective || $rj->assessment || $rj->plan)
                <div class="border-t pt-3">
                    <div class="text-xs font-medium text-gray-500 mb-3">Catatan SOAP</div>
                    <div class="space-y-3">
                        @if ($rj->subjective)
                        <div class="flex gap-3">
                            <span class="shrink-0 w-6 h-6 rounded bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold leading-none">S</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[11px] font-medium text-gray-400 mb-0.5">Subjective</div>
                                <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $rj->subjective }}</p>
                            </div>
                        </div>
                        @endif
                        @if ($rj->objective)
                        <div class="flex gap-3">
                            <span class="shrink-0 w-6 h-6 rounded bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold leading-none">O</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[11px] font-medium text-gray-400 mb-0.5">Objective</div>
                                <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $rj->objective }}</p>
                            </div>
                        </div>
                        @endif
                        @if ($rj->assessment)
                        <div class="flex gap-3">
                            <span class="shrink-0 w-6 h-6 rounded bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold leading-none">A</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[11px] font-medium text-gray-400 mb-0.5">Assessment</div>
                                <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $rj->assessment }}</p>
                            </div>
                        </div>
                        @endif
                        @if ($rj->plan)
                        <div class="flex gap-3">
                            <span class="shrink-0 w-6 h-6 rounded bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold leading-none">P</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[11px] font-medium text-gray-400 mb-0.5">Plan</div>
                                <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $rj->plan }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ============================================
             RAWAT INAP CARD
        ============================================ --}}
        @if ($ri)
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="font-semibold">Rawat Inap</h3>
                @if ($ri->tgl_pulang)
                <span class="badge badge-gray">Pulang: {{ $ri->cara_pulang }}</span>
                @else
                <span class="badge badge-teal">Dirawat</span>
                @endif
            </div>
            <div class="card-body text-sm space-y-3">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <div class="text-xs text-gray-500 mb-0.5">DPJP</div>
                        <div class="font-medium">{{ $ri->dpjp->nama_lengkap ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-0.5">Kamar Saat Ini</div>
                        <div class="font-medium">
                            @php $kamarAktif = $ri->kamarInap()->whereNull('keluar')->first(); @endphp
                            {{ $kamarAktif?->kamar?->no_kamar ?? '—' }}
                            @if ($kamarAktif?->kamar?->kelas)
                            <span class="text-xs text-gray-500">({{ $kamarAktif->kamar->kelas->nama }})</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-0.5">Tgl Masuk RI</div>
                        <div class="font-medium">{{ $ri->tgl_masuk_ri?->format('d M Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-0.5">Tgl Pulang</div>
                        <div class="font-medium">{{ $ri->tgl_pulang?->format('d M Y H:i') ?? '—' }}</div>
                    </div>
                </div>

                @if ($ri->alasan_masuk)
                <div class="border-t pt-2">
                    <div class="text-xs text-gray-500 mb-0.5">Alasan Masuk</div>
                    <p class="whitespace-pre-line">{{ $ri->alasan_masuk }}</p>
                </div>
                @endif

                @if ($ri->tgl_pulang && $ri->resume_medis)
                <div class="border-t pt-2">
                    <div class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Resume Medis</div>
                    <p class="whitespace-pre-line">{{ Str::limit($ri->resume_medis, 300) }}</p>
                    <a href="{{ route('pdf.resume', $ri) }}" target="_blank"
                        class="text-xs text-primary-600 hover:underline mt-1 inline-block">📄 Lihat Resume Medis (PDF) →</a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ============================================
             DIAGNOSA
        ============================================ --}}
        @if ($kunjungan->diagnosa->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Diagnosa (ICD-10)</h3>
                <span class="text-xs text-gray-500">{{ $kunjungan->diagnosa->count() }} diagnosa</span>
            </div>
            <div class="card-body space-y-2">
                @foreach ($kunjungan->diagnosa as $dx)
                <div class="flex items-start gap-3 p-2.5 rounded-lg {{ $dx->tipe === 'PRIMER' ? 'bg-primary-50' : 'bg-gray-50' }}">
                    <span class="badge {{ $dx->tipe === 'PRIMER' ? 'badge-teal' : 'badge-gray' }} text-[10px]">{{ $dx->tipe }}</span>
                    <div class="flex-1 text-sm">
                        <span class="font-mono font-semibold">{{ $dx->icd10_kode }}</span>
                        — {{ $dx->icd10->nama }}
                        @if ($dx->catatan)
                        <div class="text-xs text-gray-500 mt-0.5">{{ $dx->catatan }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ============================================
             ORDER LAB
        ============================================ --}}
        @if ($kunjungan->orderLab->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Pemeriksaan Laboratorium</h3>
                <span class="text-xs text-gray-500">{{ $kunjungan->orderLab->count() }} order</span>
            </div>
            <div class="card-body space-y-3">
                @foreach ($kunjungan->orderLab as $order)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    {{-- Order Header --}}
                    <div class="px-3 py-2 bg-gray-50 flex items-center justify-between text-sm">
                        <a href="{{ route('lab.show', $order) }}" class="font-mono font-semibold text-primary-600 hover:underline">{{ $order->no_order }}</a>
                        <div class="flex gap-1">
                            <span class="badge badge-{{ $order->prioritas->value === 'CITO' ? 'red' : 'gray' }}">
                                {{ $order->prioritas->label() }}
                            </span>
                            <span class="badge badge-gray">{{ $order->status->label() }}</span>
                        </div>
                    </div>
                    {{-- Hasil Lab Table --}}
                    @if ($order->hasil->isNotEmpty())
                    <table class="w-full text-xs">
                        <thead class="text-gray-500 bg-gray-50/50">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium">Parameter</th>
                                <th class="text-center px-3 py-2 font-medium">Hasil</th>
                                <th class="text-center px-3 py-2 font-medium">Rujukan</th>
                                <th class="text-center px-3 py-2 font-medium w-16">Flag</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->hasil as $h)
                            @php
                                $isNormal = $h->flag->value === 'N';
                                $isCritical = in_array($h->flag->value, ['LL', 'HH']);
                            @endphp
                            <tr class="border-t {{ $isCritical ? 'bg-red-50' : (!$isNormal ? 'bg-amber-50/50' : '') }}">
                                <td class="px-3 py-1.5">{{ $h->parameter->nama }}</td>
                                <td class="text-center px-3 py-1.5 font-semibold {{ $isCritical ? 'text-red-700' : (!$isNormal ? 'text-amber-700' : 'text-gray-900') }}">
                                    {{ $h->hasil }} {{ $h->satuan }}
                                </td>
                                <td class="text-center px-3 py-1.5 text-gray-500">{{ $h->nilai_rujukan }}</td>
                                <td class="text-center px-3 py-1.5">
                                    <span class="badge {{ $isCritical ? 'bg-red-500 text-white' : ($isNormal ? 'badge-green' : 'bg-amber-200 text-amber-800') }}">{{ $h->flag->value }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ============================================
             ORDER RADIOLOGI
        ============================================ --}}
        @if (isset($kunjungan->orderRadiologi) && $kunjungan->orderRadiologi->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Pemeriksaan Radiologi</h3>
                <span class="text-xs text-gray-500">{{ $kunjungan->orderRadiologi->count() }} order</span>
            </div>
            <div class="card-body space-y-3">
                @foreach ($kunjungan->orderRadiologi as $order)
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <a href="{{ route('rad.show', $order) }}" class="font-mono text-sm font-semibold text-primary-600 hover:underline">{{ $order->no_order }}</a>
                        <div class="flex gap-1">
                            <span class="badge badge-{{ $order->prioritas->value === 'CITO' ? 'red' : 'gray' }}">
                                {{ $order->prioritas->label() }}
                            </span>
                            <span class="badge badge-gray">{{ $order->status->label() }}</span>
                        </div>
                    </div>
                    @foreach ($order->details as $detail)
                    <div class="text-sm text-gray-700">• {{ $detail->pemeriksaan->nama ?? '—' }}</div>
                    @endforeach
                    @foreach ($order->hasil as $hasil)
                    @if ($hasil->ada_temuan_kritis)
                    <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-800">
                        ⚠ <b>Temuan Kritis</b>: {{ Str::limit($hasil->kesan, 150) }}
                    </div>
                    @elseif ($hasil->kesan)
                    <div class="mt-2 text-xs text-gray-600">
                        <b>Kesan:</b> {{ Str::limit($hasil->kesan, 200) }}
                    </div>
                    @endif
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ============================================
             RESEP
        ============================================ --}}
        @if ($kunjungan->resep->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Resep</h3>
                <span class="text-xs text-gray-500">{{ $kunjungan->resep->count() }} resep</span>
            </div>
            <div class="card-body space-y-3">
                @foreach ($kunjungan->resep as $resep)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-3 py-2 bg-gray-50 flex items-center justify-between text-sm">
                        <a href="{{ route('resep.show', $resep) }}" class="font-mono font-semibold text-primary-600 hover:underline">{{ $resep->no_resep }}</a>
                        <span class="badge badge-gray">{{ $resep->status }}</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($resep->details as $item)
                        <div class="px-3 py-2 flex items-center justify-between text-sm">
                            <div>
                                <span class="font-medium text-gray-900">{{ $item->obat->nama }}</span>
                                <span class="text-gray-500 text-xs ml-1">({{ $item->signa }})</span>
                            </div>
                            <span class="text-gray-500 text-xs shrink-0 ml-3">{{ $item->jumlah }} {{ $item->obat->satuan }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ============================================
             CPPT (untuk RI / IGD lama)
        ============================================ --}}
        @if (isset($kunjungan->cppt) && $kunjungan->cppt->isNotEmpty())
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="font-semibold">CPPT (Catatan Perkembangan)</h3>
                <span class="text-xs text-gray-500">{{ $kunjungan->cppt->count() }} catatan</span>
            </div>
            <div class="card-body space-y-2 max-h-96 overflow-y-auto">
                @foreach ($kunjungan->cppt->sortByDesc('waktu_catatan') as $cppt)
                <div class="border-l-2 border-primary-300 pl-3 py-2 bg-gray-50 rounded-r">
                    <div class="text-xs text-gray-500 mb-1">
                        <b>{{ $cppt->waktu_catatan->format('d M Y H:i') }}</b> —
                        {{ $cppt->user->name ?? '-' }}
                        <span class="badge badge-gray ml-1">{{ $cppt->profesi }}</span>
                    </div>
                    <div class="text-xs space-y-0.5">
                        <div><b class="text-blue-700">S:</b> {{ Str::limit($cppt->subjective, 120) }}</div>
                        <div><b class="text-amber-700">A:</b> {{ Str::limit($cppt->assessment, 120) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- =====================================================
         KOLOM KANAN (1/3) — info pasien & kunjungan
    ===================================================== --}}
    <div class="space-y-6">

        {{-- Pasien Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Pasien</h3>
            </div>
            <div class="card-body text-sm space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(substr($kunjungan->pasien->nama, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">{{ $kunjungan->pasien->nama }}</div>
                        <div class="text-xs text-gray-500 font-mono">{{ $kunjungan->pasien->no_rm }}</div>
                    </div>
                </div>

                <div class="border-t pt-2 grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <div class="text-gray-400">Umur</div>
                        <div class="font-medium text-gray-800">{{ $kunjungan->pasien->umur }} thn</div>
                    </div>
                    <div>
                        <div class="text-gray-400">JK</div>
                        <div class="font-medium text-gray-800">{{ $kunjungan->pasien->jenis_kelamin->label() }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Gol. Darah</div>
                        <div class="font-medium text-gray-800">{{ $kunjungan->pasien->gol_darah ?: '—' }}</div>
                    </div>
                </div>

                @if ($kunjungan->pasien->rekamMedis?->alergi_obat)
                <div class="border-t pt-2">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-2.5">
                        <div class="text-xs text-red-700 font-semibold uppercase mb-0.5">⚠ Alergi Obat</div>
                        <div class="text-sm text-red-700">{{ $kunjungan->pasien->rekamMedis->alergi_obat }}</div>
                    </div>
                </div>
                @endif

                <a href="{{ route('pasien.show', $kunjungan->pasien) }}" class="block text-xs text-primary-600 hover:underline pt-1">
                    Lihat profil lengkap →
                </a>
            </div>
        </div>

        {{-- Detail Kunjungan Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Detail Kunjungan</h3>
            </div>
            <div class="card-body text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Penjamin</span>
                    <span class="font-medium">{{ $kunjungan->penjamin->label() }}</span>
                </div>
                @if ($kunjungan->no_sep)
                <div class="flex justify-between">
                    <span class="text-gray-500">No. SEP</span>
                    <span class="font-mono text-xs">{{ $kunjungan->no_sep }}</span>
                </div>
                @endif
                @if ($kunjungan->no_rujukan)
                <div class="flex justify-between">
                    <span class="text-gray-500">No. Rujukan</span>
                    <span>{{ $kunjungan->no_rujukan }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Didaftarkan</span>
                    <span>{{ $kunjungan->tgl_masuk->format('d M Y H:i') }}</span>
                </div>
                @if ($kunjungan->tgl_keluar)
                <div class="flex justify-between">
                    <span class="text-gray-500">Selesai</span>
                    <span>{{ $kunjungan->tgl_keluar->format('d M Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Tindakan Card --}}
        @if ($kunjungan->tindakan->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-sm">Tindakan Medis</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($kunjungan->tindakan as $t)
                <div class="px-5 py-2.5 text-sm flex items-center justify-between">
                    <span class="text-gray-700">{{ $t->tindakan->nama ?? '—' }}</span>
                    <span class="text-xs text-gray-500 font-mono">Rp {{ number_format($t->tarif ?? 0, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Audit trail ringkas --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-sm">Riwayat Aktivitas</h3>
            </div>
            <div class="card-body text-xs space-y-1 text-gray-600">
                <div>Dibuat: {{ $kunjungan->created_at->format('d M Y H:i') }}</div>
                @if ($kunjungan->updated_at != $kunjungan->created_at)
                <div>Diupdate: {{ $kunjungan->updated_at->diffForHumans() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     MODAL ACTIONS — IGD Disposisi
===================================================== --}}
@if ($isIgd && $triase && $sudahDiagnosa)
@include('igd._action-modals', ['kunjungan' => $kunjungan])
@endif

@endsection