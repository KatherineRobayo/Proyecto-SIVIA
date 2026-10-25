<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->role === User::ROLE_COMPANY_ADMIN && $user->empresa_id, 403);
        $empresaId = (int) $user->empresa_id;
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);
        $desde = $validated['desde'] ?? now()->startOfMonth()->toDateString();
        $hasta = $validated['hasta'] ?? now()->toDateString();

        $ventas = Venta::forEmpresa($empresaId)->whereBetween('created_at', [
            \Illuminate\Support\Carbon::parse($desde)->startOfDay(),
            \Illuminate\Support\Carbon::parse($hasta)->endOfDay(),
        ]);
        $movimientos = Movimiento::query()
            ->whereHas('producto', fn ($query) => $query->where('empresa_id', $empresaId))
            ->whereBetween('movimientos.created_at', [
                \Illuminate\Support\Carbon::parse($desde)->startOfDay(),
                \Illuminate\Support\Carbon::parse($hasta)->endOfDay(),
            ]);

        return view('dashboard.reportes', [
            'desde' => $desde,
            'hasta' => $hasta,
            'ventas' => (clone $ventas)->with(['user', 'cliente'])->latest()->paginate(20)->withQueryString(),
            'totalVentas' => (clone $ventas)->count(),
            'ingresos' => (clone $ventas)->sum('total'),
            'entradas' => (clone $movimientos)->where('tipo', 'entrada')->sum('cantidad'),
            'salidas' => (clone $movimientos)->where('tipo', 'salida')->sum('cantidad'),
            'valorInventario' => Producto::forEmpresa($empresaId)
                ->where('estado', 'activo')
                ->selectRaw('SUM(precio_compra * stock) as total')
                ->value('total') ?? 0,
        ]);
    }
}
