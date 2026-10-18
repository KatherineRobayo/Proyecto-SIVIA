@extends('layouts.smart-order')

@section('title', 'SIVIA POS')

@section('content')
<div class="relative min-h-screen overflow-hidden bg-neutral-950 px-4 py-16 text-white sm:px-6 lg:px-8">
    <div class="pointer-events-none fixed inset-0 opacity-20" aria-hidden="true">
        <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-blue-500/20 blur-[140px]"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-amber-400/10 blur-[140px]"></div>
    </div>

    <main class="relative z-10 mx-auto max-w-5xl pt-8">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-neutral-400 transition hover:text-blue-300">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            Volver al inicio
        </a>

        @isset($selectedBusiness)
            <section class="mt-8 rounded-3xl border border-blue-900/30 bg-neutral-900/80 p-6 shadow-2xl backdrop-blur-xl sm:p-10">
                <div class="mb-8 flex flex-col gap-6 border-b border-blue-900/20 pb-8 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Negocio seleccionado</p>
                        <h1 class="font-serif text-3xl font-bold text-white sm:text-4xl">{{ $selectedBusiness->nombre }}</h1>
                        @if ($selectedBusiness->direccion)
                            <p class="mt-2 text-neutral-400">{{ $selectedBusiness->direccion }}</p>
                        @endif
                    </div>
                    <a href="{{ route('smart.order') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-500/30 px-5 py-3 font-semibold text-blue-300 transition hover:bg-blue-500/10">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">storefront</span>
                        Cambiar negocio
                    </a>
                </div>

                <div class="rounded-2xl border border-neutral-800 bg-neutral-950/80 p-8 text-center sm:p-12">
                    <span class="material-symbols-outlined text-5xl text-blue-400" aria-hidden="true">inventory_2</span>
                    <h2 class="mt-4 font-serif text-2xl font-bold text-white">Catálogo de {{ $selectedBusiness->nombre }}</h2>
                    <p class="mx-auto mt-3 max-w-xl leading-relaxed text-neutral-400">
                        Este espacio queda asociado exclusivamente a este negocio. Los productos de su catálogo se prepararán aquí en la siguiente fase.
                    </p>
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
@endsection
