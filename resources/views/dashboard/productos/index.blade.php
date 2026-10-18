@extends('dashboard.layouts.dashboard')

@section('title','SIVIA | Productos')

@section('content')

@php
$productos = $productos ?? collect();
@endphp

<div class="px-6 py-6 md:px-8">

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500 text-green-400">
            {{ session('success') }}
        </div>
    @endif

{{-- ================= HEADER ================= --}}
<div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 mb-8">

    <div>
        <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight flex items-center gap-3">
            <span class="material-symbols-outlined text-blue-400 text-[34px]">inventory_2</span>
            Gestión de Productos
        </h1>

        <p class="text-neutral-400 mt-2">
            Administra inventario, stock, precios y rendimiento comercial.
        </p>
    </div>

    <div class="flex items-center gap-3 flex-nowrap overflow-x-auto pb-1">

    {{-- NUEVO --}}
    <button onclick="openModal()"
    class="h-12 px-4 rounded-xl flex items-center gap-2 whitespace-nowrap
    bg-blue-600 hover:bg-blue-500 text-white font-semibold transition-all">

        <span class="material-symbols-outlined text-[20px]">add</span>
        Nuevo Producto
    </button>

    {{-- VOZ --}}
    <button
    class="h-12 px-4 rounded-xl flex items-center gap-2 whitespace-nowrap
    border border-cyan-500/30 bg-cyan-500/10 text-cyan-300 hover:bg-cyan-500/20 transition-all">

        <span class="material-symbols-outlined text-[20px]">mic</span>
        Crear por Voz
    </button>

    {{-- IMPORTAR --}}
    <button
    class="h-12 px-4 rounded-xl flex items-center gap-2 whitespace-nowrap
    border border-neutral-700 text-neutral-300 hover:border-white transition-all">

        <span class="material-symbols-outlined text-[20px]">upload_file</span>
        Importar
    </button>

</div>

</div>


{{-- ================= KPI ================= --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">

    <div class="rounded-2xl bg-neutral-900 border border-neutral-800 p-5">
        <p class="text-sm text-neutral-400">Productos Registrados</p>
        <h3 class="text-3xl font-bold text-white mt-2">{{ $productos->count() }}</h3>
    </div>

    <div class="rounded-2xl bg-neutral-900 border border-neutral-800 p-5">
        <p class="text-sm text-neutral-400">Categorías</p>
        <h3 class="text-3xl font-bold text-cyan-400 mt-2">
            {{ $productos->pluck('categoria')->filter()->unique()->count() }}
        </h3>
    </div>

    <div class="rounded-2xl bg-neutral-900 border border-neutral-800 p-5">
        <p class="text-sm text-neutral-400">Bajo Stock</p>
        <h3 class="text-3xl font-bold text-red-400 mt-2">
            {{ $productos->filter(fn($p)=>$p->stock <= $p->stock_minimo)->count() }}
        </h3>
    </div>

    <div class="rounded-2xl bg-neutral-900 border border-neutral-800 p-5">
        <p class="text-sm text-neutral-400">Valor Inventario</p>
        <h3 class="text-2xl font-bold text-emerald-400 mt-2">
            ${{ number_format($productos->sum(fn($p)=>$p->stock * $p->precio_compra),0,',','.') }}
        </h3>
    </div>

</div>



{{-- ================= BUSCADOR ================= --}}
<div class="rounded-2xl bg-neutral-900 border border-neutral-800 p-4 mb-8 flex flex-col lg:flex-row gap-3">

    <div class="relative w-full lg:w-[420px]">

        <span class="material-symbols-outlined absolute left-4 top-3 text-neutral-500">
            search
        </span>

        <input type="text"
        id="buscadorProductos"
        placeholder="Buscar producto..."
        class="w-full pl-12 pr-4 py-3 rounded-xl bg-neutral-950 border border-neutral-800 text-white focus:border-blue-500 outline-none">

    </div>

    <select id="filtroProductos"
    class="px-4 py-3 rounded-xl bg-neutral-950 border border-neutral-800 text-white">

        <option value="todos">Todos</option>
        <option value="precio_alto">Precio Alto</option>
        <option value="precio_bajo">Precio Bajo</option>
        <option value="stock_bajo">Bajo Stock</option>
        <option value="bebidas">Bebidas</option>
        <option value="comidas">Comidas</option>
        <option value="postres">Postres</option>
        <option value="farmacia">Farmacia</option>

    </select>

</div>



{{-- ================= PRODUCTOS ================= --}}

<div id="gridProductos"
class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

@forelse($productos as $producto)

<div class="producto-card w-full max-w-[280px] h-[460px] 
rounded-2xl bg-neutral-900 border border-neutral-800 
hover:border-blue-500/40 hover:scale-[1.02] transition-all p-4 
flex flex-col justify-between mx-auto"
data-nombre="{{ strtolower($producto->nombre) }}"
data-categoria="{{ strtolower($producto->categoria) }}"
data-precio="{{ $producto->precio_venta }}"
data-stock="{{ $producto->stock }}"
data-minimo="{{ $producto->stock_minimo }}">


    {{-- IMAGEN --}}
    <div class="flex justify-center mb-4">
    <div class="bg-neutral-950 rounded-xl p-4">
        @if($producto->imagen)
            <img src="{{ asset('storage/'.$producto->imagen) }}"
            class="h-32 object-contain mx-auto">
        @else
            <span class="material-symbols-outlined text-[60px] text-neutral-700">
                inventory_2
            </span>
        @endif
    </div>
</div>


    {{-- INFO --}}
    <div class="mb-4">

        <h3 class="text-xl font-bold text-white">
            {{ $producto->nombre }}
        </h3>

        <p class="text-sm text-neutral-400">
            {{ $producto->categoria ?? 'General' }}
        </p>

    </div>

    <div class="flex justify-between items-center mb-4 px-1">

        {{-- PRECIO --}}
        <div>
            <p class="text-[11px] text-neutral-500">Precio</p>
            <p class="text-lg font-bold text-emerald-400">
                ${{ number_format($producto->precio_venta,0,',','.') }}
            </p>
        </div>

        {{-- SEPARADOR --}}
        <div class="w-px h-8 bg-neutral-800"></div>

        {{-- STOCK --}}
         <div class="text-right">
            <p class="text-[11px] text-neutral-500">Stock</p>
            <p class="text-sm font-semibold text-white">
                 {{ $producto->stock }}
            </p>

        @if($producto->stock <= $producto->stock_minimo)
            <p class="text-[11px] text-red-400">Bajo</p>
        @else
            <p class="text-[11px] text-emerald-400">Disponible</p>
        @endif
    </div>

</div>
    {{-- BOTONES --}}
    <div class="flex gap-2 mt-4">

    <button onclick='editarProducto(@json($producto))'
        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-lg 
        bg-blue-600 hover:bg-blue-500 text-white text-sm transition-all">

    <span class="material-symbols-outlined text-[18px]">edit</span>
        Editar
    </button>

    {{-- HISTORIAL --}}
    <button onclick="alert('Funcionalidad futura: historial del producto')"
    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-lg 
    border border-neutral-700 text-neutral-400 
    hover:text-blue-400 hover:border-blue-400 hover:bg-blue-500/10
    text-sm transition-all duration-200">

        <span class="material-symbols-outlined text-[18px]">bar_chart</span>
    </button>


</div>

{{-- ELIMINAR ABAJO --}}
<form method="POST" action="{{ route('productos.destroy',$producto) }}" class="mt-2">
@csrf
@method('DELETE')

<button type="submit"
onclick="return confirm('¿Eliminar producto?')"
class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg 
border border-red-500/40 text-red-400 
hover:bg-red-500/10 hover:border-red-400
text-sm transition-all duration-200">

    <span class="material-symbols-outlined text-[18px]">delete</span>
    Eliminar
</button>

</form>
</div>

@empty

<div class="col-span-full rounded-2xl bg-neutral-900 border border-neutral-800 p-10 text-center">
    <p class="text-neutral-400 text-lg">
        No hay productos registrados.
    </p>
</div>

@endforelse

</div>

</div>



{{-- ================= MODAL ================= --}}
<div id="modalProducto"
class="fixed inset-0 bg-black/70 hidden z-50 flex justify-end">

<div class="w-full max-w-xl bg-neutral-950 h-full overflow-y-auto p-8">

<div class="flex justify-between items-center mb-6">

<h2 class="text-2xl font-bold text-white">
Nuevo Producto
</h2>

<button onclick="closeModal()" class="text-neutral-400 hover:text-white">
✕
</button>

</div>

@if ($errors->any())
<div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500 text-red-400">
    @foreach ($errors->all() as $error)
        <p class="text-sm">{{ $error }}</p>
    @endforeach
</div>
@endif

<form id="formProducto"
method="POST"
action="{{ route('productos.store') }}"
enctype="multipart/form-data"
class="space-y-4">


@csrf
<input type="hidden" name="_method" id="formMethod" value="POST">

<div class="grid grid-cols-2 gap-4">

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">Nombre</label>
        <input id="nombre" type="text" name="nombre" required
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white focus:border-blue-500 outline-none">
    </div>

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">Código</label>
        <input id="codigo" type="text" name="codigo"
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white focus:border-blue-500 outline-none">
    </div>

</div>

<div class="grid grid-cols-2 gap-4">

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">Categoría</label>
        <select id="categoria" name="categoria"
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white">
            <option value="">Selecciona Categoría</option>
            <option>Bebidas</option>
            <option>Comidas</option>
            <option>Postres</option>
            <option>Farmacia</option>
            <option>Aseo</option>
            <option>Lácteos</option>
            <option>Snacks</option>
            <option>Otros</option>
        </select>
    </div>

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">IVA (%)</label>
        <input id="iva" type="number" name="iva"
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white">
    </div>

</div>

<div class="grid grid-cols-2 gap-4">

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">Precio compra</label>
        <input id="precio_compra" type="number" name="precio_compra" min="0" step="100" required
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white">
    </div>

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">Precio venta</label>
        <input id="precio_venta" type="number" name="precio_venta" min="0" step="100" required
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white">
    </div>

</div>

<div class="grid grid-cols-2 gap-4">

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">Stock</label>
        <input id="stock" type="number" name="stock" required
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white">
    </div>

    <div>
        <label class="text-sm text-neutral-400 mb-1 block">Stock mínimo</label>
        <input id="stock_minimo" type="number" name="stock_minimo" required
        class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white">
    </div>

</div>

<div>
    <label class="text-sm text-neutral-400 mb-1 block">Descripción</label>
    <textarea id="descripcion" name="descripcion"
    class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white"></textarea>
</div>

<div>
    <label class="text-sm text-neutral-400 mb-1 block">Imagen</label>
    <input id="imagen" type="file" name="imagen"
    class="w-full px-4 py-3 rounded-xl bg-neutral-900 border border-neutral-800 text-white">
</div>

<button class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition-all">
    Guardar Producto
</button>



<script>

function openModal(){
document.getElementById('modalProducto').classList.remove('hidden');
}

function closeModal(){
    document.getElementById('modalProducto').classList.add('hidden');

    document.getElementById('formProducto').reset();
    document.getElementById('formProducto').action = "{{ route('productos.store') }}";
    document.getElementById('formMethod').value = "POST";

    document.querySelector('#modalProducto h2').innerText = "Nuevo Producto";
}

document.getElementById('buscadorProductos').addEventListener('keyup',filtrar);
document.getElementById('filtroProductos').addEventListener('change',filtrar);

function filtrar(){

let texto = document.getElementById('buscadorProductos').value.toLowerCase();
let filtro = document.getElementById('filtroProductos').value;

document.querySelectorAll('.producto-card').forEach(card=>{

let nombre = card.dataset.nombre;
let categoria = card.dataset.categoria;
let precio = parseFloat(card.dataset.precio);
let stock = parseInt(card.dataset.stock);
let minimo = parseInt(card.dataset.minimo);

let visible = nombre.includes(texto);

if(filtro == 'stock_bajo'){
visible = visible && stock <= minimo;
}

if(filtro == 'bebidas' || filtro == 'comidas' || filtro == 'postres' || filtro == 'farmacia'){
visible = visible && categoria.includes(filtro);
}

card.style.display = visible ? 'block' : 'none';

});

}


function editarProducto(producto){
    console.log(producto); // <- prueba esto

    document.getElementById('modalProducto').classList.remove('hidden');

    document.getElementById('formProducto').action = '/productos/' + producto.id;
    document.getElementById('formMethod').value = 'PUT';

    document.querySelector('#modalProducto h2').innerText = "Editar Producto";

    document.querySelector('[name=nombre]').value = producto.nombre;
    document.querySelector('[name=codigo]').value = producto.codigo ?? '';
    document.querySelector('[name=categoria]').value = producto.categoria ?? '';
    document.querySelector('[name=precio_compra]').value = producto.precio_compra;
    document.querySelector('[name=precio_venta]').value = producto.precio_venta;
    document.querySelector('[name=iva]').value = producto.iva;
    document.querySelector('[name=stock]').value = producto.stock;
    document.querySelector('[name=stock_minimo]').value = producto.stock_minimo;
    document.querySelector('[name=descripcion]').value = producto.descripcion ?? '';
}
</script>



@endsection