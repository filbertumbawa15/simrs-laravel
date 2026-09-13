# 05 - Radiologi

Panduan untuk **Radiografer** (role `RADIOGRAFER`) dan **Dokter Radiolog** (role `DOKTER_RADIOLOG`).

**Contoh username:** `rad.tono` (Radiografer), `dr.dewi` (Dokter Radiolog)

## Perbedaan Role

| Fitur | RADIOGRAFER | DOKTER_RADIOLOG |
|---|---|---|
| Lihat worklist order radiologi | ✅ | ✅ |
| Eksekusi pemeriksaan (upload gambar) | ✅ | ❌ |
| **Baca hasil** (interpretasi) | ❌ | ✅ |
| **Validasi hasil** (finalize) | ❌ | ✅ |

Prinsip sama dengan lab: **separation of duties**.

---

## Alur Kerja Radiografer

### 1. Buka Worklist Order Radiologi

Menu **Radiologi** — worklist semua order aktif.

📸 **SCREENSHOT: `05-rad-worklist.png`** — worklist radiologi.

Filter:
- **Status:** Diorder / Dilakukan / Menunggu Bacaan / Selesai
- **Prioritas:** RUTIN / **CITO**

🚨 **KRITIS:** Sebelum eksekusi X-Ray/CT ke pasien wanita, **WAJIB cek kolom "Hamil"** di detail order. Kalau ada tanda ⚠️ Hamil, JANGAN lakukan X-Ray tanpa konfirmasi ulang ke DPJP.

### 2. Persiapan Pasien

Sebelum panggil pasien ke ruang tembak:

1. Klik detail order → cek:
   - **Jenis pemeriksaan** (X-Ray Thorax PA/AP, USG Abdomen, CT Kepala, dll)
   - **Keterangan klinis** (mengapa dokter minta pemeriksaan ini)
   - **Persiapan puasa?** Untuk USG Abdomen biasanya perlu puasa 6-8 jam
   - **Hamil?** — cek ini!

📸 **SCREENSHOT: `05-detail-order-rad.png`** — detail order radiologi.

2. Panggil pasien, konfirmasi:
   - Identitas (nama + tgl lahir — jangan hanya nama)
   - Sudah puasa (kalau perlu)
   - Untuk wanita usia subur: konfirmasi ulang tidak hamil, atau siklus haid terakhir kapan

### 3. Eksekusi Pemeriksaan

Setelah selesai tembak/scan:

1. Kembali ke detail order → klik **📸 Eksekusi Pemeriksaan**
2. Form eksekusi terbuka:

📸 **SCREENSHOT: `05-form-eksekusi.png`** — form eksekusi dengan upload image.

Isi:
- **Kondisi Teknis** — parameter foto (mis. "Thorax PA, kV 65, mAs 8, jarak 150cm, pasien inspirasi maksimal")
- **Upload Image** — pilih file(s) dari komputer

⚠️ **PERHATIAN:** Format yang didukung: JPG, PNG, PDF (untuk report), DICOM (.dcm). Max **20 MB per file**. Kalau lebih besar, kompres dulu atau kirim langsung ke PACS.

3. Klik **Upload & Simpan**
4. Status order → **MENUNGGU_BACAAN**

📸 **SCREENSHOT: `05-eksekusi-selesai.png`** — halaman order dengan images ter-upload, ready untuk radiolog.

### 4. Hapus Image (kalau salah upload)

Kalau upload image yang salah / blur, hapus **selama status belum SELESAI**:

1. Detail order → cari image yang salah
2. Klik icon 🗑️
3. Konfirmasi

Setelah radiolog validasi, image tidak bisa dihapus lagi (arsip permanen).

---

## Alur Kerja Dokter Radiolog

### 1. Worklist Order yang Perlu Dibaca

Menu **Radiologi** → filter status = **MENUNGGU_BACAAN**.

### 2. Lihat Gambar

Detail order → section **Gambar**. Klik untuk lihat image full-size di tab baru.

📸 **SCREENSHOT: `05-viewer-image.png`** — preview image radiologi terbuka.

💡 **TIP:** Untuk DICOM, sistem hanya menyimpan file. Buka pakai viewer DICOM khusus (RadiAnt, OsiriX) di komputer Anda kalau perlu measurement/window level.

### 3. Input Bacaan

1. Klik **📝 Input Bacaan** per pemeriksaan (kalau order berisi multi-pemeriksaan)
2. Form bacaan terbuka:

📸 **SCREENSHOT: `05-form-bacaan.png`** — form input bacaan radiologi.

Isi:
- **Bacaan** — deskripsi radiologis lengkap (mis. "Cor: CTR 55%, aortic knob prominent. Pulmo: infiltrat lobus inferior kanan berbatas tidak tegas. Sinus dan diafragma normal. Tulang costa dan clavicula intact.")
- **Kesan** — kesimpulan singkat (mis. "Pneumonia lobar dekstra. Kardiomegali suspek.")
- **Saran** — follow-up yang disarankan (mis. "Ulang thorax 5-7 hari setelah terapi antibiotik")
- **Ada Temuan Kritis?** — CENTANG jika ada temuan yang butuh tindakan segera:
  - Pneumothorax
  - Perdarahan intracranial
  - Fraktur mayor
  - Emboli paru
  - Free air di abdomen
  - dll

🚨 **KRITIS:** Centang **Ada Temuan Kritis** dengan hati-hati — memicu email peringatan otomatis ke DPJP. False positive = spam DPJP, false negative = pasien terlambat ditangani.

3. Klik **Simpan Bacaan**

### 4. Validasi (Finalize)

Setelah semua pemeriksaan di-baca:

1. Klik **✅ Validasi Order**
2. Sistem cek:
   - Semua pemeriksaan sudah ada bacaan? ✅
   - Ada minimum 1 image? ✅
3. Kalau OK → status **SELESAI**, bacaan resmi release
4. Kalau ada Temuan Kritis → email peringatan ke DPJP dikirim otomatis

📸 **SCREENSHOT: `05-validasi-berhasil.png`** — halaman order dengan status SELESAI + validator + tanggal.

⚠️ **PERHATIAN:** Setelah validasi, bacaan TIDAK BISA diedit. Baca ulang sebelum klik Validasi.

---

## Cetak Hasil Radiologi (Coming Soon)

Fitur cetak PDF hasil radiologi belum ada di v1 (roadmap Tier 3). Untuk sementara, DPJP akses hasil via halaman detail kunjungan pasien.

---

## FAQ Radiologi

**Q: Pasien saat tembak batuk / gerak — foto blur. Bisa ulang?**
A: Ya, ulang saja. Upload versi baru yang jelas. Hapus versi blur sebelum validasi.

**Q: Pasien datang tanpa order (walk-in).**
A: Radiografer TIDAK BOLEH eksekusi tanpa order. Suruh pasien ke dokter dulu untuk buat order.

**Q: Order X-Ray untuk pasien hamil (dokter tidak tahu pasien hamil).**
A: STOP. Konfirmasi ulang ke pasien. Kalau positif hamil, telpon DPJP untuk pertimbangkan USG sebagai alternatif. Kalau X-Ray tetap diperlukan, apron pelindung wajib.

**Q: Bacaan saya sudah divalidasi tapi ternyata salah interpretasi. Bagaimana?**
A: Buat **addendum** (bacaan tambahan) di detail order — belum ada tombol khusus di v1, gunakan catatan atau lapor IT untuk workaround. Bacaan asli tetap terarsip.

**Q: Radiolog on-call malam, tidak on-site. Bisa baca dari rumah?**
A: Bisa, akses aplikasi via VPN (tanya IT). Image tetap kelihatan di browser. Untuk DICOM, download file dulu, buka di viewer rumah.

---

## Troubleshooting

**Upload image gagal ("File terlalu besar")**
→ Max 20 MB per file. Kompres pakai [tinyjpg.com](https://tinyjpg.com) atau tools DICOM converter.

**Image ter-upload tapi tidak muncul di preview**
→ Format tidak didukung. Cek: JPG/PNG untuk foto biasa, PDF untuk report, DCM untuk DICOM asli.

**"Validasi" error "Belum semua pemeriksaan dibaca"**
→ Cek order kalau ada multi-pemeriksaan. Semua harus punya bacaan sebelum bisa validate.

**"Validasi" error "Tidak ada image"**
→ Minimum 1 image harus ter-upload sebelum bisa finalize. Kalau memang tidak ada image (mis. konsul saja), lapor IT — workflow ini belum ter-cover.

---

## Kontak

- Konsultasi teknis: **Ketua Instalasi Radiologi**
- Alat rusak (X-Ray/USG/CT/MRI): **Teknisi Alat / Vendor**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
