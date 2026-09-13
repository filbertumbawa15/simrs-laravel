# SIHRS — Pre-Launch Checklist

Checklist go-live untuk deployment SIHRS ke production (RS pilot).
Kerjakan **bertahap** — jangan skip. Setiap section punya dependency ke section sebelumnya.

**Cara pakai:** Copy file ini ke Notion / Google Docs untuk tim, lalu tandai checkbox saat item selesai. Estimasi total: **3–5 hari kerja** (belum termasuk training operator).

---

## 1. Persiapan Server (VM)

- [ ] VM Linux (Ubuntu 22.04 LTS / Debian 12 direkomendasikan) siap dengan spek min:
  - 4 vCPU, 8 GB RAM, 100 GB SSD
  - Untuk 100–150 bed RS tipe C, ini cukup. Skala lebih besar: 8 vCPU, 16 GB RAM
- [ ] PHP **8.2+** terinstall dengan ekstensi: `pdo_mysql`, `mbstring`, `xml`, `bcmath`, `gd`, `zip`, `intl`, `openssl`, `curl`, `redis` (opsional)
- [ ] Composer 2.5+
- [ ] Node.js 18+ dan npm
- [ ] MySQL 8+ / MariaDB 10.6+ terinstall & running
- [ ] Nginx (recommended) atau Apache
- [ ] Redis 6+ (opsional, tapi highly recommended untuk RS aktif)
- [ ] Supervisor (untuk queue worker)
- [ ] Certbot (Let's Encrypt) atau SSL certificate dari provider
- [ ] Firewall (ufw): buka port 80, 443, 22. Tutup 3306 dari luar
- [ ] Timezone server = `Asia/Jakarta` (`timedatectl set-timezone Asia/Jakarta`)
- [ ] User non-root untuk deployment (mis. `sihrs`) — jangan pakai root

---

## 2. Database

- [ ] Buat database production:
  ```sql
  CREATE DATABASE sihrs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'sihrs_app'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT_MIN_20_KARAKTER';
  GRANT ALL PRIVILEGES ON sihrs.* TO 'sihrs_app'@'localhost';
  FLUSH PRIVILEGES;
  ```
- [ ] **JANGAN pakai user root** di `.env`. Buat user khusus (`sihrs_app`) dengan akses hanya ke DB `sihrs`
- [ ] Password DB minimum 20 karakter (mix huruf, angka, simbol)
- [ ] Set `innodb_buffer_pool_size` = 40% dari RAM di `my.cnf` (default 128M kekecilan)
- [ ] Enable slow query log untuk monitoring performa: `slow_query_log=ON`, `long_query_time=1`
- [ ] Test koneksi dari user aplikasi: `mysql -u sihrs_app -p sihrs -e "SELECT 1"`

---

## 3. Deploy Kode Aplikasi

- [ ] Clone repo ke `/var/www/sihrs` (atau path standar Anda):
  ```bash
  sudo git clone <repo-url> /var/www/sihrs
  sudo chown -R sihrs:www-data /var/www/sihrs
  cd /var/www/sihrs
  ```
- [ ] Install dependencies production:
  ```bash
  composer install --no-dev --optimize-autoloader
  npm ci && npm run build
  ```
- [ ] Set permission folder writable:
  ```bash
  sudo chmod -R 775 storage bootstrap/cache
  sudo chown -R www-data:www-data storage bootstrap/cache
  ```
- [ ] Storage symlink: `php artisan storage:link`

---

## 4. Konfigurasi .env

- [ ] Copy template: `cp .env.production.example .env`
- [ ] Generate APP_KEY: `php artisan key:generate --force`
- [ ] Isi **semua** yang bertanda `GANTI:` di file — TIDAK ADA yang boleh kosong kecuali sesi opsional (BPJS, SATUSEHAT, S3)
- [ ] Verify security-critical env sudah benar:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL=https://...` (HTTPS)
  - [ ] `SESSION_ENCRYPT=true`
  - [ ] `SESSION_SECURE_COOKIE=true`
  - [ ] `SESSION_SAME_SITE=lax`
  - [ ] `BCRYPT_ROUNDS=12`
  - [ ] `SENTRY_SEND_DEFAULT_PII=false`
  - [ ] `ACTIVITY_LOGGER_ENABLED=true`
- [ ] Test email keluar (kirim ke diri sendiri):
  ```bash
  php artisan tinker
  >>> Mail::raw('Test', fn($m) => $m->to('admin@sihrs.namars.id')->subject('Test'));
  ```
- [ ] Isi identitas RS di section `SIHRS_RS_*` — muncul di semua PDF & email

---

## 5. Optimasi & Cache Production

- [ ] Cache config, route, view, event:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  ```
- [ ] Migration + seed master data (tanpa pasien seeder — PasienSeeder auto-skip di production):
  ```bash
  php artisan migrate --force
  php artisan db:seed --force
  ```
- [ ] Verify seeder jalan: cek jumlah role (`19`), poli (`8`), dokter (`10`), kamar (`88`) via tinker atau phpMyAdmin
- [ ] **UBAH SEMUA PASSWORD DEFAULT** — 19 user seeder pakai password `password`:
  ```bash
  php artisan tinker
  >>> User::where('username', 'admin')->first()->update(['password' => Hash::make('PASSWORD_BARU_KUAT')]);
  # Ulangi untuk setiap user, ATAU nonaktifkan yang tidak dipakai:
  >>> User::whereIn('username', ['dr.iqbal', 'dr.andika', ...])->update(['is_active' => false]);
  ```
- [ ] Buat akun admin baru dengan nama Anda + password kuat, hapus/nonaktifkan `admin` default

---

## 6. Web Server + HTTPS

- [ ] Nginx config `/etc/nginx/sites-available/sihrs`:
  ```nginx
  server {
      listen 80;
      server_name sihrs.namars.id;
      return 301 https://$server_name$request_uri;
  }
  server {
      listen 443 ssl http2;
      server_name sihrs.namars.id;

      root /var/www/sihrs/public;
      index index.php;

      ssl_certificate     /etc/letsencrypt/live/sihrs.namars.id/fullchain.pem;
      ssl_certificate_key /etc/letsencrypt/live/sihrs.namars.id/privkey.pem;
      ssl_protocols TLSv1.2 TLSv1.3;

      client_max_body_size 25M;  # untuk upload DICOM radiologi

      location / {
          try_files $uri $uri/ /index.php?$query_string;
      }

      location ~ \.php$ {
          fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
          fastcgi_index index.php;
          fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
          include fastcgi_params;
      }

      location ~ /\.(?!well-known).* { deny all; }
  }
  ```
- [ ] Enable & reload: `sudo ln -s /etc/nginx/sites-available/sihrs /etc/nginx/sites-enabled/ && sudo nginx -t && sudo systemctl reload nginx`
- [ ] Setup SSL via Let's Encrypt: `sudo certbot --nginx -d sihrs.namars.id`
- [ ] Certbot auto-renew: `sudo certbot renew --dry-run` — pastikan hijau
- [ ] Test HTTPS: buka `https://sihrs.namars.id`, browser tidak nampilkan warning gembok
- [ ] Test HTTP redirect: `curl -I http://sihrs.namars.id` — harus 301 ke HTTPS

---

## 7. Queue Worker + Scheduler

- [ ] Supervisor config `/etc/supervisor/conf.d/sihrs-worker.conf`:
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
  stopwaitsecs=3600
  ```
- [ ] Reload supervisor: `sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start sihrs-worker:*`
- [ ] Verify worker running: `sudo supervisorctl status`
- [ ] Cron scheduler `sudo crontab -u www-data -e`:
  ```
  * * * * * cd /var/www/sihrs && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Test scheduler: `sudo -u www-data php artisan schedule:list` — harus muncul `sihrs:backup` daily 02:00

---

## 8. Backup

- [ ] Manual backup pertama: `php artisan sihrs:backup` — verify file `.sql.gz` muncul di `BACKUP_PATH`
- [ ] Cek scheduler aktif: `sudo -u www-data php artisan schedule:run` (dry test)
- [ ] **Offsite backup mandatory** — pilih 1:
  - **rclone** ke S3/Wasabi/Google Drive:
    ```bash
    sudo apt install rclone
    rclone config  # setup remote
    # tambah ke cron user root:
    0 3 * * * rclone copy /var/backups/sihrs remote:sihrs-backup/$(date +\%Y-\%m) --min-age 1h
    ```
  - **Rsync ke server lain** (kalau ada infrastruktur internal)
  - **Manual weekly** ke external HDD (last resort, cocok untuk RS kecil)
- [ ] **UJI RESTORE**: minimum 1x sebelum go-live, lakukan restore backup ke DB staging dan pastikan data utuh
- [ ] Dokumentasi restore procedure di `docs/RESTORE_PROCEDURE.md` untuk on-call staff

---

## 9. Monitoring & Alerting

- [ ] Setup uptime monitor (pilih 1):
  - **UptimeRobot** (free) — ping `https://sihrs.namars.id/up` tiap 5 menit
  - **Grafana + Prometheus** — self-host untuk metric lebih detail
  - **BetterStack / Pingdom** — SaaS berbayar
- [ ] Konfigurasi alert monitor ke WA/Slack/email tim IT saat down > 2 menit
- [ ] Endpoint `/up/health` monitoring (JSON response, cek DB + cache + disk + backup age):
  ```bash
  curl https://sihrs.namars.id/up/health
  ```
  Set threshold alert: status `!= "ok"` → notify
- [ ] Sentry project dibuat, DSN diisi di `.env`, verify test event:
  ```bash
  php artisan sentry:test
  ```
- [ ] Log rotation aktif — cek `/etc/logrotate.d/nginx` ada, dan Laravel `storage/logs/` di-rotate daily (dibawah oleh `LOG_CHANNEL=daily`)
- [ ] Setup notif WA/email untuk **failed queue jobs** (opsional tapi recommended)

---

## 10. Security Verification

- [ ] Jalankan `php artisan test` di server — harus **all green** (95 test):
  ```bash
  # kalau .env production DB tidak boleh dites, buat .env.testing yang pakai SQLite
  ```
- [ ] Test manual security header:
  ```bash
  curl -I https://sihrs.namars.id/login
  # harus muncul: X-Frame-Options, X-Content-Type-Options, Strict-Transport-Security, CSP
  ```
- [ ] Test login rate limit: 6x login gagal berturut → dapat 429
- [ ] Verify HTTPS-only cookies: browser DevTools → Application → Cookies → semua flag `Secure` = true
- [ ] Cek tidak ada file `.env` public: `curl -I https://sihrs.namars.id/.env` harus 404 atau 403
- [ ] Verify `/storage/` folder tidak accessible: `curl -I https://sihrs.namars.id/storage/logs/laravel.log` harus 403/404
- [ ] Scan external: [Mozilla Observatory](https://observatory.mozilla.org/) — target skor **B+ atau A**
- [ ] Scan SSL: [SSL Labs](https://www.ssllabs.com/ssltest/) — target **A/A+**

---

## 11. Data Migration (jika BUKAN greenfield)

*Skip section ini kalau RS Anda baru memakai SIHRS dari 0.*

- [ ] Buat script migrasi khusus dari sistem lama → SIHRS schema:
  - Prioritas 1: **Pasien** (no_rm, NIK, nama, tgl_lahir, alamat)
  - Prioritas 2: **Riwayat kunjungan aktif** (30 hari terakhir)
  - Prioritas 3: **Stok obat + batch aktif** (untuk FEFO)
  - Prioritas 4: History rawat inap lama (kalau perlu)
- [ ] Test dry-run di DB staging — bandingkan jumlah record source vs target
- [ ] Reserve range no_rm untuk data lama (mis. 10000–99999 = data lama, 100000+ = data baru dari SIHRS)
- [ ] Verify sample data manual: pilih 10 pasien random, cek accurate
- [ ] Jalankan migrasi final saat **maintenance window** (mis. jam 22:00–02:00)

---

## 12. Team & Operasional Readiness

- [ ] User untuk **setiap staff** dibuat (tidak boleh sharing akun — audit trail wajib):
  - Dokter (1 akun per dokter)
  - Perawat (1 akun per perawat)
  - Apoteker + TTK
  - Analis lab
  - Radiografer + radiolog
  - Kasir + supervisor
  - Petugas BPJS
  - Manager / auditor / direksi
- [ ] Password kuat di-generate untuk setiap staff, disampaikan via kanal aman (WA/email, jangan chat grup)
- [ ] Training operator **per role** — durasi total ~4 jam (broken down: 30 menit per role)
- [ ] SOP tercetak per role, tempel di stasiun kerja:
  - Registrasi
  - Poli RJ
  - IGD
  - Rawat inap
  - Apotek
  - Lab
  - Radiologi
  - Kasir
- [ ] Kontak on-call IT + eskalasi (nama, WA, jam siaga)
- [ ] Bikin channel WA/Slack khusus untuk report bug/pertanyaan dari operator

---

## 13. Legal & Compliance Sign-off

- [ ] **SATUSEHAT** — Kalau belum bridging, plan roadmap kapan. Mandatory sejak 2023 tapi tidak instant-blocker untuk operasional
- [ ] **BPJS V-Claim** — Kalau RS terima pasien BPJS, wajib bridging. Test staging dulu sebelum production
- [ ] **PDU (UU 27/2022)**:
  - [ ] Audit log aktif (verify di `/audit-log`)
  - [ ] Kebijakan retensi 5 tahun terkonfirmasi (`ACTIVITY_LOGGER_RETENTION_DAYS=1825`)
  - [ ] Consent form pasien (offline) — untuk pengolahan data
  - [ ] Data breach response plan (siapa notif ke siapa dalam 72 jam sesuai UU)
- [ ] **Permenkes 24/2022 (RME)**:
  - [ ] Sistem sudah support tanda tangan elektronik (SIHRS pakai QR verify → cukup)
  - [ ] Backup + DR plan terdokumentasi
- [ ] **Ijin operasional Dinkes** — isi di `SIHRS_RS_IZIN` di `.env`

---

## 14. Soft Launch & Cutover Plan

- [ ] **Pilot 1 poli / 1 shift** dulu — jangan langsung semua unit
- [ ] Rekomendasi urutan rollout (per minggu):
  - Minggu 1: Pendaftaran + Poli Umum (1 shift)
  - Minggu 2: + Farmasi + Billing UMUM
  - Minggu 3: + Lab
  - Minggu 4: + Poli spesialis lain
  - Minggu 5: + IGD (setelah confident dengan flow)
  - Minggu 6: + Rawat Inap
  - Minggu 7: + Radiologi
  - Minggu 8: + BPJS klaim (kalau bridging sudah lulus)
- [ ] **Parallel run** dengan sistem lama minimum 2 minggu (double entry — capek tapi safety net)
- [ ] Rollback plan: kalau ada critical bug, prosedur balik ke manual/sistem lama
- [ ] Sosialisasi ke **pasien** (misal poster "Kami menggunakan sistem baru, mohon maaf antrian mungkin lebih lama minggu pertama")

---

## 15. Post-Launch: 24 Jam Pertama

- [ ] IT on-site standby (WAJIB — respon < 5 menit)
- [ ] Monitor `/up/health` tiap jam manual
- [ ] Cek Sentry tiap 2 jam untuk unhandled exception
- [ ] Cek `storage/logs/laravel.log` untuk error yang tidak sampai ke Sentry
- [ ] Cek audit log — pastikan aktivitas ter-record (login, buat pasien, dll)
- [ ] Backup pertama setelah go-live diverifikasi manual (bukan hanya lihat file ada, tapi test restore ke DB staging)
- [ ] End-of-day: rekap dari kasir dibandingkan dengan `/billing` — pastikan match

---

## 16. Post-Launch: Minggu Pertama

- [ ] Review daily dengan tim operator (15 menit standup):
  - Apa yang lancar?
  - Apa yang bikin bingung?
  - Bug ditemukan?
- [ ] Tuning performa berdasarkan slow query log
- [ ] Adjust rate limit kalau ternyata operator legitimate ke-throttle
- [ ] Update SOP kalau ada workflow yang tidak sesuai kenyataan
- [ ] Backup weekly ke offsite berjalan (verify sekali seminggu)

---

## 🎯 Definition of Done (Go-Live Ready)

Anda boleh flip switch ke production ketika **semua section 1–13 selesai**, DAN:

- [ ] `curl https://sihrs.namars.id/up/health` return `"status": "ok"` dan semua sub-check `"status": "ok"`
- [ ] `php artisan test` all green
- [ ] Login berhasil dengan akun admin baru (bukan default)
- [ ] Bikin 1 pasien test, 1 kunjungan test, 1 resep test, 1 pembayaran test, 1 PDF kuitansi berhasil di-scan QR-nya → verifikasi jalan
- [ ] Sentry menerima minimal 1 test event
- [ ] Backup file `.sql.gz` sukses dibuat + berhasil di-restore ke DB staging
- [ ] Uptime monitor aktif & alert ter-test (matikan 1 menit → dapat notif)

**Kalau semua ini ✅, Anda siap go-live.** 🚀

---

## 📞 Kontak Darurat (isi sebelum go-live)

| Peran | Nama | WA | Jam siaga |
|---|---|---|---|
| IT On-call | | | |
| Vendor/developer SIHRS | | | |
| DBA (kalau ada) | | | |
| Direktur RS | | | |
| PIC BPJS | | | |
