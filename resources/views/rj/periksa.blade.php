@extends('layouts.app')

@section('title', 'Pemeriksaan')
@section('page-header', true)
@section('page-title', 'Pemeriksaan RJ — #'.$rj->no_antrian)
@section('page-subtitle', $rj->kunjungan->pasien->nama.' • '.$rj->poli->nama)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- Kolom kiri: data pasien (1/4) --}}
    <div class="space-y-4">
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Pasien</h3></div>
            <div class="card-body text-sm">
                <div class="font-semibold text-base">{{ $rj->kunjungan->pasien->nama }}</div>
                <div class="text-xs text-gray-500 mb-3">
                    {{ $rj->kunjungan->pasien->no_rm }}
                </div>
                <div class="space-y-1 text-xs">
                    <div><span class="text-gray-500">JK:</span> {{ $rj->kunjungan->pasien->jenis_kelamin->label() }}</div>
                    <div><span class="text-gray-500">Umur:</span> {{ $rj->kunjungan->pasien->umur_lengkap }}</div>
                    <div><span class="text-gray-500">Gol. Darah:</span>
                        <strong class="text-red-600">{{ $rj->kunjungan->pasien->gol_darah ?: '—' }}</strong>
                    </div>
                </div>

                @if ($rj->kunjungan->pasien->rekamMedis?->alergi_obat)
                    <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs">
                        <div class="font-semibold text-red-700">⚠ Alergi Obat</div>
                        <div class="text-red-800">{{ $rj->kunjungan->pasien->rekamMedis->alergi_obat }}</div>
                    </div>
                @endif
                @if ($rj->kunjungan->pasien->rekamMedis?->riwayat_penyakit)
                    <div class="mt-2 p-2 bg-gray-50 rounded text-xs">
                        <div class="font-semibold text-gray-700">Riwayat</div>
                        <div class="text-gray-600">{{ Str::limit($rj->kunjungan->pasien->rekamMedis->riwayat_penyakit, 150) }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick action: order lab / buat resep --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Tindakan</h3></div>
            <div class="card-body space-y-2">
                <a href="#" class="btn-secondary w-full justify-start" onclick="alert('Modul Lab — Tier 2'); return false;">
                    🧪 Order Pemeriksaan Lab
                </a>
                @can('farmasi.create_resep')
                    <a href="{{ route('resep.create', ['kunjungan_id' => $rj->kunjungan_id]) }}"
                       class="btn-secondary w-full justify-start">
                        💊 Buat Resep
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Kolom utama: SOAP + diagnosa (3/4) --}}
    <div class="lg:col-span-3">
        <form method="POST" action="{{ route('rj.soap', $rj) }}" class="space-y-6"
              x-data="diagnosaApp(@js($rj->kunjungan->diagnosa->map(fn($d) => ['icd10_kode' => $d->icd10_kode, 'nama' => $d->icd10->nama, 'tipe' => $d->tipe, 'catatan' => $d->catatan])))">
            @csrf

            {{-- Tanda Vital --}}
            <div class="card">
                <div class="card-header"><h3 class="font-semibold">Tanda Vital</h3></div>
                <div class="card-body grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    @php $tv = $rj->tanda_vital ?? []; @endphp
                    <div>
                        <label class="label text-xs">TD Sistol</label>
                        <input name="tanda_vital[td_sistol]" type="number" value="{{ old('tanda_vital.td_sistol', $tv['td_sistol'] ?? '') }}" class="input" placeholder="mmHg">
                    </div>
                    <div>
                        <label class="label text-xs">TD Diastol</label>
                        <input name="tanda_vital[td_diastol]" type="number" value="{{ old('tanda_vital.td_diastol', $tv['td_diastol'] ?? '') }}" class="input" placeholder="mmHg">
                    </div>
                    <div>
                        <label class="label text-xs">Nadi</label>
                        <input name="tanda_vital[nadi]" type="number" value="{{ old('tanda_vital.nadi', $tv['nadi'] ?? '') }}" class="input" placeholder="x/menit">
                    </div>
                    <div>
                        <label class="label text-xs">Respirasi</label>
                        <input name="tanda_vital[respirasi]" type="number" value="{{ old('tanda_vital.respirasi', $tv['respirasi'] ?? '') }}" class="input" placeholder="x/menit">
                    </div>
                    <div>
                        <label class="label text-xs">Suhu</label>
                        <input name="tanda_vital[suhu]" type="number" step="0.1" value="{{ old('tanda_vital.suhu', $tv['suhu'] ?? '') }}" class="input" placeholder="°C">
                    </div>
                    <div>
                        <label class="label text-xs">SpO₂</label>
                        <input name="tanda_vital[spo2]" type="number" value="{{ old('tanda_vital.spo2', $tv['spo2'] ?? '') }}" class="input" placeholder="%">
                    </div>
                    <div>
                        <label class="label text-xs">BB</label>
                        <input name="tanda_vital[bb]" type="number" step="0.1" value="{{ old('tanda_vital.bb', $tv['bb'] ?? '') }}" class="input" placeholder="kg">
                    </div>
                    <div>
                        <label class="label text-xs">TB</label>
                        <input name="tanda_vital[tb]" type="number" step="0.1" value="{{ old('tanda_vital.tb', $tv['tb'] ?? '') }}" class="input" placeholder="cm">
                    </div>
                </div>
            </div>

            {{-- SOAP --}}
            <div class="card">
                <div class="card-header"><h3 class="font-semibold">SOAP Note</h3></div>
                <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">S — Subjective (Keluhan / Anamnesa)</label>
                        <textarea name="subjective" rows="4" class="textarea"
                                  placeholder="Apa yang dikeluhkan pasien?">{{ old('subjective', $rj->subjective) }}</textarea>
                    </div>
                    <div>
                        <label class="label">O — Objective (Pemeriksaan Fisik)</label>
                        <textarea name="objective" rows="4" class="textarea"
                                  placeholder="Inspeksi, palpasi, perkusi, auskultasi…">{{ old('objective', $rj->objective) }}</textarea>
                    </div>
                    <div>
                        <label class="label">A — Assessment (Penilaian)</label>
                        <textarea name="assessment" rows="4" class="textarea"
                                  placeholder="Kesimpulan dari S+O (diagnosa di section bawah)">{{ old('assessment', $rj->assessment) }}</textarea>
                    </div>
                    <div>
                        <label class="label">P — Plan (Rencana Terapi)</label>
                        <textarea name="plan" rows="4" class="textarea"
                                  placeholder="Obat, tindakan, follow-up…">{{ old('plan', $rj->plan) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Edukasi Pasien</label>
                        <textarea name="edukasi" rows="2" class="textarea"
                                  placeholder="Edukasi diet, gaya hidup, kepatuhan obat…">{{ old('edukasi', $rj->edukasi) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Diagnosa ICD-10 --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="font-semibold">Diagnosa (ICD-10)</h3>
                        <p class="text-xs text-gray-500">Minimal 1 diagnosa PRIMER wajib sebelum menyelesaikan pemeriksaan</p>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Search box --}}
                    <div class="relative mb-4">
                        <input type="text" x-model="searchTerm" @input.debounce.300ms="searchIcd"
                               class="input" placeholder="Cari ICD-10 berdasarkan kode atau nama…">
                        <div x-show="results.length > 0" x-cloak
                             class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                            <template x-for="r in results" :key="r.kode">
                                <button type="button" @click="add(r)"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm border-b last:border-0">
                                    <span class="font-mono font-semibold text-primary-700" x-text="r.kode"></span>
                                    — <span x-text="r.nama"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Selected list --}}
                    <div class="space-y-2">
                        <template x-for="(dx, i) in selected" :key="i">
                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                                <select x-model="dx.tipe" :name="`diagnosa[${i}][tipe]`" class="select w-32 text-xs">
                                    <option value="PRIMER">PRIMER</option>
                                    <option value="SEKUNDER">SEKUNDER</option>
                                    <option value="KOMPLIKASI">KOMPLIKASI</option>
                                </select>
                                <div class="flex-1 text-sm">
                                    <span class="font-mono font-semibold" x-text="dx.icd10_kode"></span>
                                    — <span x-text="dx.nama"></span>
                                    <input type="hidden" :name="`diagnosa[${i}][icd10_kode]`" :value="dx.icd10_kode">
                                </div>
                                <input type="text" x-model="dx.catatan" :name="`diagnosa[${i}][catatan]`"
                                       placeholder="Catatan…" class="input text-xs w-48">
                                <button type="button" @click="remove(i)" class="text-red-500 hover:text-red-700 px-2">✕</button>
                            </div>
                        </template>
                        <p x-show="selected.length === 0" class="text-sm text-gray-400 italic">
                            Belum ada diagnosa. Cari ICD-10 di atas untuk menambahkan.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('rj.antrian') }}" class="btn-secondary">← Kembali ke Antrian</a>
                <div class="flex gap-2">
                    <button type="submit" class="btn-secondary">Simpan Draft</button>
                </div>
            </div>
        </form>

        {{-- Form selesaikan pemeriksaan (terpisah supaya tidak ikut submit SOAP) --}}
        <form method="POST" action="{{ route('rj.selesai', $rj) }}" class="mt-4">
            @csrf
            <div class="card bg-primary-50 ring-primary-200">
                <div class="card-body flex items-center justify-between">
                    <div class="text-sm text-primary-900">
                        Setelah semua dokumentasi lengkap (SOAP + minimal 1 diagnosa primer),
                        tutup pemeriksaan agar pasien lanjut ke farmasi/kasir.
                    </div>
                    <button class="btn-success"
                            onclick="return confirm('Yakin selesaikan pemeriksaan? Pastikan SOAP sudah disimpan terlebih dahulu.')">
                        ✓ Selesaikan Pemeriksaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function diagnosaApp(initial) {
    return {
        searchTerm: '',
        results: [],
        selected: initial || [],
        async searchIcd() {
            if (this.searchTerm.length < 2) { this.results = []; return; }
            const res = await axios.get('{{ route('rj.icd.search') }}', { params: { q: this.searchTerm } });
            this.results = res.data;
        },
        add(item) {
            if (this.selected.find(s => s.icd10_kode === item.kode)) return;
            const tipe = this.selected.some(s => s.tipe === 'PRIMER') ? 'SEKUNDER' : 'PRIMER';
            this.selected.push({ icd10_kode: item.kode, nama: item.nama, tipe, catatan: '' });
            this.searchTerm = '';
            this.results = [];
        },
        remove(i) { this.selected.splice(i, 1); },
    };
}
</script>

@endsection
