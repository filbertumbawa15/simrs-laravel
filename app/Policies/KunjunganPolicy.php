<?php

namespace App\Policies;

use App\Enums\TipeKunjungan;
use App\Models\Kunjungan;
use App\Models\User;

class KunjunganPolicy
{
    public function disposisi(User $user, Kunjungan $kunjungan): bool
    {
        if ($user->hasRole('SUPER_ADMIN')) {
            return true;
        }

        if ($kunjungan->tipe !== TipeKunjungan::IGD) {
            return false;
        }

        return $user->hasAnyRole(['DOKTER', 'DOKTER_SPESIALIS'])
            && $user->can('igd.disposisi');
    }

    public function periksa(User $user, Kunjungan $kunjungan): bool
    {
        if ($user->hasRole('SUPER_ADMIN')) {
            return true;
        }

        return $user->hasAnyRole(['DOKTER', 'DOKTER_SPESIALIS', 'PERAWAT', 'PERAWAT_RI'])
            && $user->can('igd.periksa');
    }

    public function triase(User $user, Kunjungan $kunjungan): bool
    {
        if ($user->hasRole('SUPER_ADMIN')) return true;

        return $kunjungan->tipe === TipeKunjungan::IGD
            && $user->hasAnyRole(['PERAWAT', 'PERAWAT_RI', 'DOKTER', 'DOKTER_SPESIALIS'])
            && $user->can('igd.triase');
    }
}
