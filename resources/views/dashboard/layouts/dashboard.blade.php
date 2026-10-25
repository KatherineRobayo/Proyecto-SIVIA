<!DOCTYPE html>
<html class="dark" lang="es" style="background:#131313">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sivia Pos | Dashboard')</title>

    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    {{-- AnimeJS v3 — animaciones del sistema --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js"
            integrity="sha512-aNMyYYxdIxIaot0Y1/PLuEu3eipGCmsEUBrUq+7aVyPGMFH8z0eTP0tkqAvv34fzN6z+201d3T8HPb1svWSKHQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: {
                colors: {
                    "surface-container":"#1f1f1f","on-primary":"#3a3000",
                    "surface-tint":"#e9c400","surface-container-low":"#1b1b1b",
                    "surface-container-high":"#2a2a2a","surface-container-lowest":"#0e0e0e",
                    "surface-container-highest":"#353535","primary-container":"#ffd700",
                    "on-primary-container":"#705e00","primary-fixed":"#ffe16d",
                    "primary-fixed-dim":"#e9c400","outline-variant":"#4d4732","outline":"#999077",
                    "error":"#ffb4ab","error-container":"#93000a","on-error-container":"#ffdad6",
                    "background":"#131313","on-background":"#e2e2e2","on-surface-variant":"#d0c6ab",
                    "secondary":"#c8c6c5","surface-variant":"#353535","surface-dim":"#131313",
                    "on-surface":"#e2e2e2","surface":"#131313","primary":"#fff6df",
                    "inverse-on-surface":"#303030",
                },
                fontFamily: { "headline":["Noto Serif"],"body":["Noto Serif"],"label":["Noto Serif"] },
            }},
        };
    </script>

    <style>
        body { font-family: 'Noto Serif', serif; }
        .material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; vertical-align:middle; }
        .premium-grid {background-image: radial-gradient(circle at 2px 2px,rgba(59,130,246,.05) 1px,transparent 0) !important;
}

                /* [QoL: Sidebar colapsable] */
        #kf-sidebar { width:16rem; transition:width .3s cubic-bezier(.4,0,.2,1); overflow:hidden; }
        #kf-sidebar.kf-collapsed { width:4rem; }
        #kf-sidebar.kf-collapsed .kf-label,
        #kf-sidebar.kf-collapsed .kf-brand { display:none; }
        #kf-main { margin-left:16rem; transition:margin-left .3s cubic-bezier(.4,0,.2,1); }
        #kf-main.kf-expanded { margin-left:4rem; }

        .nav-active { background:linear-gradient(to right,rgba(245,158,11,.1),transparent); color:#fbbf24; border-left:2px solid #3b82f6; }
        .nav-item { transition:all .25s ease; color:#737373; }
        .nav-item:hover { color:#fcd34d; background:rgba(120,53,15,.05); }
        .kpi-card { transition:transform .2s ease,border-color .2s ease; }
        .kpi-card:hover { transform:translateY(-2px); }
        .table-row-anim { transition:background .15s ease; }
        ::-webkit-scrollbar { width:4px; }
        ::-webkit-scrollbar-track { background:#131313; }
        ::-webkit-scrollbar-thumb { background:#4d4732; border-radius:4px; }
    </style>

    @stack('styles')
</head>
<body class="text-on-surface selection:bg-primary-container selection:text-on-primary-container">

{{-- [QoL: Golden Stardust Background] --}}
<x-golden-stardust />

{{-- NAVBAR --}}
<header class="fixed top-0 w-full flex items-center justify-between px-4 h-16
               bg-neutral-950/80 backdrop-blur-md z-50
               border-b border-blue-900/30 shadow-2xl shadow-black/50">
    <div class="flex items-center gap-3">
                    {{-- [QoL: Toggle sidebar] --}}
        <button id="kf-toggle" class="text-neutral-400 hover:text-blue-400 p-2 rounded-lg hover:bg-blue-500/10 transition-all">
            <span class="material-symbols-outlined text-lg">menu</span>
        </button>
        <a href="{{ route('dashboard') }}" class="text-xl font-bold italic text-blue-500 font-serif tracking-tight hover:text-blue-400 transition-colors">
            Sivia Pos
        </a>
    </div>

    <div class="flex items-center gap-3">
        <div class="hidden md:flex items-center bg-surface-container-low border border-blue-900/20 rounded-lg px-3 py-1.5 gap-2">
            <span class="material-symbols-outlined text-blue-500 text-sm">search</span>
            <input id="globalSearch" type="text" placeholder="Buscar en el sistema..."
                    class="bg-transparent border-none focus:ring-0 text-sm w-44 text-on-surface placeholder-neutral-500 outline-none">
        </div>

        {{-- [QoL: notificaciones con mini-panel desplegable]  |parte superior derecha del dashboard | / por evaluar funcion en tiempo real--}}
        <div class="relative">
            <button id="kf-notif-btn" class="text-neutral-400 hover:text-blue-200 hover:bg-white/5 p-2 rounded-full transition-colors">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            @if($notifCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[1rem] h-4 px-0.5 rounded-full bg-error text-[9px] font-bold text-black flex items-center justify-center">
                {{ $notifCount > 9 ? '9+' : $notifCount }}
            </span>
            @endif

            <div id="kf-notif-panel"
                 class="absolute right-0 top-12 w-80 bg-neutral-950 border border-blue-900/30
                        rounded-xl shadow-2xl shadow-black/60 overflow-hidden z-50 hidden">

                {{-- Header --}}
                <div class="px-4 py-3 border-b border-blue-900/20 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-500">Notificaciones</span>
                    <span class="text-[10px] {{ $notifCount > 0 ? 'text-error' : 'text-neutral-500' }}">
                        {{ $notifCount }} alerta{{ $notifCount !== 1 ? 's' : '' }}
                    </span>
                </div>

                {{-- Alertas de stock crítico --}}
                @if($notifAlertas->isNotEmpty())
                <div class="px-4 py-2 border-b border-blue-900/20 max-h-44 overflow-y-auto">
                    <p class="text-[9px] text-error/70 uppercase tracking-[0.15em] font-bold mb-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs" style="font-variation-settings:'FILL' 1">warning</span>
                        Stock Crítico
                    </p>
                    @foreach($notifAlertas as $prod)
                    <a href="{{ route('inventario.index') }}"
                       class="flex items-center justify-between py-1.5 border-b border-blue-900/10 last:border-0 hover:bg-error/5 transition-colors rounded px-1 -mx-1">
                        <span class="text-xs text-on-surface truncate max-w-[65%]">{{ $prod->nombre }}</span>
                        <span class="text-[10px] font-bold text-error font-mono flex-shrink-0">
                            {{ $prod->cantidad }} {{ $prod->unidad ?? 'u.' }}
                        </span>
                    </a>
                    @endforeach
                </div>
                @endif


                {{-- Últimos movimientos del día --}}
                @if($notifMovimientos->isNotEmpty())
                <div class="px-4 py-2 border-b border-blue-900/20">
                    <p class="text-[9px] text-blue-500/70 uppercase tracking-[0.15em] font-bold mb-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">swap_vert</span>
                        Movimientos de Hoy
                    </p>
                    @foreach($notifMovimientos as $mov)
                    <div class="flex items-center justify-between py-1.5 border-b border-blue-900/10 last:border-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-[9px] px-1.5 py-0.5 rounded font-bold flex-shrink-0
                                {{ $mov->tipo === 'entrada' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                {{ ucfirst($mov->tipo) }}
                            </span>
                            <span class="text-xs text-neutral-400 truncate">{{ $mov->producto->nombre ?? '—' }}</span>
                        </div>
                        <span class="text-[9px] text-neutral-600 flex-shrink-0 ml-2">{{ $mov->created_at->format('H:i') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Estado vacío --}}
                @if($notifCount === 0 && $notifMovimientos->isEmpty())
                <div class="p-6 text-center text-neutral-500 text-xs italic">
                    Sin notificaciones pendientes
                </div>
                @endif

                {{-- Footer --}}
                <div class="px-4 py-3 border-t border-blue-900/20 flex items-center justify-between">
                    <a href="{{ route('inventario.index') }}"
                       class="text-[10px] text-blue-500 hover:text-blue-400 uppercase tracking-widest transition-colors">
                        Ver inventario →
                    </a>
                    <a href="{{ route('movimientos.index') }}"
                       class="text-[10px] text-neutral-500 hover:text-blue-400 uppercase tracking-widest transition-colors">
                        Movimientos →
                    </a>
                </div>

            </div>
        </div>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('ajustes.index') }}" class="text-neutral-400 hover:text-blue-200 hover:bg-white/5 p-2 rounded-full transition-colors" title="Administrar usuarios">
            <span class="material-symbols-outlined">person_add</span>
        </a>
        @endif

        <a href="{{ route(auth()->user()->isAdmin() ? 'ajustes.index' : 'profile.edit') }}"
           class="flex items-center gap-2 pl-2 border-l border-blue-900/30 hover:opacity-80 transition-opacity"
           title="Ajustes de perfil">
            <div class="h-8 w-8 rounded-full border border-blue-500/50 bg-surface-container-high overflow-hidden flex items-center justify-center flex-shrink-0">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-full h-full object-cover">
                @else
                    <span class="material-symbols-outlined text-blue-500 text-lg">person</span>
                @endif
            </div>
            <div class="hidden md:block text-right">
                <p class="text-xs font-bold text-on-surface leading-tight">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-blue-500/70 uppercase tracking-wider">{{ auth()->user()->role }}</p>
            </div>
        </a>
    </div>
</header>

{{-- SIDEBAR colapsable --}}
<aside id="kf-sidebar"
       class="fixed h-full left-0 top-0 flex flex-col pt-20 pb-6 z-40
              border-r border-blue-900/20 bg-neutral-950
              shadow-[10px_0_30px_-15px_rgba(0,0,0,1)]"
       style="background:linear-gradient(to right,#0a0a0a 98%,#d4af37 100%);">

    <div class="px-4 mb-6 overflow-hidden whitespace-nowrap">
        <h2 class="kf-brand text-blue-500 font-bold tracking-widest uppercase font-serif text-sm">Sivia Pos</h2>
        <p class="kf-brand text-[10px] text-neutral-500 tracking-[0.2em] uppercase mt-1">Management Suite</p>
    </div>

    <nav class="flex-1 space-y-0.5 overflow-y-auto overflow-x-hidden px-2">

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('dashboard') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0" style="{{ request()->routeIs('dashboard') ? 'font-variation-settings:FILL 1' : '' }}">dashboard</span>
            <span class="kf-label">Dashboard</span>
        </a>

        @if(auth()->user()->empresa_id)
        <a href="{{ route('inventario.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('inventario.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0">inventory_2</span>
            <span class="kf-label">Inventario</span>
        </a>

        <a href="{{ route('productos.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('productos.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="kf-label">Productos</span>
        </a>

        <a href="{{ route('pedidos.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('pedidos.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0">restaurant_menu</span>
            <span class="kf-label">Pedidos</span>
        </a>

        <a href="{{ route('caja.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('caja.*', 'ventas.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0">payments</span>
            <span class="kf-label">Caja</span>
        </a>

        <a href="{{ route('ventas.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('ventas.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0">receipt_long</span>
            <span class="kf-label">Ventas</span>
        </a>

        <a href="{{ route('recibos.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('recibos.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0">receipt_long</span>
            <span class="kf-label">Recibos</span>
        </a>
        @endif

        @if(auth()->user()->isAdmin())

        <div class="px-3 pt-4 pb-1 overflow-hidden">
        <span class="kf-label text-[9px] text-blue-900/70 uppercase tracking-[0.2em] font-bold">
        Solo Admin
        </span>
        </div>

        @if(auth()->user()->empresa_id)
        <a href="{{ route('reportes.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('reportes.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0">analytics</span>
            <span class="kf-label">Reportes</span>
        </a>
        @endif

        <a href="{{ route(auth()->user()->isAdmin() ? 'ajustes.index' : 'profile.edit') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm whitespace-nowrap {{ request()->routeIs('ajustes.*') ? 'nav-active' : 'nav-item' }}">
            <span class="material-symbols-outlined text-lg flex-shrink-0">settings</span>
            <span class="kf-label">Ajustes</span>
        </a>
        @endif

    </nav>

    <div class="px-2 mt-4 border-t border-blue-900/20 pt-4 space-y-0.5">

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-serif text-sm text-left hover:text-error transition-colors whitespace-nowrap">
                <span class="material-symbols-outlined text-lg flex-shrink-0">logout</span>
                <span class="kf-label">Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>

{{-- MAIN --}}
<main id="kf-main" class="pt-16 min-h-screen premium-grid">

    @if(session('success'))
    <div class="mx-8 mt-4 px-4 py-3 rounded-lg bg-green-900/20 border border-green-700/30 flex items-center gap-3">
        <span class="material-symbols-outlined text-green-400">check_circle</span>
        <p class="text-sm text-green-400">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mx-8 mt-4 px-4 py-3 rounded-lg bg-error-container/20 border border-error/30 flex items-center gap-3">
        <span class="material-symbols-outlined text-error">error</span>
        <p class="text-sm text-error">{{ session('error') }}</p>
    </div>
    @endif

    @yield('content')

</main>

@stack('scripts')

<script>
// ═══════════════════════════════════════════════════════════════
// Sivia Pos — Sistema de Apariencia Global (palette + theme)
// CSS injection real: sobreescribe clases Tailwind via data-attrs
// ═══════════════════════════════════════════════════════════════
(function () {

var KF_PALETTES = {
    gold:      { a5:'#3b82f6', a4:'#fbbf24', a2:'#fde68a', a9bg:'#451a03', aLow:'#78350f' },
    champagne: { a5:'#d4af37', a4:'#e0c354', a2:'#f0d878', a9bg:'#3d2f00', aLow:'#6b550a' },
    copper:    { a5:'#b87333', a4:'#cc8844', a2:'#e0aa77', a9bg:'#2c1a0a', aLow:'#5c3a1a' },
    antique:   { a5:'#c8971f', a4:'#d9aa36', a2:'#ebcc7a', a9bg:'#38200a', aLow:'#644a0e' },
    warm:      { a5:'#e8892a', a4:'#f0a050', a2:'#f8c88a', a9bg:'#3d1a00', aLow:'#7a3a10' },
    pearl:     { a5:'#d4b896', a4:'#dfc8a8', a2:'#edd8be', a9bg:'#2a1e14', aLow:'#6a543e' },
};

var KF_THEMES = {
    dark: {
        bg:'#131313', sFrom:'#0a0a0a',
        sLowest:'#0e0e0e', sLow:'#1b1b1b', sC:'#1f1f1f', sHigh:'#2a2a2a', sTop:'#353535',
        onS:'#e2e2e2', n400:'#a3a3a3', n500:'#737373', n600:'#525252',
        navBg:'rgba(10,10,10,0.8)', n950:'#030303',
        iB:'#2a2a2a', iT:'#e2e2e2', iP:'#525252',
        b10:'rgba(120,53,15,0.1)', b20:'rgba(120,53,15,0.2)', b30:'rgba(120,53,15,0.3)',
        dot:'rgba(59,130,246,0.05)',
    },
    dim: {
        bg:'#1a1812', sFrom:'#100e08',
        sLowest:'#141209', sLow:'#211e14', sC:'#252018', sHigh:'#302b1c', sTop:'#3a3424',
        onS:'#e2ddc8', n400:'#9e9a85', n500:'#706c5a', n600:'#4d4a3c',
        navBg:'rgba(10,9,6,0.85)', n950:'#0a0904',
        iB:'#302b1c', iT:'#e2ddc8', iP:'#4d4a3c',
        b10:'rgba(120,53,15,0.12)', b20:'rgba(120,53,15,0.25)', b30:'rgba(120,53,15,0.35)',
        dot:'rgba(212,175,55,0.04)',
    },
    light: {
        bg:'#f0ead8', sFrom:'#e0d8c4',
        sLowest:'#faf8f2', sLow:'#ede8dc', sC:'#e8e2d4', sHigh:'#ffffff', sTop:'#ddd8cc',
        onS:'#1a1714', n400:'#6b6050', n500:'#8a7a68', n600:'#b0a090',
        navBg:'rgba(240,234,216,0.94)', n950:'#ede8dc',
        iB:'#ffffff', iT:'#1a1714', iP:'#b0a090',
        b10:'rgba(120,80,0,0.10)', b20:'rgba(120,80,0,0.18)', b30:'rgba(120,80,0,0.28)',
        dot:'rgba(0,0,0,0.04)',
    },
};

// hex → rgba helper
function h2r(hex, a) {
    var h = hex.replace('#','');
    var r = parseInt(h.slice(0,2),16), g = parseInt(h.slice(2,4),16), b = parseInt(h.slice(4,6),16);
    return a !== undefined ? 'rgba('+r+','+g+','+b+','+a+')' : 'rgb('+r+','+g+','+b+')';
}

// construye el CSS completo para palette + theme actuales
function kfBuildCSS(pal, theme) {
    var p = KF_PALETTES[pal] || KF_PALETTES.gold;
    var t = KF_THEMES[theme]  || KF_THEMES.dark;
    var TH = 'html[data-theme="'+theme+'"]';
    var PL = 'html[data-palette="'+pal+'"]';
    var r  = [];

    // ── THEME ──────────────────────────────────────────────────
    // background en html (no body) — body transparente para golden-stardust
    r.push(TH+' html{background:'+t.bg+'!important}');
    r.push(TH+' .text-on-surface{color:'+t.onS+'!important}');
    r.push(TH+' .text-neutral-400{color:'+t.n400+'!important}');
    r.push(TH+' .text-neutral-500{color:'+t.n500+'!important}');
    r.push(TH+' .text-neutral-600{color:'+t.n600+'!important}');
    r.push(TH+' .bg-surface-container-lowest{background:'+t.sLowest+'!important}');
    r.push(TH+' .bg-surface-container-low{background:'+t.sLow+'!important}');
    r.push(TH+' .bg-surface-container{background:'+t.sC+'!important}');
    r.push(TH+' .bg-surface-container-high{background:'+t.sHigh+'!important}');
    r.push(TH+' .bg-surface-container-highest{background:'+t.sTop+'!important}');
    r.push(TH+' input,'+TH+' select{background:'+t.iB+'!important;color:'+t.iT+'!important}');
    r.push(TH+' ::placeholder{color:'+t.iP+'!important}');
    r.push(TH+' .border-blue-900\\/10{border-color:'+t.b10+'!important}');
    r.push(TH+' .border-blue-900\\/20{border-color:'+t.b20+'!important}');
    r.push(TH+' .border-blue-900\\/30{border-color:'+t.b30+'!important}');
    r.push(TH+' .premium-grid{background-image:radial-gradient(circle at 2px 2px,'+t.dot+' 1px,transparent 0)!important}');
    r.push(TH+' ::-webkit-scrollbar-track{background:'+t.bg+'!important}');
    r.push(TH+' .nav-item{color:'+t.n500+'!important}');
    if(theme==='light'){
        r.push(TH+' .kpi-card{background:'+t.sLowest+'!important}');
        r.push(TH+' .selection\\:bg-primary-container ::selection{background:'+h2r('#3b82f6',0.2)+'!important}');
    }

    // ── PALETTE ────────────────────────────────────────────────
    r.push(PL+' .text-blue-500{color:'+p.a5+'!important}');
    r.push(PL+' .text-blue-400{color:'+p.a4+'!important}');
    r.push(PL+' .text-blue-500\\/70{color:'+h2r(p.a5,.7)+'!important}');
    r.push(PL+' .border-blue-900\\/10{border-color:'+h2r(p.aLow,.1)+'!important}');
    r.push(PL+' .border-blue-900\\/20{border-color:'+h2r(p.aLow,.2)+'!important}');
    r.push(PL+' .border-blue-900\\/30{border-color:'+h2r(p.aLow,.3)+'!important}');
    r.push(PL+' .border-blue-500\\/20{border-color:'+h2r(p.a5,.2)+'!important}');
    r.push(PL+' .border-blue-500\\/30{border-color:'+h2r(p.a5,.3)+'!important}');
    r.push(PL+' .border-blue-500\\/40{border-color:'+h2r(p.a5,.4)+'!important}');
    r.push(PL+' .border-blue-500\\/50{border-color:'+h2r(p.a5,.5)+'!important}');
    r.push(PL+' .border-blue-500\\/60{border-color:'+h2r(p.a5,.6)+'!important}');
    r.push(PL+' .bg-blue-500{background:'+p.a5+'!important}');
    r.push(PL+' .bg-blue-500\\/5{background:'+h2r(p.a5,.05)+'!important}');
    r.push(PL+' .bg-blue-500\\/10{background:'+h2r(p.a5,.1)+'!important}');
    r.push(PL+' .bg-blue-500\\/20{background:'+h2r(p.a5,.2)+'!important}');
    r.push(PL+' .bg-blue-950{background:'+p.a9bg+'!important}');
    r.push(PL+' .bg-blue-950\\/20{background:'+h2r(p.a9bg,.2)+'!important}');
    r.push(PL+' .bg-blue-950\\/30{background:'+h2r(p.a9bg,.3)+'!important}');
    r.push(PL+' .bg-primary-container{background:'+p.a5+'!important}');
    r.push(PL+' .hover\\:text-blue-400:hover{color:'+p.a4+'!important}');
    r.push(PL+' .hover\\:text-blue-200:hover{color:'+p.a2+'!important}');
    r.push(PL+' .hover\\:bg-blue-500\\/10:hover{background:'+h2r(p.a5,.1)+'!important}');
    r.push(PL+' .hover\\:bg-blue-500\\/20:hover{background:'+h2r(p.a5,.2)+'!important}');
    r.push(PL+' .hover\\:border-blue-500\\/50:hover{border-color:'+h2r(p.a5,.5)+'!important}');
    r.push(PL+' .nav-active{background:linear-gradient(to right,'+h2r(p.a5,.1)+',transparent)!important;color:'+p.a4+'!important;border-left-color:'+p.a5+'!important}');
    r.push(PL+' .nav-item:hover{color:'+p.a4+'!important}');
    r.push(PL+' ::-webkit-scrollbar-thumb{background:'+p.aLow+'!important}');
    r.push(PL+' ::selection{background:'+h2r(p.a5,.25)+'!important}');
    r.push(PL+' .focus\\:border-blue-500\\/60:focus{border-color:'+h2r(p.a5,.6)+'!important}');

    return r.join('\n');
}

// inyecta o actualiza el <style> de apariencia
function kfInjectCSS(pal, theme) {
    var el = document.getElementById('kf-vars');
    if (!el) { el = document.createElement('style'); el.id = 'kf-vars'; document.head.appendChild(el); }
    el.textContent = kfBuildCSS(pal, theme);
}

// aplica palette + theme completo
function kfApply(pal, theme, skipSidebar) {
    var p = KF_PALETTES[pal] || KF_PALETTES.gold;
    var t = KF_THEMES[theme]  || KF_THEMES.dark;

    // data attributes (activan los selectores CSS)
    document.documentElement.setAttribute('data-palette', pal);
    document.documentElement.setAttribute('data-theme',   theme);

    // background solo en <html> — body debe quedar transparente
    // para que el canvas golden-stardust (z:-10) sea visible debajo del contenido
    document.documentElement.style.background = t.bg;

    // sidebar (gradiente con acento de paleta)
    if (!skipSidebar) {
        var sb = document.getElementById('kf-sidebar');
        if (sb) sb.style.background = 'linear-gradient(to right,'+t.sFrom+' 98%,'+p.a5+' 100%)';
    }

    // navbar backdrop (bg-neutral-950/80 inline override)
    var nb = document.querySelector('header');
    if (nb) nb.style.background = t.navBg;

    kfInjectCSS(pal, theme);
    localStorage.setItem('kf_palette', pal);
    localStorage.setItem('kf_theme',   theme);
}

// exponer globalmente para que la página de ajustes lo use
window.KF = { apply: kfApply, palettes: KF_PALETTES, themes: KF_THEMES };

// ── aplicar preferencias guardadas inmediatamente (antes del DOM) ──
var _pal   = localStorage.getItem('kf_palette') || 'blue';
var _theme = localStorage.getItem('kf_theme')   || 'dark';
// Inyectar CSS de inmediato (head ya existe)
kfInjectCSS(_pal, _theme);
// Backgrounds en html antes del paint
var _t = KF_THEMES[_theme] || KF_THEMES.dark;
document.documentElement.style.background = _t.bg;
document.documentElement.setAttribute('data-palette', _pal);
document.documentElement.setAttribute('data-theme',   _theme);

})(); // fin IIFE

document.addEventListener('DOMContentLoaded', function () {

    // Aplicar resto de preferencias (sidebar, navbar — requieren DOM)
    var _pal   = localStorage.getItem('kf_palette') || 'blue';
    var _theme = localStorage.getItem('kf_theme')   || 'dark';
    window.KF.apply(_pal, _theme);

    // [QoL: Sidebar toggle]
    var sidebar  = document.getElementById('kf-sidebar');
    var main     = document.getElementById('kf-main');
    var toggle   = document.getElementById('kf-toggle');
    var collapsed = false;

    if (toggle) {
        toggle.addEventListener('click', function () {
            collapsed = !collapsed;
            sidebar.classList.toggle('kf-collapsed', collapsed);
            main.classList.toggle('kf-expanded', collapsed);
        });
    }

    // [QoL: notificaciones mini-panel] ── polling en tiempo real cada 30s
    var notifBtn    = document.getElementById('kf-notif-btn');
    var notifPanel  = document.getElementById('kf-notif-panel');
    var notifBadge  = notifBtn ? notifBtn.parentElement.querySelector('span.absolute') : null;
    var lastMovId   = 0;

    if (notifBtn && notifPanel) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notifPanel.classList.toggle('hidden');
        });
        document.addEventListener('click', function () {
            notifPanel.classList.add('hidden');
        });
        notifPanel.addEventListener('click', function (e) { e.stopPropagation(); });
    }

    // Polling de notificaciones — actualiza badge y panel cada 30 segundos
    function fetchNotificaciones() {
        fetch('{{ route("api.notificaciones") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                // Actualizar badge de alertas de stock
                var badgeEl = document.querySelector('#kf-notif-btn ~ span.absolute');
                if (badgeEl) {
                    if (data.alertas_count > 0) {
                        mostrarToastStock(data.alertas_count);
                        badgeEl.textContent = data.alertas_count > 9 ? '9+' : data.alertas_count;
                        badgeEl.style.display = 'flex';
                    } else {
                        badgeEl.style.display = 'none';
                    }
                }

                // Toast si llega un nuevo movimiento de entrada
                if (data.ultimo_mov_id > lastMovId && lastMovId > 0) {
                    var nuevoMov = data.movimientos.find(function (m) { return m.id > lastMovId && m.tipo === 'entrada'; });
                    if (nuevoMov) {
                        mostrarToastMovimiento(nuevoMov);
                    }
                }
                if (data.ultimo_mov_id > 0) lastMovId = data.ultimo_mov_id;
            })
            .catch(function () {}); // silencioso en error de red
    }

    // Toast de stock Bajo
    function mostrarToastStock(count) {
    var toast = document.createElement('div');
    toast.className = 'fixed top-5 right-5 z-[999] flex items-center gap-3 px-5 py-3 rounded-xl ' +
                      'border border-red-500/40 bg-neutral-950/95 text-red-300 text-sm ' +
                      'font-semibold shadow-2xl backdrop-blur-sm';

    toast.innerHTML =
        '<span class="material-symbols-outlined text-red-400" style="font-variation-settings:\'FILL\' 1">warning</span>' +
        '<div>' +
        '<p class="text-xs text-neutral-400 leading-none mb-0.5">Stock crítico</p>' +
        '<p>' + count + ' productos en estado crítico</p>' +
        '</div>';

    document.body.appendChild(toast);

    setTimeout(function () {
        toast.style.transition = 'opacity .4s';
        toast.style.opacity = '0';
        setTimeout(function () { toast.remove(); }, 400);
        }, 5000);
    }
    
    // Toast de nuevo movimiento de entrada
    function mostrarToastMovimiento(mov) {
        var toast = document.createElement('div');
        toast.className = 'fixed top-5 right-5 z-[999] flex items-center gap-3 px-5 py-3 rounded-xl ' +
                          'border border-emerald-500/40 bg-neutral-950/95 text-emerald-300 text-sm ' +
                          'font-semibold shadow-2xl backdrop-blur-sm';
        toast.style.cssText = 'animation: kf-toast-in .35s cubic-bezier(.22,1,.36,1);';
        toast.innerHTML = '<span class="material-symbols-outlined text-emerald-400" ' +
                          'style="font-variation-settings:\'FILL\' 1">arrow_downward</span>' +
                          '<div><p class="text-xs text-neutral-400 leading-none mb-0.5">Nuevo movimiento</p>' +
                          '<p>' + mov.producto + ' — +' + mov.cantidad + ' (' + mov.hora + ')</p></div>';
        document.body.appendChild(toast);
        setTimeout(function () {
            toast.style.transition = 'opacity .4s';
            toast.style.opacity = '0';
            setTimeout(function () { toast.remove(); }, 400);
        }, 5000);
    }

    // Iniciar con el ID del último movimiento actual y luego polling
    fetchNotificaciones();
    setInterval(fetchNotificaciones, 30000);

    // [LAYOUT: Animaciones KPI cards]
    document.querySelectorAll('.kpi-card').forEach(function (c, i) {
        c.style.opacity = '0'; c.style.transform = 'translateY(16px)';
        c.style.transition = 'opacity .5s ease,transform .5s ease';
        setTimeout(function () { c.style.opacity = '1'; c.style.transform = 'translateY(0)'; }, 80 + i * 70);
    });

    // [LAYOUT: Animaciones filas tabla] 
    document.querySelectorAll('.table-row-anim').forEach(function (r, i) {
        r.style.opacity = '0'; r.style.transform = 'translateX(-8px)';
        r.style.transition = 'opacity .4s ease,transform .4s ease';
        setTimeout(function () { r.style.opacity = '1'; r.style.transform = 'translateX(0)'; }, 300 + i * 60);
    });

    // [LAYOUT: Contadores en pesos colombianos (COP)] ─────por probar
    document.querySelectorAll('[data-counter]').forEach(function (el) {
        var target = parseFloat(el.dataset.counter) || 0;
        var prefix = el.dataset.prefix || '';
        var steps = 50, stepTime = 1200 / steps, current = 0;
        var tm = setInterval(function () {
            current += target / steps;
            if (current >= target) { current = target; clearInterval(tm); }
            el.textContent = prefix + Math.floor(current).toLocaleString('es-CO');
        }, stepTime);
    });

    // [LAYOUT: Barras de grafica] ─ por probar
    document.querySelectorAll('.bar-anim').forEach(function (b, i) {
        var h = b.dataset.height || '50%';
        b.style.height = '0%';
        b.style.transition = 'height .6s cubic-bezier(.34,1.56,.64,1)';
        setTimeout(function () { b.style.height = h; }, 400 + i * 60);
    });

});


// [Boton de buscar Navbar principal ] 
document.getElementById('globalSearch').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        let value = this.value.toLowerCase().trim();

        if (value.includes('inventario')) {
            window.location.href = '/dashboard/inventario';
        } else if (value.includes('productos')) {
            window.location.href = '/dashboard/productos';
        } else if (value.includes('pedidos')) {
            window.location.href = '/dashboard/pedidos';
        } else if (value.includes('recibos')) {
            window.location.href = '/dashboard/recibos';
        } else if (value.includes('caja')) {
            window.location.href = '/dashboard/caja';
        } else if (value.includes('reportes')) {
            window.location.href = '/dashboard/reportes';
        } 
        else if (value.includes('movimiento') || value.includes('movimientos') || value.includes('mov')) {
            window.location.href = '/dashboard/movimientos';
         
        } else if (value.includes('ajustes')) {
            window.location.href = '/dashboard/ajustes';
        } else {
            alert('Módulo no encontrado');
        }
    }
});
</script>

</body>
</html>