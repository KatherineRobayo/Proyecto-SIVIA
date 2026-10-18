<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SmartOrderController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\AjustesController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ChatbotController;


Route::get('/', function () {
    return view('home');
});

// Pedidos inteligentes 
Route::get('/pedido-inteligente', [SmartOrderController::class, 'index'])
    ->name('smart.order');

Route::post('/pedido-inteligente/negocio', [SmartOrderController::class, 'selectBusiness'])
    ->name('smart.order.business');

Route::get('/pedido-inteligente/catalogo', [SmartOrderController::class, 'catalog'])
    ->name('smart.order.catalog');

Route::post('/chatbot/mensaje', [ChatbotController::class, 'message'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.message');


// SOLO ADMIN puede acceder al register
Route::get('register', [RegisteredUserController::class, 'create'])
    ->middleware(['auth', 'role:admin'])
    ->name('register');

    // DASHBOARD PRINCIPAL + APIs

    Route::middleware(['auth', 'role:admin,almacenista'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/api/kpis', [DashboardController::class, 'kpisJson'])
        ->name('api.kpis');

    Route::get('/api/notificaciones', [DashboardController::class, 'notificacionesJson'])
        ->name('api.notificaciones');

    Route::get('/api/chart-data', [DashboardController::class, 'chartDataJson'])
        ->name('api.chart-data');
});

    // Rutas privadas
    
Route::middleware('auth')->group(function () {

    // Perfil 

     Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Modulos - Pedidos
    Route::resource('pedidos', PedidoController::class);
    Route::post('/pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])
        ->name('pedidos.cambiarEstado');

    Route::get('/recibos', fn() => 'Recibos')
        ->name('recibos.index');

    Route::get('/caja', fn() => 'Caja')
        ->name('caja.index');

    Route::get('/reportes', fn() => 'Reportes')
        ->name('reportes.index');

    Route::get('/movimientos', fn() => 'Movimientos')
        ->name('movimientos.index');

    // Inventario

    Route::get('/inventario', [InventarioController::class, 'index'])
        ->name('inventario.index');

    Route::post('/productos/ajustar-stock', [InventarioController::class, 'ajustarStock'])
        ->name('productos.ajustarStock');
    
     // Ajustes funcional 
     
     Route::prefix('ajustes')->group(function () {

        Route::get('/', [AjustesController::class, 'index'])
            ->name('ajustes.index');

        Route::put('/password', [AjustesController::class, 'updatePassword'])
            ->name('ajustes.password');

        Route::put('/email', [AjustesController::class, 'updateEmail'])
            ->name('ajustes.email');

     // Usuarios

     Route::post('/usuarios', [AjustesController::class, 'storeUser'])
            ->name('ajustes.usuario.store');

        Route::put('/usuarios/{user}', [AjustesController::class, 'updateUser'])
            ->name('ajustes.usuario.update');

        Route::delete('/usuarios/{user}', [AjustesController::class, 'destroyUser'])
            ->name('ajustes.usuario.destroy');

    // Empresas  

     Route::post('/empresas', [AjustesController::class, 'storeEmpresa'])
            ->name('ajustes.empresa.store');

        Route::put('/empresas/{empresa}', [AjustesController::class, 'updateEmpresa'])
            ->name('ajustes.empresa.update');

        Route::delete('/empresas/{empresa}', [AjustesController::class, 'destroyEmpresa'])
            ->name('ajustes.empresa.destroy');

    }); 
});

// Productos
Route::resource('productos', ProductoController::class)
    ->except(['edit', 'create']);

// AUTH
require __DIR__.'/auth.php';
