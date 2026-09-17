@extends('layouts.app')

@section('title', 'Pendaftaran Kunjungan')
@section('page-header', true)
@section('page-title', 'Pendaftaran Kunjungan')
@section('page-subtitle', $pasien ? 'Untuk pasien: '.$pasien->nama.' ('.$pasien->no_rm.')' : 'Pilih pasien dulu')

@section('content')

@if (! $pasien)
<div class="alert alert-warning">
    Silakan pilih pasien dulu dari <a href="{{ route('pasien.index') }}" class="underline">daftar pasien</a>.
</div>
@else

<form method="POST" action="{{ route('kunjungan.store') }}" class="space-y-6"
    x-data="{
        tipe: '{{ old('tipe', 'RJ') }}',
        penjamin: '{{ old('penjamin', 'UMUM') }}',
        poliId: '{{ old('poli_id', '') }}',
        dokterId: '{{ old('dokter_id', '') }}',
        dokterList: [],
        loadingDokter: false,
        async loadDokter() {
            this.dokterId = '';
            if (! this.poliId) {
                this.dokterList = [];
                return;
            }
            this.loadingDokter = true;
            try {
                const res = await fetch(`/kunjungan/dokter-by-poli/${this.poliId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const json = await res.json();
                this.dokterList = json.data || [];
            } catch (e) {
                console.error('Load dokter failed:', e);
                window.notify?.error('Gagal memuat daftar dokter. Coba lagi.');
                this.dokterList = [];
            } finally {
                this.loadingDokter = false;
            }
        },
    }"
    x-init="if (poliId) loadDokter()">
    @csrf
    <input type="hidden" name="pasien_id" value="{{ $pasien->id }}">

    {{-- Ringkas data pasien --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-lg font-semibold">
                    {{ strtoupper(substr($pasien->nama, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-gray-900">{{ $pasien->nama }}</div>
                    <div class="text-sm text-gray-500">
                        {{ $pasien->no_rm }} • {{ $pasien->jenis_kelamin->label() }} • {{ $pasien->umur }} tahun
                    </div>
                </div>
                @if ($pasien->rekamMedis?->alergi_obat)
                <div class="badge badge-red">⚠ Alergi: {{ $pasien->rekamMedis->alergi_obat }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Pre-check: kunjungan aktif --}}
    @if ($kunjunganAktif ?? null)
    <div class="card border-2 border-amber-400 bg-amber-50">
        <div class="card-body">
            <div class="flex items-start gap-3">
                <div class="text-amber-600 text-2xl">⚠</div>
                <div class="flex-1">
                    <div class="font-bold text-amber-900 mb-1">Pasien masih memiliki kunjungan aktif</div>
                    <p class="text-sm text-amber-800 mb-3">
                        Sebelum membuat kunjungan baru, kunjungan sebelumnya harus diselesaikan terlebih dahulu:
                    </p>
                    <div class="bg-white border border-amber-200 rounded-lg p-3 mb-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-mono text-sm font-medium text-gray-900">
                                    {{ $kunjunganAktif->no_kunjungan }}
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    <span class="badge badge-teal text-xs">{{ $kunjunganAktif->tipe->label() }}</span>
                                    ·
                                    <span class="badge badge-yellow text-xs">{{ $kunjunganAktif->status->label() }}</span>
                                    · Sejak {{ $kunjunganAktif->tgl_masuk->diffForHumans() }}
                                </div>
                            </div>
                            <a href="{{ route('kunjungan.show', $kunjunganAktif) }}"
                               class="btn-primary btn-sm whitespace-nowrap">
                                Buka kunjungan →
                            </a>
                        </div>
                    </div>
                    <p class="text-xs text-amber-700">
                        <strong>Yang harus dilakukan:</strong>
                        @if ($kunjunganAktif->tipe->value === 'RJ')
                            Selesaikan pemeriksaan RJ (dokter selesai SOAP + diagnosa) → bayar → kunjungan otomatis SELESAI.
                        @elseif ($kunjunganAktif->tipe->value === 'RI')
                            Pulangkan pasien via menu Rawat Inap → bayar tagihan → kunjungan otomatis SELESAI.
                        @else
                            Selesaikan flow IGD (triase → pemeriksaan → pulang/admisi RI/rujuk) → bayar → SELESAI.
                        @endif
                        Kalau sudah tidak dipakai, bisa batalkan lewat halaman detail kunjungan.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tipe & Penjamin --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Tipe & Penjamin</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Tipe Kunjungan <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                    'RJ' => ['label' => 'Rawat Jalan', 'color' => 'blue'],
                    'IGD' => ['label' => 'IGD', 'color' => 'red'],
                    'RI' => ['label' => 'Rawat Inap', 'color' => 'purple'],
                    ] as $val => $cfg)
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe" value="{{ $val }}" x-model="tipe" class="sr-only peer">
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center
                                        peer-checked:border-primary-600 peer-checked:bg-primary-50 transition">
                            <div class="font-semibold">{{ $cfg['label'] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="penjamin" class="label">Penjamin <span class="text-red-500">*</span></label>
                <select id="penjamin" name="penjamin" x-model="penjamin" required class="select">
                    <option value="UMUM">Umum</option>
                    <option value="BPJS">BPJS Kesehatan</option>
                    <option value="ASURANSI">Asuransi Swasta</option>
                </select>
            </div>

            <div x-show="penjamin === 'BPJS'" x-transition>
                <label class="label">No. SEP <span class="text-red-500">*</span></label>
                <input name="no_sep" type="text" value="{{ old('no_sep') }}" class="input font-mono"
                    placeholder="Generate dari V-Claim BPJS">
                <p class="help">Wajib diisi untuk pasien BPJS. Diperoleh dari aplikasi V-Claim.</p>
            </div>

            <div>
                <label class="label">No. Rujukan (opsional)</label>
                <input name="no_rujukan" type="text" value="{{ old('no_rujukan') }}" class="input">
            </div>
        </div>
    </div>

    {{-- Hint kalau tipe = RI --}}
    <div class="card" x-show="tipe === 'RI'" x-transition>
        <div class="card-body bg-purple-50 border-l-4 border-purple-500">
            <div class="flex items-start gap-3">
                <span class="text-purple-600 text-lg">🏥</span>
                <div class="text-sm text-purple-900">
                    <div class="font-semibold mb-1">Alur Rawat Inap</div>
                    <p>Setelah pendaftaran, Anda akan diarahkan ke <strong>form admisi</strong>
                        untuk pilih kamar & DPJP. Kunjungan RI baru akan aktif setelah pasien
                        ditempatkan di kamar.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Hint kalau tipe = IGD --}}
    <div class="card" x-show="tipe === 'IGD'" x-transition>
        <div class="card-body bg-red-50 border-l-4 border-red-500">
            <div class="flex items-start gap-3">
                <span class="text-red-600 text-lg">🚨</span>
                <div class="text-sm text-red-900">
                    <div class="font-semibold mb-1">Alur IGD</div>
                    <p>Setelah pendaftaran, pasien muncul di <strong>IGD Board</strong>.
                        Petugas IGD wajib melakukan <strong>triase</strong> dalam 5 menit
                        (per standar KARS/JCI).</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Pilih Poli + Dokter (RJ only) — Dokter reactive filter by poli --}}
    <div class="card" x-show="tipe === 'RJ'" x-transition>
        <div class="card-header">
            <h3 class="font-semibold text-gray-800">Tujuan Poli</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Poli <span class="text-red-500">*</span></label>
                <select name="poli_id" x-model="poliId" @change="loadDokter()" class="select">
                    <option value="">— Pilih poli —</option>
                    @foreach ($poli as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} @if($p->lokasi)({{ $p->lokasi }})@endif</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label flex items-center gap-2">
                    Dokter <span class="text-red-500">*</span>
                    <span x-show="loadingDokter" class="text-xs text-gray-400">⏳ memuat…</span>
                </label>
                <select name="dokter_id" x-model="dokterId" class="select"
                        :disabled="!poliId || loadingDokter">
                    <template x-if="!poliId">
                        <option value="">— Pilih poli dulu —</option>
                    </template>
                    <template x-if="poliId && !loadingDokter && dokterList.length === 0">
                        <option value="">— Tidak ada dokter yang praktek di poli ini —</option>
                    </template>
                    <template x-if="poliId && dokterList.length > 0">
                        <option value="">— Pilih dokter —</option>
                    </template>
                    <template x-for="d in dokterList" :key="d.id">
                        <option :value="d.id" x-text="`${d.nama_lengkap} (${d.spesialisasi})`"
                                :selected="d.id === dokterId"></option>
                    </template>
                </select>
                <p class="help" x-show="poliId && !loadingDokter && dokterList.length === 0">
                    Belum ada jadwal dokter di poli ini. Hubungi admin master data.
                </p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('pasien.show', $pasien) }}" class="btn-secondary">Batal</a>
        @if ($kunjunganAktif ?? null)
            <button type="button"
                    onclick="window.notify.warning('Selesaikan atau batalkan dulu kunjungan aktif {{ $kunjunganAktif->no_kunjungan }} sebelum daftarkan kunjungan baru.', 'Tidak Bisa Melanjutkan')"
                    class="btn-primary btn-lg opacity-50 cursor-not-allowed">
                Daftarkan Kunjungan
            </button>
        @else
            <button type="submit" class="btn-primary btn-lg">Daftarkan Kunjungan</button>
        @endif
    </div>
</form>

@endif
@endsection