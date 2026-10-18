<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Movimiento;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();

    if ($user->role === 'admin') {

        // KPIs REALES
        $totalProductos = Producto::count();

        $alertasStock = Producto::whereColumn('stock', '<=', 'stock_minimo')->count();

        $valorInventario = Producto::sum(
            \DB::raw('precio_compra * stock')
        );

        $movimientosHoy = 0; // luego lo conectamos

        $entradas = Producto::sum('stock'); // todo lo que ha entrado
        $salidas = 0; // por ahora no tienes ventas conectadas

        $ingresos = Producto::sum(\DB::raw('precio_venta * stock'));
        $egresos = Producto::sum(\DB::raw('precio_compra * stock'));
        

        // ACTIVIDAD RECIENTE (por ahora productos creados)
        $movimientos = Producto::latest()
            ->take(5)
            ->get();

        // PRODUCTOS EN ALERTA
        $productosAlerta = Producto::whereColumn('stock', '<=', 'stock_minimo')
            ->take(5)
            ->get();

        // GRÁFICA (simulada pero dinámica básica)
        $chartSemanal = collect([
            ['dia' => 'Lun', 'entradas' => rand(1,5), 'salidas' => rand(1,5)],
            ['dia' => 'Mar', 'entradas' => rand(1,5), 'salidas' => rand(1,5)],
            ['dia' => 'Mié', 'entradas' => rand(1,5), 'salidas' => rand(1,5)],
            ['dia' => 'Jue', 'entradas' => rand(1,5), 'salidas' => rand(1,5)],
            ['dia' => 'Vie', 'entradas' => rand(1,5), 'salidas' => rand(1,5)],
        ]);

        $movimientos = Movimiento::with(['producto','user'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.dashboard_principal', compact(
            'movimientos',
            'totalProductos',
            'alertasStock',
            'valorInventario',
            'movimientosHoy',
            'movimientos',
            'productosAlerta',
            'chartSemanal',
            'entradas', 
            'salidas',
            'ingresos',
            'egresos'
        ));
    }

    
    if ($user->role === 'almacenista') {
        return view('dashboard.almacen');
    }

    abort(403);
}
}