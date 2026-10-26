@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Gestión de Pedidos</h1>
        </div>
        <div>
            <a href="{{ route('pedidos.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 font-bold text-white hover:bg-blue-500">
                <i class="bi bi-plus-circle"></i> Nuevo Pedido
            </a>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 rounded-lg border border-green-700 bg-green-900/30 px-4 py-3 text-green-300">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-lg border border-red-700 bg-red-900/30 px-4 py-3 text-red-300">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mt-6 rounded-xl border border-neutral-800 bg-neutral-900 p-6 text-white">
        <div class="table-responsive">
            <table class="w-full text-left text-sm text-neutral-300">
                <thead>
                    <tr>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Número</th>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Empresa</th>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Usuario</th>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Total</th>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Estado</th>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Método Pago</th>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Fecha Entrega</th>
                        <th class="border-b border-neutral-700 pb-3 text-blue-300 font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                            <td class="py-3 px-2"><strong class="text-white">{{ $pedido->numero_pedido }}</strong></td>
                            <td class="py-3 px-2">{{ $pedido->empresa->nombre ?? 'N/A' }}</td>
                            <td class="py-3 px-2">{{ $pedido->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-2">${{ number_format($pedido->total, 2) }}</td>
                            <td class="py-3 px-2">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $pedido->estado === 'entregado' ? 'bg-green-500/15 text-green-300' : ($pedido->estado === 'cancelado' ? 'bg-red-500/15 text-red-300' : 'bg-blue-500/15 text-blue-300') }}">
                                    {{ ucfirst($pedido->estado) }}
                                </span>
                            </td>
                            <td class="py-3 px-2">{{ ucfirst($pedido->metodo_pago) }}</td>
                            <td class="py-3 px-2">{{ $pedido->fecha_entrega_esperada?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                            <td class="py-3 px-2">
                                <a href="{{ route('pedidos.show', $pedido) }}" class="font-semibold text-blue-400 hover:text-blue-300">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                @if ($pedido->estado === 'pendiente' && auth()->user()->role === \App\Models\User::ROLE_COMPANY_ADMIN)
                                    <a href="{{ route('pedidos.edit', $pedido) }}" class="ml-3 font-semibold text-blue-400 hover:text-blue-300">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="border-b border-neutral-800">
                            <td colspan="8" class="py-3 px-2 text-center text-neutral-400">
                                <p>No hay pedidos registrados</p>
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
