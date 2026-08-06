<?php

namespace App\Policies;

use App\Models\Atividade;
use App\Models\User;

class AtividadePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Atividade $atividade): bool
    {
        if ($user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return true;
        }

        return $user->can('view', $atividade->projeto);
    }

    public function create(User $user): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->isCoordenador();
    }

    public function update(User $user, Atividade $atividade): bool
    {
        if ($user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return true;
        }

        return $atividade->colaborador_id === $user->colaborador?->id;
    }

    public function delete(User $user, Atividade $atividade): bool
    {
        if ($user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return true;
        }

        return $atividade->colaborador_id === $user->colaborador?->id;
    }

    public function restore(User $user, Atividade $atividade): bool
    {
        return false;
    }

    public function forceDelete(User $user, Atividade $atividade): bool
    {
        return false;
    }
}
