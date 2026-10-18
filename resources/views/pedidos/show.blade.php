@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Pedido {{ $pedido->numero_pedido }}</h1>
        </div>
        <div class="col-md-4 text-end">
            @if ($pedido->estado === 'pendiente')
                <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <form action="{{ route('pedidos.destroy', $pedido) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar este pedido?')">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Información del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Empresa:</strong><br>
                            {{ $pedido->empresa->nombre ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Usuario:</strong><br>
                            {{ $pedido->user->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Fecha Creación:</strong><br>
                            {{ $pedido->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Última Actualización:</strong><br>
                            {{ $pedido->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Fecha Entrega Esperada:</strong><br>
                            {{ $pedido->fecha_entrega_esperada?->format('d/m/Y H:i') ?? 'No especificada' }}
                        </div>
                        <div class="col-md-6">
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

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Detalles del Pedido</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pedido->detalles as $detalle)
                                <tr>
                                    <td>{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</td>
                                    <td class="text-end">{{ $detalle->cantidad }}</td>
                                    <td class="text-end">${{ number_format($detalle->precio, 2) }}</td>
                                    <td class="text-end"><strong>${{ number_format($detalle->subtotal, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No hay productos en este pedido</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Estado y Totales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Estado Actual:</strong><br>
                        <span class="badge bg-{{ $pedido->estado === 'entregado' ? 'success' : ($pedido->estado === 'cancelado' ? 'danger' : 'info') }} fs-6">
                            {{ ucfirst($pedido->estado) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Método de Pago:</strong><br>
                        {{ ucfirst($pedido->metodo_pago) }}
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>${{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between fs-5">
                            <strong>Total:</strong>
                            <strong>${{ number_format($pedido->total, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            @if ($pedido->estado !== 'entregado' && $pedido->estado !== 'cancelado')
                <div class="card">
                    <div class="card-header">
                        <h5>Cambiar Estado</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pedidos.cambiarEstado', $pedido) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <select name="estado" class="form-control" required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fecha Entrega (si aplica)</label>
                                <input type="datetime-local" name="fecha_entrega_real" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> Actualizar Estado
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Volver a Pedidos</a>
    </div>
</div>
@endsection
