# Screenshot Checklist untuk IT

Daftar 60+ screenshot yang perlu dicapture untuk melengkapi user guide.
Setiap panduan role sudah punya placeholder `📸 SCREENSHOT: [nama-file.png]` — capture dan simpan ke `docs/user-guide/screenshots/[nama-file.png]`.

## Setup Sebelum Capture

1. **Environment:** capture di lingkungan **staging** (bukan produksi) supaya data pasien tidak ke-expose
2. **Data:** pakai sample data seeder (`php artisan db:seed --class=SampleSkenarioSeeder`) — sudah ada 5 skenario end-to-end
3. **Browser:** Chrome full HD (1920×1080), zoom 100%
4. **Tool:** Windows Snipping Tool / Cmd+Shift+4 / DevTools Ctrl+Shift+P → "Capture full size screenshot"
5. **Sensor data:** kalau harus capture dari produksi, blur nama/NIK/alamat pasien dengan pen tool

Format:
- **PNG**, kompres pakai [tinypng.com](https://tinypng.com) sebelum commit
- **Nama file:** persis sesuai placeholder — case-sensitive

---

## Daftar Screenshot (Checklist)

### 00 - Getting Started (7 file)

- [ ] `00-halaman-login.png` — halaman login pertama kali (tanpa data)
- [ ] `00-form-login.png` — form login diisi (blur password)
- [ ] `00-dashboard-layout.png` — dashboard setelah login (login sebagai `admin`)
- [ ] `00-status-badge.png` — close-up beberapa status badge di dashboard
- [ ] `00-auto-refresh-badge.png` — close-up badge auto-refresh di IGD board
- [ ] `00-cetak-dialog.png` — dialog print browser saat cetak PDF
- [ ] `00-logout-menu.png` — dropdown user menu dengan logout

### 01 - Registrasi (7 file)

Login sebagai `reg.mira`.

- [ ] `01-pasien-list-search.png` — halaman `/pasien` dengan kolom cari (kosong)
- [ ] `01-form-pasien-baru.png` — form `/pasien/create` kosong
- [ ] `01-pasien-baru-berhasil.png` — halaman `/pasien/{id}` setelah simpan
- [ ] `01-pasien-detail-with-riwayat.png` — detail pasien lama dengan riwayat kunjungan (pakai pasien seeder mis. Budi Hartono)
- [ ] `01-form-kunjungan.png` — form `/kunjungan/create` kosong
- [ ] `01-kunjungan-berhasil.png` — detail kunjungan setelah dibuat
- [ ] `01-tiket-antrian-pdf.png` — preview PDF tiket 58mm (dari `/pdf/tiket/{rj}`)

### 02 - Dokter (11 file)

Login sebagai `dr.andika` (Sp.PD) atau `dr.iqbal`.

- [ ] `02-antrian-rj-list.png` — `/rj/antrian` semua poli
- [ ] `02-antrian-tombol-panggil.png` — close-up 1 baris antrian dengan tombol Panggil ter-hover
- [ ] `02-form-soap-empty.png` — `/rj/{rj}/periksa` form SOAP kosong
- [ ] `02-form-soap-diisi.png` — form SOAP dengan sample data terisi (S, O, A, P + tanda vital)
- [ ] `02-diagnosa-search-icd.png` — dropdown autocomplete ICD-10 saat ketik "diare"
- [ ] `02-order-lab-form.png` — form `/lab/create` dengan 3-5 parameter di-select
- [ ] `02-form-resep.png` — form `/resep/create` dengan 2 obat contoh
- [ ] `02-selesai-berhasil.png` — halaman antrian setelah pasien selesai (pasien hilang dari list)
- [ ] `02-form-admisi-ri.png` — form `/ri/admisi` dengan kamar dropdown terbuka
- [ ] `02-form-discharge.png` — form `/ri/{ri}/pulang`
- [ ] `02-detail-kunjungan-hasil-lab.png` — detail kunjungan dengan hasil lab tervalidasi

### 03 - Perawat (6 file)

Login sebagai `pwt.rina` (untuk umum) atau `pwt.yusuf` (untuk RI).

- [ ] `03-tanda-vital-form.png` — close-up section tanda vital di form SOAP
- [ ] `03-ri-list.png` — `/ri` halaman daftar pasien inap aktif
- [ ] `03-bed-board.png` — `/kamar/board` bed management
- [ ] `03-cppt-form.png` — form tambah CPPT keperawatan
- [ ] `03-update-status-kamar.png` — dialog ubah status kamar dari KOTOR ke TERSEDIA
- [ ] `03-pasien-detail-riwayat.png` — detail pasien dengan tab riwayat kunjungan

### 04 - Laboratorium (8 file)

Login sebagai `lab.budi` (Analis) untuk 5 pertama, `dr.doni` (PK) untuk 3 terakhir.

- [ ] `04-lab-worklist.png` — `/lab` worklist dengan filter status & prioritas
- [ ] `04-detail-order-lab.png` — detail order lab dengan parameter
- [ ] `04-form-input-hasil.png` — form input hasil (setidaknya 3 parameter)
- [ ] `04-hasil-dengan-flag-kritis.png` — hasil dengan flag HH ter-highlight merah (contoh: HbA1c 12%)
- [ ] `04-worklist-validasi.png` — worklist filter status = VALIDASI
- [ ] `04-review-hasil-lengkap.png` — halaman review hasil sebelum validate
- [ ] `04-hasil-tervalidasi.png` — halaman hasil setelah divalidasi (nama validator + tgl)
- [ ] `04-pdf-hasil-lab.png` — preview PDF hasil lab

### 05 - Radiologi (7 file)

Login sebagai `rad.tono` (Radiografer) untuk 4 pertama, `dr.dewi` (Radiolog) untuk 3 terakhir.

- [ ] `05-rad-worklist.png` — `/rad` worklist
- [ ] `05-detail-order-rad.png` — detail order dengan flag hamil kalau ada
- [ ] `05-form-eksekusi.png` — form eksekusi dengan upload image
- [ ] `05-eksekusi-selesai.png` — order dengan images ter-upload
- [ ] `05-viewer-image.png` — preview image radiologi terbuka di tab baru
- [ ] `05-form-bacaan.png` — form input bacaan (Bacaan/Kesan/Saran + checkbox kritis)
- [ ] `05-validasi-berhasil.png` — order status SELESAI

### 06 - Farmasi (6 file)

Login sebagai `apt.fitri`.

- [ ] `06-resep-worklist.png` — `/resep` worklist dengan filter
- [ ] `06-detail-resep-verify.png` — detail resep status BARU siap diverifikasi
- [ ] `06-detail-resep-dispense.png` — detail resep status DIVERIFIKASI siap dispense
- [ ] `06-serahkan-success.png` — detail resep status DISERAHKAN dengan batch_used
- [ ] `06-pdf-resep.png` — preview PDF resep
- [ ] `06-dashboard-alert-stok.png` — dashboard dengan alert obat menipis (login `admin` untuk lihat lengkap)

### 07 - Kasir (7 file)

Login sebagai `kasir.lina` (5 pertama) atau `kasir.ratna` (2 terakhir).

- [ ] `07-billing-list.png` — `/billing` daftar tagihan
- [ ] `07-detail-tagihan.png` — halaman detail tagihan dengan rincian item
- [ ] `07-form-pembayaran.png` — form terima pembayaran
- [ ] `07-pembayaran-berhasil.png` — halaman tagihan setelah pembayaran (status LUNAS)
- [ ] `07-pdf-kuitansi.png` — preview PDF kuitansi
- [ ] `07-tagihan-cicilan.png` — tagihan status CICILAN dengan history pembayaran
- [ ] `07-export-excel.png` — dialog download Excel (dari `kasir.ratna`)

### 08 - IGD (3 file)

Login sebagai `pwt.rina` atau `dr.iqbal` yang ditugaskan IGD.

- [ ] `08-igd-board.png` — `/igd` board dengan pasien di 4-5 section
- [ ] `08-form-triase.png` — form triase awal
- [ ] `08-triase-berhasil.png` — IGD board setelah pasien dikategorikan

### 09 - Manager & Direksi (4 file)

Login sebagai `mgr.surya` (3 pertama), `auditor.heru` (terakhir).

- [ ] `09-dashboard-manager.png` — dashboard KPI
- [ ] `09-export-rekap.png` — Excel rekap tagihan (screenshot Excel yang sudah di-download)
- [ ] `09-bed-management-manager.png` — bed board view manager
- [ ] `09-audit-log-viewer.png` — `/audit-log` halaman audit log

---

## Total: **66 screenshot**

## Progress

- [ ] Getting Started (7/7)
- [ ] Registrasi (7/7)
- [ ] Dokter (11/11)
- [ ] Perawat (6/6)
- [ ] Laboratorium (8/8)
- [ ] Radiologi (7/7)
- [ ] Farmasi (6/6)
- [ ] Kasir (7/7)
- [ ] IGD (3/3)
- [ ] Manager (4/4)

**Estimasi waktu:** 4-6 jam untuk full 66 screenshot (termasuk setup data + capture + crop + kompres + rename).

## Setelah Semua Screenshot Selesai

1. Commit semua ke `docs/user-guide/screenshots/`
2. Generate PDF konsolidasi (opsional):
   ```bash
   # Pakai pandoc untuk convert markdown + screenshot jadi PDF
   pandoc docs/user-guide/*.md -o docs/user-guide/SIHRS-User-Guide.pdf \
       --pdf-engine=xelatex \
       --toc \
       --toc-depth=2
   ```
3. Print & jilid untuk setiap unit / poli
4. Upload ke intranet RS (kalau ada Confluence/Notion)

## Tips Screenshot Berkualitas

- **Highlight tombol/area penting** dengan kotak merah pakai [Skitch](https://evernote.com/products/skitch) atau [Greenshot](https://getgreenshot.org/)
- **Anonymize data pasien:** blur nama, NIK, alamat pakai pen tool Snipping Tool
- **Consistent zoom:** capture semua di zoom 100%, agar font size konsisten
- **Crop tidak perlu window seluruh:** fokus ke area menu / form yang dijelaskan
- **File size:** kompres ke < 200 KB per file (via tinypng) — biar dokumen tidak berat
