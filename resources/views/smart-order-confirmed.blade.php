@extends('layouts.smart-order')

@section('title', 'Pedido confirmado | SIVIA')

@section('content')
<main class="min-h-screen bg-neutral-950 px-4 py-10 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">
        <section class="rounded-3xl border border-blue-500/30 bg-neutral-900/90 p-6 shadow-2xl shadow-blue-950/20 sm:p-10">
            <div class="text-center">
                <span class="material-symbols-outlined text-6xl text-blue-300" aria-hidden="true">task_alt</span>
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Pedido enviado</p>
                <h1 class="mt-2 font-serif text-3xl font-bold">¡Tu pedido fue confirmado!</h1>
                <p class="mt-3 text-neutral-400">
                    {{ $order->empresa->nombre }} recibió el pedido
                    <span class="font-semibold text-white">{{ $order->numero_pedido }}</span>.
                </p>
            </div>

            <ul class="mt-8 divide-y divide-neutral-800 border-y border-neutral-800">
                @foreach ($order->detalles as $detail)
                    <li class="flex justify-between gap-4 py-4 text-sm">
                        <span>{{ $detail->cantidad }} × {{ $detail->producto->nombre }}</span>
                        <span class="font-semibold">${{ number_format((float) $detail->subtotal, 2, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-5 flex justify-between gap-4 text-lg font-bold">
                <span>Total</span>
                <span class="text-blue-300">${{ number_format((float) $order->total, 2, ',', '.') }}</span>
            </div>

            <a href="{{ route('smart.order.catalog') }}" class="mt-8 flex justify-center rounded-xl bg-blue-500 px-5 py-3 font-bold text-neutral-950 transition hover:bg-blue-400">
                Volver al catálogo
            </a>
        </section>
    </div>
</main>
@endsection
