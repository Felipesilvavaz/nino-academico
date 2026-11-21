<?php

use App\Http\Controllers\AcompanhamentoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AviarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GranjaController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\ProdutorController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// UC01 – Autenticar usuário
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // UC02 – Manter usuários (somente administrador)
    Route::resource('users', UserController::class)->except('show')->middleware('can:admin');

    // UC03 – Manter granjas e aviários (administrador e técnico)
    Route::middleware('can:cadastros')->group(function () {
        Route::resource('produtores', ProdutorController::class)
            ->except('show')
            ->parameters(['produtores' => 'produtor']);
        Route::resource('granjas', GranjaController::class);
        Route::resource('granjas.aviarios', AviarioController::class)
            ->shallow()
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    // UC04 e UC05 – operações de lote (administrador e técnico)
    Route::middleware('can:operar')->group(function () {
        Route::resource('lotes', LoteController::class)->except(['index', 'show']);
        Route::patch('lotes/{lote}/encerrar', [LoteController::class, 'encerrar'])->name('lotes.encerrar');
        Route::resource('lotes.acompanhamentos', AcompanhamentoController::class)
            ->shallow()
            ->only(['store', 'edit', 'update', 'destroy']);
    });

    // Consulta de lotes liberada também para o gestor
    Route::resource('lotes', LoteController::class)->only(['index', 'show']);

    // UC06 – Consultar desempenho do lote
    Route::get('/relatorios/desempenho', [RelatorioController::class, 'desempenho'])->name('relatorios.desempenho');
});
