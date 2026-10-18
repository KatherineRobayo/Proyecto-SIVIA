@extends('layouts.app')
@section('title', 'Sivia Pos | Sistema Pos Inteliente de Ventas e Inventario')

@push('styles')
<style>
    /* ── Estado inicial de elementos animados ── */
    .hero-badge,
    .hero-title,
    .hero-desc,
    .hero-cta > * {
        opacity: 0;
    }
    .reveal-heading { opacity: 0; }
    .reveal-card-item { opacity: 0; }
    .stats-item { opacity: 0; }

    /* ── Scroll-to-top transition ── */
    #scroll-top {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    /* ── Glow pulsante en botón primario ── */
    @keyframes blue-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.3); }
        50%       { box-shadow: 0 0 20px 6px rgba(255, 215, 0, 0.15); }
    }
    .btn-primary-glow { animation: blue-pulse 3s ease-in-out infinite; }

    /* ── Shimmer en navbar brand al hover ── */
    .brand-shimmer {
        background: linear-gradient(90deg, #ffd700 0%, #fff8dc 50%, #ffd700 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        transition: background-position 0.4s ease;
    }
    .brand-shimmer:hover { background-position: right center; }

    /* ── Barra de progreso decorativa en módulos ── */
    .module-bar { transition: width 0.6s cubic-bezier(.4,0,.2,1); }

    /* ── Reactive cards ─────────────────────────────────────── */
    .reactive-card {
        position: relative;
        background: rgba(10, 10, 10, 0.85); /* antes era muy transparente */
        border: 1px solid rgba(59, 130, 246, 0.25); /* azul suave */
        will-change: transform;
        transition: border-color 0.35s ease, box-shadow 0.35s ease;
    }
    .reactive-card:hover {
        border-color: rgba(255, 215, 0, 0.22);
        box-shadow: 0 24px 64px rgba(0,0,0,0.5),
                    0 0 0 1px rgba(255,215,0,0.08),
                    inset 0 1px 0 rgba(255,215,0,0.06);
    }
    /* glow interno que sigue al cursor */
    .card-glow {
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.4s ease;
        background: radial-gradient(
            circle at var(--gx, 50%) var(--gy, 50%),
            rgba(255, 215, 0, 0.11) 0%,
            rgba(255, 215, 0, 0.04) 38%,
            transparent 68%
        );
        z-index: 0;
    }
    .reactive-card:hover .card-glow { opacity: 1; }
    /* todo el contenido sobre el glow */
    .reactive-card > *:not(.card-glow) { position: relative; z-index: 1; }
    /* spring al soltar */
    .reactive-card.returning {
        transition: transform 0.65s cubic-bezier(0.34, 1.56, 0.64, 1),
                    border-color 0.35s ease, box-shadow 0.35s ease;
    }
</style>
@endpush

@section('content')

<x-golden-stardust />

{{-- ── Scroll to Top ───────────────────────────────────────────────────── --}}
<button id="scroll-top"
        class="fixed bottom-8 right-8 z-50 w-11 h-11 rounded-full
               bg-blue-500/20 border border-blue-500/40 text-blue-400
               flex items-center justify-center
               hover:bg-blue-500/30 hover:scale-110 transition-all
               opacity-0 pointer-events-none shadow-lg shadow-blue-900/20">
    <span class="material-symbols-outlined text-lg">arrow_upward</span>
</button>

{{-- ── NAVBAR ──────────────────────────────────────────────────────────── --}}
<nav id="main-nav"
     class="fixed top-0 w-full flex flex-wrap items-center justify-between px-6 h-16
        - bg-neutral-950/80 backdrop-blur-md
        + bg-neutral-950 
        
            border-b border-blue-900/30 shadow-2xl shadow-black/50
            transition-colors duration-300">

    {{-- Brand --}}
    <div class="brand-shimmer text-2xl font-bold italic font-serif tracking-tight cursor-default">
        Sivia pos
    </div>

    {{-- Desktop links --}}
    <div class="hidden md:flex items-center gap-8 font-serif text-sm">
        <a href="#benefits"
           class="text-neutral-400 hover:text-blue-300 px-3 py-1 transition-colors hover:bg-white/5 rounded">
            Beneficios
        </a>
        <a href="#features"
           class="text-neutral-400 hover:text-blue-300 px-3 py-1 transition-colors hover:bg-white/5 rounded">
            Módulos
        </a>
        <a href="#cta"
           class="text-neutral-400 hover:text-blue-300 px-3 py-1 transition-colors hover:bg-white/5 rounded">
            Contacto
        </a>
    </div>

    {{-- Auth + hamburger --}}
    <div class="flex items-center gap-4">
        @auth
            <span class="hidden md:block text-xs font-bold text-blue-500/70 uppercase tracking-widest">
                {{ auth()->user()->name }}
            </span>
            <a href="{{ route('dashboard') }}"
               class="px-5 py-2 bg-primary-container text-on-primary-container font-bold text-sm
                      rounded-lg hover:brightness-110 transition-all shadow-lg shadow-blue-900/20">
                Dashboard
            </a>
        @else
            <a href="{{ route('login') }}"
               class="hidden md:block text-neutral-400 hover:text-blue-300 font-serif text-sm transition-colors">
                Iniciar sesión
            </a>
            <a href="{{ route('login') }}"
               class="px-5 py-2 bg-primary-container text-on-primary-container font-bold text-sm
                      rounded-lg hover:brightness-110 transition-all shadow-lg shadow-blue-900/20 btn-primary-glow">
                Acceder
            </a>
        @endauth

        {{-- Hamburger --}}
        <button id="menu-btn"
                class="md:hidden p-1.5 rounded-lg text-neutral-400 hover:text-blue-400
                       hover:bg-blue-500/10 transition-all border border-transparent
                       hover:border-blue-900/30">
            <span id="icon-open"  class="material-symbols-outlined">menu</span>
            <span id="icon-close" class="material-symbols-outlined hidden">close</span>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-menu"
         class="hidden w-full border-t border-blue-900/20 bg-neutral-950/95 py-4 px-2 space-y-1 md:hidden">
        <a href="#benefits"
           class="block px-4 py-3 text-sm text-neutral-400 hover:text-blue-300
                  hover:bg-blue-500/5 rounded-lg transition-colors">
            Beneficios
        </a>
        <a href="#features"
           class="block px-4 py-3 text-sm text-neutral-400 hover:text-blue-300
                  hover:bg-blue-500/5 rounded-lg transition-colors">
            Módulos
        </a>
        <a href="#cta"
           class="block px-4 py-3 text-sm text-neutral-400 hover:text-blue-300
                  hover:bg-blue-500/5 rounded-lg transition-colors">
            Contacto
        </a>
        @guest
        <div class="pt-2 border-t border-blue-900/20 mt-2">
            <a href="{{ route('login') }}"
               class="block px-4 py-3 text-sm font-bold text-blue-500
                      hover:bg-blue-500/5 rounded-lg transition-colors">
                Iniciar sesión →
            </a>
        </div>
        @endguest
    </div>
</nav>

{{-- ── HERO ─────────────────────────────────────────────────────────────── --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-16">

    <div class="absolute inset-0 z-0">
        <img src="{{ asset('images/hero-kitchen.webp') }}"
             alt="Cocina de alta gama"
             class="w-full h-full object-cover opacity-40"
             onerror="this.style.display='none'">
        <div class="absolute inset-0 bg-gradient-to-t from-background via-background/60 to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-5xl px-6 text-center space-y-8">

        <div class="hero-badge inline-flex items-center gap-2 px-4 py-1.5 rounded-full
                    border border-blue-500/40 bg-blue-500/10
                    text-blue-400 text-xs font-bold tracking-widest uppercase mb-4">
            El Estándar de tu Negocio en Gestión
        </div>

        <h1 class="hero-title text-5xl md:text-7xl lg:text-8xl font-serif font-extrabold
                   tracking-tight text-primary-fixed leading-[1.1]
                   drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
            Domina tu <br>
            <span class="text-blue-500 italic">Negocio con Sivia Pos</span>
        </h1>

        <p class="hero-desc max-w-2xl mx-auto text-lg md:text-xl text-on-surface-variant font-serif">
            El sistema POS inteligente diseñado para MiPymes.
            Control de ventas,Inventario en tiempo real, cálculos automatizados e inteligencia operativa con IA.
        </p>

        <div class="hero-cta flex flex-col sm:flex-row items-center justify-center gap-6 pt-8">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="px-10 py-4 bg-primary-container text-on-primary-container font-bold
                          rounded-lg hover:bg-primary-fixed transition-all btn-primary-glow
                          shadow-xl shadow-blue-900/20 active:opacity-80 hover:scale-105">
                    Ir al Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-10 py-4 border border-blue-500/30 text-blue-400 font-bold
                          rounded-lg hover:bg-blue-500/10 transition-all active:opacity-80">
                    Acceder al Sistema
                </a>
                <a href="#benefits"
                   class="px-10 py-4 border border-blue-500/30 text-blue-400 font-bold
                          rounded-lg hover:bg-blue-500/10 transition-all active:opacity-80">
                    Explorar Módulos
                </a>

                {{-- BOTÓN IA PÚBLICO --}}
                <a href="{{ route('smart.order') }}"
                    class="px-10 py-4 border border-blue-400/40 text-blue-300 font-bold
                        rounded-lg bg-blue-400/10 hover:bg-blue-400/20
                        -all shadow-xl shadow-amber-900/20
                        hover:scale-105 group relative overflow-hidden">
                <span class="relative z-10 flex items-center gap-2">
                <span class="material-symbols-outlined text-lg group-hover:rotate-12 transition-transform">
                    auto_awesome
                </span>
                Hacer pedido
                </span>
            <span class="absolute inset-0 bg-gradient-to-r from-transparent via-amber-300/10 to-transparent
                         translate-x-[-120%] group-hover:translate-x-[120%]
                         transition-transform duration-1000"></span>
        </a>
            @endauth
        </div>

    </div>
</section>

{{-- ── STATS BAR ────────────────────────────────────────────────────────── --}}
<section id="stats-bar"
         class="relative z-10 py-10 border-y border-blue-900/20 - bg-neutral-950/70 backdrop-blur-md" + bg-neutral-950>
    <div class="max-w-4xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-0">
        @php
        $stats = [
            ['value' => '6',    'suffix' => '',  'label' => 'Módulos del Sistema'],
            ['value' => '2',    'suffix' => '',  'label' => 'Roles de Acceso'],
            ['value' => '100',  'suffix' => '%', 'label' => 'Moneda COP Nativa'],
            ['value' => 'RT',   'suffix' => '',  'label' => 'Datos en Tiempo Real'],
        ];
        @endphp
        @foreach($stats as $s)
        <div class="stats-item text-center py-2
                    {{ !$loop->last ? 'border-r border-blue-900/20' : '' }}">
            <p class="text-4xl font-headline font-bold text-blue-400 leading-none">
                {{ $s['value'] }}<span class="text-2xl">{{ $s['suffix'] }}</span>
            </p>
            <p class="text-[10px] text-neutral-500 uppercase tracking-widest mt-2">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ── BENEFITS ─────────────────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-6 py-24" id="benefits">

    <div class="reveal-heading text-center mb-16 space-y-4">
        <h2 class="text-4xl md:text-5xl font-serif font-bold text-blue-500">
            Control Total del Negocio
        </h2>
        <p class="text-on-surface-variant max-w-xl mx-auto">
            Cada proceso de tu negocio gestionado desde una sola plataforma inteligente.
        </p>
    </div>

    <div class="reveal-cards grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Card 1: Alertas --}}
        <div class="reveal-card-item md:col-span-2 reactive-card rounded-xl p-8
                    flex flex-col justify-between min-h-[320px] shadow-2xl group">
            <div class="card-glow" aria-hidden="true"></div>
            <div>
                <div class="card-icon h-12 w-12 rounded-lg bg--500/10 border border-blue-500/30
                            flex items-center justify-center text-blue-500 mb-6
                            group-hover:bg-blue-500 group-hover:text-black transition-all duration-300">
                    <span class="material-symbols-outlined">notifications_active</span>
                </div>
                <h3 class="text-2xl font-serif font-bold text-primary mb-3">Alertas Inteligentes</h3>
                <p class="text-on-surface-variant text-lg">
                    Controla tu inventario sin pérdidas. El sistema detecta niveles bajos
                    de productos antes de afectar tus ventas.
                </p>
            </div>
            <div class="mt-8 flex items-center gap-2 text-blue-500 font-bold
                        group-hover:translate-x-2 transition-transform duration-300">
                Gestión de Stock
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </div>

        {{-- Card 2: Cálculo --}}
        <div class="reveal-card-item reactive-card rounded-xl p-8
                    flex flex-col justify-between shadow-2xl group">
            <div class="card-glow" aria-hidden="true"></div>
            <div>
                <div class="card-icon h-12 w-12 rounded-lg bg-blue-500/10 border border-blue-500/30
                            flex items-center justify-center text-blue-500 mb-6
                            group-hover:bg-blue-500 group-hover:text-black transition-all duration-300">
                    <span class="material-symbols-outlined">calculate</span>
                </div>
                <h3 class="text-2xl font-serif font-bold text-primary mb-3">Cálculo Automático</h3>
                <p class="text-on-surface-variant">
                    Cálculo automático de ventas, costos e IVA en tiempo real en COP.
                </p>
            </div>
            <div class="mt-8">
                <div class="w-full h-1 bg-neutral-800 rounded-full overflow-hidden">
                    <div class="module-bar w-3/4 h-full bg-blue-500"></div>
                </div>
            </div>
        </div>

        {{-- Card 3: Inventario --}}
        <div class="reveal-card-item reactive-card rounded-xl p-8
                    flex flex-col justify-between shadow-2xl group">
            <div class="card-glow" aria-hidden="true"></div>
            <div>
                <div class="card-icon h-12 w-12 rounded-lg bg-blue-500/10 border border-blue-500/30
                            flex items-center justify-center text-blue-500 mb-6
                            group-hover:bg-blue-500 group-hover:text-black transition-all duration-300">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
                <h3 class="text-2xl font-serif font-bold text-primary mb-3">Inventario en Vivo</h3>
                <p class="text-on-surface-variant">
                    Inventario actualizado en tiempo real desde cualquier dispositivo y usuario.
                </p>
            </div>
            <div class="mt-6 rounded-lg h-32 bg-neutral-900/50 border border-blue-900/20
                        flex items-center justify-center text-neutral-700
                        group-hover:border-blue-900/40 transition-colors">
                <span class="material-symbols-outlined text-4xl group-hover:text-blue-900 transition-colors">shelves</span>
            </div>
        </div>

        {{-- Card 4: Reportes --}}
        <div class="reveal-card-item md:col-span-2 reactive-card rounded-xl p-8
                    flex items-center gap-8 shadow-2xl overflow-hidden group">
            <div class="card-glow" aria-hidden="true"></div>
            <div class="flex-1">
                <div class="card-icon h-12 w-12 rounded-lg bg-blue-500/10 border border-blue-500/30
                            flex items-center justify-center text-blue-500 mb-6
                            group-hover:bg-blue-500 group-hover:text-black transition-all duration-300">
                    <span class="material-symbols-outlined">analytics</span>
                </div>
                <h3 class="text-2xl font-serif font-bold text-primary mb-3">Reportes Ejecutivos</h3>
                <p class="text-on-surface-variant">
                   Reportes de ventas, movimientos e indicadores clave para la toma de decisiones.
                </p>
            </div>
            <div class="hidden lg:flex w-1/3 aspect-square
                        bg-gradient-to-br from-blue-500/20 to-transparent
                        rounded-full items-center justify-center border border-blue-500/10
                        group-hover:from-blue-500/30 transition-all duration-500">
                <span class="material-symbols-outlined text-6xl text-blue-500/40
                             group-hover:text-blue-500/60 transition-colors">bar_chart</span>
            </div>
        </div>

    </div>
</section>

{{-- ── MODULES ──────────────────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-6 py-16" id="features">

    <div class="reveal-heading text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-primary mb-3">
            Módulos del Sistema
        </h2>
        <p class="text-on-surface-variant">Todo lo que necesitas para gestionar tu negocio en un solo sistema </p>
    </div>

    <div class="reveal-cards grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
        $modules = [
            ['icon' => 'dashboard',       'label' => 'Dashboard',  'desc' => 'Resumen en vivo'],
            ['icon' => 'point_of_sale',   'label' => 'Ventas',     'desc' => 'Registro de ventas'],
            ['icon' => 'receipt_long',    'label' => 'Facturación','desc' => 'Comprobantes'],
            ['icon' => 'inventory_2',     'label' => 'Inventario', 'desc' => 'Stock y alertas'],
            ['icon' => 'restaurant_menu', 'label' => 'Pedidos',    'desc' => 'Proveedores'],
            ['icon' => 'analytics',       'label' => 'Reportes',   'desc' => 'Análisis'],
        ];
        @endphp
        @foreach($modules as $mod)
        <div class="reveal-card-item flex flex-col items-center gap-3 p-6
                    bg-surface-container-low border border-blue-900/20 rounded-xl
                    hover:border-blue-500/40 hover:-translate-y-1.5 hover:shadow-lg
                    hover:shadow-blue-900/20 transition-all duration-300 group">
            <span class="material-symbols-outlined text-3xl text-blue-500/60
                         group-hover:text-blue-500 group-hover:scale-110
                         transition-all duration-300" style="font-variation-settings:'FILL' 0">
                {{ $mod['icon'] }}
            </span>
            <div class="text-center">
                <p class="text-xs font-bold text-neutral-400 uppercase tracking-widest
                           group-hover:text-blue-300 transition-colors">
                    {{ $mod['label'] }}
                </p>
                <p class="text-[9px] text-neutral-600 mt-0.5 group-hover:text-neutral-500 transition-colors">
                    {{ $mod['desc'] }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ── CTA ──────────────────────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-6 py-20" id="cta">
    <div class="reveal-card-item relative rounded-3xl overflow-hidden bg-neutral-950
                border border-blue-900/40 p-12 md:p-20 text-center
                hover:border-blue-900/60 transition-colors duration-500">

        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="h-full w-full bg-[radial-gradient(circle_at_center,_#ffd700_0%,_transparent_70%)]"></div>
        </div>

        <h2 class="text-4xl md:text-5xl font-serif font-bold text-primary leading-tight mb-8">
            ¿Listo para mejorar tu <br>
            <span class="text-blue-500 italic">negocio?</span>
        </h2>
        <p class="text-on-surface-variant max-w-xl mx-auto mb-10 text-lg">
            Accede al sistema con tus credenciales y toma el control total de tus ventas e inventario.
        </p>

        <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="px-12 py-4 bg-blue-500 text-black font-black uppercase tracking-widest
                          rounded-full hover:scale-105 transition-transform btn-primary-glow">
                    Ir al Panel
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-12 py-4 bg-blue-500 text-black font-black uppercase tracking-widest
                          rounded-full hover:scale-105 transition-transform btn-primary-glow">
                    Acceder al Sistema
                </a>
            @endauth
        </div>
    </div>
</section>

{{-- ── FOOTER ───────────────────────────────────────────────────────────── --}}
<footer class="bg-surface-container-lowest border-t border-blue-900/20 py-16 px-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">

        <div class="space-y-4">
            <div class="text-2xl font-bold italic text-blue-500 font-serif">Sivia pos</div>
            <p class="text-on-surface-variant text-sm leading-relaxed">
                Sistema POS inteliente diseñado para MiPymes.
                Control de ventas, inventarioy reportes en una sola plataforma.
            </p>
            <div class="flex items-center gap-2 pt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <span class="text-[10px] text-neutral-500 uppercase tracking-widest">Sistema interno · No público</span>
            </div>
        </div>

        <div class="space-y-5">
            <h4 class="text-blue-500 font-bold font-serif uppercase tracking-widest text-xs">
                Funcionalidades
            </h4>
            @php
            $features = [
                ['icon' => 'inventory_2',     'title' => 'Inventario en Vivo',   'desc' => 'Control total de stock con alertas automáticas de bajo mínimo.'],
                ['icon' => 'restaurant_menu', 'title' => 'Pedidos & Recibos',    'desc' => 'Flujo completo de órdenes a proveedores con IVA y trazabilidad.'],
                ['icon' => 'payments',        'title' => 'Caja & Reportes',      'desc' => 'KPIs financieros en COP, movimientos y análisis de desempeño.'],
                ['icon' => 'manage_accounts', 'title' => 'Control de Roles',     'desc' => 'Acceso diferenciado para Admin y Almacenista.'],
            ];
            @endphp
            @foreach($features as $f)
            <div class="flex items-start gap-3 group">
                <span class="material-symbols-outlined text-blue-500/50 text-base mt-0.5
                             group-hover:text-blue-500 transition-colors flex-shrink-0">{{ $f['icon'] }}</span>
                <div>
                    <p class="text-xs font-bold text-neutral-300">{{ $f['title'] }}</p>
                    <p class="text-[11px] text-neutral-500 leading-relaxed mt-0.5">{{ $f['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="space-y-5">
            <h4 class="text-blue-500 font-bold font-serif uppercase tracking-widest text-xs">
                Stack Tecnológico
            </h4>
            @php
            $stack = [
                ['label' => 'Laravel 12',      'sub' => 'Backend + Eloquent ORM'],
                ['label' => 'MySQL',           'sub' => 'Base de datos relacional'],
                ['label' => 'Blade Templates', 'sub' => 'Motor de vistas nativo'],
                ['label' => 'Tailwind CSS',    'sub' => 'Estilos utilitarios'],
                ['label' => 'Alpine.js',       'sub' => 'Reactividad ligera'],
                ['label' => 'AnimeJS',         'sub' => 'Animaciones y transiciones'],
                ['label' => 'Bootstrap 5',     'sub' => 'Componentes responsivos'],
            ];
            @endphp
            <div class="space-y-3">
                @foreach($stack as $s)
                <div class="flex items-center gap-3">
                    <span class="w-1 h-1 rounded-full bg-blue-500/60 flex-shrink-0"></span>
                    <div>
                        <span class="text-xs font-bold text-neutral-300">{{ $s['label'] }}</span>
                        <span class="text-[10px] text-neutral-600 ml-2">{{ $s['sub'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-5">
            <h4 class="text-blue-500 font-bold font-serif uppercase tracking-widest text-xs">
                Proyecto
            </h4>
            <div class="space-y-2">
                <p class="text-[10px] text-neutral-600 uppercase tracking-widest">Roles del sistema</p>
                <div class="flex flex-col gap-1.5">
                    <span class="inline-flex items-center gap-2 text-xs text-neutral-400">
                        <span class="material-symbols-outlined text-blue-500 text-sm" style="font-variation-settings:'FILL' 1">shield</span>
                        Administrador — Acceso total
                    </span>
                    <span class="inline-flex items-center gap-2 text-xs text-neutral-400">
                        <span class="material-symbols-outlined text-neutral-500 text-sm" style="font-variation-settings:'FILL' 1">person</span>
                        Almacenista — Inventario y movimientos
                    </span>
                </div>
            </div>
            <div class="pt-2 border-t border-blue-900/20">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 text-xs font-bold text-blue-500
                          hover:text-blue-400 transition-colors uppercase tracking-widest">
                    <span class="material-symbols-outlined text-sm">login</span>
                    Iniciar sesión
                </a>
            </div>
        </div>

    </div>

    <div class="max-w-7xl mx-auto pt-10 mt-10
                border-t border-blue-900/10
                flex flex-col md:flex-row justify-between items-center gap-4
                text-xs text-neutral-600">
        <p>&copy; {{ date('Y') }} Sivia Pos</p>
        <p class="text-[10px] uppercase tracking-widest">Laravel 12 · MySQL · Blade · Tailwind</p>
    </div>
</footer>

{{-- Asistente informativo disponible en la página principal --}}
<x-chatbot.widget />

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Hero — animación de entrada en cascada ──────────────────────────
    anime.timeline({ easing: 'easeOutExpo' })
        .add({
            targets: '.hero-badge',
            opacity: [0, 1], translateY: [20, 0],
            duration: 600,
        })
        .add({
            targets: '.hero-title',
            opacity: [0, 1], translateY: [50, 0],
            letterSpacing: ['0.08em', '-0.01em'],
            duration: 1000,
        }, '-=300')
        .add({
            targets: '.hero-desc',
            opacity: [0, 1], translateY: [20, 0],
            duration: 700,
        }, '-=500')
        .add({
            targets: '.hero-cta > *',
            opacity: [0, 1], translateY: [20, 0], scale: [0.92, 1],
            delay: anime.stagger(150),
            duration: 600,
        }, '-=400');

    // ── 2. IntersectionObserver — animaciones al hacer scroll ──────────────
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;

            if (el.id === 'stats-bar') {
                anime({
                    targets: '#stats-bar .stats-item',
                    opacity: [0, 1], translateY: [24, 0],
                    delay: anime.stagger(100),
                    duration: 600, easing: 'easeOutExpo',
                });
            }

            if (el.classList.contains('reveal-heading')) {
                anime({
                    targets: el,
                    opacity: [0, 1], translateY: [35, 0],
                    duration: 800, easing: 'easeOutExpo',
                });
            }

            if (el.classList.contains('reveal-cards')) {
                anime({
                    targets: el.querySelectorAll('.reveal-card-item'),
                    opacity: [0, 1], translateY: [45, 0], scale: [0.96, 1],
                    delay: anime.stagger(100, { start: 80 }),
                    duration: 750, easing: 'easeOutExpo',
                });
            }

            // CTA standalone card
            if (el.classList.contains('reveal-card-item') && !el.closest('.reveal-cards')) {
                anime({
                    targets: el,
                    opacity: [0, 1], scale: [0.95, 1],
                    duration: 800, easing: 'easeOutExpo',
                });
            }

            observer.unobserve(el);
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('#stats-bar, .reveal-heading, .reveal-cards, #cta .reveal-card-item')
        .forEach(el => observer.observe(el));

    // ── 3. Scroll-to-top ───────────────────────────────────────────────────
    const scrollBtn = document.getElementById('scroll-top');
    window.addEventListener('scroll', () => {
        const visible = window.scrollY > 300;
        scrollBtn.style.opacity        = visible ? '1' : '0';
        scrollBtn.style.pointerEvents  = visible ? 'auto' : 'none';
        scrollBtn.style.transform      = visible ? 'translateY(0)' : 'translateY(12px)';
    }, { passive: true });
    scrollBtn?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // ── 4. Navbar: borde dorado al hacer scroll ────────────────────────────
    const nav = document.getElementById('main-nav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('border-blue-500/50', window.scrollY > 60);
        nav.classList.toggle('border-blue-900/30', window.scrollY <= 60);
    }, { passive: true });

    // ── 5. Mobile menu toggle ──────────────────────────────────────────────
    const menuBtn   = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const iconOpen  = document.getElementById('icon-open');
    const iconClose = document.getElementById('icon-close');
    let menuOpen = false;

    menuBtn?.addEventListener('click', () => {
        menuOpen = !menuOpen;
        mobileMenu.classList.toggle('hidden', !menuOpen);
        iconOpen.classList.toggle('hidden', menuOpen);
        iconClose.classList.toggle('hidden', !menuOpen);
    });

    // Cerrar mobile menu al hacer click en un link
    mobileMenu?.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            menuOpen = false;
            mobileMenu.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
        });
    });

    // ── 6. Reactive cards — tilt 3D + glow cursor + icon pop ──────────────
    document.querySelectorAll('.reactive-card').forEach(card => {
        const glow    = card.querySelector('.card-glow');
        const iconBox = card.querySelector('.card-icon');
        const icon    = iconBox?.querySelector('.material-symbols-outlined');

        // — Tracking del cursor
        card.addEventListener('mousemove', (e) => {
            const r = card.getBoundingClientRect();
            const x = e.clientX - r.left;
            const y = e.clientY - r.top;

            // Glow sigue al cursor (CSS vars)
            if (glow) {
                glow.style.setProperty('--gx', (x / r.width  * 100) + '%');
                glow.style.setProperty('--gy', (y / r.height * 100) + '%');
                glow.style.background = `radial-gradient(circle at ${x}px ${y}px,
                    rgba(255,215,0,0.13) 0%,
                    rgba(255,215,0,0.04) 40%,
                    transparent 68%)`;
            }

            // Tilt 3D suave (directo en transform para inmediatez)
            const rotX = ((y / r.height) - 0.5) * -10;
            const rotY = ((x / r.width)  - 0.5) *  10;
            card.classList.remove('returning');
            card.style.transform = `perspective(900px) rotateX(${rotX}deg) rotateY(${rotY}deg) scale(1.015)`;
        });

        // — Salida: spring de vuelta
        card.addEventListener('mouseleave', () => {
            card.classList.add('returning');
            card.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg) scale(1)';
            setTimeout(() => card.classList.remove('returning'), 700);

            // Icon: vuelve a estado normal
            if (icon) {
                anime({ targets: icon, scale: 1, rotate: '0deg', duration: 500, easing: 'easeOutElastic(1, 0.5)' });
            }
        });

        // — Entrada: icon pop con AnimeJS
        card.addEventListener('mouseenter', () => {
            if (icon) {
                anime({
                    targets: icon,
                    scale: [1, 1.28],
                    rotate: ['0deg', '6deg'],
                    duration: 350,
                    easing: 'easeOutBack',
                });
            }
        });
    });

});
</script>
@endpush
