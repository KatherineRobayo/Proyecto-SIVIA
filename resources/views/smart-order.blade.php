@extends('layouts.smart-order')

@section('title', 'SIVIA POS')

@section('content')
<div class="relative min-h-screen overflow-hidden bg-neutral-950 px-4 pb-8 pt-5 text-white sm:px-6 lg:px-8 lg:pt-6">
    <div class="pointer-events-none fixed inset-0 opacity-20" aria-hidden="true">
        <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-blue-500/20 blur-[140px]"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-amber-400/10 blur-[140px]"></div>
    </div>

    <main class="relative z-10 mx-auto max-w-screen-2xl pt-0">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-neutral-400 transition hover:text-blue-300">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            Volver al inicio
        </a>

        @if (! isset($selectedBusiness) && ! empty($smartOrderError))
            <p class="mt-6 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-200" role="alert">
                {{ $smartOrderError }}
            </p>
        @endif

        @if (isset($selectedBusiness))
            @php
                $cartItems = $cartSummary['items'] ?? collect();
                $cartHasIssues = $cartSummary['hasIssues'] ?? false;
            @endphp
            @if (session('success'))
                <p class="mt-6 rounded-xl border border-blue-400/20 bg-blue-400/5 px-4 py-3 text-sm text-blue-200" role="status">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <section class="mt-8">
                <div class="mx-auto mb-10 flex flex-col items-center gap-4 text-center">
                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Negocio seleccionado</p>
                        <h1 class="font-serif text-2xl font-bold text-white sm:text-3xl">{{ $selectedBusiness->nombre }}</h1>
                        @if ($selectedBusiness->direccion)
                            <p class="mt-2 text-neutral-400">{{ $selectedBusiness->direccion }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('smart.order.assistant') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-500/15 px-5 py-3 font-semibold text-blue-200 ring-1 ring-blue-400/30 transition hover:bg-blue-500/25">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">auto_awesome</span>
                            ✨ ¿Necesitas ayuda para pedir?
                        </a>
                        <a href="{{ route('smart.order') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-500/30 px-5 py-3 font-semibold text-blue-300 transition hover:bg-blue-500/10">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">storefront</span>
                            Cambiar negocio
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <section class="lg:col-span-8" aria-labelledby="catalog-title">
                    <div class="mb-4">
                        <h2 id="catalog-title" class="font-serif text-2xl font-bold text-white">Catálogo</h2>
                        <p class="mt-2 text-sm text-neutral-400">Productos disponibles de {{ $selectedBusiness->nombre }}.</p>
                    </div>

                    @if (! empty($catalogError))
                        <div class="rounded-2xl border border-red-500/20 bg-red-500/10 px-5 py-6 text-center text-sm text-red-200" role="alert">
                            {{ $catalogError }}
                        </div>
                    @else
                        <form method="GET" action="{{ route('smart.order.catalog') }}" class="mb-6 flex flex-col gap-2 sm:flex-row">
                            <label class="sr-only" for="catalog-search">Buscar productos o categorías</label>
                            <input id="catalog-search" type="search" name="search" value="{{ request('search') }}"
                                   placeholder="Buscar productos o categorías"
                                   class="min-w-0 flex-1 rounded-xl border border-blue-900/30 bg-neutral-950 px-3 py-2.5 text-sm text-white placeholder:text-neutral-600 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:px-4">
                            <label class="sr-only" for="catalog-category">Filtrar por categoría</label>
                            <select id="catalog-category" name="categoria"
                                    class="w-full shrink-0 rounded-xl border border-blue-900/30 bg-neutral-950 px-2 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:w-48 sm:px-4">
                                <option value="">Todas las categorías</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) request('categoria') === (string) $category->id)>
                                        {{ $category->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="shrink-0 rounded-xl bg-blue-500 px-3 py-2.5 text-sm font-semibold text-neutral-950 transition hover:bg-blue-400 sm:px-5">
                                Buscar
                            </button>
                        </form>

                        @if ($products->isEmpty())
                            <div class="rounded-2xl border border-neutral-800 bg-neutral-950/80 p-8 text-center sm:p-12">
                                <span class="material-symbols-outlined text-5xl text-blue-400" aria-hidden="true">inventory_2</span>
                                <h3 class="mt-4 font-serif text-xl font-bold text-white">No hay productos disponibles</h3>
                                <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-neutral-400">
                                    @if (request()->filled('search') || request()->filled('categoria'))
                                        No encontramos productos disponibles con esos filtros. Prueba otra búsqueda o categoría.
                                    @else
                                        Este negocio todavía no tiene productos activos con existencias.
                                    @endif
                                </p>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                                @foreach ($products as $product)
                                    @php
                                        $cartItem = $cartItems->firstWhere('productId', $product->id);
                                    @endphp
                                    <article class="flex flex-col overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-950/80">
                                        @if ($product->imagen)
                                            <img src="{{ asset('storage/' . $product->imagen) }}" alt="{{ $product->nombre }}"
                                                 loading="lazy" class="h-40 w-full object-cover">
                                        @else
                                            <div class="flex h-40 w-full items-center justify-center bg-neutral-900 text-blue-300" aria-hidden="true">
                                                <span class="material-symbols-outlined text-4xl">inventory_2</span>
                                            </div>
                                        @endif
                                        <div class="flex min-w-0 flex-1 flex-col p-4">
                                            <div>
                                                @if ($product->categoria)
                                                    <span class="block truncate text-[10px] uppercase tracking-widest text-blue-400/70">
                                                        {{ $product->categoria->nombre }}
                                                    </span>
                                                @endif
                                                <h3 class="mt-2 truncate font-serif text-lg font-bold text-white">{{ $product->nombre }}</h3>
                                                <p data-product-price class="mt-1 text-xl font-bold leading-tight text-blue-300">
                                                    ${{ number_format((float) $product->precio_venta, 0, ',', '.') }}
                                                </p>
                                                <p class="mt-1 text-xs text-neutral-400">Disponible · {{ $product->stock }}</p>
                                            </div>
                                            <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-4">
                                                <details class="group relative">
                                                    <summary class="cursor-pointer list-none rounded-lg border border-neutral-700 px-2.5 py-1.5 text-xs font-semibold text-neutral-300 transition hover:border-blue-400/50 hover:text-blue-200">
                                                        Personalizar
                                                    </summary>
                                                    <div class="absolute bottom-full left-0 z-10 mb-2 w-56 rounded-xl border border-neutral-700 bg-neutral-900 p-3 shadow-xl">
                                                        <label class="text-xs font-medium text-neutral-300" for="note-{{ $product->id }}">Nota del producto</label>
                                                        <textarea id="note-{{ $product->id }}" rows="2" maxlength="160" placeholder="Preferencias para este producto"
                                                                  class="mt-1 w-full resize-none rounded-lg border border-neutral-700 bg-neutral-950 px-2.5 py-2 text-xs text-white placeholder:text-neutral-600 focus:border-blue-500 focus:outline-none"></textarea>
                                                        <p class="mt-1 text-[10px] text-neutral-500">Las notas son visuales y no se guardan en el pedido.</p>
                                                    </div>
                                                </details>
                                                <div data-cart-quantity-controls data-product-id="{{ $product->id }}" data-stock="{{ $product->stock }}" class="inline-flex items-center rounded-lg border border-blue-500/30 bg-blue-500/10" aria-label="Cantidad de {{ $product->nombre }}" style="display: {{ $cartItem ? 'inline-flex' : 'none' }}">
                                                    <form method="POST" action="{{ route('smart.order.cart.update', $product->id) }}"
                                                          data-cart-step="down" data-update-url="{{ route('smart.order.cart.update', $product->id) }}" data-remove-url="{{ route('smart.order.cart.remove', $product->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="quantity" value="{{ $cartItem ? $cartItem['quantity'] - 1 : 0 }}">
                                                        <button type="submit" aria-label="Reducir {{ $product->nombre }}" class="grid h-8 w-8 place-items-center rounded-l-lg text-blue-100 transition hover:bg-blue-500/20">−</button>
                                                    </form>
                                                    <span data-cart-item-quantity class="min-w-7 text-center text-sm font-bold text-white">{{ $cartItem['quantity'] ?? 0 }}</span>
                                                    <form method="POST" action="{{ route('smart.order.cart.update', $product->id) }}"
                                                          data-cart-step="up" data-update-url="{{ route('smart.order.cart.update', $product->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="quantity" value="{{ $cartItem ? $cartItem['quantity'] + 1 : 1 }}">
                                                        <button type="submit" aria-label="Aumentar {{ $product->nombre }}" @disabled($cartItem && $cartItem['quantity'] >= $product->stock)
                                                                class="grid h-8 w-8 place-items-center rounded-r-lg text-blue-100 transition hover:bg-blue-500/20 disabled:cursor-not-allowed disabled:opacity-40">+</button>
                                                    </form>
                                                </div>
                                                <form method="POST" action="{{ route('smart.order.cart.add') }}" data-cart-add-form data-stock="{{ $product->stock }}" style="display: {{ $cartItem ? 'none' : 'block' }}">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="rounded-lg bg-blue-500 px-3 py-2 text-xs font-bold text-neutral-950 transition hover:bg-blue-400" @disabled($product->stock < 1)>
                                                        + Agregar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            @if ($products->hasPages())
                                <nav class="mt-6" aria-label="Páginas del catálogo">
                                    {{ $products->links() }}
                                </nav>
                            @endif
                        @endif
                    @endif
                </section>
            @php
                $orderSubtotal = $cartSummary['total'] / 1.19;
                $orderTax = $cartSummary['total'] - $orderSubtotal;
            @endphp
            <aside class="h-fit rounded-2xl border border-neutral-800 bg-neutral-900 p-5 shadow-xl shadow-black/20 lg:col-span-4"
                   aria-label="Tu Pedido">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg text-blue-300" aria-hidden="true">shopping_bag</span>
                        <h2 class="font-serif text-lg font-bold text-white">Tu Pedido</h2>
                        <span data-cart-quantity-total class="rounded-full bg-blue-500/15 px-2 py-0.5 text-xs font-bold text-blue-200">{{ $cartSummary['quantity'] }}</span>
                    </div>
                    <a href="{{ route('smart.order.assistant') }}" class="text-xs font-semibold text-blue-200 hover:text-blue-100">Asistente</a>
                </div>

                <p data-cart-feedback class="pointer-events-none fixed right-4 top-20 z-50 hidden max-w-sm rounded-lg px-3 py-2 text-xs shadow-xl" role="status" aria-live="polite"></p>
                <div data-cart-empty-state class="py-7 text-center" @if ($cartItems->isNotEmpty()) hidden @endif>
                    <span class="material-symbols-outlined text-4xl text-neutral-600" aria-hidden="true">shopping_cart</span>
                    <p class="mt-2 text-sm text-neutral-400">Agrega productos para comenzar.</p>
                </div>
                <div data-cart-populated @if ($cartItems->isEmpty()) hidden @endif>
                    <ul data-cart-list class="mt-3 max-h-64 space-y-2 overflow-y-auto border-y border-neutral-800 py-3">
                        @foreach ($cartItems as $item)
                            <li data-cart-item data-product-id="{{ $item['productId'] }}" class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-white">{{ $item['product']?->nombre ?? 'Producto no disponible' }}</p>
                                    <p class="text-xs text-neutral-400">$<span data-cart-unit-price>{{ number_format($item['unitPrice'], 2, ',', '.') }}</span> c/u</p>
                                </div>
                                @if ($item['product'] && $item['product']->estado === 'activo')
                                    <div class="inline-flex shrink-0 items-center rounded-lg border border-neutral-700" data-cart-quantity-controls data-product-id="{{ $item['productId'] }}" data-stock="{{ $item['product']->stock }}">
                                        @if ($item['quantity'] === 1)
                                            <form method="POST" action="{{ route('smart.order.cart.remove', $item['productId']) }}"
                                                  data-cart-step="down" data-update-url="{{ route('smart.order.cart.update', $item['productId']) }}" data-remove-url="{{ route('smart.order.cart.remove', $item['productId']) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Quitar {{ $item['product']->nombre }}" class="grid h-7 w-7 place-items-center rounded-l-lg text-neutral-200 hover:bg-neutral-800">−</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('smart.order.cart.update', $item['productId']) }}"
                                                  data-cart-step="down" data-update-url="{{ route('smart.order.cart.update', $item['productId']) }}" data-remove-url="{{ route('smart.order.cart.remove', $item['productId']) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                                <button type="submit" aria-label="Reducir {{ $item['product']->nombre }}" class="grid h-7 w-7 place-items-center rounded-l-lg text-neutral-200 hover:bg-neutral-800">−</button>
                                            </form>
                                        @endif
                                        <span data-cart-item-quantity class="min-w-6 text-center text-xs font-bold">{{ $item['quantity'] }}</span>
                                        <form method="POST" action="{{ route('smart.order.cart.update', $item['productId']) }}"
                                              data-cart-step="up" data-update-url="{{ route('smart.order.cart.update', $item['productId']) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                            <button type="submit" aria-label="Aumentar {{ $item['product']->nombre }}" @disabled($item['quantity'] >= $item['product']->stock)
                                                    class="grid h-7 w-7 place-items-center rounded-r-lg text-neutral-200 hover:bg-neutral-800 disabled:cursor-not-allowed disabled:opacity-40">+</button>
                                        </form>
                                    </div>
                                @endif
                                <span data-cart-unavailable class="text-xs text-amber-200" @if ($item['available']) hidden @endif>No disponible</span>
                            </li>
                        @endforeach
                    </ul>

                    <dl class="mt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between gap-3 text-neutral-400">
                            <dt>Subtotal (antes de IVA)</dt>
                            <dd>$<span data-cart-subtotal>{{ number_format($orderSubtotal, 2, ',', '.') }}</span></dd>
                        </div>
                        <div class="flex justify-between gap-3 text-neutral-400">
                            <dt>IVA (19%, incluido)</dt>
                            <dd>$<span data-cart-tax>{{ number_format($orderTax, 2, ',', '.') }}</span></dd>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-neutral-800 pt-2 font-bold text-white">
                            <dt>Total</dt>
                            <dd class="text-blue-300">$<span data-cart-total>{{ number_format($cartSummary['total'], 2, ',', '.') }}</span></dd>
                        </div>
                    </dl>

                    <form id="checkout-form" method="POST" action="{{ route('smart.order.confirm') }}"
                          data-review-url="{{ route('smart.order.review') }}" class="mt-3 space-y-2.5">
                        @csrf
                        <input type="hidden" name="confirmation_token" value="">
                        <label class="block text-xs font-medium text-neutral-300">
                            Nombre del cliente
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" maxlength="120" required autocomplete="name"
                                   class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white placeholder:text-neutral-600 focus:border-blue-500 focus:outline-none"
                                   placeholder="Tu nombre">
                        </label>
                        <label class="block text-xs font-medium text-neutral-300">
                            Teléfono <span class="font-normal text-neutral-500">(opcional)</span>
                            <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" maxlength="30" autocomplete="tel"
                                   class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white placeholder:text-neutral-600 focus:border-blue-500 focus:outline-none"
                                   placeholder="Tu teléfono">
                        </label>
                        <p id="checkout-error" class="hidden rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-xs text-red-200" role="alert"></p>
                        <p data-cart-issue class="rounded-lg border border-amber-400/20 bg-amber-400/5 px-3 py-2 text-xs text-amber-200" @if (! $cartHasIssues) hidden @endif>Ajusta los productos sin existencias suficientes antes de confirmar.</p>
                        <button id="checkout-button" type="submit" @disabled($cartHasIssues)
                                class="w-full rounded-xl bg-blue-500 px-4 py-2.5 text-sm font-bold text-neutral-950 transition hover:bg-blue-400 disabled:cursor-not-allowed disabled:opacity-50">
                            Confirmar Pedido
                        </button>
                    </form>
                </div>
            </aside>
                </div>
            </section>

        @else
            <section class="mx-auto mt-12 max-w-3xl text-center">
                <div class="mx-auto inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-blue-300">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">storefront</span>
                    Pedidos SIVIA
                </div>
                <h1 class="mt-6 font-serif text-4xl font-bold leading-tight sm:text-6xl">
                    ¿En qué negocio deseas comprar?
                </h1>
                <p class="mx-auto mt-4 max-w-xl text-base leading-relaxed text-neutral-400 sm:text-lg">
                    Busca y selecciona un negocio para continuar a su catálogo.
                </p>
            </section>

            <section class="mx-auto mt-10 max-w-5xl rounded-3xl border border-blue-900/30 bg-neutral-900/80 p-5 shadow-2xl backdrop-blur-xl sm:p-8">
                @if ($businesses->isEmpty())
                    <div class="rounded-2xl border border-amber-400/20 bg-amber-400/5 px-5 py-8 text-center">
                        <span class="material-symbols-outlined text-4xl text-amber-300" aria-hidden="true">storefront</span>
                        <h2 class="mt-3 font-serif text-xl font-bold text-white">No hay negocios disponibles</h2>
                        <p class="mt-2 text-sm text-neutral-400">Cuando haya negocios registrados, aparecerán aquí para que puedas elegir.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('smart.order.business') }}" id="business-form">
                        @csrf

                        <label for="business-search" class="mb-2 block text-xs font-bold uppercase tracking-widest text-neutral-400">Buscar negocio</label>
                        <div class="relative mb-6">
                            <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-neutral-500" aria-hidden="true">search</span>
                            <input id="business-search" type="search" autocomplete="off"
                                   placeholder="Escribe el nombre del negocio"
                                   class="w-full rounded-xl border border-blue-900/30 bg-neutral-950 py-4 pl-12 pr-4 text-white placeholder:text-neutral-600 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        @error('empresa_id')
                            <p class="mb-4 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">Selecciona un negocio válido para continuar.</p>
                        @enderror

                        <fieldset>
                            <legend class="mb-3 text-xs font-bold uppercase tracking-widest text-neutral-500">Negocios disponibles</legend>
                            <div id="business-list" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($businesses as $business)
                                    <label data-business-card data-business-name="{{ mb_strtolower($business->nombre) }}"
                                           class="group flex min-h-44 cursor-pointer flex-col rounded-2xl border border-neutral-800 bg-neutral-950/70 p-5 transition hover:-translate-y-1 hover:border-blue-500/40 hover:bg-blue-500/5 hover:shadow-lg hover:shadow-blue-950/20 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-500/10 has-[:checked]:shadow-lg has-[:checked]:shadow-blue-950/20">
                                        <span class="mb-5 flex items-start justify-between">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300 transition group-hover:border-blue-400/40 group-hover:bg-blue-500/15">
                                                <span class="material-symbols-outlined" aria-hidden="true">storefront</span>
                                            </span>
                                            <input class="mt-1 h-5 w-5 accent-blue-500 focus:ring-blue-500" type="radio" name="empresa_id" value="{{ $business->id }}" required aria-label="Seleccionar {{ $business->nombre }}">
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-serif text-lg font-bold text-white">{{ $business->nombre }}</span>
                                            @if ($business->direccion)
                                                <span class="mt-2 block truncate text-sm text-neutral-500">{{ $business->direccion }}</span>
                                            @else
                                                <span class="mt-2 block text-sm text-neutral-500">Negocio disponible</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <p id="business-empty" class="hidden rounded-xl border border-neutral-800 px-4 py-6 text-center text-sm text-neutral-500">
                                No encontramos negocios con ese nombre.
                            </p>
                        </fieldset>

                        <button id="continue-button" type="submit" disabled
                                class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-blue-500 px-6 py-4 font-bold text-neutral-950 shadow-lg shadow-blue-950/30 transition hover:bg-blue-400 disabled:cursor-not-allowed disabled:opacity-40">
                            Continuar al catálogo
                            <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                        </button>
                    </form>
                @endif
            </section>
        @endisset
    </main>
</div>

@if (! isset($selectedBusiness) && $businesses->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('business-search');
            const cards = [...document.querySelectorAll('[data-business-card]')];
            const emptyState = document.getElementById('business-empty');
            const continueButton = document.getElementById('continue-button');

            search.addEventListener('input', () => {
                const query = search.value.trim().toLocaleLowerCase();
                let visibleCards = 0;

                cards.forEach((card) => {
                    const matches = card.dataset.businessName.includes(query);
                    card.classList.toggle('hidden', !matches);
                    visibleCards += matches ? 1 : 0;
                });

                emptyState.classList.toggle('hidden', visibleCards > 0);
            });

            document.querySelectorAll('input[name="empresa_id"]').forEach((input) => {
                input.addEventListener('change', () => {
                    continueButton.disabled = false;
                });
            });
        });
    </script>
@endif
@if (isset($selectedBusiness))
    <script>
        let cartMutationPending = false;

        let cartFeedbackTimer;

        const setCartFeedback = (message, isError = false) => {
            const feedback = document.querySelector('[data-cart-feedback]');
            window.clearTimeout(cartFeedbackTimer);
            feedback.textContent = message;
            feedback.classList.remove('hidden', 'border', 'border-red-500/20', 'bg-red-500/10', 'text-red-200', 'border-blue-400/20', 'bg-blue-400/5', 'text-blue-200');
            feedback.classList.add('border', ...(isError
                ? ['border-red-500/20', 'bg-red-500/10', 'text-red-200']
                : ['border-blue-400/20', 'bg-blue-400/5', 'text-blue-200']));
            feedback.setAttribute('role', isError ? 'alert' : 'status');
            if (!isError) {
                cartFeedbackTimer = window.setTimeout(() => feedback.classList.add('hidden'), 2500);
            }
        };

        const syncCartControls = () => {
            document.querySelectorAll('[data-cart-quantity-controls]').forEach((controls) => {
                const quantity = Number(controls.querySelector('[data-cart-item-quantity]')?.textContent ?? 0);
                const stock = Number(controls.dataset.stock ?? 0);
                controls.querySelectorAll('form[data-cart-step] button').forEach((button) => {
                    button.disabled = cartMutationPending || (button.form.dataset.cartStep === 'up' && quantity >= stock);
                });
            });
            document.querySelectorAll('[data-cart-add-form] button').forEach((button) => {
                button.disabled = cartMutationPending || Number(button.form.dataset.stock) < 1;
            });
        };

        const createCartStepForm = (item, step, csrfToken) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = item.updateUrl;
            form.dataset.cartStep = step;
            form.dataset.updateUrl = item.updateUrl;
            form.dataset.removeUrl = item.removeUrl;
            form.className = 'shrink-0';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = csrfToken;
            form.append(token);

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            form.append(method);

            const quantity = document.createElement('input');
            quantity.type = 'hidden';
            quantity.name = 'quantity';
            // AQUÍ ESTÁ EL ARREGLO: Paréntesis para que calcule bien la resta
            quantity.value = String(step === 'up' ? (item.quantity + 1) : (item.quantity - 1));
            form.append(quantity);

            const button = document.createElement('button');
            button.type = 'button'; 
            button.value = ''; // Evita que envíe valores basura
            button.setAttribute('aria-label', `${step === 'up' ? 'Aumentar' : 'Reducir'} ${item.productName}`);
            button.className = `grid h-7 w-7 place-items-center text-neutral-200 hover:bg-neutral-800 ${step === 'up' ? 'rounded-r-lg' : 'rounded-l-lg'}`;
            button.textContent = step === 'up' ? '+' : '−';
    
            button.addEventListener('click', () => form.requestSubmit());
    
            form.append(button);
    
            return form;
        };

        const createCartRow = (item, csrfToken) => {
            const row = document.createElement('li');
            row.dataset.cartItem = '';
            row.dataset.productId = String(item.productId);
            row.className = 'flex items-center justify-between gap-2';

            const details = document.createElement('div');
            details.className = 'min-w-0';
            const name = document.createElement('p');
            name.className = 'truncate text-sm font-medium text-white';
            name.textContent = item.productName;
            const unitPrice = document.createElement('p');
            unitPrice.className = 'text-xs text-neutral-400';
            unitPrice.append('$');
            const unitPriceValue = document.createElement('span');
            unitPriceValue.dataset.cartUnitPrice = '';
            unitPriceValue.textContent = item.unitPrice;
            unitPrice.append(unitPriceValue, ' c/u');
            details.append(name, unitPrice);
            row.append(details);

            if (item.active) {
                const controls = document.createElement('div');
                controls.dataset.cartQuantityControls = '';
                controls.dataset.productId = String(item.productId);
                controls.dataset.stock = String(item.stock);
                controls.className = 'inline-flex shrink-0 items-center rounded-lg border border-neutral-700';
                controls.append(createCartStepForm(item, 'down', csrfToken));

                const quantity = document.createElement('span');
                quantity.dataset.cartItemQuantity = '';
                quantity.className = 'min-w-6 text-center text-xs font-bold';
                quantity.textContent = String(item.quantity);
                controls.append(quantity, createCartStepForm(item, 'up', csrfToken));
                row.append(controls);
            }

            const unavailable = document.createElement('span');
            unavailable.dataset.cartUnavailable = '';
            unavailable.className = 'text-xs text-amber-200';
            unavailable.hidden = item.available;
            unavailable.textContent = 'No disponible';
            row.append(unavailable);
            return row;
        };

        const updateCartDisplay = (data) => {
            const items = data.items;
            document.querySelector('[data-cart-quantity-total]').textContent = data.quantity;
            document.querySelector('[data-cart-subtotal]').textContent = data.subtotal;
            document.querySelector('[data-cart-tax]').textContent = data.tax;
            document.querySelector('[data-cart-total]').textContent = data.total;

            document.querySelectorAll('[data-cart-quantity-controls]').forEach((controls) => {
                const id = controls.dataset.productId;
                const item = items[id];
                const card = controls.closest('article');
                if (!item) {
                    controls.style.display = 'none';
                    const addForm = card?.querySelector('[data-cart-add-form]');
                    if (addForm) addForm.style.display = 'block';
                    return;
                }

                controls.style.display = 'inline-flex';
                controls.dataset.stock = item.stock;
                const addForm = card?.querySelector('[data-cart-add-form]');
                if (addForm) {
                    addForm.dataset.stock = item.stock;
                    addForm.style.display = 'none';
                }
                controls.querySelector('[data-cart-item-quantity]').textContent = item.quantity;
                const price = card?.querySelector('[data-product-price]');
                if (price) price.textContent = `$${item.catalogPrice}`;
            });

            document.querySelectorAll('[data-cart-item]').forEach((row) => {
                const item = items[row.dataset.productId];
                if (!item) {
                    row.remove();
                    return;
                }

                const quantity = row.querySelector('[data-cart-item-quantity]');
                if (quantity) quantity.textContent = item.quantity;
                row.querySelector('[data-cart-unit-price]').textContent = item.unitPrice;
                const controls = row.querySelector('[data-cart-quantity-controls]');
                if (controls) controls.dataset.stock = item.stock;
                const unavailable = row.querySelector('[data-cart-unavailable]');
                if (unavailable) unavailable.hidden = item.available;
            });

            const csrfToken = document.querySelector('[data-cart-add-form] input[name="_token"]')?.value
                ?? document.querySelector('meta[name="csrf-token"]')?.content
                ?? '';
            const cartList = document.querySelector('[data-cart-list]');
            Object.values(items).forEach((item) => {
                const existingRow = cartList.querySelector(`[data-cart-item][data-product-id="${item.productId}"]`);
                if (!existingRow) cartList.append(createCartRow(item, csrfToken));
            });

            const hasItems = Object.keys(items).length > 0;
            document.querySelector('[data-cart-empty-state]').hidden = hasItems;
            document.querySelector('[data-cart-populated]').hidden = !hasItems;
            const checkoutButton = document.getElementById('checkout-button');
            if (checkoutButton) checkoutButton.disabled = data.hasIssues;
            const cartIssue = document.querySelector('[data-cart-issue]');
            if (cartIssue) cartIssue.hidden = !data.hasIssues;
            syncCartControls();
            setCartFeedback(data.message);
        };

        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('form[data-cart-step], form[data-cart-add-form]');
            if (!form) return;

            event.preventDefault();
            if (cartMutationPending) return;

            const requestData = new FormData(form);
            let url = form.action;
            let removeItem = false;
            if (form.dataset.cartStep) {
                const controls = form.closest('[data-cart-quantity-controls]');
                const quantity = Number(controls.querySelector('[data-cart-item-quantity]').textContent);
                const nextQuantity = quantity + (form.dataset.cartStep === 'up' ? 1 : -1);
                removeItem = nextQuantity < 1;
                url = removeItem ? form.dataset.removeUrl : form.dataset.updateUrl;
                requestData.set('_method', removeItem ? 'DELETE' : 'PUT');
                if (!removeItem) requestData.set('quantity', String(nextQuantity));
            }

            cartMutationPending = true;
            syncCartControls();
            setCartFeedback('Actualizando cantidad...');
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: requestData,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const contentType = response.headers.get('content-type') ?? '';
                if (!contentType.toLowerCase().includes('application/json')) {
                    console.warn('Unexpected smart-order cart response', {
                        status: response.status,
                        contentType,
                        redirected: response.redirected,
                    });
                    throw new Error(response.redirected
                        ? 'La sesión del pedido expiró. Recarga la página e inténtalo de nuevo.'
                        : 'El servidor devolvió una respuesta inesperada. Intenta de nuevo.');
                }

                let data;
                try {
                    data = await response.json();
                } catch (parseError) {
                    console.warn('Invalid smart-order cart JSON response', { status: response.status });
                    throw new Error('No se pudo interpretar la respuesta del carrito. Intenta de nuevo.');
                }
                if (!response.ok) {
                    const validationMessage = Object.values(data.errors ?? {}).flat()[0];
                    throw new Error(validationMessage || data.message || 'No se pudo actualizar la cantidad.');
                }
                if (!data || typeof data !== 'object' || !data.items || typeof data.total !== 'string') {
                    console.warn('Unexpected smart-order cart JSON shape', { status: response.status });
                    throw new Error('El servidor devolvió datos incompletos para el carrito. Intenta de nuevo.');
                }

                updateCartDisplay(data);
            } catch (error) {
                if (!(error instanceof Error)) {
                    console.warn('Unexpected smart-order cart request failure');
                }
                setCartFeedback(error.message || 'No se pudo actualizar la cantidad. Intenta de nuevo.', true);
            } finally {
                cartMutationPending = false;
                syncCartControls();
            }
        });

        syncCartControls();
    </script>
@endif
@if (isset($selectedBusiness))
    <script>
        const checkoutForm = document.getElementById('checkout-form');
        checkoutForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = document.getElementById('checkout-button');
            const error = document.getElementById('checkout-error');
            button.disabled = true;
            button.textContent = 'Validando pedido…';
            error.classList.add('hidden');
            error.textContent = '';

            try {
                const response = await fetch(checkoutForm.dataset.reviewUrl, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    throw new Error('No fue posible validar el pedido.');
                }

                const reviewHtml = await response.text();
                const reviewDocument = new DOMParser().parseFromString(reviewHtml, 'text/html');
                const token = reviewDocument.querySelector('input[name="confirmation_token"]')?.value;
                if (!token) {
                    throw new Error('Revisa la disponibilidad de los productos antes de confirmar.');
                }

                checkoutForm.querySelector('input[name="confirmation_token"]').value = token;
                button.textContent = 'Enviando pedido…';
                checkoutForm.submit();
            } catch (checkoutError) {
                error.textContent = checkoutError.message || 'No se pudo iniciar la confirmación. Intenta de nuevo.';
                error.classList.remove('hidden');
                button.disabled = false;
                button.textContent = 'Confirmar Pedido';
            }
        });
    </script>
@endif
