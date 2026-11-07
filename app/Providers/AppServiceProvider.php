<?php

namespace App\Providers;

use App\Enums\TipoContrato;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('checkuser', function (Request $request) {
            $user = $request->input('user');
            $user = is_string($user) ? Str::lower(trim($user)) : '';
            $response = fn () => response('', 429);

            return [
                Limit::perMinute(5)->by('ip:'.$request->ip())->response($response),
                Limit::perMinute(5)->by('user:'.hash('sha256', $user))->response($response),
            ];
        });

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

            return $user->isPrestador() && $user->colaborador?->contrato === TipoContrato::CLT;
        });

        Gate::define('view-relatorio-produtividade-pj', function (User $user) {
            if ($user->isAdmin() || $user->isCoordenador()) {
                return true;
            }

            return $user->isPrestador() && $user->colaborador?->contrato === TipoContrato::PJ;
        });

        Gate::define('manage-parametros-produtividade-pj', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('download-planilha-contabilidade', function (User $user) {
            return $user->isAdmin();
        });

    }
}
