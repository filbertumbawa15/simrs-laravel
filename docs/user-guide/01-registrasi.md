# 01 - Registrasi / Pendaftaran

Panduan untuk **Petugas Pendaftaran** — role `REGISTRASI` (contoh username: `reg.mira`).

## Tanggung Jawab Anda

- Mendaftarkan pasien baru (belum pernah berobat di RS ini)
- Mencari pasien lama yang datang lagi
- Membuat **kunjungan** untuk pasien (RJ / RI / IGD)
- Mencetak tiket antrian
- Menerima info penjamin (Umum / BPJS / Asuransi swasta)

## Yang TIDAK Boleh/Bisa Anda Lakukan

- ❌ Menghapus data pasien (kalau ada duplikat, hubungi IT)
- ❌ Melihat rekam medis / hasil lab / resep
- ❌ Menerima pembayaran (itu Kasir)

---

## Alur Kerja Harian

### Skenario A: Pasien Baru Datang

**Langkah 1 — Cek dulu apakah pasien pernah berobat**

Sebelum daftar baru, WAJIB cek dulu. Ketik nama atau NIK di kolom pencarian menu **Pasien**.

📸 **SCREENSHOT: `01-pasien-list-search.png`** — halaman daftar pasien dengan kolom cari.

💡 **TIP:** Cari pakai NIK lebih akurat daripada nama (nama bisa typo). Kalau NIK tidak ada di KTP (pasien anak), pakai nama ibu + tgl lahir.

Kalau **tidak ketemu** → lanjut ke Langkah 2.
Kalau **ketemu** → langsung ke Skenario B (Pasien Lama Datang Lagi).

**Langkah 2 — Daftar Pasien Baru**

1. Klik tombol **+ Pasien Baru** di kanan atas
2. Isi form data pasien:

📸 **SCREENSHOT: `01-form-pasien-baru.png`** — form registrasi pasien kosong.

| Field | Wajib? | Catatan |
|---|---|---|
| NIK | Opsional (kosongkan kalau tidak punya, mis. bayi baru lahir) | 16 digit, unik |
| Nama Lengkap | ✅ Wajib | Sesuai KTP |
| Tempat Lahir | Opsional | |
| Tanggal Lahir | ✅ Wajib | Umur pasien dihitung otomatis |
| Jenis Kelamin | ✅ Wajib | L / P |
| Alamat lengkap | ✅ Wajib | Isi minimum RT/RW/Kelurahan/Kecamatan/Kabupaten |
| No. Telp | Wajib | Untuk kontak susulan |
| Nama Ayah / Ibu | Optional (wajib untuk pasien anak) | |
| Kontak Darurat | Recommended | Nama, hubungan, telp keluarga terdekat |

3. Klik **Simpan**
4. Sistem generate **No. RM otomatis** (mis. `100051`) — catat/hafalkan atau langsung lanjut buat kunjungan

⚠️ **PERHATIAN:** Kalau NIK sudah pernah dipakai pasien lain, sistem tolak. Cek dulu pasien mana yang punya NIK itu — kemungkinan pasien Anda pernah datang tapi dengan nama beda.

📸 **SCREENSHOT: `01-pasien-baru-berhasil.png`** — halaman detail pasien setelah berhasil disimpan (No. RM ter-generate).

---

### Skenario B: Pasien Lama Datang Lagi

1. Menu **Pasien**, ketik nama/NIK/No.RM di kolom cari
2. Klik nama pasien di hasil pencarian → halaman detail
3. Klik tombol **+ Kunjungan Baru** (kanan atas halaman detail pasien)

📸 **SCREENSHOT: `01-pasien-detail-with-riwayat.png`** — halaman detail pasien dengan tombol buat kunjungan + riwayat kunjungan sebelumnya.

---

### Langkah 3 — Buat Kunjungan (untuk pasien baru atau lama)

Setelah pasien terdaftar, buat kunjungan. Alternatif: dari menu **Kunjungan → + Kunjungan Baru** dan pilih pasien.

📸 **SCREENSHOT: `01-form-kunjungan.png`** — form buat kunjungan.

Isi form:

| Field | Value |
|---|---|
| **Tipe Kunjungan** | Pilih: **RJ** (Rawat Jalan) — paling umum |
| | atau **IGD** (kalau gawat darurat) |
| | atau **RI** (rawat inap — biasanya dari admisi/rujukan dokter, bukan langsung dari registrasi) |
| **Poli** (kalau RJ) | Pilih poli tujuan (Umum / Anak / PD / dst) |
| **Dokter** (kalau RJ) | Pilih dokter yang praktek hari ini di poli tsb |
| **Penjamin** | **UMUM** / **BPJS** / **ASURANSI** |
| **No. SEP** (kalau BPJS) | Isi dari kartu SEP pasien (14 digit) |
| **No. Rujukan** (kalau ada) | Dari FKTP/RS perujuk |

⚠️ **PERHATIAN:** Kalau pasien masih punya kunjungan **aktif** (belum SELESAI dari hari sebelumnya), sistem tolak buat kunjungan baru. Suruh dokter/kasir selesaikan kunjungan lama dulu.

Klik **Simpan** — kunjungan berhasil dibuat, muncul **No. Kunjungan** (mis. `RJ/2026/09/00042`).

📸 **SCREENSHOT: `01-kunjungan-berhasil.png`** — halaman detail kunjungan yang baru dibuat, dengan No. Antrian ter-generate.

---

### Langkah 4 — Cetak Tiket Antrian

1. Dari halaman antrian RJ (menu **Antrian RJ**), cari nama pasien
2. Klik tombol **🖨️** di baris pasien
3. Tab baru terbuka → PDF tiket 58mm (untuk thermal printer)
4. Ctrl+P → pilih printer thermal → Print

📸 **SCREENSHOT: `01-tiket-antrian-pdf.png`** — preview PDF tiket antrian.

💡 **TIP:** Kalau printer thermal Anda 80mm, cetak di kertas A5 aja pakai printer biasa — masih terbaca.

---

## FAQ Registrasi

**Q: Pasien lupa bawa KTP tapi butuh cepat. Boleh isi NIK 0000000000000000?**
A: Boleh, tapi WAJIB update NIK begitu KTP disediakan. Kalau tidak, klaim BPJS bisa ditolak nanti.

**Q: Pasien sama tapi ada 2 record di sistem (double). Bagaimana?**
A: JANGAN hapus salah satu. Screenshot kedua record, kirim ke IT untuk merge. Sementara pakai record dengan No. RM yang lebih kecil (lebih lama).

**Q: Pasien anak (bayi) belum punya nama resmi. Isi apa?**
A: Format standar: `By. Ny. [Nama Ibu]`. Update setelah bayi punya nama resmi.

**Q: Pasien datang tapi doktr yang biasa periksa sedang libur/cuti. Apa yang saya lakukan?**
A: Pilih dokter lain di poli yang sama. Kalau tidak ada, tawarkan poli/dokter alternatif ke pasien. Kalau pasien menolak, batalkan pembuatan kunjungan.

**Q: Kunjungan salah dibuat (typo tipe atau poli). Bagaimana batalin?**
A: Anda bisa batalin **selama status masih TERDAFTAR**. Buka detail kunjungan → tombol **Batal**. Kalau sudah DALAM_PEMERIKSAAN, hubungi IT.

---

## Troubleshooting

**Form daftar pasien error "NIK sudah terdaftar"**
→ Pasien pernah datang. Cari di daftar pasien pakai NIK tsb. Kalau ternyata orang beda dengan NIK sama — screenshot kirim IT.

**Tombol "Simpan" tidak reaksi**
→ Ada field wajib yang belum diisi. Scroll ke atas, cek label merah.

**Ketik cepat di form, tiba-tiba tidak bisa submit → "Terlalu banyak percobaan"**
→ Rate limit terpicu (30/menit). Tunggu 1 menit, coba lagi.

---

## Kontak

- Masalah operasional: **Supervisor Pendaftaran**
- Masalah aplikasi: **IT Support** (kontak di [README](README.md))
