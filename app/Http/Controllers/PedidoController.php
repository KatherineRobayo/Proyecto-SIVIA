<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PedidoController extends Controller
{
    /**
     * Mostrar lista de pedidos
     */
    public function index(): View
    {
        $pedidos = Pedido::with(['empresa', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Mostrar formulario para crear nuevo pedido
     */
    public function create(): View
    {
        $productos = Producto::where('estado', 'activo')->get();
        return view('pedidos.create', compact('productos'));
    }

    /**
     * Guardar nuevo pedido
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descripcion' => 'nullable|string',
            'fecha_entrega_esperada' => 'nullable|date',
            'metodo_pago' => 'required|string',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        try {
            $pedido = Pedido::create([
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'numero_pedido' => Pedido::generarNumeroPedido(),
                'descripcion' => $validated['descripcion'],
                'fecha_entrega_esperada' => $validated['fecha_entrega_esperada'],
                'metodo_pago' => $validated['metodo_pago'],
                'estado' => 'pendiente',
                'total' => 0,
            ]);

            $total = 0;
            foreach ($validated['productos'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio'];
                $total += $subtotal;

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'subtotal' => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);

            return redirect()->route('pedidos.show', $pedido)
                ->with('success', 'Pedido creado exitosamente: ' . $pedido->numero_pedido);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles del pedido
     */
    public function show(Pedido $pedido): View
    {
        $pedido->load(['empresa', 'user', 'detalles.producto']);
        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Mostrar formulario para editar pedido
     */
    public function edit(Pedido $pedido): View
    {
        if ($pedido->estado !== 'pendiente') {
            abort(403, 'Solo se pueden editar pedidos en estado pendiente');
        }

        $productos = Producto::where('estado', 'activo')->get();
        $pedido->load('detalles');

        return view('pedidos.edit', compact('pedido', 'productos'));
    }

    /**
     * Actualizar pedido
     */
    public function update(Request $request, Pedido $pedido): RedirectResponse
    {
        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden editar pedidos en estado pendiente');
        }

        $validated = $request->validate([
            'descripcion' => 'nullable|string',
            'fecha_entrega_esperada' => 'nullable|date',
            'metodo_pago' => 'required|string',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        try {
            $pedido->update([
                'descripcion' => $validated['descripcion'],
                'fecha_entrega_esperada' => $validated['fecha_entrega_esperada'],
                'metodo_pago' => $validated['metodo_pago'],
            ]);

            // Eliminar detalles anteriores
            $pedido->detalles()->delete();

            // Crear nuevos detalles
            $total = 0;
            foreach ($validated['productos'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio'];
                $total += $subtotal;

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'subtotal' => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);

            return redirect()->route('pedidos.show', $pedido)
                ->with('success', 'Pedido actualizado exitosamente');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado del pedido
     */
    public function cambiarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,confirmado,enviado,entregado,cancelado',
            'fecha_entrega_real' => 'nullable|date',
        ]);

        $pedido->update([
            'estado' => $validated['estado'],
            'fecha_entrega_real' => $validated['estado'] === 'entregado' 
                ? $validated['fecha_entrega_real'] ?? now() 
                : null,
        ]);

        return back()->with('success', 'Estado del pedido actualizado a: ' . $validated['estado']);
    }

    /**
     * Eliminar pedido
     */
    public function destroy(Pedido $pedido): RedirectResponse
    {
        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden eliminar pedidos en estado pendiente');
        }

        $numero = $pedido->numero_pedido;
        $pedido->delete();

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido ' . $numero . ' eliminado exitosamente');
    }
}
