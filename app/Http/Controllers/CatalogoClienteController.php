<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use App\Models\Movimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogoClienteController extends Controller
{
    public function index(): View
    {
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre', 'direccion', 'logo']);

        return view('catalogo.clientes.empresas', compact('empresas'));
    }

    public function show(Empresa $empresa): View
    {
        $productos = Producto::forEmpresa($empresa->id)
            ->where('estado', 'activo')
            ->where('stock', '>', 0)
            ->with('categoria')
            ->orderBy('nombre')
            ->get();

        return view('catalogo.clientes.productos', compact('empresa', 'productos'));
    }

    public function myOrders(Request $request): View
    {
        abort_unless($request->user()?->role === \App\Models\User::ROLE_CUSTOMER, 403);
        $pedidos = Pedido::query()
            ->where('cliente_id', $request->user()->id)
            ->with('empresa')
            ->latest()
            ->paginate(20);

        return view('catalogo.clientes.pedidos', compact('pedidos'));
    }

    public function store(Request $request, Empresa $empresa): RedirectResponse
    {
        abort_unless($request->user()?->role === \App\Models\User::ROLE_CUSTOMER, 403);

        $validated = $request->validate([
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('productos', 'id')
                    ->where('empresa_id', $empresa->id)
                    ->where('estado', 'activo'),
            ],
            'productos.*.cantidad' => ['required', 'integer', 'min:0'],
        ]);

        $pedido = DB::transaction(function () use ($validated, $empresa, $request): Pedido {
            $numeroPedido = Pedido::generarNumeroPedido($empresa->id);
            $items = collect($validated['productos'])
                ->filter(fn (array $item): bool => (int) $item['cantidad'] > 0)
                ->keyBy('id');

            if ($items->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'productos' => 'Selecciona al menos un producto y una cantidad mayor que cero.',
                ]);
            }
            $productos = Producto::forEmpresa($empresa->id)
                ->whereIn('id', $items->keys())
                ->where('estado', 'activo')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($productos->count() !== $items->count()) {
                abort(422, 'Uno o más productos ya no están disponibles en esta empresa.');
            }

            foreach ($items as $id => $item) {
                if ($productos[$id]->stock < (int) $item['cantidad']) {
                    abort(422, "Stock insuficiente para {$productos[$id]->nombre}.");
                }
            }

            $pedido = Pedido::create([
                'empresa_id' => $empresa->id,
                'user_id' => null,
                'cliente_id' => $request->user()->id,
                'numero_pedido' => $numeroPedido,
                'estado' => 'pendiente',
                'metodo_pago' => 'pendiente',
                'total' => 0,
                'stock_reserved' => true,
            ]);

            $total = 0;
            foreach ($items as $id => $item) {
                $producto = $productos[$id];
                $cantidad = (int) $item['cantidad'];
                $subtotal = round($cantidad * (float) $producto->precio_venta, 2);
                $total += $subtotal;

                $producto->decrement('stock', $cantidad);

                Movimiento::create([
                    'producto_id' => $producto->id,
                    'user_id' => $request->user()->id,
                    'tipo' => 'salida',
                    'cantidad' => $cantidad,
                    'descripcion' => "Reserva del pedido {$pedido->numero_pedido}",
                ]);

                DetallePedido::create([
                    'empresa_id' => $empresa->id,
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio' => $producto->precio_venta,
                    'subtotal' => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);

            return $pedido;
        });

        return redirect()
            ->route('catalogo.clientes.empresas')
            ->with('success', "Pedido {$pedido->numero_pedido} registrado. El negocio confirmará su preparación.");
    }
}
