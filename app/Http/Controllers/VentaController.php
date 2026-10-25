<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(): View
    {
        $empresaId = $this->empresaId();
        $ventas = Venta::forEmpresa($empresaId)
            ->with(['user', 'cliente', 'detalles.producto'])
            ->latest()
            ->paginate(20);

        return view('ventas.index', compact('ventas'));
    }

    public function create(): View
    {
        $productos = Producto::forEmpresa($this->empresaId())
            ->where('estado', 'activo')
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();
        $clientes = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('ventas.create', compact('productos', 'clientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = $this->empresaId();
        $validated = $request->validate([
            'metodo_pago' => ['required', Rule::in(['efectivo', 'tarjeta', 'transferencia'])],
            'cliente_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', User::ROLE_CUSTOMER)
                    ->where('activo', true),
            ],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('productos', 'id')
                    ->where('empresa_id', $empresaId)
                    ->where('estado', 'activo'),
            ],
            'productos.*.cantidad' => ['required', 'integer', 'min:0'],
        ]);

        $venta = DB::transaction(function () use ($validated, $empresaId, $request): Venta {
            $items = collect($validated['productos'])
                ->filter(fn (array $item): bool => (int) $item['cantidad'] > 0)
                ->keyBy('id');
            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'productos' => 'Selecciona al menos un producto y una cantidad mayor que cero.',
                ]);
            }
            $productos = Producto::forEmpresa($empresaId)
                ->whereIn('id', $items->keys())
                ->where('estado', 'activo')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($productos->count() !== $items->count()) {
                throw ValidationException::withMessages([
                    'productos' => 'Uno o más productos ya no están disponibles.',
                ]);
            }

            foreach ($items as $id => $item) {
                if ($productos[$id]->stock < (int) $item['cantidad']) {
                    throw ValidationException::withMessages([
                        'productos' => "Stock insuficiente para {$productos[$id]->nombre}.",
                    ]);
                }
            }

            $venta = Venta::create([
                'empresa_id' => $empresaId,
                'user_id' => $request->user()->id,
                'cliente_id' => $validated['cliente_id'] ?? null,
                'total' => 0,
                'metodo_pago' => $validated['metodo_pago'],
            ]);

            $total = 0;
            foreach ($items as $id => $item) {
                $producto = $productos[$id];
                $cantidad = (int) $item['cantidad'];
                $subtotal = round($cantidad * (float) $producto->precio_venta, 2);
                $total += $subtotal;
                $producto->decrement('stock', $cantidad);

                DetalleVenta::create([
                    'empresa_id' => $empresaId,
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio' => $producto->precio_venta,
                    'subtotal' => $subtotal,
                ]);

                Movimiento::create([
                    'producto_id' => $producto->id,
                    'user_id' => $request->user()->id,
                    'tipo' => 'salida',
                    'cantidad' => $cantidad,
                    'descripcion' => "Venta {$venta->id}",
                ]);
            }

            $venta->update(['total' => $total]);

            return $venta;
        });

        return redirect()->route('ventas.index')->with('success', "Venta {$venta->id} registrada.");
    }

    private function empresaId(): int
    {
        $user = request()->user();
        abort_unless(
            $user?->activo && $user->empresa_id
                && in_array($user->role, [User::ROLE_COMPANY_ADMIN, User::ROLE_EMPLOYEE], true),
            403
        );

        return (int) $user->empresa_id;
    }
}
