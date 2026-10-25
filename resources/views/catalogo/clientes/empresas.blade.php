@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Negocios disponibles</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="row g-3">
        @forelse ($empresas as $empresa)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">{{ $empresa->nombre }}</h2>
                        @if ($empresa->direccion)
                            <p>{{ $empresa->direccion }}</p>
                        @endif
                        <a class="btn btn-primary" href="{{ route('catalogo.clientes.productos', $empresa) }}">Ver productos</a>
                    </div>
                </div>
            </div>
        @empty
            <p>No hay negocios disponibles.</p>
        @endforelse
    </div>
</div>
@endsection
