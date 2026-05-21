<?php

namespace App\Policies;

use App\Models\Pasien;
use App\Models\User;

class PasienPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pasien.view');
    }

    public function view(User $user, Pasien $pasien): bool
    {
        return $user->can('pasien.view');
    }

    public function create(User $user): bool
    {
        return $user->can('pasien.create');
    }

    public function update(User $user, Pasien $pasien): bool
    {
        return $user->can('pasien.update');
    }

    public function delete(User $user, Pasien $pasien): bool
    {
        return $user->can('pasien.delete');
    }
}
