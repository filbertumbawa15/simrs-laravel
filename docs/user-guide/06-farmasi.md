# 06 - Farmasi (Apotek)

Panduan untuk **Apoteker** (role `APOTEKER`) dan **TTK Apotek** (role `TTK_APOTEK`).

**Contoh username:** `apt.fitri` (Apoteker), `tta.dewi` (TTK)

## Perbedaan Role

| Fitur | APOTEKER | TTK_APOTEK |
|---|---|---|
| Lihat worklist resep | ✅ | ✅ |
| **Verifikasi resep** (cek dosis, interaksi) | ✅ | ❌ |
| **Serahkan obat** (dispense + kurangi stok) | ✅ | ✅ |
| Lihat detail stok obat | ✅ | ✅ |

Prinsip: verifikasi wajib oleh apoteker (lisensi), tapi dispensing bisa oleh TTK di bawah supervisi.

---

## Alur Kerja Harian

### 1. Buka Worklist Resep

Menu **Farmasi** — worklist resep aktif.

📸 **SCREENSHOT: `06-resep-worklist.png`** — worklist resep dengan filter status.

Filter status:
- **BARU** — baru diorder dokter, belum verifikasi
- **DIVERIFIKASI** — sudah dicek apoteker, siap disiapkan/diserahkan
- **DISERAHKAN** — sudah diserahkan ke pasien (arsip)
- **BATAL**

💡 **TIP:** Urutan kerja normal: BARU → verify → DIVERIFIKASI → dispense → DISERAHKAN.

---

### 2. Verifikasi Resep (APOTEKER only)

Ini tugas krusial apoteker — cek keamanan resep sebelum obat disiapkan.

1. Klik detail resep dari worklist (status BARU)
2. Detail resep menampilkan:
   - Data pasien (nama, umur, alamat, alergi obat kalau ada di rekam medis!)
   - Diagnosa kerja
   - List obat + jumlah + signa

📸 **SCREENSHOT: `06-detail-resep-verify.png`** — halaman detail resep sebelum verifikasi.

**Yang harus Anda cek:**

- ✅ **Alergi:** Cek section alergi obat di data pasien. Kalau ada alergi terhadap obat yang di-resep, JANGAN verifikasi. Telpon dokter untuk ganti.
- ✅ **Dosis:** Sesuai umur/BB pasien?
- ✅ **Interaksi:** Ada interaksi obat berbahaya di antara obat yang di-resep?
- ✅ **Frekuensi:** Signa masuk akal? (3x1 untuk antibiotik OK, 3x1 untuk obat sekali sehari salah)
- ✅ **Duplikasi:** Ada obat sama dengan mekanisme aksi mirip?
- ✅ **Kontraindikasi:** Sesuai kondisi (mis. NSAID untuk pasien ulkus? Metformin untuk gagal ginjal?)

**Kalau semua OK:**
- Klik **✅ Verifikasi Resep**
- Status → **DIVERIFIKASI**
- Nama Anda dan waktu verifikasi tercatat

**Kalau ada masalah:**
- **Jangan verifikasi**
- Telpon dokter yang order (kontak di detail resep)
- Diskusi + minta koreksi
- Dokter edit resep (kalau memungkinkan) atau buat resep baru
- Baru verifikasi resep yang sudah dikoreksi

⚠️ **PERHATIAN:** Verifikasi resep tanpa cek = malpraktik farmasi. Nama Anda tercantum sebagai verifikator — Anda bertanggung jawab.

---

### 3. Serahkan Obat (Dispense)

Setelah verifikasi, obat disiapkan dan diserahkan ke pasien.

1. Cari resep status **DIVERIFIKASI**
2. Ambil obat dari rak sesuai list — sistem otomatis pilih batch **FEFO** (First Expired First Out — batch dengan tanggal expired paling dekat keluar duluan)

📸 **SCREENSHOT: `06-detail-resep-dispense.png`** — halaman detail resep siap dispense.

3. Klik **📦 Serahkan Obat**
4. Sistem cek dulu:
   - Apakah stok cukup untuk semua obat?
   - Jika ada obat yang stok tidak cukup → error, tampilkan "Stok Paracetamol tidak cukup. Butuh 30, tersedia 12."
5. Kalau cukup, sistem otomatis:
   - Kurangi stok dari batch FEFO
   - Kalau 1 batch tidak cukup, ambil dari 2+ batch (split)
   - Catat setiap batch yang dipakai di `batch_used`
   - Log mutasi stok dengan saldo sebelum & sesudah (audit trail)

⚠️ **PERHATIAN:**
- Sistem TIDAK bisa dispense parsial. Semua obat dalam 1 resep harus tersedia. Kalau 1 kurang, semua transaksi rollback.
- Kalau stok memang kosong: telpon dokter, dokter buat resep alternatif, atau catatan bahwa pasien beli di apotek luar.

📸 **SCREENSHOT: `06-serahkan-success.png`** — halaman resep setelah diserahkan (status DISERAHKAN, batch_used ter-record).

6. Berikan obat ke pasien beserta:
   - Etiket obat (masih manual, print dari label printer terpisah)
   - Info signa & aturan pakai (edukasi ke pasien)
   - Efek samping yang perlu diperhatikan

---

### 4. Cetak Resep (untuk arsip pasien atau dokumen legal)

1. Detail resep → tombol **🖨️ Cetak Resep**
2. PDF terbuka dengan kop RS, data pasien, list obat, tanda tangan digital dokter, QR verifikasi
3. Print

📸 **SCREENSHOT: `06-pdf-resep.png`** — preview PDF resep.

---

## Lihat & Kelola Stok Obat

### Cek Stok Aktual

Menu **Farmasi** → ada indikator stok di autocomplete saat cari obat. Untuk view lengkap semua stok:

*(Fitur listing stok belum ada di v1 — via query DB atau via dashboard alert)*

📸 **SCREENSHOT: `06-dashboard-alert-stok.png`** — dashboard menampilkan alert obat menipis & mendekati expired.

Di **Dashboard** (kalau role Anda punya akses), section **Alert**:
- Obat stok < minimum
- Obat expired < 90 hari

### Terima Barang Masuk (Coming Soon)

Fitur input barang masuk belum ada di v1. Sementara ini dilakukan via IT — hubungi mereka kalau ada penambahan stok baru dari distributor.

---

## FAQ Farmasi

**Q: Pasien tanya "ini obat apa?"**
A: Buka detail resep pasien di sistem, jelaskan sesuai catatan. Kalau butuh info efek samping/interaksi detail, buka MIMS atau referensi lain.

**Q: Salah dispense obat (mis. Paracetamol dispense jadi Metformin karena mirip nama).**
A: SEGERA:
1. Panggil balik pasien (kalau belum minum)
2. Lapor ke apoteker penanggung jawab
3. Buat laporan insiden (kertas + di sistem via catatan)
4. Sistem tidak bisa "un-dispense" — mutasi stok tetap tercatat. IT bisa reverse manual (mutasi masuk manual)

**Q: Resep racikan (puyer), bagaimana input?**
A: Dokter yang centang "is_racikan" di form resep. Anda tinggal siapkan racikan sesuai instruksi. Stok obat komponen tetap dikurangi.

**Q: Pasien beli obat dari luar (tidak dari apotek RS). Bagaimana?**
A: Kalau memang stok kosong, dokter catat di resep dengan jumlah 0 + catatan "Beli di luar". Resep tetap diverifikasi tapi tidak dispense (tidak kurangi stok).

**Q: Ada obat yang di-recall (BPOM tarik dari peredaran). Bagaimana handle stok?**
A: IT bisa lakukan **penyesuaian stok** (jenis mutasi = RUSAK atau PENYESUAIAN). Fitur belum di UI — request via IT untuk mark obat & batch tertentu.

---

## Troubleshooting

**Dispense error "Stok obat kosong" padahal fisik masih ada**
→ Kemungkinan:
1. Sistem beda dengan aktual (perlu penyesuaian — lapor IT)
2. Batch expired, sistem skip (cek exp_date, mungkin sudah lewat)
3. Batch di-lock oleh dispensing lain paralel (tunggu 5 detik, coba lagi)

**Verifikasi resep tidak ada tombolnya**
→ Cek role Anda. TTK_APOTEK tidak bisa verifikasi. Apoteker yang harus verifikasi.

**Pasien komplain tidak dapat obat lengkap padahal saya sudah dispense**
→ Buka detail resep, cek section `batch_used` — kalau kosong, dispense belum berhasil. Kalau ada, konfirmasi pasien terima berapa item, sesuaikan dengan list resep.

**Autocomplete obat lambat**
→ Ketik minimum 3 karakter untuk trigger search. Kalau tetap lambat, refresh halaman.

---

## Kontak

- Konsultasi obat: **Apoteker Penanggung Jawab (APJ)**
- Adverse event / MESO: **Komite Farmasi & Terapi**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
