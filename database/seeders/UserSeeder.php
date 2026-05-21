<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['username' => 'admin', 'name' => 'Administrator Sistem', 'email' => 'admin@sihrs.local', 'role' => 'SUPER_ADMIN'],
            ['username' => 'dr.andika', 'name' => 'dr. Andika Pratama, Sp.PD', 'email' => 'andika@sihrs.local', 'role' => 'DOKTER'],
            ['username' => 'dr.sari', 'name' => 'dr. Sari Wulandari, Sp.A', 'email' => 'sari@sihrs.local', 'role' => 'DOKTER'],
            ['username' => 'dr.iqbal', 'name' => 'dr. Iqbal Nasution', 'email' => 'iqbal@sihrs.local', 'role' => 'DOKTER'],
            ['username' => 'apt.fitri', 'name' => 'apt. Fitri Anggraeni, S.Farm', 'email' => 'fitri@sihrs.local', 'role' => 'APOTEKER'],
            ['username' => 'tta.dewi', 'name' => 'Dewi Lestari, A.Md.Farm', 'email' => 'dewi@sihrs.local', 'role' => 'TTK_APOTEK'],
            ['username' => 'lab.budi', 'name' => 'Budi Santoso, A.Md.AK', 'email' => 'budi.lab@sihrs.local', 'role' => 'ANALIS_LAB'],
            ['username' => 'reg.mira', 'name' => 'Mira Sari', 'email' => 'mira@sihrs.local', 'role' => 'REGISTRASI'],
            ['username' => 'kasir.lina', 'name' => 'Lina Hutapea', 'email' => 'lina@sihrs.local', 'role' => 'KASIR'],
            ['username' => 'mgr.surya', 'name' => 'Surya Wijaya', 'email' => 'surya@sihrs.local', 'role' => 'MANAGER'],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['username' => $data['username']],
                array_merge($data, [
                    'password' => 'password',
                    'is_active' => true,
                ])
            );

            $user->syncRoles([$role]);
        }
    }
}
