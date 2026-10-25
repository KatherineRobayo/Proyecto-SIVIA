@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Mis pedidos</h1>
    <div class="table-responsive mt-4">
        <table class="table">
            <thead><tr><th>Número</th><th>Negocio</th><th>Fecha</th><th>Estado</th><th>Total</th></tr></thead>
            <tbody>
            @forelse ($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->numero_pedido }}</td>
                    <td>{{ $pedido->empresa?->nombre ?? 'Negocio no disponible' }}</td>
                    <td>{{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($pedido->estado) }}</td>
                    <td>${{ number_format($pedido->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aún no tienes pedidos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $pedidos->links() }}
</div>
@endsection
