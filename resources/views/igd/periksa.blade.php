@extends('layouts.app')

@section('title', 'Pemeriksaan IGD - '.$kunjungan->pasien->nama)
@section('page-header', false)

@php
$rj = $kunjungan->rawatJalan;
$triase = $kunjungan->triase;
$tv = $rj?->tanda_vital ?? [];

$triaseColor = $triase ? match($triase->kategori) {
'MERAH' => 'bg-red-600',
'KUNING' => 'bg-yellow-500',
'HIJAU' => 'bg-green-600',
'HITAM' => 'bg-gray-800',
default => 'bg-gray-400',
} : 'bg-gray-400';
@endphp

@section('content')

{{-- Header banner --}}
<div class="bg-gradient-to-r from-red-600 to-red-700 -mx-6 -mt-6 px-6 py-5 mb-6 shadow-md">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <div class="text-white/80 text-xs uppercase tracking-wider mb-1">🚨 Pemeriksaan IGD</div>
            <h1 class="text-2xl font-bold text-white">{{ $kunjungan->pasien->nama }}</h1>
            <div class="mt-1 text-white/90 text-sm">
                {{ $kunjungan->pasien->no_rm }} • {{ $kunjungan->pasien->umur }} thn, {{ $kunjungan->pasien->jenis_kelamin->label() }} •
                {{ $kunjungan->no_kunjungan }}
            </div>
        </div>
        <div class="text-right">
            @if ($triase)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $triaseColor }} text-white rounded-full text-xs font-bold shadow">
                Triase: {{ $triase->kategori }}
            </span>
            @endif
            <a href="{{ route('kunjungan.show', $kunjungan) }}" class="block mt-2 text-xs text-white/80 hover:text-white">← Kembali</a>
        </div>
    </div>
</div>

{{-- Alergi alert --}}
@if ($kunjungan->pasien->rekamMedis?->alergi_obat)
<div class="mb-5 px-4 py-3 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-center gap-3">
    <span class="text-red-600 text-lg">⚠</span>
    <div class="text-sm">
        <span class="font-bold text-red-900 uppercase text-xs tracking-wide">Alergi:</span>
        <span class="text-red-800 font-medium ml-1">{{ $kunjungan->pasien->rekamMedis->alergi_obat }}</span>
    </div>
</div>
@endif

<form method="POST" action="{{ route('igd.periksa.store', $kunjungan) }}"
    x-data="igdPeriksaForm({{ $kunjungan->diagnosa->count() }}, {{ $kunjungan->diagnosa->where('tipe', 'PRIMER')->count() }})"
    @submit="onSubmit($event)">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ====== KOLOM KIRI: SOAP + TTV ====== --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- TANDA VITAL --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span>🩺</span> Tanda Vital
                    </h3>
                </div>
                <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach ([
                    'td_sistol' => ['label' => 'TD Sistol', 'unit' => 'mmHg', 'placeholder' => '120'],
                    'td_diastol' => ['label' => 'TD Diastol', 'unit' => 'mmHg', 'placeholder' => '80'],
                    'nadi' => ['label' => 'Nadi', 'unit' => 'bpm', 'placeholder' => '80'],
                    'respirasi' => ['label' => 'Respirasi', 'unit' => '/min', 'placeholder' => '18'],
                    'suhu' => ['label' => 'Suhu', 'unit' => '°C', 'placeholder' => '36.5'],
                    'spo2' => ['label' => 'SpO₂', 'unit' => '%', 'placeholder' => '98'],
                    'bb' => ['label' => 'Berat', 'unit' => 'kg', 'placeholder' => '70'],
                    'tb' => ['label' => 'Tinggi', 'unit' => 'cm', 'placeholder' => '170'],
                    ] as $key => $meta)
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                            {{ $meta['label'] }} <span class="text-gray-400 normal-case font-normal">({{ $meta['unit'] }})</span>
                        </label>
                        <input type="number" step="0.1" name="tanda_vital[{{ $key }}]"
                            value="{{ old("tanda_vital.$key", $tv[$key] ?? '') }}"
                            placeholder="{{ $meta['placeholder'] }}"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    @endforeach
                    <div class="col-span-2 md:col-span-4">
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                            GCS (E_M_V_)
                        </label>
                        <input type="text" name="tanda_vital[gcs]"
                            value="{{ old('tanda_vital.gcs', $tv['gcs'] ?? '') }}"
                            placeholder="E4M6V5"
                            class="w-full md:w-1/2 border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            {{-- SOAP --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span>📋</span> SOAP Note
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    @foreach ([
                    'subjective' => ['letter' => 'S', 'label' => 'Subjective', 'desc' => 'Keluhan, anamnesa, riwayat', 'rows' => 3],
                    'objective' => ['letter' => 'O', 'label' => 'Objective', 'desc' => 'Pemeriksaan fisik (inspeksi, palpasi, perkusi, auskultasi)', 'rows' => 4],
                    'assessment' => ['letter' => 'A', 'label' => 'Assessment', 'desc' => 'Penilaian klinis dokter', 'rows' => 2],
                    'plan' => ['letter' => 'P', 'label' => 'Plan', 'desc' => 'Rencana terapi, tindakan, follow-up', 'rows' => 3],
                    ] as $field => $meta)
                    <div>
                        <label class="flex items-center gap-2 mb-1.5">
                            <span class="w-6 h-6 bg-primary-600 text-white text-xs font-bold rounded flex items-center justify-center">{{ $meta['letter'] }}</span>
                            <span class="font-semibold text-sm text-gray-800">{{ $meta['label'] }}</span>
                            <span class="text-xs text-gray-500">— {{ $meta['desc'] }}</span>
                        </label>
                        <textarea name="{{ $field }}" rows="{{ $meta['rows'] }}"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Tulis di sini...">{{ old($field, $rj?->$field) }}</textarea>
                    </div>
                    @endforeach

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5">Edukasi Pasien/Keluarga</label>
                        <textarea name="edukasi" rows="2"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Edukasi tentang kondisi, terapi, kapan harus kembali...">{{ old('edukasi', $rj?->edukasi) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- DIAGNOSA ICD-10 --}}
            <div id="diagnosa-section" class="bg-white rounded-xl shadow-sm border-2 transition-colors"
                :class="diagnosaError ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-200'">
                <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 flex items-center justify-between rounded-t-xl">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span>🔖</span> Diagnosa (ICD-10)
                        <span class="text-xs font-normal text-red-600">*wajib min. 1 PRIMER sebelum Simpan & Lanjut</span>
                    </h3>
                    <span class="text-xs font-semibold px-2 py-1 rounded"
                        :class="totalPrimer >= 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                        x-text="totalPrimer >= 1 ? '✓ Lengkap' : 'Belum ada PRIMER'"></span>
                </div>
                <div class="p-5 space-y-3">
                    {{-- Validation error --}}
                    <div x-show="diagnosaError" x-transition class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                        <span x-text="diagnosaError"></span>
                    </div>

                    {{-- Existing diagnosa --}}
                    @if ($kunjungan->diagnosa->isNotEmpty())
                    <div class="space-y-2 mb-3">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Sudah Tercatat</div>
                        @foreach ($kunjungan->diagnosa as $dx)
                        <div class="flex items-center gap-3 p-2.5 rounded-lg {{ $dx->tipe === 'PRIMER' ? 'bg-primary-50 border border-primary-200' : 'bg-gray-50 border border-gray-200' }}">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $dx->tipe === 'PRIMER' ? 'bg-primary-600 text-white' : 'bg-gray-400 text-white' }}">{{ $dx->tipe }}</span>
                            <span class="font-mono text-sm font-semibold">{{ $dx->icd10_kode }}</span>
                            <span class="flex-1 text-sm text-gray-700">{{ $dx->icd10->nama }}</span>
                            <form method="POST" action="{{ route('diagnosa.destroy', $dx) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="button" onclick="if (confirm('Hapus diagnosa ini?')) this.closest('form').submit()" class="text-red-500 hover:text-red-700 text-xs">✕</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Tambah baru --}}
                    <div class="border-t border-gray-100 pt-3">
                        <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Tambah Diagnosa</div>
                        <div class="relative" @keydown.escape.prevent="closeDropdown()" @click.outside="closeDropdown()">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 110-14 7 7 0 010 14z"/></svg>
                                </span>
                                <input type="text" x-model="query" x-ref="icdInput"
                                    @input.debounce.250ms="search()"
                                    @focus="if (query.length >= 2 && results.length === 0) search(); else if (results.length > 0) positionDropdown()"
                                    @keydown.arrow-down.prevent="navigate(1)"
                                    @keydown.arrow-up.prevent="navigate(-1)"
                                    @keydown.enter.prevent="pickActive()"
                                    placeholder="Ketik nama penyakit atau kode ICD-10 (min 2 huruf)..."
                                    role="combobox" aria-autocomplete="list" :aria-expanded="results.length > 0"
                                    class="w-full pl-10 pr-10 py-2.5 border-gray-300 rounded-lg text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition">
                                <button type="button" x-show="query.length > 0"
                                    @click="query=''; results=[]; activeIndex=-1; $refs.icdInput.focus()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition text-xs">✕</button>
                                <span x-show="loading" class="absolute right-9 top-1/2 -translate-y-1/2 text-primary-500">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                </span>
                            </div>

                            {{-- Dropdown panel --}}
                            <div x-show="results.length > 0 || (query.length >= 2 && !loading)"
                                x-ref="dropdown"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                :class="dropUp ? 'bottom-full mb-2' : 'top-full mt-2'"
                                class="absolute z-20 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-xl ring-1 ring-black/5 overflow-hidden">

                                {{-- Header info --}}
                                <div x-show="results.length > 0"
                                    class="px-3 py-1.5 bg-gradient-to-r from-primary-50 to-blue-50 border-b border-gray-200 flex items-center justify-between text-[10px]">
                                    <span class="font-semibold text-primary-700 uppercase tracking-wider">
                                        <span x-text="results.length"></span> hasil ditemukan
                                    </span>
                                    <span class="text-gray-500 hidden sm:inline">↑↓ navigasi · Enter pilih · Esc tutup</span>
                                </div>

                                {{-- Result list --}}
                                <div class="max-h-72 overflow-y-auto" x-ref="resultList">
                                    <template x-for="(r, i) in results" :key="r.kode">
                                        <button type="button"
                                            @click="select(r)"
                                            @mouseenter="activeIndex = i"
                                            :data-index="i"
                                            :class="activeIndex === i ? 'bg-primary-50 border-l-4 border-l-primary-500' : 'border-l-4 border-l-transparent hover:bg-gray-50'"
                                            class="w-full text-left px-3 py-2.5 border-b border-gray-100 last:border-b-0 text-sm flex items-start gap-3 transition-colors">
                                            <span class="shrink-0 mt-0.5 inline-flex items-center justify-center min-w-[60px] px-2 py-0.5 rounded font-mono text-xs font-bold"
                                                :class="activeIndex === i ? 'bg-primary-600 text-white' : 'bg-gray-100 text-primary-700'"
                                                x-text="r.kode"></span>
                                            <span class="flex-1 text-gray-800 leading-snug" x-html="highlight(r.nama)"></span>
                                            <svg x-show="activeIndex === i" class="w-4 h-4 mt-0.5 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        </button>
                                    </template>
                                </div>

                                {{-- Empty state --}}
                                <div x-show="query.length >= 2 && !loading && results.length === 0"
                                    class="px-4 py-6 text-center">
                                    <div class="text-3xl mb-1">🔎</div>
                                    <div class="text-sm font-medium text-gray-700">Tidak ada hasil</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Coba kata kunci lain atau kode ICD-10 (mis: <span class="font-mono">J18</span>, <span class="font-mono">A09</span>)</div>
                                </div>
                            </div>

                            {{-- Hint kalau belum ngetik --}}
                            <div x-show="query.length > 0 && query.length < 2"
                                class="mt-1.5 text-xs text-gray-500 italic flex items-center gap-1">
                                <span>💡</span> Ketik minimal 2 karakter
                            </div>
                        </div>

                        <template x-if="selected">
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="text-sm mb-2">
                                    <span class="font-mono font-bold text-primary-700" x-text="selected.kode"></span>
                                    <span x-text="' — ' + selected.nama"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-2">
                                    <select x-model="tipe" class="border-gray-300 rounded text-xs">
                                        <option value="PRIMER">PRIMER</option>
                                        <option value="SEKUNDER">SEKUNDER</option>
                                        <option value="KOMPLIKASI">KOMPLIKASI</option>
                                    </select>
                                    <input type="text" x-model="catatan" placeholder="Catatan (opsional)" class="col-span-2 border-gray-300 rounded text-xs">
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="add()" class="btn-primary text-xs">+ Tambah</button>
                                    <button type="button" @click="reset()" class="btn-secondary text-xs">Batal</button>
                                </div>
                            </div>
                        </template>

                        {{-- Hidden inputs untuk diagnosa baru --}}
                        <template x-for="(d, idx) in newDiagnosa" :key="idx">
                            <div class="mt-2 flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded text-xs">
                                <span class="px-1.5 py-0.5 bg-green-600 text-white font-bold rounded" x-text="d.tipe"></span>
                                <span class="font-mono font-semibold" x-text="d.kode"></span>
                                <span class="flex-1" x-text="d.nama"></span>
                                <button type="button" @click="newDiagnosa.splice(idx, 1)" class="text-red-500">✕</button>
                                <input type="hidden" :name="`diagnosa_baru[${idx}][icd10_kode]`" :value="d.kode">
                                <input type="hidden" :name="`diagnosa_baru[${idx}][tipe]`" :value="d.tipe">
                                <input type="hidden" :name="`diagnosa_baru[${idx}][catatan]`" :value="d.catatan">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        {{-- ====== KOLOM KANAN: INFO + QUICK ACTIONS ====== --}}
        <div class="space-y-5">
            {{-- Triase summary --}}
            @if ($triase)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="{{ $triaseColor }} px-4 py-3 text-white">
                    <h3 class="font-semibold text-sm">Triase {{ $triase->kategori }}</h3>
                </div>
                <div class="p-4 text-xs space-y-2">
                    <div>
                        <div class="text-gray-500 uppercase font-semibold mb-1">Keluhan Utama</div>
                        <p class="text-gray-800">{{ $triase->keluhan_utama }}</p>
                    </div>
                    <div class="text-gray-500 border-t border-gray-100 pt-2">
                        oleh <b>{{ $triase->petugas->name ?? '—' }}</b><br>
                        {{ $triase->waktu_triase->format('d M Y H:i') }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick order --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 text-sm">Order Cepat</h3>
                </div>
                <div class="p-4 space-y-2">
                    @can('lab.order')
                    <a href="{{ route('lab.create', ['kunjungan' => $kunjungan->id]) }}"
                        class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:border-primary-400 hover:bg-primary-50 transition text-sm">
                        <span class="text-lg">🧪</span>
                        <span class="font-medium text-gray-800">Order Lab</span>
                    </a>
                    @endcan
                    @if (Route::has('radiologi.create'))
                    @can('radiologi.order')
                    <a href="{{ route('radiologi.create', ['kunjungan' => $kunjungan->id]) }}"
                        class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:border-primary-400 hover:bg-primary-50 transition text-sm">
                        <span class="text-lg">📷</span>
                        <span class="font-medium text-gray-800">Order Radiologi</span>
                    </a>
                    @endcan
                    @endif
                    @can('resep.create')
                    <a href="{{ route('resep.create', ['kunjungan' => $kunjungan->id]) }}"
                        class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:border-primary-400 hover:bg-primary-50 transition text-sm">
                        <span class="text-lg">💊</span>
                        <span class="font-medium text-gray-800">Buat Resep</span>
                    </a>
                    @endcan
                </div>
            </div>

            {{-- Submit panel --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-4">
                <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 text-sm">Simpan Pemeriksaan</h3>
                </div>
                <div class="p-4 space-y-2">
                    <button type="submit" name="action" value="save_draft" class="w-full btn-secondary justify-center">
                        💾 Simpan Draft
                    </button>
                    <button type="submit" name="action" value="save_continue" class="w-full btn-primary justify-center">
                        ✓ Simpan & Lanjut
                    </button>
                    <p class="text-[10px] text-gray-500 text-center mt-2">
                        Setelah simpan, kembali ke detail kunjungan untuk disposisi (admisi/rujuk/pulang).
                    </p>
                </div>
            </div>
        </div>

    </div>
</form>

<script>
    function igdPeriksaForm(existingCount, existingPrimerCount) {
        return {
            // ICD-10 search state
            query: '',
            results: [],
            loading: false,
            selected: null,
            tipe: 'PRIMER',
            catatan: '',
            newDiagnosa: [],
            activeIndex: -1,
            dropUp: false,

            // Validation state
            existingCount: existingCount,
            existingPrimerCount: existingPrimerCount,
            diagnosaError: '',

            get totalPrimer() {
                const newPrimer = this.newDiagnosa.filter(d => d.tipe === 'PRIMER').length
                    + (this.selected && this.tipe === 'PRIMER' ? 1 : 0);
                return this.existingPrimerCount + newPrimer;
            },

            async search() {
                if (this.query.length < 2) {
                    this.results = [];
                    this.activeIndex = -1;
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch(`{{ route('icd10.search') }}?q=${encodeURIComponent(this.query)}`);
                    this.results = await res.json();
                    this.activeIndex = this.results.length > 0 ? 0 : -1;
                    this.positionDropdown();
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            },

            positionDropdown() {
                this.$nextTick(() => {
                    const input = this.$refs.icdInput;
                    if (!input) return;
                    const rect = input.getBoundingClientRect();
                    const estimatedHeight = 320; // max-h-72 + header
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;
                    this.dropUp = spaceBelow < estimatedHeight + 20 && spaceAbove > spaceBelow;
                });
            },

            navigate(delta) {
                if (this.results.length === 0) return;
                this.activeIndex = (this.activeIndex + delta + this.results.length) % this.results.length;
                this.$nextTick(() => {
                    const list = this.$refs.resultList;
                    const item = list?.querySelector(`[data-index="${this.activeIndex}"]`);
                    item?.scrollIntoView({ block: 'nearest' });
                });
            },

            pickActive() {
                if (this.activeIndex >= 0 && this.results[this.activeIndex]) {
                    this.select(this.results[this.activeIndex]);
                }
            },

            highlight(text) {
                if (!this.query || this.query.length < 2) return this.escapeHtml(text);
                const q = this.query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const re = new RegExp(`(${q})`, 'gi');
                return this.escapeHtml(text).replace(re, '<mark class="bg-yellow-200 text-gray-900 rounded px-0.5">$1</mark>');
            },

            escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            },

            closeDropdown() {
                this.results = [];
                this.activeIndex = -1;
            },

            select(r) {
                this.selected = r;
                this.results = [];
                this.activeIndex = -1;
                this.query = '';
            },

            add() {
                if (!this.selected) return;
                this.newDiagnosa.push({
                    kode: this.selected.kode,
                    nama: this.selected.nama,
                    tipe: this.tipe,
                    catatan: this.catatan,
                });
                this.reset();
            },

            reset() {
                this.selected = null;
                this.tipe = 'PRIMER';
                this.catatan = '';
            },

            onSubmit(e) {
                this.diagnosaError = '';
                const action = e.submitter?.value;

                if (action !== 'save_continue') return; // Draft boleh kosong

                // Auto-commit pending ICD-10 yang belum di-Tambah
                if (this.selected) {
                    this.add();
                }

                const totalAll = this.existingCount + this.newDiagnosa.length;
                const totalPrimerNow = this.existingPrimerCount
                    + this.newDiagnosa.filter(d => d.tipe === 'PRIMER').length;

                if (totalAll < 1) {
                    e.preventDefault();
                    this.diagnosaError = 'Diagnosa (ICD-10) wajib diisi minimal 1 sebelum Simpan & Lanjut. Silakan cari & tambahkan diagnosa di bawah.';
                    document.getElementById('diagnosa-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    this.$refs.icdInput?.focus();
                    return;
                }

                if (totalPrimerNow < 1) {
                    e.preventDefault();
                    this.diagnosaError = 'Minimal 1 diagnosa harus bertipe PRIMER. Tambahkan diagnosa PRIMER terlebih dahulu.';
                    document.getElementById('diagnosa-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            },
        };
    }
</script>

@endsection