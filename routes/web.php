<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SmartOrderController;
use App\Http\Controllers\SmartOrderAssistantController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\AjustesController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CatalogoClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\MovimientoController;


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
Route::post('/pedido-inteligente/carrito', [SmartOrderController::class, 'addToCart'])
    ->name('smart.order.cart.add');
Route::get('/pedido-inteligente/carrito', [SmartOrderController::class, 'cart'])
    ->name('smart.order.cart');
Route::put('/pedido-inteligente/carrito/{producto}', [SmartOrderController::class, 'updateCartItem'])
    ->name('smart.order.cart.update');
Route::delete('/pedido-inteligente/carrito/{producto}', [SmartOrderController::class, 'removeFromCart'])
    ->name('smart.order.cart.remove');
Route::delete('/pedido-inteligente/carrito', [SmartOrderController::class, 'clearCart'])
    ->name('smart.order.cart.clear');
Route::get('/pedido-inteligente/revisar', [SmartOrderController::class, 'review'])
    ->name('smart.order.review');
Route::post('/pedido-inteligente/confirmar', [SmartOrderController::class, 'confirm'])
    ->name('smart.order.confirm');
Route::get('/pedido-inteligente/confirmado', [SmartOrderController::class, 'confirmed'])
    ->name('smart.order.confirmed');
Route::get('/pedido-inteligente/asistente', [SmartOrderAssistantController::class, 'index'])
    ->name('smart.order.assistant');
Route::post('/pedido-inteligente/asistente/interpretar', [SmartOrderAssistantController::class, 'interpret'])
    ->middleware('throttle:5,1')
    ->name('smart.order.assistant.interpret');
Route::post('/pedido-inteligente/asistente/aceptar', [SmartOrderAssistantController::class, 'accept'])
    ->name('smart.order.assistant.accept');

// Ruta del Chatbot
Route::post('/chatbot/mensaje', [ChatbotController::class, 'message'])->name('chatbot.message');

Route::get('/catalogo', [CatalogoClienteController::class, 'index'])
    ->name('catalogo.clientes.empresas');
Route::get('/catalogo/{empresa}', [CatalogoClienteController::class, 'show'])
    ->name('catalogo.clientes.productos');
Route::get('/mis-pedidos', [CatalogoClienteController::class, 'myOrders'])
    ->middleware(['auth', 'role:cliente'])
    ->name('catalogo.clientes.pedidos.index');
Route::post('/catalogo/{empresa}/pedidos', [CatalogoClienteController::class, 'store'])
    ->middleware(['auth', 'role:cliente'])
    ->name('catalogo.clientes.pedidos.store');


    // DASHBOARD PRINCIPAL + APIs

    Route::middleware(['auth', 'role:admin_global,admin_empresa,empleado'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
    });

    Route::middleware(['auth', 'role:admin_empresa,empleado'])->group(function () {
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
});

Route::middleware(['auth', 'role:admin_empresa,empleado'])->group(function () {
    Route::resource('pedidos', PedidoController::class);
    Route::post('/pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])
        ->name('pedidos.cambiarEstado');

    Route::get('/recibos', fn() => 'Recibos')
        ->name('recibos.index');

    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::get('/caja', [VentaController::class, 'create'])->name('caja.index');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');

});

Route::middleware(['auth', 'role:admin_empresa'])->group(function () {
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
});

Route::middleware(['auth', 'role:admin_global,admin_empresa'])->prefix('ajustes')->group(function () {
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

});

Route::middleware(['auth', 'role:admin_global'])->prefix('ajustes')->group(function () {
    // Empresas
     Route::post('/empresas', [AjustesController::class, 'storeEmpresa'])
            ->name('ajustes.empresa.store');

        Route::put('/empresas/{empresa}', [AjustesController::class, 'updateEmpresa'])
            ->name('ajustes.empresa.update');

        Route::delete('/empresas/{empresa}', [AjustesController::class, 'destroyEmpresa'])
            ->name('ajustes.empresa.destroy');

});

Route::middleware(['auth', 'role:admin_empresa,empleado'])->group(function () {
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/productos/ajustar-stock', [InventarioController::class, 'ajustarStock'])
        ->name('productos.ajustarStock');
});

Route::middleware(['auth', 'role:admin_empresa'])->group(function () {
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
    Route::patch('/productos/{producto}', [ProductoController::class, 'update']);
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
});

// AUTH
require __DIR__.'/auth.php';
