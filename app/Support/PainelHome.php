<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PainelHome
{
    public static function routeFor(?User $user): string
    {
        if ($user !== null && Gate::forUser($user)->allows('admin-or-coordenador')) {
            return route('painel.dashboard');
        }

        return route('admin.projetos');
    }
}
