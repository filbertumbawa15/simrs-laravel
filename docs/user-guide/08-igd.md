# 08 - IGD & Triase

Panduan untuk **Petugas IGD** — biasanya perawat jaga IGD (role `PERAWAT`) atau dokter jaga IGD (role `DOKTER`).

Tidak ada role khusus "IGD" — tugas ini dilakukan bergantian oleh perawat/dokter yang jaga.

## Alur Kerja IGD

Berbeda dengan poli RJ, IGD prioritas berdasarkan **kegawatan** (triase), bukan urutan datang.

### 1. Pasien Datang ke IGD

Registrasi (di depan IGD) buat kunjungan dengan tipe **IGD**. Pasien muncul di **IGD Board** dengan kategori awal "**BELUM TRIASE**" (prioritas tertinggi — perlu segera dinilai).

### 2. Buka IGD Board

Menu **IGD Board**.

📸 **SCREENSHOT: `08-igd-board.png`** — IGD board dengan pasien di 4 kategori triase.

Board menampilkan pasien dalam 5 section (urut prioritas):

| Warna | Kategori | Arti | Target Waktu Tangani |
|---|---|---|---|
| ⚪ Belum Triase | Baru datang, belum dinilai | **SEGERA** — max 5 menit |
| 🔴 MERAH | **Resusitasi** — henti jantung/napas, syok berat, trauma mayor | **SEGERA** — nol menit tunggu |
| 🟡 KUNING | **Emergent** — mengancam jiwa tapi tidak imminent | < 10 menit |
| 🟢 HIJAU | **Urgent** — perlu penanganan tapi bisa tunggu | < 30 menit |
| ⚫ HITAM | **DOA (Dead on Arrival)** / expectant | Kamar jenazah / paliatif |

⚠️ **PERHATIAN:** Board auto-refresh 15 detik. Kalau Anda sedang isi form, pause dulu (tombol ⏸).

### 3. Triase — Kategorisasi Pasien

Setiap pasien baru **wajib triase dalam 5 menit** setelah datang.

1. Di IGD Board, section **Belum Triase**, cari pasien
2. Klik tombol **🩺 Triase**
3. Form triase terbuka:

📸 **SCREENSHOT: `08-form-triase.png`** — form triase awal.

Isi:
- **Kategori Triase:**
  - **MERAH** — pasien tidak stabil, butuh resusitasi/intervensi segera
  - **KUNING** — tanda vital abnormal, butuh evaluasi cepat
  - **HIJAU** — kondisi stabil, bisa antri
  - **HITAM** — DOA atau kondisi tidak survivable
- **Keluhan Utama** — text singkat
- **Tanda Vital:**
  - TD sistol/diastol
  - Nadi
  - Respirasi
  - Suhu
  - SpO2
  - GCS (Glasgow Coma Scale) — untuk trauma/penurunan kesadaran

4. Klik **Simpan Triase**

📸 **SCREENSHOT: `08-triase-berhasil.png`** — IGD board setelah pasien dikategorikan.

Pasien pindah dari "Belum Triase" ke section warna sesuai kategori.

### 4. Pemeriksaan Dokter Jaga

Setelah triase, dokter jaga IGD periksa sesuai prioritas (Merah dulu).

Alur pemeriksaan sama dengan Rawat Jalan (SOAP + diagnosa), lihat [02 - Dokter](02-dokter.md).

Bedanya:
- Tidak ada nomor antrian (prioritas triase)
- Setelah selesai periksa, pasien bisa:
  - Pulang (kalau ringan, resep + kontrol)
  - Rawat inap (admisi via DPJP spesialis)
  - Rujuk ke RS lain (kalau tidak mampu ditangani)

### 5. Admisi ke Rawat Inap dari IGD

Kalau pasien perlu RI dari IGD:

1. Dokter jaga IGD selesaikan pemeriksaan dulu
2. Konsul via telpon ke DPJP spesialis (mis. konsul internis untuk pasien DHF)
3. DPJP setuju → login ke sistem → menu **Rawat Inap** → **Admisi Baru**
4. Pilih pasien dari kunjungan aktif → lanjut alur admisi (lihat [02 - Dokter](02-dokter.md#alur-kerja-rawat-inap))

Alternatif kalau DPJP tidak on-site, dokter IGD bisa admit atas nama DPJP dengan koordinasi telpon (dokumentasi persetujuan di CPPT).

---

## FAQ IGD

**Q: Pasien datang keadaan tidak sadar, tidak ada identitas.**
A: Daftar dengan nama "**Mr. X**" atau "**Mrs. Y**" + estimasi umur. Update identitas ketika keluarga datang.

**Q: Pasien MERAH datang bersamaan dengan pasien lain, siapa duluan?**
A: MERAH SELALU DULU. Sisanya ditunda sampai MERAH stabil. Kalau resource tidak cukup, minta reinforcement dari dokter poli atau on-call.

**Q: Salah triase (mis. HIJAU ternyata mestinya KUNING).**
A: Buka detail kunjungan → belum ada UI untuk edit triase di v1. Sementara pakai catatan CPPT. IT bisa update DB langsung kalau perlu.

**Q: Pasien DOA, bagaimana?**
A: Triase HITAM. Tetap lengkapi data. Kunjungan tetap tercatat untuk statistik & administrasi (surat kematian dari dokter).

**Q: Multiple patient event (mass casualty, kecelakaan bus, dll).**
A: Aktifkan protokol MCI (Mass Casualty Incident) sesuai SOP RS. Sistem masih handle satu-satu — perlu multiple petugas triase paralel.

---

## Troubleshooting

**IGD Board tidak update saat pasien baru datang**
→ Cek auto-refresh badge di kanan atas — pastikan tidak paused. Refresh manual (F5) kalau perlu.

**Tidak bisa klik "Triase" untuk pasien yang sudah pernah ditriase**
→ Sistem tidak izinkan retriase di v1. Untuk update kategori, buka detail kunjungan → catat perubahan di CPPT dengan alasan.

**Kategori HITAM tidak muncul di dropdown**
→ Cek — HITAM ada di enum. Kalau tidak muncul, kemungkinan browser cache. Ctrl+Shift+R (hard refresh).

---

## Kontak

- Kasus rumit medis: **Dokter Konsulen / DPJP on-call**
- Kasus mass casualty: **Direktur Pelayanan Medis + Kepala IGD**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
