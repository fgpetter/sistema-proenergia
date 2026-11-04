<?php

use App\Http\Controllers\CheckUserController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/checkuser', CheckUserController::class)->middleware('throttle:checkuser');
