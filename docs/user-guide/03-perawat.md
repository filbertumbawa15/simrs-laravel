# 03 - Perawat

Panduan untuk **Perawat Umum** (role `PERAWAT`) dan **Perawat Rawat Inap** (role `PERAWAT_RI`).

**Contoh username:** `pwt.rina` (Perawat), `pwt.yusuf` (Perawat RI)

## Tanggung Jawab Anda

### Perawat Umum (RJ / IGD)
- Membantu dokter periksa pasien
- Cek dan input **tanda vital** pasien di form SOAP
- Assist ambil sampel lab
- Melihat data pasien untuk keperluan asuhan keperawatan
- Triase IGD

### Perawat Rawat Inap (khusus PERAWAT_RI)
Semua di atas, **plus:**
- Isi **CPPT keperawatan** (Subjective/Objective sesuai pengamatan)
- Request **pindah kamar** (untuk approval DPJP)
- Update kondisi pasien harian
- Update status kamar (KOTOR setelah pasien pulang → siap cleaning)

## Yang TIDAK Boleh Anda Lakukan

- ❌ Membuat diagnosa (tugas dokter)
- ❌ Buat resep atau serahkan obat
- ❌ Validasi hasil lab
- ❌ Admisi/discharge pasien (tugas DPJP)

---

## Alur Kerja Harian — Bantuan Dokter di Poli RJ

### 1. Panggil Pasien Sesuai Instruksi Dokter

Sama dengan alur dokter (tombol **📢 Panggil**), atau dokter yang klik sendiri. Koordinasi dengan dokter poli.

### 2. Ukur Tanda Vital

Sebelum atau saat dokter periksa, ukur:
- **TD** — Tekanan darah (mmHg)
- **N** — Nadi (bpm)
- **R** — Respirasi (per menit)
- **S** — Suhu (°C)
- **SpO2** — Saturasi oksigen (%)
- **BB** — Berat badan (kg)
- **TB** — Tinggi badan (cm)

Input tanda vital ini di form SOAP saat dokter membuka pemeriksaan, atau bilang ke dokter untuk diisi.

📸 **SCREENSHOT: `03-tanda-vital-form.png`** — bagian input tanda vital di form SOAP.

💡 **TIP:** Kalau pasien sering datang (kontrol rutin), tanda vital sebelumnya tampak di sidebar kiri untuk perbandingan trend.

### 3. Assist Prosedur / Tindakan Kecil

Kalau dokter perlu bantuan untuk tindakan (injeksi, EKG, nebulizer):
- Persiapkan alat
- Dokter yang isi tindakan di sistem (menu Tindakan di detail kunjungan)

---

## Alur Kerja — Rawat Inap (PERAWAT_RI)

### 1. Buka Daftar Pasien Rawat Inap Anda

Menu **Rawat Inap** — list semua pasien inap aktif.

📸 **SCREENSHOT: `03-ri-list.png`** — halaman daftar pasien rawat inap.

Filter berdasarkan kelas kamar / DPJP kalau perlu.

### 2. Lihat Bed Management

Menu **Bed Management** — visualisasi status semua tempat tidur.

📸 **SCREENSHOT: `03-bed-board.png`** — bed management board dengan color-code status.

Warna:
- 🟢 Hijau = TERSEDIA
- 🔴 Merah = TERISI (klik untuk lihat pasien)
- 🟡 Kuning = KOTOR (perlu cleaning service)
- ⚪ Abu = MAINTENANCE (rusak, tidak bisa dipakai)

Klik kamar → detail pasien yang menempati.

### 3. Isi CPPT Keperawatan

Setiap shift (pagi/siang/malam), Anda WAJIB isi CPPT.

1. Menu **Rawat Inap** → cari pasien Anda
2. Halaman detail RI → section **CPPT**
3. Klik **+ Tambah CPPT**
4. Pilih profesi: **PERAWAT**
5. Isi:
   - **Subjective:** apa keluhan pasien (mis. "Pasien mengeluh nyeri di area operasi skala 4/10")
   - **Objective:** observasi Anda (mis. "TD 130/85, N 88, luka operasi tertutup rapi, tidak ada rembesan")
   - **Assessment:** penilaian keperawatan (mis. "Nyeri terkontrol, luka baik")
   - **Plan:** rencana asuhan (mis. "Lanjutkan analgetik sesuai instruksi DPJP, ganti verban 2x/hari")
   - **Instruksi:** dari DPJP kalau ada
6. Klik **Simpan**

📸 **SCREENSHOT: `03-cppt-form.png`** — form CPPT keperawatan.

💡 **TIP:** CPPT semua profesi (dokter, perawat, apoteker, gizi) tampil di 1 timeline sesuai urutan waktu — mudah untuk dokter jaga malam baca update semuanya.

### 4. Update Status Kamar setelah Pasien Pulang

Setelah DPJP klik "Pulangkan Pasien", kamar auto-set jadi **KOTOR**. Setelah cleaning selesai:

1. Menu **Bed Management**
2. Cari kamar tsb (warna kuning)
3. Klik kamar → tombol **Ubah Status ke TERSEDIA**
4. Konfirmasi

📸 **SCREENSHOT: `03-update-status-kamar.png`** — dialog update status kamar.

Kamar kembali hijau, siap untuk pasien berikutnya.

### 5. Request Pindah Kamar

Kalau pasien minta pindah (mis. dari kelas III ke VIP karena bayar sendiri):

1. Detail RI pasien → tombol **Pindah Kamar**
2. Pilih kamar tujuan
3. Isi alasan
4. Simpan

💡 **TIP:** Idealnya request pindah kamar dilakukan oleh DPJP (dokter spesialis penanggung jawab). Kalau DPJP tidak on-site, Anda bisa request sendiri, tapi lapor via WA/telpon dulu.

---

## Alur Kerja — Triase IGD (Perawat Jaga IGD)

Kalau Anda perawat jaga IGD, Anda juga bisa lakukan triase awal (kategorisasi kegawatan).

Buka [08 - IGD & Triase](08-igd.md) untuk panduan lengkap.

---

## Lihat Riwayat Medis Pasien

Sebelum berikan asuhan, cek riwayat pasien:

1. Menu **Pasien** → cari nama pasien
2. Halaman detail pasien menampilkan:
   - Data demografi
   - Rekam medis permanen (alergi obat, riwayat penyakit)
   - Riwayat semua kunjungan sebelumnya

📸 **SCREENSHOT: `03-pasien-detail-riwayat.png`** — halaman detail pasien dengan tab riwayat.

🚨 **KRITIS:** SELALU cek section **Alergi Obat** sebelum berikan obat apapun. Kalau ada alergi terhadap obat yang mau diberikan, jangan berikan — lapor DPJP.

---

## FAQ Perawat

**Q: Saya perawat poli tapi ditugaskan sementara ke IGD. Bisa?**
A: Bisa, akses menu tetap sama untuk role PERAWAT. Bedanya di IGD Anda juga akan triase.

**Q: Bisa edit tanda vital yang salah input?**
A: Selama pasien belum SELESAI, buka form SOAP lagi, ubah, simpan ulang. Ter-record di audit log.

**Q: Kalau dokter tidak ada di poli tapi pasien sudah datang, apa yang saya lakukan?**
A: Tanya ke registrasi apakah ada dokter pengganti. Kalau tidak, minta pasien menunggu atau reschedule.

**Q: Saya lupa isi CPPT kemarin. Bisa isi retroactive?**
A: Bisa, tapi WAJIB isi tanggal/jam yang benar (bukan hari ini). Timestamp CPPT ter-record di audit — jangan back-date tanpa alasan valid.

---

## Troubleshooting

**Bed Management tidak update saat kamar dipulangkan**
→ Refresh halaman (F5). Kalau tetap tidak update, hubungi IT.

**Tidak bisa klik tombol "Tambah CPPT"**
→ Cek role Anda di sudut atas. Kalau tertulis `PERAWAT` (bukan PERAWAT_RI), Anda tidak bisa isi CPPT rawat inap. Hubungi IT untuk tambah akses.

**Tanda vital pasien anak, tidak ada kolom "kepala lingkar"**
→ Sistem v1 belum ada kolom khusus antropometri anak. Tulis di kolom Objective/PE dalam SOAP.

---

## Kontak

- Konsultasi asuhan keperawatan: **Kepala Ruangan Anda**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
