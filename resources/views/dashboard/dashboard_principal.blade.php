@extends('dashboard.layouts.dashboard')
@section('title', 'Sivia pos | Dashboard')

@push('styles')
<style>
/* ── Entradas ─────────────────────────────────────────────────────────── */
@keyframes kf-fade-up {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes kf-warning-pulse {
    0%, 100% { filter: drop-shadow(0 0 0px #ef4444); }
    50%       { filter: drop-shadow(0 0 6px #ef4444cc); }
}
@keyframes kf-shimmer {
    from { background-position: -200% center; }
    to   { background-position:  200% center; }
}

/* KPI cards con animación escalonada */
.kpi-dash { animation: kf-fade-up .5s cubic-bezier(.22,1,.36,1) both; }
.kpi-dash:nth-child(1) { animation-delay: .05s; }
.kpi-dash:nth-child(2) { animation-delay: .12s; }
.kpi-dash:nth-child(3) { animation-delay: .19s; }
.kpi-dash:nth-child(4) { animation-delay: .26s; }

/* Accent bar izquierda en KPI cards */
.kpi-dash { position: relative; }
.kpi-dash::before {
    content: '';
    position: absolute;
    left: 0; top: 16px; bottom: 16px;
    width: 3px;
    border-radius: 2px;
    background: var(--kpi-accent, #f59e0b);
    opacity: 0;
    transform: scaleY(.4);
    transition: opacity .25s, transform .25s cubic-bezier(.22,1,.36,1);
}
.kpi-dash:hover::before { opacity: 1; transform: scaleY(1); }

/* Barras del chart: origen en bottom para scaleY */
.bar-anim {
    transform-origin: bottom;
    transform: scaleY(0);
    transition: transform .6s cubic-bezier(.22,1,.36,1);
    will-change: transform;
}
.bar-anim.kf-ready { transform: scaleY(1); }

/* Filas tabla con stagger */
.row-stagger {
    opacity: 0;
    transform: translateX(-8px);
    transition: opacity .3s ease, transform .3s ease;
}
.row-stagger.kf-visible { opacity: 1; transform: translateX(0); }

/* Warning pulse */
.kf-warning-icon { animation: kf-warning-pulse 2s ease-in-out infinite; }

/* Acceso rápido shimmer en hover */
.acceso-card {
    background-size: 200% auto;
    transition: border-color .2s, background-color .2s, box-shadow .2s;
}
.acceso-card:hover {
    box-shadow: 0 4px 20px -4px rgba(245,158,11,.15);
}
.acceso-card:hover .acceso-icon {
    background-color: rgba(245,158,11,.2);
    transform: scale(1.1);
}
.acceso-icon { transition: background-color .2s, transform .2s cubic-bezier(.22,1,.36,1); }

/* Número KPI highlight al recibir update AJAX */
@keyframes kf-num-flash { 0%,100%{color:inherit;} 40%{color:#f59e0b;} }
.kf-num-updated { animation: kf-num-flash .6s ease; }
</style>
@endpush

@section('content')
<div class="px-8 py-8">

    <header class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-4xl font-headline font-bold text-on-surface tracking-tight">Dashboard Ejecutivo</h1>
            <p class="text-neutral-500 mt-1 font-serif italic text-sm">
                Bienvenido, {{ auth()->user()->name }} · {{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('inventario.index') }}"
               class="px-5 py-2 bg-blue-600 text-white text-xs font-bold uppercase tracking-widest hover:brightness-110 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">inventory_2</span>
                Ver Inventario
            </a>
        </div>
    </header>

                                                            {{-- KPIs (lado derecho) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        {{-- KPI 1: Total Productos — accent blue --}}
        <div class="kpi-card kpi-dash bg-surface-container-lowest border border-blue-900/20 p-6 relative overflow-hidden group"
             style="--kpi-accent:#2563eb">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-full -mr-10 -mt-10 group-hover:scale-125 group-hover:bg-blue-500/10 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <span class="text-neutral-500 text-xs uppercase tracking-widest">Total Productos</span>
                <span class="material-symbols-outlined text-blue-500 group-hover:scale-110 transition-transform duration-300">inventory_2</span>
            </div>
            <div class="text-3xl font-bold text-on-surface mb-1" data-counter="{{ $totalProductos }}" data-prefix="" data-kpi="total_productos">{{ $totalProductos }}</div>
            <div class="text-[10px] text-neutral-500">productos registrados</div>
        </div>

        {{-- KPI 2: Alertas Stock — accent red --}}
        <div class="kpi-card kpi-dash bg-surface-container-lowest border border-red-900/20 p-6 relative overflow-hidden group"
             style="--kpi-accent:#ef4444">
            <div class="absolute top-0 right-0 w-24 h-24 bg-error/5 rounded-full -mr-10 -mt-10 group-hover:scale-125 group-hover:bg-error/10 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <span class="text-error text-xs uppercase tracking-widest">Alertas Stock</span>
                <span class="material-symbols-outlined text-error kf-warning-icon" style="font-variation-settings:'FILL' 1">warning</span>
            </div>
            <div class="text-3xl font-bold text-on-surface mb-1" data-counter="{{ $alertasStock }}" data-kpi="alertas_stock">{{ $alertasStock }}</div>
            <div class="text-[10px] text-error font-bold">Bajo stock mínimo</div>
        </div>

        {{-- KPI 3: Valor Inventario — accent emerald --}}
        <div class="kpi-card kpi-dash bg-surface-container-lowest border border-emerald-900/20 p-6 relative overflow-hidden group"
             style="--kpi-accent:#10b981">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full -mr-10 -mt-10 group-hover:scale-125 group-hover:bg-emerald-500/10 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <span class="text-neutral-500 text-xs uppercase tracking-widest">Valor Inventario</span>
                <span class="material-symbols-outlined text-emerald-500 group-hover:scale-110 transition-transform duration-300">account_balance_wallet</span>
            </div>
            <div class="text-3xl font-bold text-on-surface mb-1" data-kpi="valor_inventario">$ {{ number_format($valorInventario, 0, ',', '.') }}</div>
            <div class="text-[10px] text-neutral-500">Pesos colombianos (COP)</div>
        </div>

        {{-- KPI 4: Movimientos Hoy — accent sky --}}
        <div class="kpi-card kpi-dash bg-surface-container-lowest border border-sky-900/20 p-6 relative overflow-hidden group"
             style="--kpi-accent:#0ea5e9">
            <div class="absolute top-0 right-0 w-24 h-24 bg-sky-500/5 rounded-full -mr-10 -mt-10 group-hover:scale-125 group-hover:bg-sky-500/10 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <span class="text-neutral-500 text-xs uppercase tracking-widest">Movimientos Hoy</span>
                <span class="material-symbols-outlined text-sky-400 group-hover:scale-110 transition-transform duration-300">swap_vert</span>
            </div>
            <div class="text-3xl font-bold text-on-surface mb-1" data-counter="{{ $movimientosHoy }}" data-kpi="movimientos_hoy">{{ $movimientosHoy }}</div>
            <div class="text-[10px] text-neutral-500">Entradas y salidas</div>
        </div>

    </div>

    {{-- Banda de alertas críticas de stock --}}
    @if($alertasStock > 0)
    <div class="mb-6 bg-error-container/10 border border-error/20 rounded-xl px-5 py-3 flex flex-wrap items-center gap-3">
        <span class="material-symbols-outlined text-error flex-shrink-0 text-xl" style="font-variation-settings:'FILL' 1">warning</span>
        <p class="text-xs font-bold text-error uppercase tracking-widest flex-shrink-0">
            {{ $alertasStock }} producto{{ $alertasStock !== 1 ? 's' : '' }} con stock crítico
        </p>
        <div class="flex flex-wrap gap-2">
            @foreach($productosAlerta as $pa)
            <span class="text-[10px] px-2 py-1 rounded border border-error/20 text-error/80 bg-error/5 font-mono whitespace-nowrap">
                {{ $pa->nombre }} — {{ $pa->cantidad }} {{ $pa->unidad ?? 'u.' }}
            </span>
            @endforeach
        </div>
        <a href="{{ route('inventario.index') }}"
           class="text-[10px] text-error/60 hover:text-error hover:underline flex-shrink-0 ml-auto transition-colors">
            Ver inventario →
        </a>
    </div>
    @endif

                                {{-- accesos directos + gráfica --}}
    <div class="grid grid-cols-12 gap-5 mb-5">

                                                     {{-- Grafica semanal --}}
        <div class="col-span-12 lg:col-span-8 bg-surface-container-lowest border border-blue-900/20 p-8 shadow-xl">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-xl font-headline font-bold text-blue-500">Dinámica Semanal</h3>
                <div class="flex flex-wrap gap-3 text-[10px] uppercase tracking-widest">
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-0.5 rounded bg-blue-500"></span>Entradas</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-0.5 rounded bg-emerald-500"></span>Salidas</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-0.5 rounded bg-sky-400"></span>Ingresos</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-0.5 rounded bg-red-400"></span>Egresos</span>
                </div>
            </div>
            <div class="relative h-48">
                <canvas id="dash-semanal-chart"></canvas>
            </div>
            {{-- Data para Chart.js —inyectada server-side --}}
            <script id="dash-chart-data" type="application/json">
            @php
                $dashChartJson = $chartSemanal->map(function ($d) {
                    return [
                        'label'    => $d['dia'],
                        'entradas' => $d['entradas'],
                        'salidas'  => $d['salidas'],
                        'ingresos' => 0,
                        'egresos'  => 0,
                    ];
                })->values()->toJson();
            @endphp
            {!! $dashChartJson !!}
            </script>
        </div>

        {{-- ANALISIS INTELIGENTE DEL SISTEMA --}}
<div class="col-span-12 lg:col-span-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-3 px-1">
        <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-[0.2em]">
            Análisis del Sistema
        </span>
    </div>

    {{-- Panel --}}
    <div class="space-y-3">

        {{-- Tarjeta principal --}}
        <div class="kpi-card p-4 rounded-xl border border-blue-900/30 bg-surface-container-highest">

            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-blue-500 text-lg">insights</span>
                <h3 class="text-sm font-bold text-on-surface">Resumen inteligente</h3>
            </div>

            <ul class="space-y-2 text-xs text-neutral-400">

                {{-- DEMANDA --}}
                @if($salidas > $entradas)
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-400 text-sm">trending_up</span> Alta demanda de productos hoy
                    </li>
                @else
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-400 text-sm">trending_down</span> Baja rotación de productos
                    </li>
                @endif

                {{-- STOCK --}}
                @if($alertasStock > 0)
                    <li class="flex items-center gap-2 text-red-400">
                        <span class="material-symbols-outlined text-blue-400 text-sm">warning</span> {{ $alertasStock }} productos en estado crítico
                    </li>
                @endif

                {{-- VENTAS --}}
                @if($ingresos > 0)
                    <li class="flex items-center gap-2 text-emerald-400">
                        <span class="material-symbols-outlined text-blue-400 text-sm">payments</span>Ventas registradas hoy: ${{ number_format($ingresos, 2) }}
                    </li>
                @else
                    <li class="flex items-center gap-2 text-red-400">
                        <span class="material-symbols-outlined text-blue-400 text-sm">money_off</span>Sin ventas registradas hoy
                    </li>
                @endif

                {{-- PRODUCTO DESTACADO --}}
                @if(isset($productoTop))
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-400 text-sm">workspace_premium</span> Más vendido: {{ $productoTop }}
                    </li>
                @endif

            </ul>

        </div>

        {{-- SUGERENCIAS --}}
        <div class="kpi-card p-4 rounded-xl border border-blue-900/20 bg-surface-container">

            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-blue-400 text-base">lightbulb</span>
                <h4 class="text-xs font-bold text-on-surface">Sugerencias</h4>
            </div>

            <ul class="space-y-1 text-[11px] text-neutral-500">

                @if($alertasStock > 0)
                    <li>• Reabastecer productos críticos</li>
                @endif

                @if($salidas > $entradas)
                    <li>• Evaluar aumento de stock</li>
                @endif

                <li>• Revisar reportes para decisiones</li>

            </ul>

        </div>

    </div>

</div>

    </div>

     {{-- tabla actividad reciente --}}
    <div class="bg-surface-container-lowest border border-blue-900/20 p-8">
        <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
            <h3 class="text-xl font-headline font-bold text-on-surface">Actividad Reciente</h3>
            <div class="flex items-center gap-3">
                {{-- Tabs de filtro --}}
                <div class="flex bg-surface-container border border-blue-900/20 rounded-lg p-0.5 gap-0.5" id="kf-mov-tabs">
                    @foreach(['todos' => 'Todos', 'entrada' => 'Entradas', 'salida' => 'Salidas'] as $val => $label)
                    <button data-tab="{{ $val }}"
                            class="kf-tab px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest rounded transition-all
                                   {{ $val === 'todos' ? 'bg-blue-500 text-white shadow' : 'text-neutral-500 hover:text-blue-400' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
                <a href="{{ route('movimientos.index') }}" class="text-xs text-blue-500 uppercase tracking-widest font-bold hover:underline">
                    Ver movimientos →
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-blue-900/20 text-[10px] uppercase tracking-[0.2em] text-neutral-500">
                        <th class="pb-4 font-normal">ID</th>
                        <th class="pb-4 font-normal">Producto</th>
                        <th class="pb-4 font-normal">Tipo</th>
                        <th class="pb-4 font-normal">Responsable</th>
                        <th class="pb-4 font-normal">Fecha</th>
                        <th class="pb-4 font-normal text-right">Cantidad</th>
                        <th class="pb-4 font-normal text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-serif">
                    @forelse($movimientos as $mov)
                    <tr class="row-stagger border-b border-blue-900/10 hover:bg-blue-500/5 transition-colors"
                        data-tipo="{{ $mov->tipo }}">
                        <td class="py-4 text-blue-500/70">#MOV-{{ str_pad($mov->id,5,'0',STR_PAD_LEFT) }}</td>
                        <td class="py-4 font-bold">{{ $mov->producto->nombre ?? 'Sin producto' }}</td>
                        <td class="py-4 text-neutral-400">{{ ucfirst($mov->tipo ?? '-') }}</td>
                        <td class="py-4 text-neutral-500 italic">{{ $mov->user->name ?? 'Sistema' }}</td>
                        <td class="py-4 text-neutral-500 italic">{{ $mov->created_at ? $mov->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-4 text-right font-bold {{ $mov->tipo === 'entrada' ? 'text-emerald-400' : 'text-error' }}">
                            {{ $mov->tipo === 'entrada' ? '+' : '-' }}{{ number_format($mov->cantidad, 0, ',', '.') }}
                        </td>
                        <td class="py-4 text-center">
                            <span class="px-2 py-1 text-[10px] uppercase font-bold tracking-tighter
                                {{ $mov->tipo === 'entrada' ? 'bg-emerald-950 text-emerald-500' : 'bg-blue-950 text-blue-500' }}">
                                {{ ucfirst($mov->tipo) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-neutral-500 italic text-sm">
                            Sin movimientos registrados aún.
                            <a href="{{ route('inventario.index') }}" class="text-blue-500 hover:underline ml-2">Ir al inventario →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {


    // ── Tabs: filtro de actividad reciente ────────────────────────────────
    var tabs = document.querySelectorAll('#kf-mov-tabs .kf-tab');
    var rows = document.querySelectorAll('tbody tr[data-tipo]');
    

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var selected = this.dataset.tab;

            // estilos de tab activo / inactivo
            tabs.forEach(function (t) {
                t.classList.remove('bg-blue-500', 'text-white', 'shadow');
                t.classList.add('text-neutral-500');
            });
            this.classList.add('bg-blue-500', 'text-white', 'shadow');
            this.classList.remove('text-neutral-500');

            // filtrar filas
            rows.forEach(function (row) {
                var show = selected === 'todos' || row.dataset.tipo === selected;
                row.style.display = show ? '' : 'none';
            });
        });
    });
    
    // ── Count-up en KPI numéricos ─────────────────────────────────────────
    document.querySelectorAll('[data-counter]').forEach(function (el) {
        var target   = parseInt(el.dataset.counter, 10) || 0;
        var prefix   = el.dataset.prefix || '';
        var duration = 900;
        var start    = performance.now();
        function step(now) {
            var p = Math.min((now - start) / duration, 1);
            var ease = 1 - Math.pow(1 - p, 3); // ease-out-cubic
            el.textContent = prefix + Math.round(ease * target).toLocaleString('es-CO');
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });

    // ── Barras del chart: crecer desde 0 ─────────────────────────────────
    var bars = document.querySelectorAll('.bar-anim');
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            bars.forEach(function (bar, i) {
                setTimeout(function () { bar.classList.add('kf-ready'); }, i * 40);
            });
        });
    });

    // ── Filas tabla con stagger ───────────────────────────────────────────
    document.querySelectorAll('.row-stagger').forEach(function (row, i) {
        setTimeout(function () { row.classList.add('kf-visible'); }, 80 + i * 45);
    });

    // ── Toggle: panel de accesos rápidos ──────────────────────────────────
    var panel     = document.getElementById('kf-accesos-panel');
    var toggleBtn = document.getElementById('kf-accesos-toggle');
    var iconEl    = document.getElementById('kf-accesos-icon');
    var labelEl   = document.getElementById('kf-accesos-label');
    var isOpen    = localStorage.getItem('kf_accesos_open') !== 'false';

    function applyAccesosState(animate) {
        if (isOpen) {
            panel.style.maxHeight = panel.scrollHeight + 'px';
            panel.style.opacity   = '1';
            iconEl.textContent    = 'expand_less';
            labelEl.textContent   = 'Ocultar';
        } else {
            panel.style.maxHeight = '0';
            panel.style.opacity   = '0';
            iconEl.textContent    = 'expand_more';
            labelEl.textContent   = 'Mostrar';
        }
    }

    // estado inicial sin animación
    panel.style.transition  = 'max-height .35s cubic-bezier(.4,0,.2,1), opacity .3s ease';
    panel.style.overflow    = 'hidden';
    panel.style.maxHeight   = isOpen ? panel.scrollHeight + 'px' : '0';
    panel.style.opacity     = isOpen ? '1' : '0';
    iconEl.textContent      = isOpen ? 'expand_less' : 'expand_more';
    labelEl.textContent     = isOpen ? 'Ocultar'     : 'Mostrar';

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            isOpen = !isOpen;
            localStorage.setItem('kf_accesos_open', isOpen);
            applyAccesosState(true);
        });
    }


});

// ── Auto-refresh KPIs cada 5 minutos ──────────────────────────────────────
(function () {
    const kpiMap = {
        total_productos:  document.querySelector('[data-kpi="total_productos"]'),
        alertas_stock:    document.querySelector('[data-kpi="alertas_stock"]'),
        valor_inventario: document.querySelector('[data-kpi="valor_inventario"]'),
        movimientos_hoy:  document.querySelector('[data-kpi="movimientos_hoy"]'),
    };

    function flashUpdate(el, newText) {
        if (!el || el.textContent === newText) return;
        el.textContent = newText;
        el.classList.remove('kf-num-updated');
        void el.offsetWidth; // reflow para reiniciar animación
        el.classList.add('kf-num-updated');
    }

    function refreshKpis() {
        fetch('{{ route("api.kpis") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                flashUpdate(kpiMap.total_productos,  data.total_productos.toLocaleString('es-CO'));
                flashUpdate(kpiMap.alertas_stock,    data.alertas_stock.toLocaleString('es-CO'));
                flashUpdate(kpiMap.movimientos_hoy,  data.movimientos_hoy.toLocaleString('es-CO'));
                flashUpdate(kpiMap.valor_inventario, '$ ' + Number(data.valor_inventario).toLocaleString('es-CO', {maximumFractionDigits: 0}));
            })
            .catch(() => {});
    }

    setInterval(refreshKpis, 5 * 60 * 1000); // cada 5 minutos
})();

// ── Gráfica de líneas semanal ─────────────────────────────────────────────
(function () {
    var raw = [];
    try { raw = JSON.parse(document.getElementById('dash-chart-data').textContent); } catch (e) {}
    var chartUrl = '{{ route("api.chart-data") }}';

    function mkChart(data) {
        var ctx = document.getElementById('dash-semanal-chart');
        if (!ctx || typeof Chart === 'undefined') return;
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(function (d) { return d.label; }),
                datasets: [
                    {
                        label: 'Entradas', data: data.map(function (d) { return d.entradas; }),
                        borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.07)',
                        fill: true, tension: 0.42, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5
                    },
                    {
                        label: 'Salidas', data: data.map(function (d) { return d.salidas; }),
                        borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.05)',
                        fill: true, tension: 0.42, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5
                    },
                    {
                        label: 'Ingresos (COP)', data: data.map(function (d) { return parseFloat(d.ingresos) || 0; }),
                        borderColor: '#38bdf8', backgroundColor: 'transparent',
                        fill: false, tension: 0.42, borderWidth: 1.5, pointRadius: 2, borderDash: [], yAxisID: 'yCOP'
                    },
                    {
                        label: 'Egresos (COP)', data: data.map(function (d) { return parseFloat(d.egresos) || 0; }),
                        borderColor: '#f87171', backgroundColor: 'transparent',
                        fill: false, tension: 0.42, borderWidth: 1.5, pointRadius: 2, borderDash: [4, 3], yAxisID: 'yCOP'
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(10,10,10,0.92)', borderColor: 'rgba(245,158,11,0.25)', borderWidth: 1,
                        titleColor: '#f59e0b', bodyColor: '#d4d4d4', padding: 10
                    }
                },
                scales: {
                    x:    { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#6b7280', font: { size: 9 } } },
                    y:    { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#6b7280', font: { size: 9 } }, beginAtZero: true },
                    yCOP: { position: 'right', grid: { drawOnChartArea: false }, beginAtZero: true,
                            ticks: { color: '#6b7280', font: { size: 9 }, callback: function (v) { return '$' + (v/1000).toFixed(0) + 'k'; } } }
                },
                elements: { line: { tension: 0.42 } },
                animation: { duration: 800, easing: 'easeInOutQuart' }
            }
        });
    }

    // Carga inicial con data SSR, luego refresca con datos COP desde API
    window.addEventListener('load', function () {
        var chart = mkChart(raw);
        // actualizar con datos financieros reales
        fetch(chartUrl + '?group=dias', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (chart) chart.destroy();
                mkChart(data);
            })
            .catch(function () {});
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.js"
        integrity="sha256-/tanOfjQ8GhxdN5s0UdF/A/HgJFEqxE9IpCKJr8Nf+o="
        crossorigin="anonymous"></script>
@endpush
