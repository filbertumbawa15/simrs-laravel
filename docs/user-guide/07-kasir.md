# 07 - Kasir

Panduan untuk **Kasir** (role `KASIR`) dan **Kasir Supervisor** (role `KASIR_SUPERVISOR`).

**Contoh username:** `kasir.lina` (Kasir), `kasir.ratna` (Supervisor)

## Perbedaan Role

| Fitur | KASIR | KASIR_SUPERVISOR |
|---|---|---|
| Lihat daftar tagihan | ✅ | ✅ |
| Terima pembayaran | ✅ | ✅ |
| Cetak kuitansi | ✅ | ✅ |
| **Void pembayaran** (batalkan) | ❌ | ✅ **Hanya supervisor** |
| Export rekap tagihan ke Excel | ❌ | ✅ |

Prinsip: transaksi bisa dibuat oleh kasir, tapi **koreksi (void)** butuh approval supervisor untuk audit trail.

---

## Alur Kerja Harian

### 1. Buka Daftar Tagihan

Menu **Billing** — list semua tagihan.

📸 **SCREENSHOT: `07-billing-list.png`** — daftar tagihan hari ini.

Filter:
- **Tanggal** — default hari ini
- **Status:**
  - **DRAFT** — belum di-finalize dokter/administrasi, bisa berubah
  - **BELUM_LUNAS** — siap dibayar (pasien umum)
  - **CICILAN** — sudah dibayar sebagian
  - **LUNAS** — selesai
  - **KLAIM** — pasien BPJS/asuransi (menunggu klaim, bukan pembayaran cash)
  - **VOID** — dibatalkan

💡 **TIP:** Fokus di status **BELUM_LUNAS** dan **CICILAN** — itu antrian kerja Anda.

---

### 2. Terima Pembayaran

#### Skenario A: Pasien Datang, Tagihan Sudah Ada di Sistem

1. Cari nama pasien / no tagihan di list
2. Klik detail tagihan
3. Halaman detail menampilkan:
   - Data pasien
   - Rincian item (konsultasi, tindakan, lab, obat, kamar)
   - Total, Diskon, PPN
   - Dibayar / Sisa
   - History pembayaran sebelumnya (kalau ada)

📸 **SCREENSHOT: `07-detail-tagihan.png`** — halaman detail tagihan lengkap.

4. Klik **💰 Terima Pembayaran**
5. Form pembayaran terbuka:

📸 **SCREENSHOT: `07-form-pembayaran.png`** — form input pembayaran.

Isi:
- **Metode Pembayaran:**
  - **TUNAI** — bayar cash
  - **DEBIT** — kartu debit (EDC)
  - **KREDIT** — kartu kredit
  - **TRANSFER** — transfer bank
  - **QRIS** — QR code payment (GoPay, Dana, ShopeePay, dll)
  - **BPJS / ASURANSI** — untuk klaim penjamin
- **Jumlah** — bisa sebagian (cicilan) atau full
- **Referensi Eksternal** (opsional) — nomor referensi EDC / bukti transfer
- **Catatan** — opsional

6. Klik **Simpan Pembayaran**

Sistem otomatis:
- Hitung sisa tagihan
- Kalau sisa = 0 → status **LUNAS**, kunjungan → **SELESAI**
- Kalau sisa > 0 → status **CICILAN**

📸 **SCREENSHOT: `07-pembayaran-berhasil.png`** — halaman tagihan setelah pembayaran (status lunas, tombol cetak kuitansi).

#### Skenario B: Tagihan Belum Di-generate

Kalau kunjungan sudah selesai tapi tagihan belum muncul, artinya belum di-generate. Biasanya sistem auto-generate saat pemeriksaan selesai. Kalau tidak:

1. Menu **Kunjungan** → cari pasien
2. Detail kunjungan → tombol **💵 Generate Tagihan**
3. Sistem hitung dari semua item (konsultasi + obat + lab + rad + kamar untuk RI)
4. Tagihan tersimpan status **DRAFT**
5. Klik **Finalize Tagihan** (locks tagihan, tidak bisa diubah lagi)
6. Lanjut ke terima pembayaran

⚠️ **PERHATIAN:** Setelah **Finalize**, tagihan tidak bisa diubah. Kalau ada item terlewat, cancel finalize (tidak ada tombol — hubungi IT).

---

### 3. Cetak Kuitansi

Setelah pembayaran tercatat:

1. Detail tagihan → tombol **🖨️ Cetak Kuitansi**
2. PDF terbuka dengan:
   - Kop RS
   - Data pasien
   - Rincian pembayaran + item
   - **Terbilang** (mis. "Seratus dua puluh ribu rupiah")
   - Tanda tangan kasir + QR verifikasi
3. Ctrl+P → Print → berikan ke pasien

📸 **SCREENSHOT: `07-pdf-kuitansi.png`** — preview PDF kuitansi.

💡 **TIP:** Print 2 rangkap — 1 untuk pasien, 1 arsip.

---

### 4. Cicilan (Pembayaran Bertahap)

Skenario: pasien bayar sebagian dulu, sisanya nanti.

1. Terima pembayaran cicilan pertama seperti biasa
2. Sistem update status → **CICILAN**, sisa tersimpan
3. Ketika pasien bayar lagi:
   - Cari tagihan yang sama
   - Klik Terima Pembayaran lagi
   - Sistem otomatis ambil sisa sebagai max jumlah

📸 **SCREENSHOT: `07-tagihan-cicilan.png`** — tagihan status CICILAN dengan history pembayaran.

Ulangi sampai lunas.

---

### 5. Void Pembayaran (KASIR_SUPERVISOR only)

Kalau pembayaran salah input (jumlah salah, salah metode), harus di-void oleh supervisor.

1. Detail tagihan → cari pembayaran yang salah
2. Klik icon **❌ Void** (hanya muncul untuk supervisor)
3. Isi **Alasan Void** (wajib) — mis. "Salah input jumlah, seharusnya Rp 250.000 bukan Rp 2.500.000"
4. Konfirmasi

⚠️ **PERHATIAN:**
- Void TIDAK menghapus record, hanya tandai `is_void=true` + reason
- Sistem hitung ulang sisa tagihan otomatis
- Kalau perlu re-entry, kasir input pembayaran baru dengan jumlah yang benar

🚨 **KRITIS:** Void = audit-heavy. Setiap void tercatat: kapan, siapa yang void, alasan. Kalau ada review keuangan, semua void akan diaudit.

---

## Rekap Harian End-of-Shift

Sebelum ganti shift / tutup kasir:

1. Menu **Billing** → filter tanggal = hari ini
2. Lihat rekap:
   - Total tagihan hari ini
   - Total dibayar hari ini
   - Metode pembayaran breakdown

Cocokkan dengan uang tunai di kasir (untuk cash), rekening bank (untuk transfer/QRIS/EDC).

Kalau ada selisih, cari transaksi yang mencurigakan (void, cicilan yang belum tercatat).

---

## Export Rekap Excel (KASIR_SUPERVISOR)

Untuk keuangan bulanan:

1. Menu **Billing**
2. Tombol **📊 Export Excel** di kanan atas
3. Filter tanggal + status
4. Excel download → berisi semua tagihan + subtotal, dibayar, sisa per baris

📸 **SCREENSHOT: `07-export-excel.png`** — dialog download Excel export.

Serahkan ke bagian keuangan/direksi untuk laporan.

---

## FAQ Kasir

**Q: Pasien BPJS tapi kelebihan iur (mis. naik kelas). Bagaimana?**
A: Sistem akan tampilkan iur pasien separately. Terima pembayaran hanya untuk iur pasien, jangan totalnya. Bagian penjamin (BPJS) tercatat sebagai KLAIM.

**Q: Pasien lupa bawa uang, mau bayar besok.**
A: Boleh biarkan status BELUM_LUNAS. Kunjungan tetap tercatat tapi belum selesai. Ketika pasien balik, terima pembayaran.

**Q: Kasir malam tidak bisa terima pembayaran karena akun kena rate limit.**
A: Rate limit 30 request/menit untuk write. Kalau input sangat cepat (mis. double-click submit), bisa terkena. Tunggu 1 menit atau logout-login.

**Q: Salah pilih metode pembayaran (misalnya klik Debit padahal Tunai).**
A: Kalau belum bayar, batal & input ulang. Kalau sudah tersimpan, minta supervisor void, lalu input baru dengan metode benar.

**Q: Pasien minta diskon.**
A: Kolom diskon ada di form (di v1 ada di level tagihan, bukan per-item). Untuk apply diskon, admin/manajer perlu edit tagihan (belum ada UI — via IT).

---

## Troubleshooting

**"Simpan Pembayaran" error "Jumlah melebihi sisa"**
→ Kolom jumlah maksimum = sisa tagihan. Cek angka.

**"Simpan Pembayaran" error "Tagihan sudah lunas"**
→ Someone else sudah bayar (biasanya di RS besar dengan multiple kasir). Refresh halaman.

**Tombol Cetak Kuitansi tidak muncul**
→ Kuitansi hanya bisa dicetak setelah pembayaran tersimpan. Refresh halaman.

**Nomor kuitansi ada slash "/" — apakah normal?**
→ Ya, format PB/2026/09/000123 — sistem otomatis replace slash saat generate PDF filename.

---

## Kontak

- Konsultasi finansial: **Bagian Keuangan / Bendahara**
- Void perlu approval: **Kasir Supervisor / Manajer Keuangan**
- Bug aplikasi: **IT Support** (kontak di [README](README.md))
