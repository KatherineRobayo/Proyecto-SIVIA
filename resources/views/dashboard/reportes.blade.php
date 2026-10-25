@extends('dashboard.layouts.dashboard')

@section('title', 'SIVIA | Reportes')

@section('content')
<div class="px-6 py-6 md:px-8">
    <h1 class="mb-6 text-3xl font-bold text-white">Reportes de la empresa</h1>
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-neutral-800 bg-neutral-900 p-4">
        <label class="text-sm text-neutral-300">Desde
            <input class="mt-1 block rounded-lg border border-neutral-700 bg-neutral-950 p-2 text-white" type="date" name="desde" value="{{ $desde }}">
        </label>
        <label class="text-sm text-neutral-300">Hasta
            <input class="mt-1 block rounded-lg border border-neutral-700 bg-neutral-950 p-2 text-white" type="date" name="hasta" value="{{ $hasta }}">
        </label>
        <button class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white" type="submit">Filtrar</button>
    </form>
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5"><p class="text-neutral-400">Ventas</p><strong class="text-2xl text-white">{{ $totalVentas }}</strong></div>
        <div class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5"><p class="text-neutral-400">Ingresos</p><strong class="text-2xl text-emerald-400">${{ number_format($ingresos, 2) }}</strong></div>
        <div class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5"><p class="text-neutral-400">Unidades ingresadas</p><strong class="text-2xl text-white">{{ $entradas }}</strong></div>
        <div class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5"><p class="text-neutral-400">Unidades salidas</p><strong class="text-2xl text-white">{{ $salidas }}</strong></div>
    </div>
    <div class="mb-6 rounded-2xl border border-neutral-800 bg-neutral-900 p-5">
        <p class="text-neutral-400">Valor del inventario actual</p>
        <strong class="text-2xl text-cyan-400">${{ number_format($valorInventario, 2) }}</strong>
    </div>
    <div class="overflow-x-auto rounded-2xl border border-neutral-800 bg-neutral-900">
        <table class="w-full text-left text-sm text-neutral-300">
            <thead class="bg-neutral-950 text-neutral-400"><tr><th class="p-4">Fecha</th><th>Registró</th><th>Cliente</th><th>Método</th><th>Total</th></tr></thead>
            <tbody>
            @forelse ($ventas as $venta)
                <tr class="border-t border-neutral-800"><td class="p-4">{{ $venta->created_at?->format('d/m/Y H:i') }}</td><td>{{ $venta->user?->name ?? 'Usuario no disponible' }}</td><td>{{ $venta->cliente?->name ?? 'Venta mostrador' }}</td><td>{{ ucfirst($venta->metodo_pago) }}</td><td>${{ number_format($venta->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center">No hay ventas en el período seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $ventas->links() }}</div>
</div>
@endsection
