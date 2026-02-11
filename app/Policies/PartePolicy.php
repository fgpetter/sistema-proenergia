<?php

namespace App\Policies;

use App\Models\Parte;
use App\Models\User;

class PartePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Parte $parte): bool
    {
        if ($user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return true;
        }

        return $user->can('view', $parte->projeto);
    }

    public function create(User $user): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->isCoordenador();
    }

    public function update(User $user, Parte $parte): bool
    {
        if ($user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return true;
        }

        return $parte->colaborador_id === $user->colaborador?->id;
    }

    public function delete(User $user, Parte $parte): bool
    {
        if ($user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return true;
        }

        return $parte->colaborador_id === $user->colaborador?->id;
    }

    public function restore(User $user, Parte $parte): bool
    {
        return false;
    }

    public function forceDelete(User $user, Parte $parte): bool
    {
        return false;
    }
}
