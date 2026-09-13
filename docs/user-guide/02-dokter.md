# 02 - Dokter (Umum & Spesialis)

Panduan untuk **Dokter Umum** (role `DOKTER`) dan **Dokter Spesialis** (role `DOKTER_SPESIALIS`).

**Contoh username:** `dr.iqbal` (Umum), `dr.andika` (Sp.PD), `dr.sari` (Sp.A)

## Perbedaan Kedua Role

| Fitur | DOKTER | DOKTER_SPESIALIS |
|---|---|---|
| Periksa Rawat Jalan (SOAP) | ✅ | ✅ |
| Order Lab / Radiologi | ✅ | ✅ |
| Buat Resep | ✅ | ✅ |
| Admisi & Discharge Rawat Inap | ❌ | ✅ (sebagai DPJP) |
| Pindah kamar pasien RI | ❌ | ✅ |
| Isi CPPT Rawat Inap | ❌ | ✅ |

Jadi kalau Anda dokter spesialis dan kunjungan Anda rawat inap, semua alur sama sampai bagian yang khusus DPJP.

---

## Alur Kerja Harian — Rawat Jalan

### Langkah 1 — Buka Antrian Poli Anda

Login → sidebar → menu **Antrian RJ**.

📸 **SCREENSHOT: `02-antrian-rj-list.png`** — halaman antrian RJ semua poli.

Filter berdasarkan poli Anda di dropdown atas.

💡 **TIP:** Bookmark halaman antrian poli Anda supaya bisa langsung buka. URL akan seperti: `https://sihrs.rs.id/rj/antrian?poli_id=xxx`

### Langkah 2 — Panggil Pasien

1. Cari pasien di baris antrian (urut nomor antrian)
2. Klik tombol **📢 Panggil** — sistem catat waktu panggilan
3. Panggil pasien via mikrofon poli (masih manual, aplikasi belum push audio)

📸 **SCREENSHOT: `02-antrian-tombol-panggil.png`** — close-up baris antrian dengan tombol panggil ter-highlight.

Status pasien berubah dari **Menunggu** → **Sudah Dipanggil**.

### Langkah 3 — Mulai Pemeriksaan

Setelah pasien masuk ruangan, klik **Mulai Periksa** di baris pasien tsb.

Halaman form SOAP terbuka. Ini "ruang kerja" Anda selama periksa pasien.

📸 **SCREENSHOT: `02-form-soap-empty.png`** — form SOAP kosong dengan sidebar informasi pasien.

Halaman ini menampilkan:
- **Kiri:** informasi pasien (nama, No. RM, umur), riwayat penyakit, alergi, riwayat kunjungan sebelumnya
- **Kanan:** form untuk isi hasil pemeriksaan

### Langkah 4 — Isi SOAP + Tanda Vital

**S (Subjective) — Anamnesa**
Keluhan pasien, riwayat penyakit sekarang, dst. Contoh: "Nyeri kepala sejak 3 hari, semakin memberat pagi hari. Tidak demam, mual (-)."

**O (Objective) — Pemeriksaan Fisik & Tanda Vital**
Isi kolom tanda vital: TD sistol/diastol, Nadi, Respirasi, Suhu, SpO2, BB, TB.
Kolom text untuk PE: "KU tampak sakit ringan, kesadaran CM, kepala/leher: normal, thorax: SP vesikuler, cor S1S2 reguler..."

**A (Assessment) — Penilaian**
Kondisi klinis selain diagnosa (mis. "kondisi umum stabil, tidak ada tanda infeksi berat").

**P (Plan) — Rencana Terapi**
Rencana: obat, follow-up, edukasi.

**Edukasi**
Apa yang Anda edukasi ke pasien (istirahat, hindari makanan tertentu, dll).

📸 **SCREENSHOT: `02-form-soap-diisi.png`** — form SOAP dengan sample data terisi.

### Langkah 5 — Isi Diagnosa (Wajib ICD-10)

Scroll ke section **Diagnosa**. Klik **+ Tambah Diagnosa**.

1. Ketik nama penyakit atau kode ICD-10 di kolom cari → autocomplete muncul
2. Pilih diagnosa
3. Pilih tipe: **PRIMER** (utama) / **SEKUNDER** / **KOMPLIKASI**
4. Catatan opsional
5. Klik **Simpan**

📸 **SCREENSHOT: `02-diagnosa-search-icd.png`** — autocomplete ICD-10 saat mengetik.

🚨 **KRITIS:** WAJIB minimum **1 diagnosa PRIMER** sebelum Anda bisa selesaikan pemeriksaan. Sistem akan tolak "Selesai" kalau tidak ada.

Bisa tambah beberapa diagnosa (mis. 1 primer + 2 sekunder untuk kasus komorbid).

### Langkah 6 — Simpan SOAP

Klik **Simpan SOAP** di bagian bawah form. Data tersimpan. Anda masih bisa edit selama pasien belum "Selesai".

💡 **TIP:** Klik Simpan berkali-kali kalau perlu — data cumulative. Bukan replace.

### Langkah 7 — Order Lab (kalau perlu)

Dari halaman detail kunjungan atau dari menu Lab, klik **+ Buat Order Lab**.

1. Pilih parameter lab yang diminta — bisa multi-select (mis. HB, Leukosit, GDS)
2. Pilih **Prioritas**: RUTIN (>1 jam) / **CITO** (segera, warna merah)
3. **Catatan Klinis** untuk analis lab (mis. "curiga DHF, cek trombosit serial")
4. **Diagnosa Kerja** (bisa auto-fill dari diagnosa Anda)
5. Klik **Simpan Order**

📸 **SCREENSHOT: `02-order-lab-form.png`** — form order lab dengan multi-select parameter.

Setelah order tersimpan, otomatis muncul di worklist Analis Lab. Pasien diarahkan ke ruang sampling.

### Langkah 8 — Order Radiologi (kalau perlu)

Sama pola dengan Lab, dari menu Radiologi → **+ Buat Order**.

1. Pilih **jenis pemeriksaan** (X-Ray Thorax PA, USG Abdomen, dst)
2. **Keterangan klinis** — dibaca radiolog
3. **Persiapan puasa?** Centang untuk USG Abdomen misalnya
4. **Hamil?** WAJIB dicek untuk pasien wanita usia subur — X-Ray kontraindikasi
5. Pilih prioritas

⚠️ **PERHATIAN:** Kalau lupa centang **Hamil?**, radiografer bisa saja melakukan X-Ray ke pasien hamil. Bahaya untuk janin. WAJIB tanya pasien.

### Langkah 9 — Buat Resep

Dari halaman detail kunjungan atau menu Resep → **+ Buat Resep**.

📸 **SCREENSHOT: `02-form-resep.png`** — form resep dengan 2 obat contoh.

Per obat:
1. Ketik nama obat di kolom cari → autocomplete muncul dengan info stok tersedia
2. Isi **Jumlah** (mis. 10 tablet)
3. Isi **Signa** (mis. `3x1`, `2x1`, `k/p` = kalau perlu)
4. Isi **Aturan Pakai** (mis. "sesudah makan")
5. **Catatan** opsional
6. Klik **+ Tambah Obat** untuk tambah baris

Kalau obat **stok kosong** atau **hampir kosong**, autocomplete tetap tampilkan tapi dengan warning. Bisa jadi apotek harus request pengadaan.

Klik **Simpan Resep** → resep otomatis masuk ke worklist apoteker.

💡 **TIP:** Kalau meracik obat (puyer), centang **is_racikan** di form.

### Langkah 10 — Selesaikan Pemeriksaan

Setelah semua urusan (SOAP, diagnosa, order, resep) beres, klik **Selesai** di halaman pemeriksaan.

Sistem cek:
- Ada diagnosa primer? ✅
- Kalau semua OK → status pasien di-update otomatis ke tahap berikutnya:
  - Kalau ada order lab → status `MENUNGGU_HASIL_LAB`
  - Kalau ada resep → status `MENUNGGU_OBAT`
  - Kalau tidak ada → status `MENUNGGU_PEMBAYARAN`

📸 **SCREENSHOT: `02-selesai-berhasil.png`** — halaman antrian setelah pasien selesai (pasien hilang dari list).

Pasien diarahkan ke tahap berikutnya (lab / apotek / kasir).

---

## Alur Kerja Rawat Inap (Khusus DOKTER_SPESIALIS)

### Admisi Pasien ke Rawat Inap

Skenario: Anda memeriksa pasien di IGD atau poli, memutuskan pasien perlu rawat inap.

1. Dari halaman pemeriksaan pasien, klik **Admisi Rawat Inap**  
   *(atau dari menu Rawat Inap → Admisi Baru)*
2. Form admisi terbuka:

📸 **SCREENSHOT: `02-form-admisi-ri.png`** — form admisi rawat inap.

3. Pilih **Kamar Tersedia** — dropdown menampilkan kamar dengan status TERSEDIA + kelas + tarif/hari
4. Anda otomatis di-set sebagai **DPJP** (Dokter Penanggung Jawab Pasien)
5. Isi **Alasan Masuk** — indikasi rawat inap (mis. "Dehidrasi berat, perlu observasi 24 jam + rehidrasi IV")
6. Klik **Simpan**

Kamar otomatis di-set jadi TERISI. Sistem generate No. Kunjungan RI.

### Isi CPPT Harian

CPPT = Catatan Perkembangan Pasien Terintegrasi. Wajib diisi minimum 1x/hari per DPJP.

1. Dari menu Rawat Inap → cari nama pasien Anda
2. Halaman detail RI menampilkan riwayat CPPT
3. Klik **+ Tambah CPPT**
4. Pilih waktu (default now), isi SOAP versi RI (Subjective/Objective/Assessment/Plan/Instruksi)
5. Simpan

Perawat, apoteker, ahli gizi juga bisa isi CPPT — semua tampak di 1 timeline (integrasi multi-profesi).

### Pindah Kamar (kalau perlu)

Skenario: pasien di kamar Kelas III minta pindah ke VIP karena keluarga bayar sendiri.

1. Halaman detail RI pasien → klik **Pindah Kamar**
2. Pilih kamar tujuan (harus status TERSEDIA)
3. Isi alasan pindah
4. Simpan

Sistem otomatis:
- Tutup penempatan lama (kamar lama jadi status KOTOR — perlu dibersihkan)
- Buka penempatan baru (kamar baru jadi TERISI)
- Billing hitung otomatis pro-rata

### Pulangkan Pasien (Discharge)

Ketika pasien sudah stabil / permintaan sendiri / meninggal:

1. Halaman detail RI → klik **Pulangkan Pasien**
2. Pilih **Cara Pulang**:
   - **SEMBUH** — kondisi klinis pulih
   - **MEMBAIK** — perbaikan tapi belum full
   - **BELUM_SEMBUH** — tidak ada perbaikan
   - **APS** (Atas Permintaan Sendiri) — pasien minta pulang meski disarankan tidak
   - **RUJUK** — dirujuk ke RS lain
   - **MENINGGAL** — dengan tanggal & waktu
3. Isi **Resume Medis** (WAJIB, minimum 50 karakter) — ringkasan perawatan
4. Isi **Instruksi Pulang** — obat, kontrol, restriksi aktivitas
5. Klik **Simpan → Finalize Resume**

📸 **SCREENSHOT: `02-form-discharge.png`** — form discharge rawat inap.

🚨 **KRITIS:** Resume medis TIDAK BISA di-edit setelah finalize. Baca ulang sebelum klik.

Sistem otomatis:
- Kamar diubah status jadi KOTOR
- Kunjungan berubah status → MENUNGGU_PEMBAYARAN
- Pasien/keluarga diarahkan ke kasir

Untuk cetak resume medis: menu PDF → Resume Medis, atau di detail RI ada tombol **Cetak Resume**.

---

## Melihat Hasil Lab / Radiologi

Ketika Anda order lab/radiologi, dan hasil sudah divalidasi:

- Ada notifikasi via email kalau ada **hasil kritis** (flag LL/HH atau temuan kritis radiologi)
- Anda bisa lihat manual di halaman **detail kunjungan pasien** → section Hasil Lab / Hasil Radiologi
- Cetak PDF hasil lab: menu PDF → Hasil Lab

📸 **SCREENSHOT: `02-detail-kunjungan-hasil-lab.png`** — detail kunjungan dengan section hasil lab yang sudah tervalidasi.

🚨 **KRITIS:** Kalau Anda dapat email "🚨 [KRITIS] Hasil Lab", segera lihat dan tindak lanjuti. Email dikirim sesaat setelah dokter PK validasi.

---

## FAQ Dokter

**Q: Saya salah pilih diagnosa PRIMER. Bisa ganti?**
A: Ya, selama kunjungan belum SELESAI. Buka detail kunjungan → hapus diagnosa lama → tambah yang benar.

**Q: Pasien saya periksa hari ini, tapi lupa selesaikan. Besok masih bisa?**
A: Bisa, tapi kunjungan pasien tetap "aktif" — dia tidak bisa daftar kunjungan baru sampai ini di-selesaikan.

**Q: Obat yang saya mau resepkan tidak ada di daftar. Bagaimana?**
A: Bisa jadi obat belum di-input master data. Hubungi apoteker atau IT untuk tambahkan.

**Q: Saya dokter tamu, hari ini pertama praktek. Belum ada di sistem.**
A: Registrasi tidak bisa buat kunjungan tanpa dokter aktif. IT harus daftarkan dulu Anda sebagai dokter + user login.

**Q: SOAP saya bisa dicetak?**
A: Ya, cetak lewat halaman detail pemeriksaan (versi ringkasan) atau via menu PDF → Resume Medis (kalau RI).

---

## Troubleshooting

**Autocomplete ICD-10 tidak muncul saat mengetik**
→ Anda mungkin ketik terlalu cepat (rate limit 120/menit). Jeda sebentar.

**"Selesai" gagal dengan pesan diagnosa primer**
→ Tambahkan minimum 1 diagnosa dengan tipe PRIMER.

**"Simpan Resep" error stok tidak cukup**
→ Anda tetap bisa simpan resep — cek dulu jumlah, atau ubah ke obat alternatif. Kalau tetap ingin resepkan obat kosong (mis. pasien beli di luar), turunkan jumlah menjadi 0 dan tulis di catatan.

**Antrian tidak refresh sendiri**
→ Cek badge auto-refresh di kanan atas. Kalau ⏸ artinya ter-pause. Klik ▶ untuk resume.

---

## Kontak

- Konsultasi medis: **Ketua Komdik / Dr. Penanggung Jawab Klinis**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
