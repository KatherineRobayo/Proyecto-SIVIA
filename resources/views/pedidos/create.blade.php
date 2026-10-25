@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Crear Nuevo Pedido</h1>
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

    <form action="{{ route('pedidos.store') }}" method="POST">
        @csrf

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
                            <option value="efectivo" {{ old('metodo_pago') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="tarjeta" {{ old('metodo_pago') === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            <option value="transferencia" {{ old('metodo_pago') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="pendiente" {{ old('metodo_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                        @error('metodo_pago')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha Entrega Esperada</label>
                        <input type="datetime-local" name="fecha_entrega_esperada" class="form-control @error('fecha_entrega_esperada') is-invalid @enderror" value="{{ old('fecha_entrega_esperada') }}">
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
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion') }}</textarea>
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
                    <div class="producto-item mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Producto</label>
                                <select name="productos[0][id]" class="form-control producto-select @error('productos.0.id') is-invalid @enderror" required>
                                    <option value="">Seleccionar producto...</option>
                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto->id }}" data-precio="{{ $producto->precio_venta }}">
                                            {{ $producto->nombre }} (Stock: {{ $producto->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="productos[0][cantidad]" class="form-control cantidad-input @error('productos.0.cantidad') is-invalid @enderror" min="1" value="{{ old('productos.0.cantidad', 1) }}" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Precio</label>
                                <input type="number" name="productos[0][precio]" class="form-control precio-input @error('productos.0.precio') is-invalid @enderror" step="0.01" min="0" value="0" readonly required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control subtotal-display" readonly>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm w-100 remove-producto" style="display:none;">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
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
            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear Pedido</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let contadorProductos = 1;

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

    document.getElementById('agregar-producto').addEventListener('click', function() {
        const container = document.getElementById('productos-container');
        const nuevoItem = document.querySelector('.producto-item').cloneNode(true);
        
        // Actualizar índices
        nuevoItem.querySelectorAll('input, select').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/\[\d+\]/, `[${contadorProductos}]`));
            }
            el.value = el.type === 'number' ? (el.name.includes('cantidad') ? 1 : 0) : '';
            el.classList.remove('is-invalid');
        });

        // Mostrar botón eliminar
        nuevoItem.querySelector('.remove-producto').style.display = 'block';

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

    // Agregar eventos a producto inicial
    agregarEventosProducto(document.querySelector('.producto-item'));
});
</script>
@endsection
