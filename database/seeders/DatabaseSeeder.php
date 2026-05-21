<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan penting karena foreign key dependency:
     *
     * 1. RolePermissionSeeder    → bikin roles & permissions dulu
     * 2. MasterDataSeeder        → poli, dokter, kamar, obat, ICD-10, dst (tidak depend ke user)
     * 3. UserSeeder              → user butuh roles dari step 1
     * 4. PasienSeeder            → butuh asuransi dari step 2
     *
     * Production: jangan jalankan PasienSeeder di environment beneran —
     * itu data dummy. Cukup 1-3 saja untuk fresh installation.
     */
    public function run(): void
    {
        $this->command->info('=== SIHRS Database Seeder ===');
        $this->command->newLine();

        $this->command->info('[1/4] Roles & permissions...');
        $this->call(RolePermissionSeeder::class);

        $this->command->info('[2/4] Master data (poli, dokter, kamar, obat, ICD-10, lab)...');
        $this->call(MasterDataSeeder::class);

        $this->command->info('[3/4] User aplikasi...');
        $this->call(UserSeeder::class);

        // Hanya seed pasien dummy di non-production
        if (! app()->isProduction()) {
            $this->command->info('[4/4] Pasien dummy (development only)...');
            $this->call(PasienSeeder::class);
        } else {
            $this->command->warn('[4/4] Skip PasienSeeder — production environment terdeteksi.');
        }

        $this->command->newLine();
        $this->command->info('✅ Seeding selesai.');
        $this->command->newLine();
        $this->command->line('  Default login:');
        $this->command->line('    Username: admin');
        $this->command->line('    Password: password');
        $this->command->newLine();
        $this->command->warn('  ⚠  Segera ubah password default di production!');
    }
}
