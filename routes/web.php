<?php

use App\Http\Controllers\Painel\DashboardController;
use App\Http\Controllers\RoutingController;
use App\Support\PainelHome;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect('/login');
    }

    return redirect(PainelHome::routeFor(Auth::user()));
});

Route::group(['prefix' => '/painel', 'middleware' => 'auth'], function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('painel.dashboard')
        ->can('admin-or-coordenador');

    Route::view('/perfil', 'profile.edit')->name('profile.edit');

    Route::group(['prefix' => '/admin'], function () {
        Route::view('/usuarios', 'admin.usuarios')->name('admin.usuarios')->can('admin');
        Route::view('/colaboradores', 'admin.colaboradores')->name('admin.colaboradores')->can('admin-or-coordenador');
        Route::view('/projetos', 'admin.projetos')->name('admin.projetos');
        Route::view('/relatorio-colaboradores', 'admin.relatorio-colaboradores')
            ->name('admin.relatorio-colaboradores')
            ->can('view-relatorio-colaboradores');
    });

});

// Rotas catch-all (devem vir por último para não interceptar rotas do Fortify)
// O Fortify já cria automaticamente as rotas GET/POST para /login, /register, /forgot-password, etc.
Route::group(['prefix' => '/sample-pages'], function () {
    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});
