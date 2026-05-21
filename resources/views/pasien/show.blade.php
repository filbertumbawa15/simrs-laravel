@extends('layouts.app')

@section('title', $pasien->nama)
@section('page-header', true)
@section('page-title', $pasien->nama)
@section('page-subtitle', 'No. RM: '.$pasien->no_rm.' • '.$pasien->jenis_kelamin->label().' • '.$pasien->umur.' tahun')

@section('page-actions')
<div class="flex gap-2">
    @can('pasien.update')
    <a href="{{ route('pasien.edit', $pasien) }}" class="btn-secondary">Edit</a>
    @endcan
    @can('kunjungan.create')
    <a href="{{ route('kunjungan.create', ['pasien_id' => $pasien->id]) }}" class="btn-primary">
        Daftarkan Kunjungan Baru
    </a>
    @endcan
</div>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Kolom kiri: identitas + rekam medis --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">Identitas Pasien</h3>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-500">NIK</dt>
                        <dd class="font-mono">{{ $pasien->nik ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Tempat, Tanggal Lahir</dt>
                        <dd>{{ $pasien->tempat_lahir ?: '—' }}, {{ $pasien->tgl_lahir->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Umur</dt>
                        <dd>{{ $pasien->umur_lengkap }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Golongan Darah</dt>
                        <dd class="font-semibold text-red-600">{{ $pasien->gol_darah ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Status Pernikahan</dt>
                        <dd>{{ $pasien->status_pernikahan ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Agama</dt>
                        <dd>{{ $pasien->agama ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Pekerjaan</dt>
                        <dd>{{ $pasien->pekerjaan ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">No. Telepon</dt>
                        <dd>{{ $pasien->telp ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500">Alamat</dt>
                        <dd>{{ $pasien->alamat_lengkap }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Rekam medis ringkas --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">Rekam Medis Permanen</h3>
                <span class="text-xs text-gray-500">Catatan klinis lintas-kunjungan</span>
            </div>
            <div class="card-body space-y-4 text-sm">
                @php $rm = $pasien->rekamMedis; @endphp

                <div>
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Riwayat Penyakit</div>
                    <p class="text-gray-700">{{ $rm?->riwayat_penyakit ?: '—' }}</p>
                </div>
                <div>
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Riwayat Keluarga</div>
                    <p class="text-gray-700">{{ $rm?->riwayat_keluarga ?: '—' }}</p>
                </div>
                <div>
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Pengobatan Saat Ini</div>
                    <p class="text-gray-700">{{ $rm?->riwayat_pengobatan ?: '—' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">⚠ Alergi Obat</div>
                        <p class="text-red-600 font-medium">{{ $rm?->alergi_obat ?: 'Tidak ada' }}</p>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">⚠ Alergi Makanan</div>
                        <p class="text-red-600 font-medium">{{ $rm?->alergi_makanan ?: 'Tidak ada' }}</p>
                    </div>
                </div>
                <div>
                    <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">Kebiasaan</div>
                    <p class="text-gray-700">{{ $rm?->kebiasaan ?: '—' }}</p>
                </div>
            </div>
        </div>

        {{-- History kunjungan --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">10 Kunjungan Terakhir</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Kunjungan</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pasien->kunjungan as $kj)
                        <tr>
                            <td>{{ $kj->tgl_masuk->format('d M Y H:i') }}</td>
                            <td class="font-mono text-xs">{{ $kj->no_kunjungan }}</td>
                            <td><span class="badge {{ $kj->tipe->badge() }}">{{ $kj->tipe->label() }}</span></td>
                            <td><span class="badge badge-gray">{{ $kj->status->label() }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('kunjungan.show', $kj) }}" class="text-primary-600 hover:underline text-xs">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-400">Belum pernah berkunjung</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Kolom kanan: kontak darurat + asuransi --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">Kontak Darurat</h3>
            </div>
            <div class="card-body text-sm">
                @if ($pasien->kontak_darurat_nama)
                <div class="font-semibold">{{ $pasien->kontak_darurat_nama }}</div>
                <div class="text-xs text-gray-500">{{ $pasien->kontak_darurat_hubungan }}</div>
                <div class="mt-1">{{ $pasien->kontak_darurat_telp }}</div>
                @else
                <p class="text-gray-400 italic">Belum diisi</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">Asuransi & Penjamin</h3>
            </div>
            <div class="card-body space-y-3">
                @forelse ($pasien->asuransi as $a)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="font-semibold text-sm">{{ $a->asuransi->nama }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        <div>No. Polis: <span class="font-mono">{{ $a->no_polis }}</span></div>
                        @if ($a->kelas_hak)
                        <div>Kelas Hak: <span class="badge badge-teal">{{ $a->kelas_hak }}</span></div>
                        @endif
                        @if ($a->valid_until)
                        <div>Berlaku s/d: {{ $a->valid_until->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 italic">Pasien umum (tanpa penjamin)</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection