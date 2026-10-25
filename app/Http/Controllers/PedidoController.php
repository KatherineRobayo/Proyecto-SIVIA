<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\DetalleVenta;
use App\Models\Movimiento;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::forEmpresa($this->empresaId())
            ->with(['empresa', 'user', 'cliente'])
            ->latest()
            ->paginate(15);

        return view('pedidos.index', compact('pedidos'));
    }

    public function create(): View
    {
        $this->authorizeOperations();
        $productos = Producto::forEmpresa($this->empresaId())
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
        $clientes = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pedidos.create', compact('productos', 'clientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeOperations();
        $empresaId = $this->empresaId();
        $validated = $request->validate([
            'descripcion' => ['nullable', 'string'],
            'fecha_entrega_esperada' => ['nullable', 'date'],
            'metodo_pago' => ['required', 'string', 'max:255'],
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
                Rule::exists('productos', 'id')->where('empresa_id', $empresaId)->where('estado', 'activo'),
            ],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $pedido = DB::transaction(function () use ($validated, $empresaId): Pedido {
            $pedido = Pedido::create([
                'empresa_id' => $empresaId,
                'user_id' => auth()->id(),
                'cliente_id' => $validated['cliente_id'] ?? null,
                'numero_pedido' => Pedido::generarNumeroPedido($empresaId),
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_entrega_esperada' => $validated['fecha_entrega_esperada'] ?? null,
                'metodo_pago' => $validated['metodo_pago'],
                'estado' => 'pendiente',
                'total' => 0,
                'stock_reserved' => false,
            ]);

            $total = $this->replaceDetailsAndReserve($pedido, $validated['productos'], $empresaId);
            $pedido->update(['total' => $total, 'stock_reserved' => true]);

            return $pedido;
        });

        return redirect()->route('pedidos.show', $pedido)
            ->with('success', "Pedido {$pedido->numero_pedido} creado y stock reservado.");
    }

    public function show(Pedido $pedido): View
    {
        $this->assertPedidoBelongsToActor($pedido);
        $pedido->load(['empresa', 'user', 'cliente', 'detalles.producto', 'venta']);

        return view('pedidos.show', compact('pedido'));
    }

    public function edit(Pedido $pedido): View
    {
        $this->authorizeAdministration();
        $this->assertPedidoBelongsToActor($pedido);
        abort_unless($pedido->estado === 'pendiente', 403, 'Solo se pueden editar pedidos pendientes.');

        $productos = Producto::forEmpresa($this->empresaId())
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
        $clientes = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $pedido->load('detalles');

        return view('pedidos.edit', compact('pedido', 'productos', 'clientes'));
    }

    public function update(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->authorizeAdministration();
        $this->assertPedidoBelongsToActor($pedido);
        abort_unless($pedido->estado === 'pendiente', 403, 'Solo se pueden editar pedidos pendientes.');

        $empresaId = $this->empresaId();
        $validated = $request->validate([
            'descripcion' => ['nullable', 'string'],
            'fecha_entrega_esperada' => ['nullable', 'date'],
            'metodo_pago' => ['required', 'string', 'max:255'],
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
                Rule::exists('productos', 'id')->where('empresa_id', $empresaId)->where('estado', 'activo'),
            ],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($pedido, $validated, $empresaId): void {
            $lockedPedido = Pedido::forEmpresa($empresaId)->lockForUpdate()->findOrFail($pedido->id);
            abort_unless($lockedPedido->estado === 'pendiente', 403);
            $total = $this->replaceDetailsAndReserve($lockedPedido, $validated['productos'], $empresaId);
            $lockedPedido->update([
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_entrega_esperada' => $validated['fecha_entrega_esperada'] ?? null,
                'metodo_pago' => $validated['metodo_pago'],
                'cliente_id' => $validated['cliente_id'] ?? null,
                'total' => $total,
                'stock_reserved' => true,
            ]);
        });

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido actualizado.');
    }

    public function cambiarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->authorizeOperations();
        $this->assertPedidoBelongsToActor($pedido);
        $validated = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'])],
            'fecha_entrega_real' => ['nullable', 'date'],
            'metodo_pago' => ['nullable', Rule::in(['efectivo', 'tarjeta', 'transferencia'])],
        ]);
        $empresaId = $this->empresaId();

        DB::transaction(function () use ($pedido, $validated, $empresaId): void {
            $order = Pedido::forEmpresa($empresaId)
                ->lockForUpdate()
                ->findOrFail($pedido->id);

            if (! $this->canTransition($order->estado, $validated['estado'])) {
                throw ValidationException::withMessages([
                    'estado' => 'La transición solicitada no está permitida.',
                ]);
            }

            if ($validated['estado'] === 'entregado') {
                $paymentMethod = $validated['metodo_pago'] ?? $order->metodo_pago;
                if (! in_array($paymentMethod, ['efectivo', 'tarjeta', 'transferencia'], true)) {
                    throw ValidationException::withMessages([
                        'metodo_pago' => 'Selecciona un método de pago antes de completar el pedido.',
                    ]);
                }
                $order->metodo_pago = $paymentMethod;
                $this->registerSaleForOrder($order, $empresaId);
            } elseif ($validated['estado'] === 'cancelado' && $order->stock_reserved) {
                $this->releaseReservedStock($order, $empresaId);
                $order->stock_reserved = false;
            }

            $order->estado = $validated['estado'];
            $order->fecha_entrega_real = $validated['estado'] === 'entregado'
                ? ($validated['fecha_entrega_real'] ?? now())
                : null;
            $order->save();
        });

        return back()->with('success', "Estado del pedido actualizado a {$validated['estado']}.");
    }

    public function destroy(Pedido $pedido): RedirectResponse
    {
        $this->authorizeAdministration();
        $this->assertPedidoBelongsToActor($pedido);
        DB::transaction(function () use ($pedido): void {
            $order = Pedido::forEmpresa($this->empresaId())->lockForUpdate()->findOrFail($pedido->id);
            abort_unless($order->estado === 'pendiente', 403);
            if ($order->stock_reserved) {
                $this->releaseReservedStock($order, (int) $order->empresa_id);
            }
            $order->delete();
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado; el stock reservado fue liberado.');
    }

    private function replaceDetailsAndReserve(Pedido $pedido, array $items, int $empresaId): float
    {
        $oldDetails = $pedido->detalles()->get();
        $productIds = collect($items)->pluck('id')
            ->merge($pedido->stock_reserved ? $oldDetails->pluck('producto_id') : [])
            ->unique()
            ->sort()
            ->values();
        $products = Producto::forEmpresa($empresaId)
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw ValidationException::withMessages([
                'productos' => 'Un producto ya no pertenece a esta empresa.',
            ]);
        }

        if ($pedido->stock_reserved) {
            foreach ($oldDetails as $detail) {
                $product = $products[$detail->producto_id];
                $product->stock += $detail->cantidad;
                $product->save();
                $this->recordMovement($product, 'entrada', $detail->cantidad, "Liberación al editar pedido {$pedido->numero_pedido}");
            }
        }

        $total = 0;
        foreach ($items as $item) {
            $product = $products[$item['id']];
            if ($product->estado !== 'activo') {
                throw ValidationException::withMessages(['productos' => 'No se puede reservar un producto inactivo.']);
            }
            $quantity = (int) $item['cantidad'];
            if ($product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'productos' => "Stock insuficiente para {$product->nombre}.",
                ]);
            }
            $subtotal = round($quantity * (float) $product->precio_venta, 2);
            $total += $subtotal;
            $product->stock -= $quantity;
            $product->save();
            $this->recordMovement($product, 'salida', $quantity, "Reserva para pedido {$pedido->numero_pedido}");
        }

        $pedido->detalles()->delete();
        foreach ($items as $item) {
            $product = $products[$item['id']];
            $quantity = (int) $item['cantidad'];
            DetallePedido::create([
                'empresa_id' => $empresaId,
                'pedido_id' => $pedido->id,
                'producto_id' => $product->id,
                'cantidad' => $quantity,
                'precio' => $product->precio_venta,
                'subtotal' => round($quantity * (float) $product->precio_venta, 2),
            ]);
        }

        return round($total, 2);
    }

    private function registerSaleForOrder(Pedido $pedido, int $empresaId): void
    {
        if ($pedido->venta()->exists()) {
            throw ValidationException::withMessages(['estado' => 'El pedido ya tiene una venta registrada.']);
        }

        $details = $pedido->detalles()->get();
        if ($details->isEmpty()) {
            throw ValidationException::withMessages(['estado' => 'No se puede completar un pedido sin productos.']);
        }

        if (! $pedido->stock_reserved) {
            $products = Producto::forEmpresa($empresaId)
                ->whereIn('id', $details->pluck('producto_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            foreach ($details as $detail) {
                $product = $products[$detail->producto_id] ?? null;
                if (! $product || $product->stock < $detail->cantidad) {
                    throw ValidationException::withMessages([
                        'estado' => 'No hay stock suficiente para completar este pedido.',
                    ]);
                }
                $product->decrement('stock', $detail->cantidad);
                $this->recordMovement($product, 'salida', $detail->cantidad, "Venta del pedido {$pedido->numero_pedido}");
            }
        }

        $sale = Venta::create([
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'cliente_id' => $pedido->cliente_id,
            'pedido_id' => $pedido->id,
            'total' => $pedido->total,
            'metodo_pago' => $pedido->metodo_pago === 'pendiente' ? 'efectivo' : $pedido->metodo_pago,
        ]);

        foreach ($details as $detail) {
            DetalleVenta::create([
                'empresa_id' => $empresaId,
                'venta_id' => $sale->id,
                'producto_id' => $detail->producto_id,
                'cantidad' => $detail->cantidad,
                'precio' => $detail->precio,
                'subtotal' => $detail->subtotal,
            ]);
        }

        $pedido->stock_reserved = true;
    }

    private function releaseReservedStock(Pedido $pedido, int $empresaId): void
    {
        $details = $pedido->detalles()->get();
        $products = Producto::forEmpresa($empresaId)
            ->whereIn('id', $details->pluck('producto_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($details as $detail) {
            $product = $products[$detail->producto_id] ?? null;
            if (! $product) {
                throw new \RuntimeException("No se encontró el producto {$detail->producto_id} del pedido {$pedido->id} para liberar la reserva.");
            }
            $product->increment('stock', $detail->cantidad);
            $this->recordMovement($product, 'entrada', $detail->cantidad, "Liberación por cancelación del pedido {$pedido->numero_pedido}");
        }
    }

    private function recordMovement(Producto $product, string $type, int $quantity, string $description): void
    {
        Movimiento::create([
            'producto_id' => $product->id,
            'user_id' => auth()->id(),
            'tipo' => $type,
            'cantidad' => $quantity,
            'descripcion' => $description,
        ]);
    }

    private function canTransition(string $current, string $next): bool
    {
        return in_array($next, match ($current) {
            'pendiente' => ['confirmado', 'cancelado', 'entregado'],
            'confirmado' => ['enviado', 'cancelado', 'entregado'],
            'enviado' => ['entregado', 'cancelado'],
            default => [],
        }, true);
    }

    private function empresaId(): int
    {
        $user = auth()->user();
        abort_unless(
            $user?->activo && $user->empresa_id
                && in_array($user->role, [User::ROLE_COMPANY_ADMIN, User::ROLE_EMPLOYEE], true),
            403,
            'Esta operación requiere una empresa asignada.'
        );

        return (int) $user->empresa_id;
    }

    private function assertPedidoBelongsToActor(Pedido $pedido): void
    {
        abort_unless((int) $pedido->empresa_id === $this->empresaId(), 404);
    }

    private function authorizeOperations(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, [User::ROLE_COMPANY_ADMIN, User::ROLE_EMPLOYEE], true),
            403
        );
    }

    private function authorizeAdministration(): void
    {
        abort_unless(auth()->user()?->role === User::ROLE_COMPANY_ADMIN, 403);
    }
}
