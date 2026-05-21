@extends('layouts.app')

@section('title', 'Detail Rawat Jalan')
@section('page-header', true)
@section('page-title', 'Rawat Jalan #'.$rj->no_antrian)
@section('page-subtitle', $rj->kunjungan->pasien->nama.' • '.$rj->poli->nama.' • '.$rj->dokter->nama_lengkap)

@section('page-actions')
    <a href="{{ route('kunjungan.show', $rj->kunjungan) }}" class="btn-secondary">
        ← Lihat Kunjungan
    </a>
    @can('rj.examine')
        @if (! $rj->waktu_selesai_periksa)
            <a href="{{ route('rj.periksa', $rj) }}" class="btn-primary">Lanjut Pemeriksaan</a>
        @endif
    @endcan
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- Tanda Vital --}}
        @if ($rj->tanda_vital)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Tanda Vital</h3></div>
                <div class="card-body grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    @php $tv = $rj->tanda_vital; @endphp
                    @if (isset($tv['td_sistol'], $tv['td_diastol']))
                        <div><div class="text-xs text-gray-500">Tekanan Darah</div><div class="font-semibold">{{ $tv['td_sistol'] }}/{{ $tv['td_diastol'] }} mmHg</div></div>
                    @endif
                    @if (isset($tv['nadi']))<div><div class="text-xs text-gray-500">Nadi</div><div class="font-semibold">{{ $tv['nadi'] }} x/m</div></div>@endif
                    @if (isset($tv['respirasi']))<div><div class="text-xs text-gray-500">Respirasi</div><div class="font-semibold">{{ $tv['respirasi'] }} x/m</div></div>@endif
                    @if (isset($tv['suhu']))<div><div class="text-xs text-gray-500">Suhu</div><div class="font-semibold">{{ $tv['suhu'] }} °C</div></div>@endif
                    @if (isset($tv['spo2']))<div><div class="text-xs text-gray-500">SpO₂</div><div class="font-semibold">{{ $tv['spo2'] }}%</div></div>@endif
                    @if (isset($tv['bb']))<div><div class="text-xs text-gray-500">BB</div><div class="font-semibold">{{ $tv['bb'] }} kg</div></div>@endif
                    @if (isset($tv['tb']))<div><div class="text-xs text-gray-500">TB</div><div class="font-semibold">{{ $tv['tb'] }} cm</div></div>@endif
                </div>
            </div>
        @endif

        {{-- SOAP --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold">SOAP Note</h3></div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @foreach ([
                    'S — Subjective' => $rj->subjective,
                    'O — Objective' => $rj->objective,
                    'A — Assessment' => $rj->assessment,
                    'P — Plan' => $rj->plan,
                ] as $label => $content)
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">{{ $label }}</div>
                        <p class="whitespace-pre-line">{{ $content ?: '—' }}</p>
                    </div>
                @endforeach
                @if ($rj->edukasi)
                    <div class="md:col-span-2">
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Edukasi</div>
                        <p class="whitespace-pre-line">{{ $rj->edukasi }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Diagnosa --}}
        @if ($rj->kunjungan->diagnosa->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Diagnosa (ICD-10)</h3></div>
                <div class="card-body space-y-2">
                    @foreach ($rj->kunjungan->diagnosa as $dx)
                        <div class="flex items-start gap-3 p-2 bg-gray-50 rounded text-sm">
                            <span class="badge badge-{{ $dx->tipe === 'PRIMER' ? 'teal' : 'gray' }}">{{ $dx->tipe }}</span>
                            <div class="flex-1">
                                <span class="font-mono font-semibold">{{ $dx->icd10_kode }}</span>
                                — {{ $dx->icd10->nama }}
                                @if ($dx->catatan)<div class="text-xs text-gray-500 mt-0.5">{{ $dx->catatan }}</div>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar info --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Timeline</h3></div>
            <div class="card-body text-xs space-y-2">
                <div><span class="text-gray-500">Didaftarkan:</span> {{ $rj->created_at->format('d M Y H:i') }}</div>
                @if ($rj->waktu_panggilan)<div><span class="text-gray-500">Dipanggil:</span> {{ $rj->waktu_panggilan->format('H:i') }}</div>@endif
                @if ($rj->waktu_mulai_periksa)<div><span class="text-gray-500">Mulai periksa:</span> {{ $rj->waktu_mulai_periksa->format('H:i') }}</div>@endif
                @if ($rj->waktu_selesai_periksa)<div><span class="text-gray-500">Selesai:</span> {{ $rj->waktu_selesai_periksa->format('H:i') }}</div>@endif
            </div>
        </div>
    </div>
</div>

@endsection
