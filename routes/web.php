<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutingController;

// Rota do painel (home do sistema autenticado)
Route::redirect('/', '/painel');

Route::group(['prefix' => '/painel','middleware' => 'auth'], function () {
    Route::view('/', 'painel.dashboard')->name('painel.dashboard');
});

// Rotas catch-all (devem vir por último para não interceptar rotas do Fortify)
// O Fortify já cria automaticamente as rotas GET/POST para /login, /register, /forgot-password, etc.
Route::group(['prefix' => '/sample-pages'], function () {
    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});