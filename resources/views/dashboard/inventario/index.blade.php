@extends('dashboard.layouts.dashboard')
@section('title', 'Sivia Pos | Inventario')

@section('content')
<div class="px-8 py-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">

    {{-- TITULO IZQUIERDA --}}
    <div>
        <h1 class="text-3xl font-bold text-white">Inventario</h1>
        <p class="text-neutral-400 text-sm">Control de productos y stock</p>
    </div>

    {{-- BOTON DERECHA --}}
    <button onclick="abrirAjusteStock()"
        class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-500 
        hover:from-blue-500 hover:to-blue-400 text-white px-4 py-2 rounded-xl 
        text-sm font-semibold shadow-lg shadow-blue-500/20 transition-all">

        <span class="material-symbols-outlined text-[18px]">tune</span>
        Ajustar Stock
    </button>

</div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl">
            <p class="text-xs text-neutral-400">Total productos</p>
            <h3 class="text-2xl text-white font-bold">{{ $totalProductos }}</h3>
        </div>

        <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl">
            <p class="text-xs text-neutral-400">Alertas</p>
            <h3 class="text-2xl font-bold {{ $alertasStock ? 'text-red-400' : 'text-green-400' }}">
                {{ $alertasStock }}
            </h3>
        </div>

        <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl">
            <p class="text-xs text-neutral-400">Valor inventario</p>
            <h3 class="text-xl text-green-400 font-bold">
                ${{ number_format($valorInventario, 0, ',', '.') }}
            </h3>
        </div>

        <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl">
            <p class="text-xs text-neutral-400">Categorías</p>
            <h3 class="text-2xl text-white font-bold">
                {{ $categorias->count() }}
            </h3>
        </div>

    </div>

    {{-- BUSCADOR --}}
    <form method="GET" class="grid md:grid-cols-3 gap-3 mb-6">

        <input type="text" name="search"
        placeholder="Buscar producto..."
        value="{{ request('search') }}"
        class="bg-neutral-900 border border-neutral-800 rounded-lg px-4 py-2 text-white">

        <select name="categoria"
        class="bg-neutral-900 border border-neutral-800 rounded-lg px-4 py-2 text-white">
            <option value="todos">Todas</option>
            @foreach($categorias as $id => $cat)
                <option value="{{ $id }}" @selected((string) request('categoria') === (string) $id)>{{ $cat }}</option>
            @endforeach
        </select>

        <button class="bg-blue-600 hover:bg-blue-500 text-white rounded-lg px-4 py-2">
            Filtrar
        </button>

    </form>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach($productos as $p)

        <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-4 hover:border-blue-500 transition">

            {{-- IMAGEN --}}
            @if($p->imagen)
                <div class="w-full h-40 mb-3 rounded-xl overflow-hidden bg-neutral-800">
                    <img src="{{ asset('storage/' . $p->imagen) }}"
                        class="w-full h-full object-cover">
                </div>
            @endif

            {{-- NOMBRE + ESTADO --}}
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-white">{{ $p->nombre }}</h2>

                @php
                    if ($p->stock <= $p->stock_minimo) {
                        $estado = 'Crítico';
                        $color = 'bg-red-500/20 text-red-400';
                    } elseif ($p->stock <= $p->stock_minimo * 2) {
                        $estado = 'Bajo';
                        $color = 'bg-yellow-500/20 text-yellow-400';
                    } else {
                        $estado = 'Óptimo';
                        $color = 'bg-green-500/20 text-green-400';
                    }
                @endphp

                <span class="text-xs px-2 py-1 rounded-full {{ $color }}">
                    {{ $estado }}
                </span>
            </div>

            <p class="text-sm text-neutral-400 mb-3">{{ $p->categoria?->nombre ?? $p->getAttribute('categoria') ?? 'Sin categoría' }}</p>

            {{-- INFO --}}
            <div class="grid grid-cols-2 gap-3 text-sm">

                <div class="bg-neutral-800/60 border border-neutral-700 p-2 rounded-lg">
                    <p class="text-xs text-neutral-400">Stock</p>
                    <p class="font-bold text-white">{{ $p->stock }}</p>
                </div>

                <div class="bg-neutral-800/60 border border-neutral-700 p-2 rounded-lg">
                    <p class="text-xs text-neutral-400">Mínimo</p>
                    <p class="font-bold text-white">{{ $p->stock_minimo }}</p>
                </div>

                <div class="bg-neutral-800/60 border border-neutral-700 p-2 rounded-lg">
                    <p class="text-xs text-neutral-400">Compra</p>
                    <p class="font-bold text-white">
                        ${{ number_format($p->precio_compra, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-neutral-800/60 border border-neutral-700 p-2 rounded-lg">
                    <p class="text-xs text-neutral-400">Venta</p>
                    <p class="font-bold text-green-400">
                        ${{ number_format($p->precio_venta, 0, ',', '.') }}
                    </p>
                </div>

            </div>

        </div>

        @endforeach

    </div>

    <div class="mt-6">
        {{ $productos->links() }}
    </div>

    <div id="modalStock" class="fixed inset-0 bg-black/70 hidden z-50 flex justify-center items-center">

    <div class="bg-neutral-950 border border-neutral-800 rounded-2xl p-6 w-full max-w-md">

        <h2 class="text-xl font-bold text-white mb-4">Ajustar Stock</h2>

        <form method="POST" action="{{ route('productos.ajustarStock') }}" class="space-y-4">
            @csrf

            {{-- PRODUCTO --}}
            <div>
                <label class="text-sm text-neutral-400">Producto</label>
                <select name="producto_id" class="w-full mt-1 px-3 py-2 rounded-lg bg-neutral-900 border border-neutral-700 text-white">
                    @foreach($productos as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->nombre }} (Stock: {{ $p->stock }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- TIPO --}}
            <div>
                <label class="text-sm text-neutral-400">Tipo de ajuste</label>
                <select name="tipo" class="w-full mt-1 px-3 py-2 rounded-lg bg-neutral-900 border border-neutral-700 text-white">
                    <option value="sumar">➕ Agregar stock</option>
                    <option value="restar">➖ Disminuir stock</option>
                </select>
            </div>

            {{-- CANTIDAD --}}
            <div>
                <label class="text-sm text-neutral-400">Cantidad</label>
                <input type="number" name="cantidad" min="1" required
                class="w-full mt-1 px-3 py-2 rounded-lg bg-neutral-900 border border-neutral-700 text-white">
            </div>

            {{-- BOTONES --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-lg font-semibold">
                    Guardar
                </button>

                <button type="button" onclick="cerrarAjusteStock()"
                class="flex-1 border border-neutral-700 text-neutral-300 py-2 rounded-lg">
                    Cancelar
                </button>
            </div>

        </form>

    </div>
</div>

</div>
@endsection

<script>
    function abrirAjusteStock(){
        document.getElementById('modalStock').classList.remove('hidden');
    }

    function cerrarAjusteStock(){
        document.getElementById('modalStock').classList.add('hidden');
    }
</script>