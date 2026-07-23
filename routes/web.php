<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\LogisticaController;
use App\Http\Controllers\MotoristaController;
use App\Http\Controllers\DiretoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AdminController;

// Public Client Tracking
Route::get('/', [ClienteController::class, 'index'])->name('client.index');
Route::post('/tracking/search', [ClienteController::class, 'search'])->name('client.search');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Estoque (Stock) Panel
    Route::middleware(['role:estoque'])->prefix('estoque')->name('estoque.')->group(function () {
        Route::get('/', [EstoqueController::class, 'index'])->name('dashboard');
        Route::get('/search', [EstoqueController::class, 'searchOrder'])->name('search');
        Route::post('/tracking/store', [EstoqueController::class, 'storeTracking'])->name('store');
    });

    // Compras (Buyer) Panel
    Route::middleware(['role:compras'])->prefix('compras')->name('compras.')->group(function () {
        Route::get('/', [ComprasController::class, 'index'])->name('dashboard');
        Route::get('/search', [ComprasController::class, 'searchOrder'])->name('search');
        Route::post('/tracking/store', [ComprasController::class, 'storeTracking'])->name('store');
    });

    // Logistica (Logistics) Panel
    Route::middleware(['role:logistica'])->prefix('logistica')->name('logistica.')->group(function () {
        Route::get('/', [LogisticaController::class, 'index'])->name('dashboard');
        Route::post('/route', [LogisticaController::class, 'routeOrder'])->name('route');
        Route::post('/ship', [LogisticaController::class, 'shipOrder'])->name('ship');
        Route::get('/tracking/{id}/qrcode', [LogisticaController::class, 'printQRCode'])->name('qrcode');
    });

    // Motorista (Driver) Panel
    Route::middleware(['role:motorista'])->prefix('motorista')->name('motorista.')->group(function () {
        Route::get('/', [MotoristaController::class, 'index'])->name('dashboard');
        Route::post('/scan', [MotoristaController::class, 'scanQRCode'])->name('scan');
    });

    // Diretoria (Management) Panel
    Route::middleware(['role:diretoria'])->prefix('diretoria')->name('diretoria.')->group(function () {
        Route::get('/', [DiretoriaController::class, 'index'])->name('dashboard');
    });

    // Admin Panel
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });

});
