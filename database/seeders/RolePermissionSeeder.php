<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission matrix dirancang berdasarkan tanggung jawab nyata di RS.
     * Prinsip:
     * - Need-to-know only (HIPAA-like): tiap role lihat data yang relevan saja
     * - Separation of duties: yang input ≠ yang validasi/finalize
     * - Audit: yang bisa void/cancel terbatas ke role berwenang
     */
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
            // Rawat Inap
            'ri.view',
            'ri.admit',
            'ri.discharge',
            'ri.transfer_kamar',
            // Lab
            'lab.view',
            'lab.order',
            'lab.sampling',
            'lab.input_hasil',
            'lab.validate',
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
            // Master
            'master.manage_dokter',
            'master.manage_obat',
            'master.manage_tarif',
            'master.manage_kamar',
            // Sistem
            'user.manage',
            'audit.view',
            'report.view',
            // PDF
            'pdf.print',
            // Radiologi
            'rad.view',
            'rad.order',
            'rad.execute',
            'rad.read',
            'rad.validate',
            // IGD
            'igd.view',
            'igd.triase',
            'igd.periksa',
            'igd.disposisi',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ============================================================
        // ROLE MATRIX
        // ============================================================
        $roles = [
            // ----- SUPER ADMIN -----
            // Akses penuh (developer / IT manager RS)
            'SUPER_ADMIN' => $permissions,

            // ----- DIREKSI -----
            // Read-only untuk dashboard & laporan, tidak intervensi operasional
            'DIREKSI' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'ri.view',
                'lab.view',
                'farmasi.view',
                'billing.view',
                'audit.view',
                'report.view',
                'rad.view'
            ],

            // ----- DOKTER UMUM -----
            // Periksa RJ + IGD, order lab, resep, lihat hasil
            'DOKTER' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'rj.examine',
                'ri.view',
                'lab.view',
                'lab.order',
                'farmasi.create_resep',
                'pdf.print',
                'rad.view',
                'rad.order',
                'igd.view',
                'igd.periksa',
                'igd.disposisi',
            ],

            // ----- DOKTER SPESIALIS (DPJP RI) -----
            // Sama seperti dokter + admit/discharge RI + transfer kamar
            'DOKTER_SPESIALIS' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'rj.examine',
                'ri.view',
                'ri.admit',
                'ri.discharge',
                'ri.transfer_kamar',
                'lab.view',
                'lab.order',
                'farmasi.create_resep',
                'pdf.print',
                'rad.view',
                'rad.order',
                'igd.view',
                'igd.periksa',
                'igd.disposisi',
            ],

            // ----- DOKTER PK (Patologi Klinik) -----
            // Khusus validasi hasil lab + lihat data klinis pasien
            'DOKTER_PK' => [
                'pasien.view',
                'kunjungan.view',
                'lab.view',
                'lab.validate',
                'pdf.print',
            ],

            // ----- PERAWAT RJ/IGD -----
            // Bantu dokter, lihat data, tidak boleh resep/order lab sendiri
            'PERAWAT' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'ri.view',
                'igd.view',
                'igd.triase',
                'igd.periksa',
            ],

            // ----- PERAWAT RI -----
            // Sama + bisa request transfer kamar
            'PERAWAT_RI' => [
                'pasien.view',
                'kunjungan.view',
                'igd.view',
                'ri.view',
                'ri.transfer_kamar',
            ],

            // ----- APOTEKER -----
            // Verifikasi resep + serahkan obat + stock in
            'APOTEKER' => [
                'pasien.view',
                'kunjungan.view',
                'farmasi.view',
                'farmasi.verify',
                'farmasi.dispense',
                'farmasi.stock_in',
                'pdf.print',
            ],

            // ----- TTK (Tenaga Teknis Kefarmasian) -----
            // Bantu apoteker — dispense saja, tidak verify
            'TTK_APOTEK' => [
                'pasien.view',
                'farmasi.view',
                'farmasi.dispense',
            ],

            // ----- ANALIS LAB -----
            // Sampling + input hasil; tidak boleh validate (separation of duties)
            'ANALIS_LAB' => [
                'pasien.view',
                'kunjungan.view',
                'lab.view',
                'lab.sampling',
                'lab.input_hasil',
            ],

            // ----- REGISTRASI / ADMISI -----
            // Buat pasien + kunjungan, tidak akses klinis
            'REGISTRASI' => [
                'pasien.view',
                'pasien.create',
                'pasien.update',
                'kunjungan.view',
                'kunjungan.create',
                'kunjungan.cancel',
            ],

            // ----- KASIR -----
            // Lihat tagihan + terima pembayaran. Tidak void (butuh supervisor).
            'KASIR' => [
                'pasien.view',
                'kunjungan.view',
                'billing.view',
                'billing.generate',
                'billing.finalize',
                'billing.payment',
                'pdf.print',
            ],

            // ----- KASIR SUPERVISOR -----
            // Sama + bisa void pembayaran (untuk koreksi)
            'KASIR_SUPERVISOR' => [
                'pasien.view',
                'kunjungan.view',
                'billing.view',
                'billing.generate',
                'billing.finalize',
                'billing.payment',
                'billing.void',
                'pdf.print',
                'report.view',
            ],

            // ----- PETUGAS BPJS -----
            // Khusus klaim BPJS
            'PETUGAS_BPJS' => [
                'pasien.view',
                'kunjungan.view',
                'billing.view',
                'bpjs.claim',
                'bpjs.verify',
                'pdf.print',
            ],

            // ----- MANAGER -----
            // Lihat semua + master data + report
            'MANAGER' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'ri.view',
                'lab.view',
                'farmasi.view',
                'billing.view',
                'master.manage_dokter',
                'master.manage_obat',
                'master.manage_tarif',
                'master.manage_kamar',
                'audit.view',
                'report.view',
                'rad.view'
            ],

            // ----- AUDITOR -----
            // Read-only ke semua data + audit log
            'AUDITOR' => [
                'pasien.view',
                'kunjungan.view',
                'rj.view',
                'ri.view',
                'lab.view',
                'farmasi.view',
                'billing.view',
                'audit.view',
                'report.view',
                'rad.view'
            ],

            'RADIOGRAFER' => [
                'pasien.view',
                'kunjungan.view',
                'rad.view',
                'rad.execute',
            ],
            'DOKTER_RADIOLOG' => [
                'pasien.view',
                'kunjungan.view',
                'rad.view',
                'rad.read',
                'rad.validate',
            ],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePerms);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
