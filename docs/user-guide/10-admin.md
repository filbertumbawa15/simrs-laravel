# 10 - Administrator IT (SUPER_ADMIN)

Panduan untuk **Administrator IT** — role `SUPER_ADMIN`.

**Contoh username:** `admin`

## Peran Anda

Anda adalah "raja" sistem — akses ke SEMUA fitur, bypass semua permission check.

Tanggung jawab utama:
- Manage user (buat, aktifkan/nonaktifkan, reset password)
- Manage master data (dokter, poli, kamar, obat, dll)
- Monitor sistem (health check, log, backup)
- Response ke insiden (bug, data corruption, security)
- Koordinasi dengan vendor developer untuk bug/feature

🚨 **KRITIS:** Karena akses Anda sangat besar, **audit log rajin memantau aktivitas Anda**. Setiap perubahan tercatat atas nama username Anda. Jangan sharing akun.

---

## Setup Awal Pasca Go-Live

### 1. Ganti Password Default

Setelah pertama kali login dengan username `admin` + password `password`:

```bash
php artisan tinker
>>> User::where('username', 'admin')->first()->update(['password' => Hash::make('PASSWORD_KUAT_BARU_ANDA')]);
```

**Wajib** password kuat: min 16 karakter, mix huruf besar/kecil/angka/simbol.

### 2. Nonaktifkan User Default Yang Tidak Dipakai

19 user seeder ter-generate saat setup. Yang tidak dipakai (mis. `dr.iqbal` fake) sebaiknya nonaktifkan atau delete:

```bash
php artisan tinker
>>> User::where('username', 'dr.iqbal')->update(['is_active' => false]);
# Atau soft delete
>>> User::where('username', 'dr.iqbal')->delete();
```

### 3. Buat User Real Sesuai Staff Aktual

Untuk setiap staff aktif di RS:

```bash
php artisan tinker
>>> $u = User::create([
...     'username' => 'dr.budi.internist',
...     'name' => 'dr. Budi Susanto, Sp.PD',
...     'email' => 'budi@rs.namars.id',
...     'password' => Hash::make('temp_password_yang_disampaikan'),
...     'is_active' => true,
...     'email_verified_at' => now(),
... ]);
>>> $u->assignRole('DOKTER_SPESIALIS');
# Kalau user adalah dokter, link ke Dokter model:
>>> $dokter = Dokter::create([...]);
>>> $u->update(['dokter_id' => $dokter->id]);
```

💡 **Roadmap:** UI CRUD user + master data direncanakan di Tier 2. Sementara via tinker/DB.

---

## Manage User

### Reset Password User

Kalau ada staff lupa password:

```bash
php artisan tinker
>>> User::where('username', 'apt.fitri')->update(['password' => Hash::make('password_baru')]);
```

Sampaikan password baru via kanal aman (bukan WA/email biasa — pakai signal, atau tulis di kertas).

### Nonaktifkan User (mis. resign)

```bash
php artisan tinker
>>> User::where('username', 'kasir.mawar')->update(['is_active' => false]);
```

User yang `is_active=false` otomatis logout kalau sedang session, dan tidak bisa login lagi. Data yang pernah mereka input tetap ada (audit trail).

### Aktifkan Kembali

```bash
>>> User::where('username', 'kasir.mawar')->update(['is_active' => true]);
```

---

## Monitor Sistem

### Health Check Endpoint

Setiap saat cek:
```bash
curl https://sihrs.namars.id/up/health
```

Output JSON:
```json
{
    "status": "ok",
    "app": "SIHRS",
    "environment": "production",
    "timestamp": "2026-09-06T14:30:00+07:00",
    "checks": {
        "database": {"status": "ok", "latency_ms": 5.23},
        "cache": {"status": "ok"},
        "storage": {"status": "ok"},
        "disk_space": {"status": "ok", "used_percent": 45.2, "free_gb": 55.3},
        "backup": {"status": "ok", "last_backup": "sihrs_sihrs_20260906_020015.sql.gz", "age_hours": 12.5, "size_mb": 45.8}
    }
}
```

Kalau `"status": "unhealthy"` → HTTP 503. Segera investigate.

### Sentry (Error Monitoring)

Login ke [sentry.io](https://sentry.io) atau URL self-host GlitchTip Anda.

Filter environment = production. Cek:
- **Unresolved errors** — bug baru muncul?
- **Frequency** — error mana yang paling sering?
- **Affected users** — berapa staff terkena?

Prioritaskan bug yang:
- Frekuensi tinggi (100+ occurrence)
- Multi-user (bukan hanya 1 orang)
- Bunyi patient-safety (mis. gagal simpan hasil lab)

### Log Files

Lokasi log di server:
- `storage/logs/laravel.log` — Laravel default (rotate daily)
- `storage/logs/audit.log` — audit trail (retensi 5 tahun)
- `storage/logs/bpjs.log` — komunikasi BPJS (kalau bridging aktif)
- `storage/logs/satusehat.log` — komunikasi SATUSEHAT
- `storage/logs/worker.log` — output queue worker
- `storage/logs/backup.log` — hasil job backup harian

Cek dengan `tail -f`:
```bash
sudo tail -f /var/www/sihrs/storage/logs/laravel.log
```

---

## Manage Master Data (Via Seeder / Tinker)

*(UI CRUD belum ada di v1 — via CLI.)*

### Tambah Dokter Baru

```bash
php artisan tinker
>>> $dokter = Dokter::create([
...     'kode' => 'DR00021',
...     'sip' => '445/000/DKK/2026',
...     'nik' => '1271011234567890',
...     'nama' => 'Ahmad Fadhil',
...     'gelar_depan' => 'dr.',
...     'gelar_belakang' => 'Sp.B',
...     'spesialisasi' => 'Bedah',
...     'telp' => '081234567890',
...     'email' => 'ahmad@sihrs.namars.id',
...     'jasa_konsul' => 200000,
...     'is_active' => true,
... ]);
```

Jangan lupa buat jadwal:
```bash
>>> $poli = Poli::where('nama', 'Poli Bedah')->first();
>>> \App\Models\JadwalDokter::create([
...     'dokter_id' => $dokter->id,
...     'poli_id' => $poli->id,
...     'hari' => 'SENIN',
...     'jam_mulai' => '08:00:00',
...     'jam_selesai' => '12:00:00',
...     'kuota' => 20,
...     'is_active' => true,
... ]);
```

### Tambah Obat Baru

```bash
>>> $obat = Obat::create([
...     'kode' => 'OBT10001',
...     'nama' => 'Levofloxacin 500mg',
...     'nama_generik' => 'Levofloxacin',
...     'golongan' => 'KERAS',
...     'bentuk_sediaan' => 'Tablet',
...     'satuan' => 'Tablet',
...     'kekuatan' => '500mg',
...     'harga_jual' => 15000,
...     'stok_minimum' => 20,
...     'is_fornas' => true,
...     'is_active' => true,
... ]);

# Tambah stok batch:
>>> \App\Models\StokObat::create([
...     'obat_id' => $obat->id,
...     'no_batch' => 'BATCH240915',
...     'jumlah_masuk' => 500,
...     'jumlah_sisa' => 500,
...     'tgl_masuk' => now(),
...     'exp_date' => '2027-03-15',
...     'hpp' => 12000,
...     'supplier' => 'PT Farma Sejahtera',
...     'no_faktur' => 'INV-2609-001',
... ]);
```

### Tambah Kamar Baru

```bash
>>> $kelas = \App\Models\KelasKamar::where('nama', 'VIP')->first();
>>> \App\Models\Kamar::create([
...     'no_kamar' => 'VIP-09',
...     'kelas_id' => $kelas->id,
...     'status' => 'TERSEDIA',
...     'lokasi' => 'Gedung A Lantai 3',
...     'kapasitas' => 1,
...     'is_active' => true,
... ]);
```

---

## Backup & Restore

### Backup Manual (di luar jadwal cron)

```bash
sudo -u www-data php artisan sihrs:backup
```

Cek file muncul di `BACKUP_PATH` (default `/var/backups/sihrs`).

### Restore Backup

🚨 **DANGER**: Restore = HAPUS DATA SAAT INI. Jangan restore di production tanpa persetujuan.

```bash
# 1. Backup dulu DB current (safety net)
sudo -u www-data php artisan sihrs:backup

# 2. Restore
gunzip -c /var/backups/sihrs/sihrs_sihrs_20260906_020015.sql.gz | mysql -u sihrs_app -p sihrs

# 3. Verify
mysql -u sihrs_app -p sihrs -e "SELECT COUNT(*) FROM pasien"
```

---

## Response Insiden

### Skenario: Data Pasien Corrupt (mis. hasil lab hilang)

1. Screenshot masalah di UI
2. Buka Audit Log → cek apakah ada aktivitas delete/update pada waktu tsb
3. Kalau ada malicious activity → identifikasi user, nonaktifkan
4. Kalau bug software → restore dari backup terdekat (setelah persetujuan direksi)
5. Report ke vendor developer via issue tracker

### Skenario: Aplikasi Down

1. Cek `/up/health` → JSON return apa?
2. Cek server: `sudo systemctl status nginx php8.2-fpm mysql`
3. Cek log: `tail -100 storage/logs/laravel.log`
4. Restart service yang mati
5. Kalau problem persist, escalate ke vendor

### Skenario: Data Breach (dicurigai)

1. **Isolate** — matikan akses external kalau bisa (block IP, matikan public route)
2. **Preserve evidence** — jangan restart server, jangan hapus log
3. **Log semua aktivitas** dari 24 jam terakhir (Audit log + web server access log)
4. **Report ke Direksi + tim legal RS + Kominfo** (kewajiban UU 27/2022 dalam 72 jam)
5. **Post-mortem** setelah semua stabil

---

## Update Aplikasi (Deploy Baru)

Kalau vendor push update:

```bash
cd /var/www/sihrs
sudo -u www-data php artisan down --render="errors::503"  # maintenance mode
sudo -u www-data git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci && sudo -u www-data npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart sihrs-worker:*
sudo -u www-data php artisan up  # keluar dari maintenance
```

Downtime target: **< 2 menit** kalau tidak ada migrasi berat.

---

## FAQ Admin

**Q: Ada staff request akses ke fitur yang tidak ada di role-nya.**
A: Jangan langsung berikan. Diskusi dulu dengan Manager unit — mungkin butuh review permission struktur.

**Q: Sistem lambat di jam sibuk (10-12 pagi).**
A: Cek `/up/health` — kalau `latency_ms` DB > 100, kemungkinan slow query. Enable slow_query_log di MySQL, cari query yang lambat.

**Q: Ada user mengeluh session logout tiba-tiba.**
A: 2 kemungkinan:
1. Session lifetime = 2 jam idle (dari `.env` `SESSION_LIFETIME=120`)
2. Akun mereka dinonaktifkan → middleware EnsureUserIsActive auto-logout

**Q: BPJS bridging kapan aktif?**
A: Tier 3 roadmap. Bilang ke user "menyusul" — sementara klaim manual.

---

## Kontak

- Vendor developer: **[nama vendor + kontak]**
- Escalation: **CTO / Kepala IT**
- Emergency: **On-call [24/7]**
