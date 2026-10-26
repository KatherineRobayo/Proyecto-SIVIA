@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-6">
    <div class="mt-6 rounded-xl border border-neutral-800 bg-neutral-900 p-6 text-white">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="mb-4 text-3xl font-bold text-blue-300">Pedido {{ $pedido->numero_pedido }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @if ($pedido->estado === 'pendiente' && auth()->user()->role === \App\Models\User::ROLE_COMPANY_ADMIN)
                <a href="{{ route('pedidos.edit', $pedido) }}" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-500">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <form action="{{ route('pedidos.destroy', $pedido) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-600" onclick="return confirm('¿Eliminar este pedido?')">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 rounded-lg border border-green-700 bg-green-900/30 px-4 py-3 text-green-300">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="mb-4 rounded-xl border border-neutral-800 bg-neutral-950">
                <div class="border-b border-neutral-800 px-5 py-4">
                    <h2 class="mb-4 text-xl font-bold text-blue-300">Información del Pedido</h2>
                </div>
                <div class="p-5">
                    <div class="mb-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <strong>Empresa:</strong><br>
                            {{ $pedido->empresa->nombre ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Registró:</strong><br>
                            {{ $pedido->user->name ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Cliente:</strong><br>
                            {{ $pedido->cliente->name ?? 'No especificado' }}
                        </div>
                    </div>

                    <div class="mb-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <strong>Fecha Creación:</strong><br>
                            {{ $pedido->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            <strong>Última Actualización:</strong><br>
                            {{ $pedido->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="mb-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <strong>Fecha Entrega Esperada:</strong><br>
                            {{ $pedido->fecha_entrega_esperada?->format('d/m/Y H:i') ?? 'No especificada' }}
                        </div>
                        <div>
                            <strong>Fecha Entrega Real:</strong><br>
                            {{ $pedido->fecha_entrega_real?->format('d/m/Y H:i') ?? 'Pendiente' }}
                        </div>
                    </div>

                    @if ($pedido->descripcion)
                        <div class="mb-3">
                            <strong>Descripción:</strong><br>
                            {{ $pedido->descripcion }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="mb-4 rounded-xl border border-neutral-800 bg-neutral-950">
                <div class="border-b border-neutral-800 px-5 py-4">
                    <h2 class="mb-4 text-xl font-bold text-blue-300">Detalles del Pedido</h2>
                </div>
                <div class="table-responsive">
                    <table class="w-full text-left text-sm text-neutral-300">
                        <thead>
                            <tr>
                                <th class="border-b border-neutral-700 px-2 py-3 text-blue-300 font-bold">Producto</th>
                                <th class="border-b border-neutral-700 px-2 py-3 text-right text-blue-300 font-bold">Cantidad</th>
                                <th class="border-b border-neutral-700 px-2 py-3 text-right text-blue-300 font-bold">Precio Unitario</th>
                                <th class="border-b border-neutral-700 px-2 py-3 text-right text-blue-300 font-bold">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pedido->detalles as $detalle)
                                <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                                    <td class="py-3 px-2">{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</td>
                                    <td class="py-3 px-2 text-right">{{ $detalle->cantidad }}</td>
                                    <td class="py-3 px-2 text-right">${{ number_format($detalle->precio, 2) }}</td>
                                    <td class="py-3 px-2 text-right"><strong class="text-white">${{ number_format($detalle->subtotal, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr class="border-b border-neutral-800">
                                    <td colspan="4" class="py-3 px-2 text-center text-neutral-400">No hay productos en este pedido</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="mb-4 rounded-xl border border-neutral-800 bg-neutral-950">
                <div class="border-b border-neutral-800 px-5 py-4">
                    <h2 class="mb-4 text-xl font-bold text-blue-300">Estado y Totales</h2>
                </div>
                <div class="p-5">
                    <div class="mb-3">
                        <strong>Estado Actual:</strong><br>
                        <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $pedido->estado === 'entregado' ? 'bg-green-500/15 text-green-300' : ($pedido->estado === 'cancelado' ? 'bg-red-500/15 text-red-300' : 'bg-blue-500/15 text-blue-300') }}">
                            {{ ucfirst($pedido->estado) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Método de Pago:</strong><br>
                        {{ ucfirst($pedido->metodo_pago) }}
                    </div>

                    <hr class="my-4 border-neutral-800">

                    <div class="mb-3">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>${{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>

                    <div class="border-t border-neutral-800 pt-3">
                        <div class="flex justify-between text-lg">
                            <strong>Total:</strong>
                            <strong class="text-blue-300">${{ number_format($pedido->total, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            @if ($pedido->estado !== 'entregado' && $pedido->estado !== 'cancelado')
                <div class="rounded-xl border border-neutral-800 bg-neutral-950">
                    <div class="border-b border-neutral-800 px-5 py-4">
                        <h2 class="mb-4 text-xl font-bold text-blue-300">Cambiar Estado</h2>
                    </div>
                    <div class="p-5">
                        <form action="{{ route('pedidos.cambiarEstado', $pedido) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <select name="estado" class="w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="mb-2 block text-sm text-neutral-300">Fecha Entrega (si aplica)</label>
                                <input type="datetime-local" name="fecha_entrega_real" class="w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            </div>

                            <div class="mb-3">
                                <label class="mb-2 block text-sm text-neutral-300">Método de pago al completar</label>
                                <select name="metodo_pago" class="w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                                    <option value="">Usar el método actual si ya está definido</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 font-bold text-white hover:bg-blue-500">
                                <i class="bi bi-check-circle"></i> Actualizar Estado
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('pedidos.index') }}" class="rounded-lg border border-neutral-700 px-4 py-2 font-semibold text-neutral-300 hover:bg-neutral-800 hover:text-white">Volver a Pedidos</a>
    </div>
    </div>
</div>
@endsection
