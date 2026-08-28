<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Se for super admin, permite acesso a todas as rotas
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('coordenador', function (User $user) {
            return $user->isCoordenador();
        });

        Gate::define('admin-or-coordenador', function (User $user) {
            return $user->isAdmin() || $user->isCoordenador();
        });

        Gate::define('prestador', function (User $user) {
            return $user->isPrestador();
        });

        Gate::define('view-relatorio-colaboradores', function (User $user) {
            if ($user->isAdmin() || $user->isCoordenador()) {
                return true;
            }

            return $user->isPrestador() && $user->colaborador !== null;
        });

        Gate::define('download-planilha-contabilidade', function (User $user) {
            return $user->isAdmin();
        });

    }
}
