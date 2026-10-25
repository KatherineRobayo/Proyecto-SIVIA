@extends('layouts.app')

@section('content')
<div class="container py-4">
    <a href="{{ route('catalogo.clientes.empresas') }}">Ver otros negocios</a>
    <h1 class="mt-3">{{ $empresa->nombre }}</h1>
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @auth
        @if (auth()->user()->role === \App\Models\User::ROLE_CUSTOMER)
            <form method="POST" action="{{ route('catalogo.clientes.pedidos.store', $empresa) }}">
                @csrf
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Producto</th><th>Precio</th><th>Disponible</th><th>Cantidad</th></tr></thead>
                        <tbody>
                        @forelse ($productos as $producto)
                            <tr>
                                <td>{{ $producto->nombre }}</td>
                                <td>${{ number_format($producto->precio_venta, 2) }}</td>
                                <td>{{ $producto->stock }}</td>
                                <td>
                                    <input class="form-control" type="number" min="0" max="{{ $producto->stock }}"
                                           name="productos[{{ $producto->id }}][cantidad]" value="0">
                                    <input type="hidden" name="productos[{{ $producto->id }}][id]" value="{{ $producto->id }}">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No hay productos disponibles.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($productos->isNotEmpty())
                    <button class="btn btn-primary" type="submit">Realizar pedido</button>
                @endif
            </form>
        @else
            <p>Inicia sesión como cliente para realizar un pedido.</p>
        @endif
    @else
        <p><a href="{{ route('login') }}">Inicia sesión</a> para realizar un pedido.</p>
    @endauth
</div>
@endsection
