<?php

namespace App\Policies;

use App\Models\Projeto;
use App\Models\User;

class ProjetoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Projeto $projeto): bool
    {
        if ($user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return true;
        }

        $colaborador = $user->colaborador;
        if (! $colaborador) {
            return false;
        }

        return $projeto->atividades()->where('colaborador_id', $colaborador->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->isCoordenador();
    }

    public function update(User $user, Projeto $projeto): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->isCoordenador();
    }

    public function delete(User $user, Projeto $projeto): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->isCoordenador();
    }

    public function restore(User $user, Projeto $projeto): bool
    {
        return false;
    }

    public function forceDelete(User $user, Projeto $projeto): bool
    {
        return false;
    }
}
