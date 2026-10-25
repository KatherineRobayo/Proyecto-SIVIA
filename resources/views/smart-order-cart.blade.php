@extends('layouts.smart-order')

@section('title', 'Tu Pedido | SIVIA')

@section('content')
<main class="min-h-screen bg-neutral-950 px-4 py-10 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('smart.order.catalog') }}" class="inline-flex items-center gap-2 text-sm text-neutral-400 transition hover:text-blue-300">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            Volver al catálogo de {{ $selectedBusiness->nombre }}
        </a>

        <header class="mt-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">{{ $selectedBusiness->nombre }}</p>
                <h1 class="mt-2 font-serif text-4xl font-bold">Tu Pedido</h1>
            </div>
            @if ($cartSummary['items']->isNotEmpty())
                <form method="POST" action="{{ route('smart.order.cart.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-neutral-700 px-4 py-2.5 text-sm text-neutral-300 transition hover:border-red-400/50 hover:text-red-200">
                        Vaciar pedido
                    </button>
                </form>
            @endif
        </header>

        @if (session('success'))
            <p class="mt-6 rounded-xl border border-blue-400/20 bg-blue-400/5 px-4 py-3 text-sm text-blue-200" role="status">{{ session('success') }}</p>
        @endif
        @if (session('order_error'))
            <p class="mt-6 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-200" role="alert">{{ session('order_error') }}</p>
        @endif
        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($cartSummary['items']->isEmpty())
            <section class="mt-8 rounded-3xl border border-neutral-800 bg-neutral-900/80 p-8 text-center sm:p-12">
                <span class="material-symbols-outlined text-5xl text-blue-300" aria-hidden="true">inventory_2</span>
                <h2 class="mt-4 font-serif text-2xl font-bold">Tu Pedido está vacío</h2>
                <p class="mt-2 text-neutral-400">Explora el catálogo y añade los productos que necesitas.</p>
                <a href="{{ route('smart.order.catalog') }}" class="mt-6 inline-flex rounded-xl bg-blue-500 px-5 py-3 font-bold text-neutral-950 transition hover:bg-blue-400">
                    Ver catálogo
                </a>
            </section>
        @else
            <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <section class="space-y-4" aria-label="Productos de Tu Pedido">
                    @foreach ($cartSummary['items'] as $item)
                        <article class="rounded-2xl border {{ $item['available'] ? 'border-neutral-800' : 'border-amber-400/40' }} bg-neutral-900/80 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h2 class="truncate font-serif text-lg font-bold">
                                        {{ $item['product']?->nombre ?? 'Producto no disponible' }}
                                    </h2>
                                    @if ($item['available'])
                                        <p class="mt-1 text-sm text-neutral-400">
                                            ${{ number_format($item['unitPrice'], 2, ',', '.') }} cada uno ·
                                            {{ $item['product']->stock }} disponibles
                                        </p>
                                        <p class="mt-2 font-semibold text-blue-300">Subtotal: ${{ number_format($item['subtotal'], 2, ',', '.') }}</p>
                                    @else
                                        <p class="mt-2 text-sm text-amber-200">
                                            @if (! $item['product'] || $item['product']->estado !== 'activo')
                                                Este producto ya no está disponible en el negocio.
                                            @else
                                                Solo quedan {{ $item['product']->stock }} unidades; ajusta la cantidad o elimínalo.
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    @if ($item['available'])
                                        <form method="POST" action="{{ route('smart.order.cart.update', $item['product']->id) }}" class="flex items-end gap-2">
                                            @csrf
                                            @method('PUT')
                                            <label class="text-xs text-neutral-400">
                                                Cantidad
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['product']->stock }}" required
                                                       class="mt-1 w-20 rounded-lg border border-neutral-700 bg-neutral-950 px-2 py-2 text-center text-white focus:border-blue-500 focus:outline-none">
                                            </label>
                                            <button type="submit" class="rounded-lg border border-blue-500/40 px-3 py-2 text-sm text-blue-200 transition hover:bg-blue-500/10">
                                                Actualizar
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('smart.order.cart.remove', $item['productId']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-neutral-700 px-3 py-2 text-sm text-neutral-300 transition hover:border-red-400/50 hover:text-red-200">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                <aside class="h-fit rounded-2xl border border-blue-500/30 bg-neutral-900/90 p-5 lg:sticky lg:top-6">
                    <h2 class="font-serif text-xl font-bold">Resumen</h2>
                    <div class="mt-4 flex justify-between gap-4 text-sm text-neutral-300">
                        <span>Unidades</span>
                        <span>{{ $cartSummary['quantity'] }}</span>
                    </div>
                    <div class="mt-4 flex justify-between gap-4 border-t border-neutral-800 pt-4 font-bold">
                        <span>Total</span>
                        <span class="text-blue-300">${{ number_format($cartSummary['total'], 2, ',', '.') }}</span>
                    </div>
                    @if ($cartSummary['hasIssues'])
                        <p class="mt-4 text-sm text-amber-200">Corrige los productos marcados antes de revisar el pedido.</p>
                    @else
                        <a href="{{ route('smart.order.review') }}" class="mt-5 flex justify-center rounded-xl bg-blue-500 px-4 py-3 text-center font-bold text-neutral-950 transition hover:bg-blue-400">
                            Revisar pedido
                        </a>
                    @endif
                </aside>
            </div>
        @endif
    </div>
</main>
@endsection
