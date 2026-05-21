@extends('layouts.app')

@section('title', 'Buat Resep')
@section('page-header', true)
@section('page-title', 'Buat Resep')
@section('page-subtitle', 'Untuk: '.$kunjungan->pasien->nama.' ('.$kunjungan->pasien->no_rm.')')

@section('content')

<form method="POST" action="{{ route('resep.store') }}" class="space-y-6"
      x-data="resepApp()">
    @csrf
    <input type="hidden" name="kunjungan_id" value="{{ $kunjungan->id }}">
    <input type="hidden" name="dokter_id" value="{{ $kunjungan->rawatJalan?->dokter_id ?? '' }}">

    {{-- Info pasien & alergi --}}
    @if ($kunjungan->pasien->rekamMedis?->alergi_obat)
        <div class="alert alert-error">
            <strong>⚠ ALERGI OBAT:</strong> {{ $kunjungan->pasien->rekamMedis->alergi_obat }}
        </div>
    @endif

    {{-- Cari obat --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Cari & Tambah Obat</h3>
        </div>
        <div class="card-body">
            <div class="relative">
                <input type="text" x-model="searchTerm" @input.debounce.300ms="searchObat"
                       class="input" placeholder="Ketik nama obat (min. 2 huruf)…">
                <div x-show="results.length > 0" x-cloak
                     class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <button type="button" @click="add(r)"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm border-b last:border-0
                                       flex items-center justify-between">
                            <div>
                                <div class="font-semibold" x-text="r.nama"></div>
                                <div class="text-xs text-gray-500">
                                    <span x-text="r.kode"></span> •
                                    <span x-text="r.kekuatan"></span> •
                                    Rp <span x-text="r.harga.toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                            <div class="text-xs" :class="r.stok < 10 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                                Stok: <span x-text="r.stok"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar item resep --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Item Resep</h3>
            <span class="text-sm font-semibold text-primary-600">
                Total: Rp <span x-text="total.toLocaleString('id-ID')"></span>
            </span>
        </div>
        <div class="card-body">
            <table class="table" x-show="items.length > 0">
                <thead>
                    <tr>
                        <th>Obat</th>
                        <th class="w-24">Jumlah</th>
                        <th class="w-32">Signa</th>
                        <th>Aturan Pakai</th>
                        <th class="text-right">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, i) in items" :key="i">
                        <tr>
                            <td>
                                <div class="font-medium" x-text="item.nama"></div>
                                <div class="text-xs text-gray-500" x-text="item.kekuatan"></div>
                                <input type="hidden" :name="`items[${i}][obat_id]`" :value="item.obat_id">
                            </td>
                            <td>
                                <input type="number" min="1" x-model.number="item.jumlah" @input="recalc"
                                       :name="`items[${i}][jumlah]`" required class="input">
                            </td>
                            <td>
                                <select x-model="item.signa" :name="`items[${i}][signa]`" required class="select">
                                    <option value="">—</option>
                                    <option>1x1</option><option>2x1</option><option>3x1</option><option>4x1</option>
                                    <option>1x2</option><option>2x2</option><option>3x2</option>
                                    <option>k/p</option>
                                </select>
                            </td>
                            <td>
                                <select x-model="item.aturan_pakai" :name="`items[${i}][aturan_pakai]`" class="select">
                                    <option value="">—</option>
                                    <option>Sebelum makan</option>
                                    <option>Sesudah makan</option>
                                    <option>Saat makan</option>
                                    <option>Sebelum tidur</option>
                                    <option>Pagi hari</option>
                                </select>
                            </td>
                            <td class="text-right font-semibold">
                                Rp <span x-text="(item.harga * item.jumlah).toLocaleString('id-ID')"></span>
                            </td>
                            <td>
                                <button type="button" @click="remove(i)" class="text-red-500 hover:text-red-700">✕</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="items.length === 0" class="text-center text-gray-400 py-8 italic">
                Belum ada obat. Cari & tambahkan dari panel atas.
            </p>
        </div>
    </div>

    <div>
        <label class="label">Catatan untuk Apoteker (opsional)</label>
        <textarea name="catatan" rows="2" class="textarea"></textarea>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('kunjungan.show', $kunjungan) }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary btn-lg" :disabled="items.length === 0">
            Kirim ke Farmasi
        </button>
    </div>
</form>

<script>
function resepApp() {
    return {
        searchTerm: '',
        results: [],
        items: [],
        total: 0,
        async searchObat() {
            if (this.searchTerm.length < 2) { this.results = []; return; }
            const res = await axios.get('{{ route('resep.obat.search') }}', { params: { q: this.searchTerm } });
            this.results = res.data;
        },
        add(obat) {
            if (this.items.find(i => i.obat_id === obat.id)) {
                alert('Obat sudah ada dalam resep'); return;
            }
            this.items.push({
                obat_id: obat.id, nama: obat.nama, kekuatan: obat.kekuatan,
                harga: obat.harga, jumlah: 1, signa: '', aturan_pakai: '',
            });
            this.searchTerm = ''; this.results = []; this.recalc();
        },
        remove(i) { this.items.splice(i, 1); this.recalc(); },
        recalc() {
            this.total = this.items.reduce((sum, item) => sum + (item.harga * (item.jumlah || 0)), 0);
        },
    };
}
</script>

@endsection
