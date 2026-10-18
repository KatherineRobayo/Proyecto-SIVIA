@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Gestión de Pedidos</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('pedidos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Pedido
            </a>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Empresa</th>
                        <th>Usuario</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Método Pago</th>
                        <th>Fecha Entrega</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr>
                            <td><strong>{{ $pedido->numero_pedido }}</strong></td>
                            <td>{{ $pedido->empresa->nombre ?? 'N/A' }}</td>
                            <td>{{ $pedido->user->name ?? 'N/A' }}</td>
                            <td>${{ number_format($pedido->total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $pedido->estado === 'entregado' ? 'success' : ($pedido->estado === 'cancelado' ? 'danger' : 'info') }}">
                                    {{ ucfirst($pedido->estado) }}
                                </span>
                            </td>
                            <td>{{ ucfirst($pedido->metodo_pago) }}</td>
                            <td>{{ $pedido->fecha_entrega_esperada?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                @if ($pedido->estado === 'pendiente')
                                    <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No hay pedidos registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $pedidos->links() }}
    </div>
</div>
@endsection
