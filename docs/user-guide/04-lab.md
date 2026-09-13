# 04 - Laboratorium

Panduan untuk **Analis Lab** (role `ANALIS_LAB`) dan **Dokter Patologi Klinik** (role `DOKTER_PK`).

**Contoh username:** `lab.budi` (Analis), `dr.doni` (Dokter PK / Validator)

## Perbedaan Role

| Fitur | ANALIS_LAB | DOKTER_PK |
|---|---|---|
| Lihat worklist order lab | ✅ | ✅ |
| Sampling (mark sampel diambil) | ✅ | ❌ (tugas analis) |
| Input hasil | ✅ | ❌ (tugas analis) |
| **Validasi hasil** (finalize) | ❌ | ✅ **Hanya dokter PK** |

Prinsip: **separation of duties** — orang yang input hasil tidak boleh sama dengan yang validasi. Ini untuk safety.

---

## Alur Kerja Analis Lab

### 1. Buka Worklist Order Lab

Menu **Laboratorium** — worklist semua order aktif hari ini.

📸 **SCREENSHOT: `04-lab-worklist.png`** — worklist lab dengan filter status & prioritas.

Filter:
- **Status:** Diorder / Sampel Diambil / Diproses / Validasi / Selesai
- **Prioritas:** RUTIN / **CITO** (merah — urgent)

💡 **TIP:** Sort by prioritas → CITO di paling atas. Kerjakan CITO dulu.

### 2. Sampling (Ambil Sampel dari Pasien)

Ketika pasien datang ke ruang sampling:

1. Cari order pasien di worklist (status = **DIORDER**)
2. Klik detail order → cek parameter apa saja yang di-order (biar tahu tabung apa yang perlu: purple untuk hematologi, kuning untuk kimia, dll)

📸 **SCREENSHOT: `04-detail-order-lab.png`** — detail order lab dengan list parameter.

3. Ambil sampel darah/urine sesuai standar
4. Balik ke sistem → klik **📌 Sampel Diambil**
5. Status berubah jadi **SAMPEL_DIAMBIL** — sistem catat waktu & analis yang sampling

### 3. Proses Sampel di Analyzer / Manual

1. Sampel dimasukkan ke analyzer (mis. Sysmex, Cobas) atau tes manual
2. Di sistem, buka detail order → klik **▶️ Mulai Proses**
3. Status → **DIPROSES**

### 4. Input Hasil

Setelah analyzer selesai / manual reading selesai:

1. Klik **📝 Input Hasil** di detail order
2. Form input hasil terbuka — 1 baris per parameter:

📸 **SCREENSHOT: `04-form-input-hasil.png`** — form input hasil lab per parameter.

Per baris:
- **Parameter** (mis. Hemoglobin)
- **Hasil** — angka atau teks (Positif/Negatif untuk kualitatif)
- **Satuan** (auto-fill dari master)
- **Rujukan Normal** (auto-fill)
- **Catatan** — opsional (mis. "sampel hemolisis, hasil ini interpretasi hati-hati")

Sistem **auto-flag** hasil berdasarkan nilai:
- 🟢 **N (Normal)** — dalam range rujukan
- 🟡 **L / H** — di bawah / di atas normal tapi tidak kritis
- 🔴 **LL / HH** — **KRITIS** (nilai kritis low / high)

📸 **SCREENSHOT: `04-hasil-dengan-flag-kritis.png`** — hasil dengan flag HH ter-highlight merah.

⚠️ **PERHATIAN:** Kalau ada flag **LL/HH**, WAJIB segera lapor telpon ke DPJP (jangan hanya andalkan email otomatis).

🚨 **Etik:** Jangan ubah hasil kalau tidak sesuai dengan output analyzer. Kalau curiga error alat, ulang test — jangan "sesuaikan" nilai.

3. Klik **Simpan Hasil** — status → **VALIDASI** (menunggu Dokter PK)

### 5. Nyatakan Selesai Input

Setelah semua parameter di-input, status otomatis **VALIDASI**. Dokter PK akan review.

---

## Alur Kerja Dokter Patologi Klinik (Validator)

### 1. Lihat Worklist untuk Validasi

Menu **Laboratorium** → filter status = **VALIDASI**.

📸 **SCREENSHOT: `04-worklist-validasi.png`** — worklist order yang perlu validasi PK.

### 2. Review Hasil Sebelum Validasi

Klik detail order → periksa satu per satu:

1. Apakah nilai masuk akal secara klinis? (mis. HB 3 g/dL — cek dulu ulang, jangan langsung release)
2. Apakah ada hasil kritis (LL/HH)? Kalau ya, apakah analis sudah tandai catatan?
3. Apakah nilai sesuai diagnosa kerja yang di-order dokter?

📸 **SCREENSHOT: `04-review-hasil-lengkap.png`** — halaman review hasil dengan semua parameter tampak.

⚠️ **PERHATIAN:** Jangan validasi tanpa membaca dulu. Nama Anda akan tercantum sebagai validator di PDF hasil — Anda bertanggung jawab.

### 3. Validasi

Kalau OK, klik **✅ Validasi Hasil**.

Sistem otomatis:
- Status order → **SELESAI**
- Hasil resmi release ke DPJP
- **Email peringatan kritis** dikirim ke DPJP kalau ada flag LL/HH atau kesan kritis
- Kunjungan pasien di-advance ke tahap berikutnya (menunggu obat / pembayaran)

📸 **SCREENSHOT: `04-hasil-tervalidasi.png`** — halaman hasil setelah divalidasi (status SELESAI, tanggal validasi, nama validator).

### 4. Kalau Ada Yang Harus Direvisi

Kalau Anda temukan error (angka salah input, salah satuan):
- **Jangan validasi**
- Hubungi analis untuk revisi
- Analis buka input hasil lagi, edit, simpan
- Anda validasi setelah revisi

---

## Cetak PDF Hasil Lab

Setelah divalidasi, PDF hasil lab bisa dicetak:

1. Detail order → tombol **🖨️ Cetak Hasil PDF**
2. PDF terbuka dengan:
   - Kop RS
   - Data pasien
   - Semua parameter + hasil + rujukan + flag (warna)
   - Tanda tangan validator (nama + tanggal validasi)
   - QR verifikasi keaslian dokumen

📸 **SCREENSHOT: `04-pdf-hasil-lab.png`** — preview PDF hasil lab.

Pasien / dokter bisa scan QR untuk verifikasi bahwa hasil sah terdaftar di sistem SIHRS.

---

## Notifikasi Hasil Kritis (Otomatis)

Ketika Dokter PK validasi hasil dengan flag **LL/HH**, sistem otomatis:

1. Kirim **email peringatan** ke alamat email DPJP (dokter perujuk)
2. Kirim CC ke Komdik / Manajer Pelayanan (kalau di-set)
3. Log ke audit trail (retensi 5 tahun)

⚠️ **PERHATIAN:** Email adalah backup, **bukan pengganti** komunikasi lisan. Untuk hasil kritis, WAJIB juga:
- Analis telpon DPJP langsung
- Catat di form Notifikasi Kritis (dokumen kertas kalau ada) siapa yang menerima info kapan

---

## FAQ Lab

**Q: Analyzer error, hasil sampai ke sistem korup. Bagaimana?**
A: Jangan simpan hasil salah. Ulang test kalau sampel cukup, atau lapor dokter untuk order ulang.

**Q: Pasien datang sampling tapi belum ada order di sistem.**
A: Cek dokter perujuk. Kalau memang belum di-order (miscommunication), minta dokter buat order via aplikasi dulu. Jangan sampling tanpa order.

**Q: Order CITO datang tapi analyzer sedang maintenance.**
A: Alihkan ke lab rujukan (bila ada), catat di catatan order. Kalau tidak ada alternatif, telpon DPJP inform delay.

**Q: Bisa cetak hasil lab yang belum divalidasi (draft)?**
A: Tidak. Sistem tolak cetak PDF sebelum status SELESAI.

**Q: Dokter PK tidak masuk hari libur. Siapa validate?**
A: Wajib ada backup PK (mis. dokter PK RS lain kerjasama, atau internist yang mumpuni). IT bisa berikan akses temporer.

---

## Troubleshooting

**Tidak bisa klik "Sampel Diambil"**
→ Cek status order — mungkin sudah SAMPEL_DIAMBIL sebelumnya (order duplikat?). Refresh halaman.

**"Simpan Hasil" error field kosong**
→ Setidaknya 1 parameter harus diisi hasil. Kalau ada parameter yang tidak dapat diukur (mis. sampel kurang), tulis "TIDAK DIPERIKSA" di kolom hasil + catatan.

**Autocomplete tidak muncul untuk cari parameter di form order**
→ Ini form dokter, bukan lab. Kalau Anda lab, Anda menerima order dari dokter, tidak buat sendiri.

---

## Kontak

- Konsultasi teknis lab: **Ketua Instalasi Laboratorium**
- Alat rusak: **Teknisi Alat / Vendor Analyzer**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
