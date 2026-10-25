<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();

    if ($user->isAdminGlobal()) {
        return redirect()->route('ajustes.index');
    }

    abort_unless($user->empresa_id, 403, 'El usuario no tiene una empresa asignada.');

    if (in_array($user->role, [\App\Models\User::ROLE_COMPANY_ADMIN, \App\Models\User::ROLE_EMPLOYEE], true)) {
        $empresaId = (int) $user->empresa_id;
        $productosEmpresa = Producto::forEmpresa($empresaId);

        $totalProductos = (clone $productosEmpresa)->count();

        $alertasStock = (clone $productosEmpresa)
            ->where('estado', 'activo')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->count();

        $valorInventario = (clone $productosEmpresa)->sum(DB::raw('precio_compra * stock'));

        $movimientosEmpresa = Movimiento::query()
            ->whereHas('producto', fn ($query) => $query->where('empresa_id', $empresaId));
        $movimientosHoy = (clone $movimientosEmpresa)->whereDate('created_at', today())->count();

        $entradas = (clone $movimientosEmpresa)->where('tipo', 'entrada')->sum('cantidad');
        $salidas = (clone $movimientosEmpresa)->where('tipo', 'salida')->sum('cantidad');

        $ingresos = Venta::forEmpresa($empresaId)
            ->whereDate('created_at', today())
            ->sum('total');

        $productosAlerta = (clone $productosEmpresa)
            ->where('estado', 'activo')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->take(5)
            ->get();

        $chartSemanal = collect($this->chartMetrics($empresaId));

        $movimientos = (clone $movimientosEmpresa)
            ->with(['producto', 'user'])
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
        ));
    }

    
    abort(403);
}

    public function kpisJson(): JsonResponse
    {
        $empresaId = $this->empresaId();
        $productos = Producto::forEmpresa($empresaId);
        $movimientos = Movimiento::query()
            ->whereHas('producto', fn ($query) => $query->where('empresa_id', $empresaId));

        return response()->json([
            'total_productos' => (clone $productos)->count(),
            'alertas_stock' => (clone $productos)
                ->where('estado', 'activo')
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count(),
            'valor_inventario' => (clone $productos)->sum(DB::raw('precio_compra * stock')),
            'movimientos_hoy' => (clone $movimientos)->whereDate('created_at', today())->count(),
        ]);
    }

    public function notificacionesJson(): JsonResponse
    {
        $empresaId = $this->empresaId();
        $movimientos = Movimiento::query()
            ->whereHas('producto', fn ($query) => $query->where('empresa_id', $empresaId))
            ->with('producto:id,nombre')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'alertas_count' => Producto::forEmpresa($empresaId)
                ->where('estado', 'activo')
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count(),
            'ultimo_mov_id' => $movimientos->max('id') ?? 0,
            'movimientos' => $movimientos->map(fn (Movimiento $movimiento) => [
                'id' => $movimiento->id,
                'tipo' => $movimiento->tipo,
                'producto' => $movimiento->producto?->nombre ?? 'Producto',
                'cantidad' => $movimiento->cantidad,
                'hora' => $movimiento->created_at?->format('H:i'),
            ]),
        ]);
    }

    public function chartDataJson(): JsonResponse
    {
        return response()->json($this->chartMetrics($this->empresaId()));
    }

    private function empresaId(): int
    {
        $user = auth()->user();
        abort_unless(
            $user?->activo && $user->empresa_id
                && in_array($user->role, [\App\Models\User::ROLE_COMPANY_ADMIN, \App\Models\User::ROLE_EMPLOYEE], true),
            403
        );

        return (int) $user->empresa_id;
    }

    private function chartMetrics(int $empresaId): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();
        $movimientos = Movimiento::query()
            ->whereHas('producto', fn ($query) => $query->where('empresa_id', $empresaId))
            ->with('producto:id,precio_compra')
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->groupBy(fn (Movimiento $movimiento) => $movimiento->created_at->toDateString());
        $ventas = Venta::forEmpresa($empresaId)
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'total'])
            ->groupBy(fn (Venta $venta) => $venta->created_at->toDateString());

        return collect(range(6, 0))
            ->map(function (int $daysAgo) use ($movimientos, $ventas): array {
                $date = now()->subDays($daysAgo)->toDateString();
                $dayMovements = $movimientos->get($date, collect());

                return [
                    'label' => now()->subDays($daysAgo)->format('D'),
                    'dia' => now()->subDays($daysAgo)->format('D'),
                    'entradas' => $dayMovements->where('tipo', 'entrada')->sum('cantidad'),
                    'salidas' => $dayMovements->where('tipo', 'salida')->sum('cantidad'),
                    'ingresos' => $ventas->get($date, collect())->sum('total'),
                    'egresos' => $dayMovements
                        ->where('tipo', 'entrada')
                        ->sum(fn (Movimiento $movimiento) => $movimiento->cantidad * ($movimiento->producto?->precio_compra ?? 0)),
                ];
            })
            ->all();
    }
}