<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AjustesController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\LogsController;



//RUTAS DE DASHBOARD
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'web'
])->controller(DashboardController::class)->group(function () {
    Route::get('dashboard', 'dashboard')->name('dashboard');
    Route::get(
        '/',
        'dashboard'
    );
    Route::get('ajustes/cuenta', [AjustesController::class, 'cuenta'])->name('ajustes.cuenta');
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('config', [ConfigController::class, 'index'])->name('config');
    Route::get('config/token', [ConfigController::class, 'token'])->name('config.token');
    Route::get('devices', [DevicesController::class, 'index'])->name('devices');
    Route::get('logs', [LogsController::class, 'index'])->name('logs');
    Route::get('reenvio-logs', [LogsController::class, 'reenvioLogs'])->name('reenvio-logs');
});
