@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Editar Pedido {{ $pedido->numero_pedido }}</h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pedidos.update', $pedido) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                <h5>Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select name="metodo_pago" class="form-control @error('metodo_pago') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            <option value="efectivo" {{ $pedido->metodo_pago === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="tarjeta" {{ $pedido->metodo_pago === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            <option value="transferencia" {{ $pedido->metodo_pago === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="pendiente" {{ $pedido->metodo_pago === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                        @error('metodo_pago')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha Entrega Esperada</label>
                        <input type="datetime-local" name="fecha_entrega_esperada" class="form-control @error('fecha_entrega_esperada') is-invalid @enderror" value="{{ $pedido->fecha_entrega_esperada?->format('Y-m-d\TH:i') }}">
                        @error('fecha_entrega_esperada')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="cliente_id">Cliente (opcional)</label>
                    <select class="form-control" id="cliente_id" name="cliente_id">
                        <option value="">Sin cliente asociado</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ $pedido->cliente_id == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ $pedido->descripcion }}</textarea>
                    @error('descripcion')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Productos</h5>
            </div>
            <div class="card-body">
                <div id="productos-container">
                    @foreach ($pedido->detalles as $index => $detalle)
                        <div class="producto-item mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Producto</label>
                                    <select name="productos[{{ $index }}][id]" class="form-control producto-select @error("productos.$index.id") is-invalid @enderror" required>
                                        <option value="">Seleccionar producto...</option>
                                        @foreach ($productos as $producto)
                                            <option value="{{ $producto->id }}" data-precio="{{ $producto->precio_venta }}" {{ $detalle->producto_id === $producto->id ? 'selected' : '' }}>
                                                {{ $producto->nombre }} (Stock: {{ $producto->stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" name="productos[{{ $index }}][cantidad]" class="form-control cantidad-input @error("productos.$index.cantidad") is-invalid @enderror" min="1" value="{{ $detalle->cantidad }}" required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Precio</label>
                                    <input type="number" name="productos[{{ $index }}][precio]" class="form-control precio-input @error("productos.$index.precio") is-invalid @enderror" step="0.01" min="0" value="{{ $productos->firstWhere('id', $detalle->producto_id)?->precio_venta ?? $detalle->precio }}" readonly required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Subtotal</label>
                                    <input type="text" class="form-control subtotal-display" readonly>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm w-100 remove-producto">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-secondary mt-3" id="agregar-producto">
                    <i class="bi bi-plus"></i> Agregar Producto
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 offset-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total:</span>
                            <strong id="total-pedido">$0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar Pedido</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let contadorProductos = {{ count($pedido->detalles) }};

    function actualizarSubtotales() {
        let total = 0;
        document.querySelectorAll('.producto-item').forEach((item, index) => {
            const cantidad = parseFloat(item.querySelector('.cantidad-input').value) || 0;
            const precio = parseFloat(item.querySelector('.precio-input').value) || 0;
            const subtotal = cantidad * precio;
            item.querySelector('.subtotal-display').value = '$' + subtotal.toFixed(2);
            total += subtotal;
        });
        document.getElementById('total-pedido').textContent = '$' + total.toFixed(2);
    }

    // Actualizar subtotales al cargar
    actualizarSubtotales();

    document.getElementById('agregar-producto').addEventListener('click', function() {
        const container = document.getElementById('productos-container');
        const primerItem = document.querySelector('.producto-item');
        const nuevoItem = primerItem.cloneNode(true);
        
        // Actualizar índices
        nuevoItem.querySelectorAll('input, select').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/\[\d+\]/, `[${contadorProductos}]`));
            }
            el.value = el.type === 'number' ? (el.name.includes('cantidad') ? 1 : 0) : '';
            el.classList.remove('is-invalid');
        });

        container.appendChild(nuevoItem);
        agregarEventosProducto(nuevoItem);
        contadorProductos++;
    });

    function agregarEventosProducto(item) {
        const selectProducto = item.querySelector('.producto-select');
        const cantidadInput = item.querySelector('.cantidad-input');
        const precioInput = item.querySelector('.precio-input');
        const btnEliminar = item.querySelector('.remove-producto');

        selectProducto.addEventListener('change', function() {
            const option = this.selectedOptions[0];
            precioInput.value = option.dataset.precio || 0;
            actualizarSubtotales();
        });

        cantidadInput.addEventListener('input', actualizarSubtotales);
        precioInput.addEventListener('input', actualizarSubtotales);

        btnEliminar.addEventListener('click', function() {
            item.remove();
            actualizarSubtotales();
        });
    }

    // Agregar eventos a todos los productos existentes
    document.querySelectorAll('.producto-item').forEach(item => {
        agregarEventosProducto(item);
    });
});
</script>
@endsection
