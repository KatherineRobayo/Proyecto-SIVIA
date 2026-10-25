@extends('dashboard.layouts.dashboard')

@section('title', 'SIVIA | Movimientos')

@section('content')
<div class="px-6 py-6 md:px-8">
    <h1 class="mb-6 text-3xl font-bold text-white">Movimientos de inventario</h1>
    <div class="overflow-x-auto rounded-2xl border border-neutral-800 bg-neutral-900">
        <table class="w-full text-left text-sm text-neutral-300">
            <thead class="bg-neutral-950 text-neutral-400">
                <tr><th class="p-4">Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Responsable</th><th>Descripción</th></tr>
            </thead>
            <tbody>
            @forelse ($movimientos as $movimiento)
                <tr class="border-t border-neutral-800">
                    <td class="p-4">{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $movimiento->producto?->nombre ?? 'Producto no disponible' }}</td>
                    <td>{{ ucfirst($movimiento->tipo) }}</td>
                    <td>{{ $movimiento->cantidad }}</td>
                    <td>{{ $movimiento->user?->name ?? 'Usuario no disponible' }}</td>
                    <td>{{ $movimiento->descripcion }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center">No hay movimientos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movimientos->links() }}</div>
</div>
@endsection
