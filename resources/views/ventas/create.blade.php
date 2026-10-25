@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Caja - Nueva venta</h1>
        <a class="btn btn-outline-secondary" href="{{ route('ventas.index') }}">Historial de ventas</a>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('ventas.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="metodo_pago">Método de pago</label>
            <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="cliente_id">Cliente (opcional)</label>
            <select class="form-select" id="cliente_id" name="cliente_id">
                <option value="">Venta mostrador</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Producto</th><th>Precio</th><th>Stock</th><th>Cantidad</th></tr></thead>
                <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre }}</td>
                        <td>${{ number_format($producto->precio_venta, 2) }}</td>
                        <td>{{ $producto->stock }}</td>
                        <td class="d-flex gap-2">
                            <input class="form-control" type="number" min="0" max="{{ $producto->stock }}"
                                   name="productos[{{ $producto->id }}][cantidad]" value="0">
                            <input type="hidden" name="productos[{{ $producto->id }}][id]" value="{{ $producto->id }}">
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No hay productos con stock disponible.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($productos->isNotEmpty())
            <button class="btn btn-primary" type="submit">Registrar venta</button>
        @endif
    </form>
</div>
@endsection
