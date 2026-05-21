<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Disable activity log selama seeding — auth()->user() null saat CLI
        activity()->disableLogging();

        try {
            $users = [
                ['username' => 'admin',         'name' => 'Administrator Sistem',         'email' => 'admin@sihrs.local',         'role' => 'SUPER_ADMIN'],
                ['username' => 'direktur',      'name' => 'dr. H. Bambang Wijaya, MARS',  'email' => 'direktur@sihrs.local',      'role' => 'DIREKSI'],
                ['username' => 'mgr.surya',     'name' => 'Surya Wijaya, S.E., M.M.',     'email' => 'manager@sihrs.local',       'role' => 'MANAGER'],
                ['username' => 'auditor.heru',  'name' => 'Heru Pranoto, S.Ak.',          'email' => 'auditor@sihrs.local',       'role' => 'AUDITOR'],
                ['username' => 'dr.iqbal',      'name' => 'dr. Iqbal Nasution',           'email' => 'iqbal@sihrs.local',         'role' => 'DOKTER'],
                ['username' => 'dr.andika',     'name' => 'dr. Andika Pratama, Sp.PD',    'email' => 'andika@sihrs.local',        'role' => 'DOKTER_SPESIALIS'],
                ['username' => 'dr.sari',       'name' => 'dr. Sari Wulandari, Sp.A',     'email' => 'sari@sihrs.local',          'role' => 'DOKTER_SPESIALIS'],
                ['username' => 'dr.doni',       'name' => 'dr. Doni Saputra, Sp.PK',      'email' => 'doni.pk@sihrs.local',       'role' => 'DOKTER_PK'],
                ['username' => 'pwt.rina',      'name' => 'Rina Lestari, A.Md.Kep',       'email' => 'rina.perawat@sihrs.local',  'role' => 'PERAWAT'],
                ['username' => 'pwt.yusuf',     'name' => 'Yusuf Hidayat, S.Kep, Ns',     'email' => 'yusuf.ri@sihrs.local',      'role' => 'PERAWAT_RI'],
                ['username' => 'apt.fitri',     'name' => 'apt. Fitri Anggraeni, S.Farm', 'email' => 'fitri.apt@sihrs.local',     'role' => 'APOTEKER'],
                ['username' => 'tta.dewi',      'name' => 'Dewi Lestari, A.Md.Farm',      'email' => 'dewi.tta@sihrs.local',      'role' => 'TTK_APOTEK'],
                ['username' => 'lab.budi',      'name' => 'Budi Santoso, A.Md.AK',        'email' => 'budi.lab@sihrs.local',      'role' => 'ANALIS_LAB'],
                ['username' => 'reg.mira',      'name' => 'Mira Sari',                    'email' => 'mira.reg@sihrs.local',      'role' => 'REGISTRASI'],
                ['username' => 'kasir.lina',    'name' => 'Lina Hutapea',                 'email' => 'lina.kasir@sihrs.local',    'role' => 'KASIR'],
                ['username' => 'kasir.ratna',   'name' => 'Ratna Manurung, S.E.',         'email' => 'ratna.spv@sihrs.local',     'role' => 'KASIR_SUPERVISOR'],
                ['username' => 'bpjs.maya',     'name' => 'Maya Indah',                   'email' => 'maya.bpjs@sihrs.local',     'role' => 'PETUGAS_BPJS'],
                ['username' => 'rad.tono',      'name' => 'Tono Sumardi, A.Md.Rad',       'email' => 'rad.tono@sihrs.local',      'role' => 'RADIOGRAFER'],
                ['username' => 'dr.dewi',       'name' => 'dr. Dewi Anggraini, Sp.Rad',   'email' => 'dewi.rad@sihrs.local',      'role' => 'DOKTER_RADIOLOG'],
            ];

            foreach ($users as $data) {
                $role = $data['role'];
                unset($data['role']);

                $user = User::updateOrCreate(
                    ['username' => $data['username']],
                    array_merge($data, [
                        'password' => Hash::make('password'),
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ])
                );

                $user->syncRoles([$role]);
            }

            $this->command->newLine();
            $this->command->info('  ✅ ' . count($users) . ' akun ter-seed. Password semua: "password"');
        } finally {
            activity()->enableLogging();
        }
    }
}
