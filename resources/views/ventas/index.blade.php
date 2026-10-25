@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Ventas de la empresa</h1>
        <a class="btn btn-primary" href="{{ route('caja.index') }}">Nueva venta</a>
    </div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>#</th><th>Fecha</th><th>Registró</th><th>Cliente</th><th>Método</th><th>Total</th></tr></thead>
            <tbody>
            @forelse ($ventas as $venta)
                <tr>
                    <td>{{ $venta->id }}</td>
                    <td>{{ $venta->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->user?->name ?? 'Usuario no disponible' }}</td>
                    <td>{{ $venta->cliente?->name ?? 'Venta mostrador' }}</td>
                    <td>{{ ucfirst($venta->metodo_pago) }}</td>
                    <td>${{ number_format($venta->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Aún no hay ventas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $ventas->links() }}
</div>
@endsection
