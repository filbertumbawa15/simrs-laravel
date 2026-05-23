{{--
    Modal-modal untuk disposisi IGD.
    Dipisah biar show.blade.php tidak terlalu panjang.

    Variabel yang tersedia: $kunjungan (dengan triase + diagnosa sudah ada)
--}}

{{-- ===================================================
     MODAL 1: ADMISI RANAP (paling kompleks)
===================================================== --}}
@can('ri.admisi')
<dialog id="modal-admisi" class="rounded-lg shadow-xl backdrop:bg-black/50 p-0 w-full max-w-2xl">
    <form method="POST" action="{{ route('igd.admisi.store', $kunjungan) }}" class="bg-white">
        @csrf
        <div class="px-6 py-4 border-b bg-amber-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-amber-900">🛏 Admisi Rawat Inap</h3>
                <button type="button" onclick="document.getElementById('modal-admisi').close()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <p class="text-xs text-amber-700 mt-1">
                Pasien akan dipindahkan dari IGD ke rawat inap. Pastikan diagnosa sudah benar.
            </p>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    DPJP (Dokter Penanggung Jawab) <span class="text-red-500">*</span>
                </label>
                <select name="dpjp_id" required class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">— Pilih DPJP —</option>
                    @foreach (\App\Models\Dokter::where('is_dpjp', true)->orderBy('nama_lengkap')->get() as $d)
                        <option value="{{ $d->id }}">{{ $d->nama_lengkap }}
                            @if ($d->spesialisasi) — {{ $d->spesialisasi }} @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kamar <span class="text-red-500">*</span>
                </label>
                <select name="kamar_id" required class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">— Pilih Kamar Tersedia —</option>
                    @foreach (\App\Models\Kamar::with('kelas')->where('status', 'TERSEDIA')->where('is_active', true)->orderBy('no_kamar')->get()->groupBy('kelas.nama') as $kelas => $kamars)
                        <optgroup label="Kelas {{ $kelas }}">
                            @foreach ($kamars as $k)
                                <option value="{{ $k->id }}">
                                    {{ $k->no_kamar }} — Rp {{ number_format((float)($k->kelas->tarif_per_hari ?? 0), 0, ',', '.') }}/hari
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Masuk Ranap <span class="text-red-500">*</span>
                </label>
                <textarea name="alasan_masuk" required rows="3" class="w-full border-gray-300 rounded-md text-sm"
                    placeholder="Indikasi rawat inap, kondisi klinis saat ini, rencana penanganan..."></textarea>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded p-3 text-xs text-blue-800">
                ℹ Sistem akan otomatis:
                <ul class="ml-4 list-disc mt-1">
                    <li>Update tipe kunjungan dari IGD ke RI</li>
                    <li>Set kamar yang dipilih jadi TERISI</li>
                    <li>Catat waktu admisi</li>
                </ul>
            </div>
        </div>

        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('modal-admisi').close()" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-warning">🛏 Admisi Sekarang</button>
        </div>
    </form>
</dialog>
@endcan

{{-- ===================================================
     MODAL 2: RUJUK KE RS LAIN
===================================================== --}}
@can('igd.disposisi')
<dialog id="modal-rujuk" class="rounded-lg shadow-xl backdrop:bg-black/50 p-0 w-full max-w-xl">
    <form method="POST" action="{{ route('igd.rujuk.store', $kunjungan) }}" class="bg-white">
        @csrf
        <div class="px-6 py-4 border-b bg-blue-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-blue-900">🚑 Rujuk ke RS Lain</h3>
                <button type="button" onclick="document.getElementById('modal-rujuk').close()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama RS Tujuan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="rs_tujuan" required class="w-full border-gray-300 rounded-md text-sm"
                    placeholder="Contoh: RSUP H. Adam Malik, Medan">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Rujukan <span class="text-red-500">*</span>
                </label>
                <select name="alasan_rujuk" required class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">— Pilih —</option>
                    <option value="FASILITAS_TIDAK_TERSEDIA">Fasilitas/Alat tidak tersedia di RS ini</option>
                    <option value="SPESIALIS_TIDAK_TERSEDIA">Dokter spesialis tidak tersedia</option>
                    <option value="KAMAR_PENUH">Kamar rawat inap penuh</option>
                    <option value="ICU_PENUH">ICU/HCU penuh</option>
                    <option value="PERMINTAAN_PASIEN">Permintaan pasien/keluarga</option>
                    <option value="LAINNYA">Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan Rujukan <span class="text-red-500">*</span>
                </label>
                <textarea name="catatan_rujuk" required rows="3" class="w-full border-gray-300 rounded-md text-sm"
                    placeholder="Detail kondisi pasien, tindakan yang sudah dilakukan, terapi yang sedang diberikan..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cara Transportasi</label>
                <select name="transportasi" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="AMBULANS_RS">Ambulans RS ini</option>
                    <option value="AMBULANS_LAIN">Ambulans pihak ketiga</option>
                    <option value="KENDARAAN_PRIBADI">Kendaraan pribadi</option>
                </select>
            </div>
        </div>

        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('modal-rujuk').close()" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-primary">🚑 Rujuk & Cetak Surat Rujukan</button>
        </div>
    </form>
</dialog>

{{-- ===================================================
     MODAL 3: PULANG DARI IGD (selesai observasi)
===================================================== --}}
<dialog id="modal-pulang-igd" class="rounded-lg shadow-xl backdrop:bg-black/50 p-0 w-full max-w-xl">
    <form method="POST" action="{{ route('igd.pulang.store', $kunjungan) }}" class="bg-white">
        @csrf
        <div class="px-6 py-4 border-b bg-green-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-green-900">🏠 Pulangkan dari IGD</h3>
                <button type="button" onclick="document.getElementById('modal-pulang-igd').close()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <p class="text-xs text-green-700 mt-1">
                Pasien selesai observasi/penanganan IGD dan diizinkan pulang.
            </p>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kondisi Saat Pulang <span class="text-red-500">*</span>
                </label>
                <select name="kondisi_pulang" required class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">— Pilih —</option>
                    <option value="SEMBUH">Sembuh</option>
                    <option value="MEMBAIK">Membaik (lanjut rawat jalan)</option>
                    <option value="STABIL">Stabil (observasi cukup)</option>
                    <option value="APS">Atas Permintaan Sendiri (APS) - menolak rawat inap</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Instruksi Pulang <span class="text-red-500">*</span>
                </label>
                <textarea name="instruksi_pulang" required rows="4" class="w-full border-gray-300 rounded-md text-sm"
                    placeholder="Obat yang harus diminum, kapan kontrol, warning sign (segera kembali ke IGD jika...)"></textarea>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded p-3 text-xs text-amber-800">
                ⚠ Pastikan pasien atau keluarga memahami instruksi. Untuk APS, wajib tanda tangan formulir penolakan.
            </div>
        </div>

        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('modal-pulang-igd').close()" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-primary">🏠 Pulangkan & Lanjut Billing</button>
        </div>
    </form>
</dialog>

{{-- ===================================================
     MODAL 4: MENINGGAL DI IGD
===================================================== --}}
<dialog id="modal-meninggal" class="rounded-lg shadow-xl backdrop:bg-black/50 p-0 w-full max-w-xl">
    <form method="POST" action="{{ route('igd.meninggal.store', $kunjungan) }}" class="bg-white">
        @csrf
        <div class="px-6 py-4 border-b bg-gray-100">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">⚱ Catat Kematian di IGD</h3>
                <button type="button" onclick="document.getElementById('modal-meninggal').close()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
        </div>

        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal & Jam Meninggal <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="tgl_meninggal" required
                    value="{{ now()->format('Y-m-d\TH:i') }}"
                    class="w-full border-gray-300 rounded-md text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sebab Kematian (Dasar) <span class="text-red-500">*</span>
                </label>
                <textarea name="sebab_kematian" required rows="3" class="w-full border-gray-300 rounded-md text-sm"
                    placeholder="Penyebab kematian sesuai ICD-10..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status Saat Tiba
                </label>
                <select name="status_tiba" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="HIDUP">Tiba dalam keadaan hidup, meninggal saat dirawat</option>
                    <option value="DOA">DOA (Death on Arrival) - sudah meninggal saat tiba</option>
                </select>
            </div>

            <div class="bg-red-50 border border-red-200 rounded p-3 text-xs text-red-800">
                ⚠ Sistem akan otomatis:
                <ul class="ml-4 list-disc mt-1">
                    <li>Update status kunjungan jadi SELESAI</li>
                    <li>Tandai pasien meninggal di rekam medis</li>
                    <li>Generate Surat Keterangan Kematian (siap cetak)</li>
                </ul>
            </div>
        </div>

        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('modal-meninggal').close()" class="btn-secondary">Batal</button>
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium">
                Catat Kematian
            </button>
        </div>
    </form>
</dialog>
@endcan
