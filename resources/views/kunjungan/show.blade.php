@extends('layouts.app')

@section('title', $kunjungan->no_kunjungan)
@section('page-header', true)
@section('page-title', 'Kunjungan '.$kunjungan->no_kunjungan)
@section('page-subtitle', $kunjungan->pasien->nama.' • '.$kunjungan->tgl_masuk->format('d M Y H:i'))

@section('page-actions')
<div class="flex items-center gap-2">
    <span class="badge {{ $kunjungan->tipe->badge() }}">{{ $kunjungan->tipe->label() }}</span>
    <span class="badge badge-yellow">{{ $kunjungan->status->label() }}</span>
</div>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Kolom kiri (2/3) --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Quick actions sesuai status --}}
        @if (in_array($kunjungan->status->value, ['TERDAFTAR', 'DALAM_PEMERIKSAAN', 'MENUNGGU_HASIL_LAB', 'MENUNGGU_OBAT', 'MENUNGGU_PEMBAYARAN']))
        <div class="card bg-primary-50 ring-primary-200">
            <div class="card-body">
                <h3 class="font-semibold text-primary-900 mb-3">Tindakan Berikutnya</h3>
                <div class="flex flex-wrap gap-2">
                    @if ($kunjungan->rawatJalan && $kunjungan->status->value === 'TERDAFTAR')
                    @can('rj.examine')
                    <a href="{{ route('rj.periksa', $kunjungan->rawatJalan) }}" class="btn-primary">
                        Mulai Pemeriksaan
                    </a>
                    @endcan
                    @endif

                    @if (! $kunjungan->tagihan && $kunjungan->status->value === 'MENUNGGU_PEMBAYARAN')
                    @can('billing.generate')
                    <form method="POST" action="{{ route('billing.generate', $kunjungan) }}">
                        @csrf
                        <button class="btn-warning">Generate Tagihan</button>
                    </form>
                    @endcan
                    @endif

                    @if ($kunjungan->tagihan)
                    <a href="{{ route('billing.show', $kunjungan->tagihan) }}" class="btn-secondary">
                        Lihat Tagihan ({{ Str::limit($kunjungan->tagihan->no_tagihan, 20) }})
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Rawat Jalan --}}
        @if ($kunjungan->rawatJalan)
        @php $rj = $kunjungan->rawatJalan; @endphp
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">Rawat Jalan</h3>
                <span class="badge badge-blue">Antrian #{{ $rj->no_antrian }}</span>
            </div>
            <div class="card-body space-y-3 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">Poli</div>
                        <div class="font-medium">{{ $rj->poli->nama }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Dokter Pemeriksa</div>
                        <div class="font-medium">{{ $rj->dokter->nama_lengkap }}</div>
                    </div>
                </div>

                @if ($rj->subjective || $rj->objective || $rj->plan)
                <div class="border-t pt-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if ($rj->subjective)
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase">S — Subjective</div>
                            <p class="whitespace-pre-line">{{ $rj->subjective }}</p>
                        </div>
                        @endif
                        @if ($rj->objective)
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase">O — Objective</div>
                            <p class="whitespace-pre-line">{{ $rj->objective }}</p>
                        </div>
                        @endif
                        @if ($rj->assessment)
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase">A — Assessment</div>
                            <p class="whitespace-pre-line">{{ $rj->assessment }}</p>
                        </div>
                        @endif
                        @if ($rj->plan)
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase">P — Plan</div>
                            <p class="whitespace-pre-line">{{ $rj->plan }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Diagnosa --}}
        @if ($kunjungan->diagnosa->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Diagnosa (ICD-10)</h3>
            </div>
            <div class="card-body space-y-2">
                @foreach ($kunjungan->diagnosa as $dx)
                <div class="flex items-start gap-3 p-2 bg-gray-50 rounded">
                    <span class="badge badge-{{ $dx->tipe === 'PRIMER' ? 'teal' : 'gray' }}">{{ $dx->tipe }}</span>
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

        {{-- Order Lab --}}
        @if ($kunjungan->orderLab->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Pemeriksaan Laboratorium</h3>
            </div>
            <div class="card-body space-y-3">
                @foreach ($kunjungan->orderLab as $order)
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-sm font-semibold">{{ $order->no_order }}</span>
                        <span class="badge badge-{{ $order->prioritas->value === 'CITO' ? 'red' : 'gray' }}">
                            {{ $order->prioritas->label() }}
                        </span>
                    </div>
                    @if ($order->hasil->isNotEmpty())
                    <table class="w-full text-xs">
                        <thead class="text-gray-500">
                            <tr>
                                <th class="text-left">Parameter</th>
                                <th>Hasil</th>
                                <th>Rujukan</th>
                                <th>Flag</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->hasil as $h)
                            <tr class="border-t">
                                <td class="py-1">{{ $h->parameter->nama }}</td>
                                <td class="text-center font-semibold">{{ $h->hasil }} {{ $h->satuan }}</td>
                                <td class="text-center text-gray-500">{{ $h->nilai_rujukan }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $h->flag->color() }}">{{ $h->flag->value }}</span>
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

        {{-- Resep --}}
        @if ($kunjungan->resep->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Resep</h3>
            </div>
            <div class="card-body space-y-3">
                @foreach ($kunjungan->resep as $resep)
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2 text-sm">
                        <a href="{{ route('resep.show', $resep) }}" class="font-mono font-semibold text-primary-600 hover:underline">{{ $resep->no_resep }}</a>
                        <span class="badge badge-gray">{{ $resep->status }}</span>
                    </div>
                    <ul class="text-sm space-y-1">
                        @foreach ($resep->details as $item)
                        <li class="flex justify-between">
                            <span>{{ $item->obat->nama }} ({{ $item->signa }})</span>
                            <span class="text-gray-500">{{ $item->jumlah }} {{ $item->obat->satuan }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Kolom kanan (1/3) --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Pasien</h3>
            </div>
            <div class="card-body text-sm space-y-2">
                <div>
                    <div class="font-semibold">{{ $kunjungan->pasien->nama }}</div>
                    <div class="text-xs text-gray-500">{{ $kunjungan->pasien->no_rm }}</div>
                </div>
                <div class="border-t pt-2 space-y-1 text-xs">
                    <div><span class="text-gray-500">Umur:</span> {{ $kunjungan->pasien->umur }} thn</div>
                    <div><span class="text-gray-500">JK:</span> {{ $kunjungan->pasien->jenis_kelamin->label() }}</div>
                    <div><span class="text-gray-500">Gol. Darah:</span> {{ $kunjungan->pasien->gol_darah ?: '—' }}</div>
                </div>
                @if ($kunjungan->pasien->rekamMedis?->alergi_obat)
                <div class="border-t pt-2 mt-2">
                    <div class="text-xs text-red-600 font-semibold uppercase">⚠ Alergi Obat</div>
                    <div class="text-sm text-red-700">{{ $kunjungan->pasien->rekamMedis->alergi_obat }}</div>
                </div>
                @endif
                <a href="{{ route('pasien.show', $kunjungan->pasien) }}" class="block text-xs text-primary-600 hover:underline pt-2">
                    Lihat profil lengkap →
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Detail Kunjungan</h3>
            </div>
            <div class="card-body text-sm space-y-2">
                <div><span class="text-gray-500">Penjamin:</span> {{ $kunjungan->penjamin->label() }}</div>
                @if ($kunjungan->no_sep)
                <div><span class="text-gray-500">No. SEP:</span> <span class="font-mono text-xs">{{ $kunjungan->no_sep }}</span></div>
                @endif
                @if ($kunjungan->no_rujukan)
                <div><span class="text-gray-500">No. Rujukan:</span> {{ $kunjungan->no_rujukan }}</div>
                @endif
                <div><span class="text-gray-500">Didaftarkan:</span> {{ $kunjungan->tgl_masuk->format('d M Y H:i') }}</div>
                @if ($kunjungan->tgl_keluar)
                <div><span class="text-gray-500">Selesai:</span> {{ $kunjungan->tgl_keluar->format('d M Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection