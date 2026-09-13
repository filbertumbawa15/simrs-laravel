# SIHRS — Panduan Pengguna

Panduan pemakaian aplikasi SIHRS per role. Dokumen ini dibuat untuk **staff RS yang akan langsung memakai aplikasi**, bukan untuk teknisi IT.

## Untuk Siapa Panduan Ini?

Cari role Anda di daftar di bawah, klik/buka file yang sesuai, ikuti step-by-step.

| Role Anda | Panduan | Perkiraan waktu baca |
|---|---|---|
| Baru pertama kali login | [00 - Getting Started](00-getting-started.md) — **WAJIB baca dulu** | 5 menit |
| Petugas Pendaftaran / Registrasi | [01 - Registrasi](01-registrasi.md) | 10 menit |
| Dokter (Umum / Spesialis) | [02 - Dokter](02-dokter.md) | 20 menit |
| Perawat (Rawat Jalan / Rawat Inap) | [03 - Perawat](03-perawat.md) | 10 menit |
| Analis Lab / Dokter Patologi Klinik | [04 - Laboratorium](04-lab.md) | 15 menit |
| Radiografer / Dokter Radiolog | [05 - Radiologi](05-radiologi.md) | 15 menit |
| Apoteker / TTK Apotek | [06 - Farmasi](06-farmasi.md) | 15 menit |
| Kasir / Kasir Supervisor | [07 - Kasir](07-kasir.md) | 10 menit |
| Petugas IGD (perawat/dokter jaga) | [08 - IGD & Triase](08-igd.md) | 10 menit |
| Manager / Direksi / Auditor | [09 - Manager & Direksi](09-manager-direksi.md) | 10 menit |
| Administrator IT | [10 - Administrator](10-admin.md) | 15 menit |

---

## Konvensi Penulisan

- 💡 **TIP** — cara kerja lebih cepat/efisien
- ⚠️ **PERHATIAN** — hal yang bisa bikin error atau data hilang kalau salah
- 🚨 **KRITIS** — patient safety issue, wajib dilakukan
- 📸 **SCREENSHOT** — tempat operator/IT ambil screenshot untuk dokumentasi
- ✅ Langkah yang benar
- ❌ Kesalahan umum yang harus dihindari

---

## Cara Menggunakan Panduan Ini

1. Cari role Anda di tabel atas
2. **Wajib** baca `00-getting-started.md` dulu, walau Anda sudah familiar dengan aplikasi web lain
3. Ikuti langkah per langkah — jangan skip
4. Kalau ada yang tidak jelas, hubungi IT (kontak di bawah)

---

## Untuk IT / Yang Menyiapkan Dokumen

Kalau Anda IT yang siapkan panduan ini untuk operator:

1. **Screenshot capture:** Di setiap tanda `📸 SCREENSHOT: [nama-file.png]`, buka aplikasi di kondisi sesuai deskripsi, capture layar, simpan ke `docs/user-guide/screenshots/[nama-file.png]`
2. **Rekomendasi tool screenshot:**
   - Windows: **Snipping Tool** (Windows + Shift + S)
   - Mac: **Cmd + Shift + 4**
   - Chrome DevTools: **Ctrl+Shift+P** → "Capture full size screenshot"
3. **Format:** PNG, resolusi 1280×800 atau 1920×1080. Kompres pakai [tinypng.com](https://tinypng.com) sebelum commit
4. **Sensor data pasien** kalau screenshot dari produksi — nama, NIK, alamat wajib di-blur (gunakan pen tool di Snipping Tool)
5. **Rebuild PDF:** Panduan bisa di-print jadi PDF via `pandoc` atau langsung print dari markdown viewer (VS Code + Markdown PDF extension)

---

## Kontak Bantuan

Kalau menemukan bug atau butuh bantuan:

| Situasi | Kontak | Jam |
|---|---|---|
| Aplikasi error / tidak bisa diakses | **IT On-call** — [WA/telp] | 24/7 |
| Data salah / butuh koreksi | **IT Support** — [WA/telp] | Jam kerja |
| Pertanyaan alur / SOP | **Supervisor unit Anda** | Jam kerja |
| Request fitur baru / masukan | **Manajer IT** — [email] | Jam kerja |

---

*Panduan ini di-update terakhir: [tanggal update oleh IT]*
*Versi SIHRS: 1.0*
