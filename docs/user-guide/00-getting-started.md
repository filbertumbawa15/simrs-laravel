# 00 - Getting Started (Wajib Baca Dulu)

Panduan dasar untuk **semua role**. Baca ini dulu sebelum masuk ke panduan role Anda.

---

## 1. Akses Aplikasi

Buka browser (**Chrome** atau **Edge** disarankan) dan ketik alamat:

> **https://sihrs.[nama-rs].id** *(ganti dengan URL yang diberikan IT)*

📸 **SCREENSHOT: `00-halaman-login.png`** — tampilan halaman login pertama kali.

⚠️ **PERHATIAN:**
- Selalu gunakan URL yang **dimulai `https://`** (ada gembok di address bar). Kalau tidak ada gembok, jangan login — hubungi IT.
- Jangan bookmark halaman login menggunakan `http://`

---

## 2. Login Pertama Kali

1. Ketik **username** (contoh: `dr.iqbal`, `apt.fitri`, `kasir.lina`) — bukan email
2. Ketik **password** (dari IT, harus diganti setelah login pertama)
3. Klik tombol **Login**

📸 **SCREENSHOT: `00-form-login.png`** — form login diisi (blur password!).

**Jika berhasil**, Anda diarahkan ke halaman **Dashboard**.

**Jika gagal**, cek:
- Username salah ketik (case sensitive)
- Password salah — coba lagi max 5x sebelum akun ke-lock selama 1 menit
- Akun dinonaktifkan — hubungi IT

⚠️ **PERHATIAN:** Setelah 5x salah password dalam 1 menit, sistem akan menolak login dari akun Anda selama 1 menit. Ini fitur keamanan anti-hacker.

---

## 3. Ganti Password Setelah Login Pertama (WAJIB)

🚨 **KRITIS**: Password default dari IT WAJIB diganti sebelum Anda kerja produktif. Kalau tidak, kalau ada masalah, dianggap Anda yang salah (audit log mencatat username Anda).

*Fitur ganti password mandiri saat ini via IT — request dulu ke IT untuk generate password baru dan sampaikan ke Anda via kanal aman.*

---

## 4. Kenali Layout Aplikasi

Setelah login, Anda melihat 3 area utama:

```
┌─────────┬────────────────────────────────────────────────┐
│         │  [Top Bar: jam realtime, nama Anda, logout]    │
│         ├────────────────────────────────────────────────┤
│ Sidebar │                                                │
│  Menu   │            Area Konten Utama                   │
│         │        (berubah sesuai menu yang dipilih)      │
│         │                                                │
└─────────┴────────────────────────────────────────────────┘
```

📸 **SCREENSHOT: `00-dashboard-layout.png`** — dashboard setelah login.

### A. Sidebar (Kiri) — Menu Navigasi
Menu yang muncul di sidebar **berbeda tergantung role Anda**. Menu yang tidak ada di sidebar Anda berarti Anda tidak berhak mengakses.

### B. Top Bar (Atas)
- Jam realtime — sinkron dengan server RS
- Nama Anda + role — pastikan benar!
- Tombol **Logout** — WAJIB klik saat selesai / meninggalkan komputer

### C. Area Konten Utama
Isi menu yang Anda klik.

---

## 5. Cara Membaca Status & Warna

Di seluruh aplikasi, warna badge/label punya arti konsisten:

| Warna | Arti | Contoh |
|---|---|---|
| 🟢 Hijau (emerald) | Normal / selesai / OK | Status Lunas, Hasil Normal, Kamar Tersedia |
| 🔵 Biru | Info / dalam proses | Sedang Diperiksa |
| 🟡 Kuning / Oranye | Perhatian / menunggu | Menunggu Pembayaran, Stok Menipis |
| 🔴 Merah | Kritis / gagal | Nilai Kritis Lab, Kamar Terisi Penuh, Tagihan Belum Lunas |
| ⚪ Abu-abu | Nonaktif / batal | Kunjungan Dibatalkan, Pasien Draft |

📸 **SCREENSHOT: `00-status-badge.png`** — contoh beberapa badge status di dashboard.

---

## 6. Tombol Umum yang Sering Anda Temui

| Tombol | Fungsi | Warning? |
|---|---|---|
| **Simpan** / **Store** | Simpan data ke DB | Kalau sudah simpan, tidak semua field bisa diedit lagi |
| **Update** / **Perbarui** | Simpan perubahan | Perubahan tercatat di audit log |
| **Batal** / **Cancel** | Kembali tanpa simpan | Aman, tidak ada data hilang |
| **Hapus** / **Delete** | Soft delete (data tidak benar-benar hilang) | Data tetap ada tapi tidak tampak — perlu IT untuk restore |
| **Cetak** / **Print** / 🖨️ | Buka PDF di tab baru untuk dicetak | Perlu allow popup |
| **Cari** / **Search** | Filter list | Otomatis update, tidak perlu tekan Enter |
| **Export Excel** 📊 | Download data ke .xlsx | Hanya role tertentu |

---

## 7. Realtime Refresh (Board & Antrian)

Halaman berikut auto-refresh setiap 15–30 detik:
- **Antrian RJ**
- **IGD Board**
- **Bed Management**

Di pojok kanan atas ada badge kecil bertuliskan "Refresh dalam Xs" dengan tombol ⏸ untuk pause.

📸 **SCREENSHOT: `00-auto-refresh-badge.png`** — close-up badge auto-refresh di IGD board.

💡 **TIP:** Kalau Anda sedang mengisi form lama, klik ⏸ dulu supaya halaman tidak refresh dan kehilangan input.

---

## 8. Cara Cetak Dokumen

Semua dokumen dicetak sebagai PDF (bukan langsung print). Alur:

1. Klik tombol **🖨️** atau **Cetak** di halaman terkait
2. Tab baru terbuka menampilkan PDF
3. Tekan **Ctrl+P** (atau Cmd+P di Mac) untuk print ke printer
4. Pilih printer tujuan (thermal 58mm untuk tiket antrian, printer normal untuk kuitansi/resume)
5. Klik **Print**

📸 **SCREENSHOT: `00-cetak-dialog.png`** — dialog print browser.

⚠️ **PERHATIAN:** Setiap dokumen PDF punya **QR Code verifikasi** di footer. QR ini bisa di-scan pasien untuk konfirmasi keaslian — jangan crop bagian ini saat print.

---

## 9. Logout — WAJIB Saat Meninggalkan Komputer

🚨 **KRITIS**: Jangan pernah tinggalkan komputer dalam kondisi login. Setiap aktivitas yang dilakukan tercatat atas nama Anda di audit log — kalau orang lain memakai akun Anda, Anda yang dianggap bertanggung jawab.

**Cara logout:**
1. Klik nama Anda di top-bar kanan atas
2. Klik menu **Logout**
3. Anda diarahkan kembali ke halaman login

📸 **SCREENSHOT: `00-logout-menu.png`** — dropdown menu user dengan opsi logout ter-highlight.

**Kalau tergesa-gesa:** Tekan **Ctrl+Shift+End** di keyboard — kombinasi darurat untuk logout cepat *(kalau di-set oleh IT — jika tidak, klik logout manual)*.

Session akan otomatis expired setelah **2 jam idle**.

---

## 10. Apa yang Harus Dilakukan Kalau Ada Masalah?

### Aplikasi lambat / hang
1. Tekan F5 refresh sekali
2. Kalau tetap lambat >30 detik, buka `https://sihrs.[nama-rs].id/up/health` di tab baru
3. Kalau muncul JSON dengan `"status": "ok"` = sistem OK, masalah di komputer/network Anda
4. Kalau tidak muncul atau error = hubungi IT segera

### Ada pesan error merah
1. Baca pesannya — biasanya menjelaskan apa yang salah (mis. "Stok Paracetamol tidak cukup")
2. Kalau pesannya teknis (angka, kode) — screenshot, kirim ke IT
3. Jangan panik dan jangan close browser — data terakhir mungkin masih bisa di-save

### Data hilang / tidak muncul
1. **Jangan re-entry** dulu — kemungkinan besar data ada tapi filter tersembunyi
2. Klik tombol **Reset** filter di halaman list
3. Kalau tetap tidak ada, hubungi IT — mereka bisa cek audit log

### Password lupa
- Hubungi IT untuk reset (self-service belum ada di v1)

---

## Selanjutnya

Lanjut baca panduan role Anda:

- [01 - Registrasi](01-registrasi.md)
- [02 - Dokter](02-dokter.md)
- [03 - Perawat](03-perawat.md)
- [04 - Laboratorium](04-lab.md)
- [05 - Radiologi](05-radiologi.md)
- [06 - Farmasi](06-farmasi.md)
- [07 - Kasir](07-kasir.md)
- [08 - IGD & Triase](08-igd.md)
- [09 - Manager & Direksi](09-manager-direksi.md)
- [10 - Administrator](10-admin.md)
