# SIHRS — Sistem Informasi Rumah Sakit

Aplikasi SIM-RS production-ready berbasis **Laravel 12** untuk rumah sakit tipe C/D (skala menengah, 120–150 tempat tidur).

Mencakup modul **Pendaftaran Pasien**, **Rawat Jalan (RJ)**, **Rawat Inap (RI)**, **IGD**, **Laboratorium**, **Farmasi (FEFO)**, dan **Billing & Pembayaran** — siap diintegrasikan dengan BPJS V-Claim, SATUSEHAT Kemenkes, dan payment gateway.

---

## 📋 Persyaratan Sistem

| Komponen        | Versi minimum                          |
|-----------------|----------------------------------------|
| PHP             | 8.2+                                   |
| Composer        | 2.5+                                   |
| Node.js         | 18+                                    |
| MySQL / MariaDB | 8.0+ / 10.6+                           |
| Redis (opsional)| 6.x (recommended untuk production)     |
| Web server      | Nginx / Apache (atau `php artisan serve` untuk dev) |

---

## 🚀 Instalasi (Development)

```bash
# 1. Clone repository
git clone <repo-url> sihrs
cd sihrs

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Edit .env, sesuaikan koneksi database:
#    DB_DATABASE=sihrs
#    DB_USERNAME=root
#    DB_PASSWORD=...

# 5. Buat database
mysql -u root -p -e "CREATE DATABASE sihrs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 6. Jalankan migration + seeder
php artisan migrate --seed

# 7. Storage symlink
php artisan storage:link

# 8. Build asset
npm run dev   # development (watch mode)
# atau
npm run build # production

# 9. Run
php artisan serve
```

Akses: <http://localhost:8000>

---

## 🔐 Kredensial Default (development)

| Username     | Password   | Role        |
|--------------|------------|-------------|
| `admin`      | `password` | SUPER_ADMIN |
| `dr.andika`  | `password` | DOKTER      |
| `dr.sari`    | `password` | DOKTER      |
| `apt.fitri`  | `password` | APOTEKER    |
| `lab.budi`   | `password` | ANALIS_LAB  |
| `reg.mira`   | `password` | REGISTRASI  |
| `kasir.lina` | `password` | KASIR       |

> ⚠ **WAJIB ubah semua password default sebelum go-live!**

Sample data: 50 pasien (10 showcase + 40 faker), 10 dokter, 88 kamar (8 VIP / 15 kelas I / 25 kelas II / 40 kelas III), formularium 15 obat dengan stok awal 500/batch, 20 ICD-10 paling umum, 12 parameter lab.

---

## 🏗 Arsitektur

```
app/
├── Enums/              # Type-safe enum (TipeKunjungan, StatusKunjungan, FlagHasilLab, dll)
├── Http/
│   ├── Controllers/    # Thin controllers, delegasi logic ke Services
│   ├── Requests/       # Validasi Form Request
│   └── Middleware/
├── Models/             # Eloquent dengan UUID primary key
├── Policies/           # Authorization per resource
├── Services/           # Business logic
│   ├── PendaftaranService.php
│   ├── RawatJalanService.php
│   ├── FarmasiService.php   # FEFO logic
│   └── BillingService.php   # Aggregasi semua biaya kunjungan
database/
├── migrations/         # Schema (UUID-based, foreign key disiplin)
├── factories/
└── seeders/
    ├── DatabaseSeeder.php       # Orchestrator
    ├── RolePermissionSeeder.php # 12 role + 30+ permissions
    ├── MasterDataSeeder.php     # Poli, dokter, kamar, obat, ICD-10, lab
    ├── UserSeeder.php
    └── PasienSeeder.php
resources/views/
├── layouts/  components/  auth/  dashboard/
├── pasien/  kunjungan/  rj/  farmasi/  billing/
```

### Konvensi penting

- **UUID** sebagai primary key (bukan auto-increment). Aman untuk integrasi BPJS/SATUSEHAT, distributed-friendly.
- **Soft delete** untuk semua data klinis (`pasien`, `kunjungan`, `tagihan`). **Tidak ada hard delete** — Permenkes 269/2008 mengharuskan retensi rekam medis minimal 5 tahun.
- **Activity Log** otomatis tercatat setiap perubahan data klinis (Spatie Activity Log, retensi 5 tahun).
- **Idempotency** di transaksi kritis: `BillingService::generateTagihan()` aman dipanggil berulang.
- **Locking & race condition**: `Pasien::generateNoRm()`, generate antrian, dan FEFO stok memakai `lockForUpdate()`.

---

## 🔄 Alur Operasional

```
Pasien datang
    │
    ▼
[Pendaftaran] ──► generate No. RM (jika baru) + buat Kunjungan
    │
    ├─► RJ ──► Antrian poli ──► Pemeriksaan (SOAP + ICD-10)
    │            │
    │            ├─► [opsional] Order Lab ──► Hasil divalidasi
    │            ├─► [opsional] Tindakan medis
    │            └─► [opsional] Resep ──► Verifikasi apoteker ──► Serahkan (FEFO)
    │
    ├─► IGD ──► Triase ──► (sama seperti RJ, lalu mungkin lanjut RI)
    │
    └─► RI ──► Admisi (pilih kamar) ──► CPPT harian ──► Pulang (resume medis)
              │
              └─► Pindah kamar bila perlu

           ──► Semua kanal bertemu di:
                  [Billing] ──► Generate tagihan dari semua item
                       │
                       ├─► Bayar tunai/EDC/QRIS (umum)
                       └─► Klaim INA-CBGs (BPJS)
```

---

## ⚙️ Konfigurasi Production

### 1. Environment

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sihrs.namars.id

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
```

### 2. Optimasi setelah deploy

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
npm run build
```

### 3. Queue worker (supervisor)

```ini
[program:sihrs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sihrs/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/sihrs/storage/logs/worker.log
```

### 4. Scheduler (cron)

```cron
* * * * * cd /var/www/sihrs && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Backup

```cron
# Database backup harian jam 2 pagi
0 2 * * * /usr/bin/mysqldump sihrs | gzip > /backup/sihrs-$(date +\%Y\%m\%d).sql.gz

# Cleanup backup > 30 hari
0 3 * * * find /backup -name "sihrs-*.sql.gz" -mtime +30 -delete
```

> 💡 **WAJIB**: setup juga offsite backup weekly ke S3/Wasabi/cloud lain.

---

## 🔌 Integrasi (slot sudah disiapkan di `.env`)

| Integrasi          | Endpoint config                        | Status      |
|--------------------|----------------------------------------|-------------|
| BPJS V-Claim       | `BPJS_VCLAIM_*`                        | Slot ready  |
| BPJS Antrol        | `BPJS_ANTROL_*`                        | Slot ready  |
| BPJS Apotek Online | `BPJS_APOTEK_*`                        | Slot ready  |
| SATUSEHAT Kemenkes | `SATUSEHAT_*`                          | Slot ready  |
| SIRANAP            | `SIRANAP_*`                            | Slot ready  |
| Midtrans (QRIS)    | `MIDTRANS_*`                           | Slot ready  |
| Wablas (WhatsApp)  | `WABLAS_*`                             | Slot ready  |

> ⚠ Service class implementasi (BPJSService, SatusehatService, dll) belum ada di repo ini — masuk roadmap Tier 3.

---

## ✅ Yang Sudah Dibangun

- ✅ Database schema lengkap (24 tabel, UUID, soft delete, audit log)
- ✅ Master data: dokter, poli, kamar, obat (dengan stok per batch), ICD-10, parameter lab, tindakan
- ✅ Modul Pasien (CRUD + rekam medis permanen)
- ✅ Modul Kunjungan (RJ/RI/IGD, multi-penjamin)
- ✅ Modul Rawat Jalan end-to-end (antrian → SOAP → ICD-10 → selesai)
- ✅ Modul Farmasi (resep → verifikasi → serah obat dengan **FEFO**)
- ✅ Modul Billing (aggregasi otomatis dari semua item → finalize → pembayaran partial/lunas)
- ✅ Authentication + Role-Based Access (12 role, 30+ permissions)
- ✅ Dashboard dengan statistik & alert (stok menipis, obat expired)
- ✅ Sample data 50 pasien realistic (nama & alamat Sumatera Utara)

## 🔜 Roadmap

**Tier 2** — Lengkapi semua kanal pelayanan
- Modul **Laboratorium** lengkap (order → sampling → input hasil → validasi)
- Modul **Rawat Inap** lengkap (admisi → bed management board → CPPT → pulang)
- Modul **Triase IGD** dengan kategori warna (Merah/Kuning/Hijau/Hitam)

**Tier 3** — Production hardening
- Testing (PHPUnit) — terutama untuk **FarmasiService** (FEFO) dan **BillingService** (idempotency)
- Docker Compose (MySQL + PHP-FPM + Nginx + Redis)
- PDF Reports (kuitansi, resume medis, hasil lab) via DomPDF
- BPJS Bridging Service (V-Claim, Antrol, Apotek Online)
- SATUSEHAT FHIR integration
- Bridging payment gateway (Midtrans QRIS)

---

## 📝 Kepatuhan Regulasi

Sistem ini dirancang mengikuti:

- **Permenkes 269/2008** — Rekam medis disimpan minimal 5 tahun. Tidak ada hard delete data klinis.
- **Permenkes 24/2022** — Rekam medis elektronik wajib di seluruh fasyankes.
- **UU 27/2022** (PDP) — Perlindungan data pribadi pasien. Implementasi:
  - Disk private untuk dokumen sensitif
  - Audit log akses data pasien
  - Password hashing bcrypt cost 12
  - Session encrypted
- **SATUSEHAT** (mandatory sejak 2023) — Slot integrasi sudah disiapkan.

---

## 🤝 Lisensi & Kontribusi

MIT License. Project ini adalah starter — implementasi spesifik per RS (branding, alur lokal, integrasi vendor) silakan fork dan customize.

---

## 📞 Bantuan

- Issues: GitHub Issues
- Dokumentasi internal RS: `/docs` (akan ditambahkan)
- Untuk training operator RS, hubungi tim IT internal

---

**Build dengan ❤ untuk healthcare workers Indonesia.**
