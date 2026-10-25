<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $this->empresaId();
        $query = Producto::forEmpresa($empresaId)
            ->where('estado', 'activo')
            ->with('categoria');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($products) use ($search): void {
                $products->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhereHas('categoria', fn ($categories) => $categories->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('categoria') && $request->input('categoria') !== 'todos') {
            $query->where('categoria_id', $request->input('categoria'));
        }

        $productos = $query->latest()->paginate(8)->withQueryString();

        $totalProductos = Producto::forEmpresa($empresaId)->count();
        $alertasStock = Producto::forEmpresa($empresaId)
            ->where('estado', 'activo')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->count();
        $valorInventario = Producto::forEmpresa($empresaId)
            ->where('estado', 'activo')
            ->selectRaw('SUM(precio_compra * stock) as total')
            ->value('total') ?? 0;
        $categorias = Categoria::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        return view('dashboard.inventario.index', compact(
            'productos',
            'totalProductos',
            'alertasStock',
            'valorInventario',
            'categorias'
        ));
    }

    public function ajustarStock(Request $request)
    {
        $empresaId = $this->empresaId();
        $data = $request->validate([
            'producto_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')->where('empresa_id', $empresaId)->where('estado', 'activo'),
            ],
            'tipo' => 'required|in:sumar,restar',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data, $empresaId): void {
            $producto = Producto::forEmpresa($empresaId)
                ->where('estado', 'activo')
                ->lockForUpdate()
                ->findOrFail($data['producto_id']);

            if ($data['tipo'] === 'sumar') {
                $producto->stock += $data['cantidad'];
                $tipoMovimiento = 'entrada';
            } else {
                if ($producto->stock < $data['cantidad']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'cantidad' => 'No puedes dejar el stock en negativo.',
                    ]);
                }
                $producto->stock -= $data['cantidad'];
                $tipoMovimiento = 'salida';
            }

            $producto->save();

            Movimiento::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => $tipoMovimiento,
                'cantidad' => $data['cantidad'],
                'descripcion' => 'Ajuste manual de inventario',
            ]);
        });

        return back()->with('success', 'Stock actualizado correctamente');
    }

    private function empresaId(): int
    {
        $user = auth()->user();
        abort_unless(
            $user?->activo && $user->empresa_id
                && in_array($user->role, [\App\Models\User::ROLE_COMPANY_ADMIN, \App\Models\User::ROLE_EMPLOYEE], true),
            403,
            'Esta operación requiere un usuario operativo asociado a una empresa.'
        );

        return (int) $user->empresa_id;
    }
}
