# 09 - Manager, Direksi & Auditor

Panduan untuk **Manager** (`MANAGER`), **Direksi** (`DIREKSI`), **Auditor** (`AUDITOR`), dan **Petugas BPJS** (`PETUGAS_BPJS`).

**Contoh username:** `mgr.surya`, `direktur`, `auditor.heru`, `bpjs.maya`

## Yang Bisa Anda Lakukan

| Fitur | MANAGER | DIREKSI | AUDITOR | PETUGAS_BPJS |
|---|---|---|---|---|
| Lihat dashboard KPI | ✅ | ✅ | ✅ | ✅ (BPJS only) |
| Lihat data pasien | ✅ | ✅ (read-only) | ✅ (read-only) | ✅ (BPJS only) |
| Lihat semua kunjungan | ✅ | ✅ | ✅ | ✅ |
| Lihat billing / laporan keuangan | ✅ | ✅ | ✅ | Klaim BPJS |
| Export Excel (Pasien, Tagihan) | ✅ | ✅ | ✅ | ❌ |
| Audit Log | ❌ | ❌ | ✅ | ❌ |
| Master data (dokter/poli/kamar/obat) | ✅ CRUD | ❌ | ❌ | ❌ |

---

## Alur Kerja Manager

### 1. Dashboard KPI Harian

Login → Dashboard.

📸 **SCREENSHOT: `09-dashboard-manager.png`** — dashboard dengan stat cards.

Yang Anda lihat:
- **Total Pasien** (all-time)
- **Kunjungan hari ini** (breakdown RJ / RI / IGD)
- **Occupancy RI** (persentase tempat tidur terisi)
- **Pendapatan hari ini** (dari pembayaran lunas)
- **Alert stok obat menipis**
- **Alert obat mendekati expired**
- **Recent registrations** (10 pasien terakhir)

### 2. Master Data — Update Data Dokter/Poli/Kamar/Obat

*(Fitur CRUD master data via UI belum ada di v1 — via IT.)*

Sementara kalau butuh update:
- Tambah dokter baru → hubungi IT
- Tambah jadwal dokter → IT
- Update tarif kamar → IT
- Formularium (tambah obat) → IT + Apoteker koordinasi

Roadmap: menu Master Data lengkap CRUD di Tier 2.

### 3. Export Rekap untuk Rapat

**Rekap Tagihan Bulanan:**
1. Menu **Billing**
2. Filter tanggal (dari-sampai)
3. Filter status (mis. LUNAS untuk hitung pendapatan real)
4. Tombol **📊 Export Excel**
5. File Excel download — buka untuk analisis

📸 **SCREENSHOT: `09-export-rekap.png`** — Excel rekap tagihan.

**Data Pasien:**
1. Menu **Pasien**
2. Filter kalau perlu (mis. cari pasien di 1 kabupaten tertentu)
3. Tombol **📊 Export Excel**

Gunakan Excel untuk pivot, chart, analisis, laporan ke direksi/pemerintah.

### 4. Monitor Occupancy Real-Time

Menu **Bed Management** — visualisasi kamar realtime.

📸 **SCREENSHOT: `09-bed-management-manager.png`** — bed board view.

Data yang penting:
- Total bed occupancy rate (target ideal: 75–85%)
- Kamar KOTOR yang terlalu lama (cleaning service issue?)
- Kamar MAINTENANCE (butuh perbaikan?)
- Distribusi per kelas (VIP kosong tapi Kelas III overloaded? Perlu shift pricing?)

---

## Alur Kerja Direksi

### 1. Dashboard High-Level

Sama seperti manager, tapi biasanya butuh:
- **Trend pendapatan** mingguan/bulanan
- **BOR** (Bed Occupancy Rate) trend
- **LOS** (Length of Stay) rata-rata
- **Case mix** (proporsi diagnosa terbanyak)

*(Trend chart belum ada di v1 dashboard — via Export Excel + buat chart manual, atau BI tool external.)*

### 2. Sign-off Laporan Bulanan

Coordinate dengan Manager untuk export rekap. Direksi hanya read-only di sistem — tidak input data.

### 3. Approval untuk Kebijakan Besar

- Tarif kamar / tindakan naik → coordinate dengan IT untuk update master data
- Add/remove role permission → coordinate dengan IT + Admin

---

## Alur Kerja Auditor

### 1. Audit Log Viewer

Menu **Audit Log** (di sidebar section "KEPATUHAN").

📸 **SCREENSHOT: `09-audit-log-viewer.png`** — halaman audit log dengan filter.

Yang Anda bisa lihat:
- Siapa (causer) mengubah data
- Kapan (timestamp)
- Data apa (subject: Pasien / Kunjungan / User)
- Aksi apa (created / updated / deleted)
- Perubahan detail (klik "Lihat detail" untuk JSON)

Filter:
- **User** — siapa yang lakukan
- **Tipe Data** — Pasien / Kunjungan / User / dll
- **Aksi** — created / updated / deleted
- **Tanggal range**

💡 **TIP:** Untuk audit routine, filter **deleted** — data yang di-hapus paling sering jadi bahan pertanyaan.

### 2. Skenario Audit Umum

**Kasus A: Pasien komplain data medisnya diubah tanpa sepengetahuan.**
1. Cari pasien di menu Pasien, catat UUID-nya
2. Buka Audit Log → filter Subject Type = `App\Models\Pasien`
3. Cari record dengan subject_id = UUID tsb
4. Lihat siapa yang update, kapan, apa yang berubah

**Kasus B: Kasir dicurigai manipulasi pembayaran.**
1. Filter Subject Type = `App\Models\Pembayaran`
2. Filter Causer = kasir tsb
3. Filter Aksi = updated / deleted
4. Cek void reasons — apakah masuk akal?

**Kasus C: Ada dispute tagihan.**
1. Cari tagihan di menu Billing
2. Buka Audit Log → filter Subject Type = `App\Models\Tagihan`
3. Cek history perubahan status, siapa yang finalize, siapa yang generate

### 3. Export untuk Investigasi

*(Export audit log ke Excel belum ada di v1 — copy-paste manual atau via IT/tinker.)*

---

## Alur Kerja Petugas BPJS

*Fitur klaim BPJS belum ter-implement full di v1 — bridging V-Claim direncanakan Tier 3.*

Sementara ini, alur manual:
1. Lihat daftar kunjungan penjamin BPJS via menu Billing → filter penjamin BPJS
2. Cetak dokumen manual (SEP + resume) untuk klaim
3. Input ke aplikasi V-Claim BPJS terpisah

Panduan lengkap akan ditambahkan setelah bridging selesai.

---

## FAQ

**Q: Saya Direksi tapi tidak bisa lihat detail resep obat pasien tertentu.**
A: Sengaja. Direksi tidak akses PHI (Protected Health Information) detail — hanya statistik agregat. Untuk detail medis, minta ke DPJP pasien.

**Q: Saya Manager mau tambah dokter baru.**
A: Belum ada UI di v1. Request ke IT dengan data lengkap: nama, kode, SIP, spesialisasi, jasa konsul, email, telp.

**Q: Saya Auditor, ingin lihat semua akses ke data pasien tertentu (bukan hanya perubahan).**
A: v1 hanya log perubahan (create/update/delete), belum log baca (read). Roadmap.

**Q: Export Excel-nya isi ribuan baris, bikin file besar. Ada opsi filter dulu?**
A: Ya, gunakan filter tanggal/status/search sebelum klik Export. File hanya berisi data yang match filter.

---

## Troubleshooting

**Dashboard tidak update angka penjualan hari ini**
→ Cache 5 menit. Refresh halaman kalau angka terlihat stale.

**Export Excel gagal / kosong**
→ Kalau filter terlalu ketat, data mungkin 0 row. Coba longgarkan filter. Kalau tetap error, hubungi IT.

**Audit log tidak muncul aktivitas terbaru**
→ Cek filter tanggal (default hari ini). Longgarkan ke "dari 7 hari lalu".

---

## Kontak

- Kebijakan strategis: **Direktur Utama**
- Data operasional: **Manager Pelayanan / Manager Keuangan**
- Audit finding: **Komite Audit Internal**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
