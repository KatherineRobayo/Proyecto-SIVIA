@extends('layouts.smart-order')

@section('title', 'Revisar pedido | SIVIA')

@section('content')
<main class="min-h-screen bg-neutral-950 px-4 py-10 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('smart.order.cart') }}" class="inline-flex items-center gap-2 text-sm text-neutral-400 transition hover:text-blue-300">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            Volver a Tu Pedido
        </a>
        <header class="mt-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">{{ $selectedBusiness->nombre }}</p>
            <h1 class="mt-2 font-serif text-4xl font-bold">Revisa tu pedido</h1>
            <p class="mt-2 text-neutral-400">Confirma que los productos y cantidades sean correctos antes de enviarlo.</p>
        </header>

        @if (session('order_error'))
            <p class="mt-6 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-200" role="alert">{{ session('order_error') }}</p>
        @endif

        <section class="mt-8 overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-900/80">
            <ul class="divide-y divide-neutral-800">
                @foreach ($cartSummary['items'] as $item)
                    <li class="flex flex-wrap items-start justify-between gap-3 p-5">
                        <div>
                            <h2 class="font-semibold">{{ $item['product']->nombre }}</h2>
                            <p class="mt-1 text-sm text-neutral-400">
                                {{ $item['quantity'] }} × ${{ number_format($item['unitPrice'], 2, ',', '.') }}
                            </p>
                        </div>
                        <p class="font-semibold text-blue-300">${{ number_format($item['subtotal'], 2, ',', '.') }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="flex justify-between gap-4 border-t border-neutral-700 bg-neutral-950/60 p-5 text-lg font-bold">
                <span>Total del pedido</span>
                <span class="text-blue-300">${{ number_format($cartSummary['total'], 2, ',', '.') }}</span>
            </div>
        </section>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <a href="{{ route('smart.order.cart') }}" class="rounded-xl border border-neutral-700 px-5 py-3 text-center font-semibold text-neutral-200 transition hover:border-blue-400/50">
                Corregir mi pedido
            </a>
            <form method="POST" action="{{ route('smart.order.confirm') }}">
                @csrf
                <input type="hidden" name="confirmation_token" value="{{ $confirmationToken }}">
                <button type="submit" class="w-full rounded-xl bg-blue-500 px-5 py-3 font-bold text-neutral-950 transition hover:bg-blue-400">
                    Confirmar pedido
                </button>
            </form>
        </div>
    </div>
</main>
@endsection
