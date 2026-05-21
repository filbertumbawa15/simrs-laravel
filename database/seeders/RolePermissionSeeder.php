<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Pasien
            'pasien.view',
            'pasien.create',
            'pasien.update',
            'pasien.delete',

            // Kunjungan
            'kunjungan.view',
            'kunjungan.create',
            'kunjungan.cancel',

            // Rawat Jalan
            'rj.view',
            'rj.examine',
            'rj.cancel',

            // Rawat Inap
            'ri.view',
            'ri.admit',
            'ri.discharge',
            'ri.transfer_kamar',

            // Lab
            'lab.order',
            'lab.sampling',
            'lab.input_hasil',
            'lab.validate',
            'lab.view',

            // Farmasi
            'farmasi.view',
            'farmasi.create_resep',
            'farmasi.verify',
            'farmasi.dispense',
            'farmasi.stock_in',

            // Billing
            'billing.view',
            'billing.generate',
            'billing.finalize',
            'billing.payment',
            'billing.void',

            // BPJS
            'bpjs.claim',
            'bpjs.verify',

            // Master Data
            'master.manage_dokter',
            'master.manage_obat',
            'master.manage_tarif',
            'master.manage_kamar',

            // Sistem
            'user.manage',
            'audit.view',
            'report.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles = [
            'SUPER_ADMIN' => $permissions,
            'DIREKSI' => ['audit.view', 'report.view', 'pasien.view', 'kunjungan.view', 'billing.view'],
            'DOKTER' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'rj.examine',
                'ri.view',
                'ri.admit',
                'ri.discharge',
                'lab.order',
                'farmasi.create_resep',
            ],
            'PERAWAT' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'ri.view',
            ],
            'APOTEKER' => [
                'pasien.view',
                'kunjungan.view',
                'farmasi.view',
                'farmasi.verify',
                'farmasi.dispense',
                'farmasi.stock_in',
            ],
            'TTK_APOTEK' => ['pasien.view', 'farmasi.view', 'farmasi.dispense'],
            'ANALIS_LAB' => [
                'pasien.view',
                'kunjungan.view',
                'lab.view',
                'lab.sampling',
                'lab.input_hasil',
            ],
            'DOKTER_LAB' => ['lab.view', 'lab.validate'],
            'REGISTRASI' => [
                'pasien.view',
                'pasien.create',
                'pasien.update',
                'kunjungan.view',
                'kunjungan.create',
                'kunjungan.cancel',
            ],
            'KASIR' => [
                'pasien.view',
                'kunjungan.view',
                'billing.view',
                'billing.generate',
                'billing.finalize',
                'billing.payment',
            ],
            'PETUGAS_BPJS' => ['billing.view', 'bpjs.claim', 'bpjs.verify'],
            'MANAGER' => ['report.view', 'audit.view', 'pasien.view', 'kunjungan.view', 'billing.view'],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePerms);
        }
    }
}
