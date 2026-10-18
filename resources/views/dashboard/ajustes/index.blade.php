@extends('dashboard.layouts.dashboard')
@section('title', 'Sivia Pos | Ajustes')

@section('content')

<div class="px-8 py-8">

    {{-- HEADER --}}
    <div class="mb-8 flex items-start justify-between flex-wrap gap-4">

        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-400 text-3xl">
                    settings
                </span>
            </div>

            <div>
                <h1 class="text-5xl font-bold text-white leading-none"
                    style="font-family: Georgia, serif;">
                    Ajustes
                </h1>

                <p class="text-neutral-400 italic mt-2 text-sm">
                    Configuración general del sistema SIVIA POS
                </p>
            </div>
        </div>

    </div>


    {{-- MENSAJES --}}
    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 px-5 py-4 rounded-2xl">
            {{ session('success') }}
        </div>
    @endif

    @if(session('password_success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 px-5 py-4 rounded-2xl">
            {{ session('password_success') }}
        </div>
    @endif



    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">


        {{-- IZQUIERDA --}}
        <div class="xl:col-span-2 space-y-6">


            {{-- PERFIL --}}
            <div class="bg-black/60 border border-blue-500/20 rounded-2xl p-6">

                <div class="flex items-center gap-3 mb-5">
                    <span class="material-symbols-outlined text-blue-400">person</span>
                    <h2 class="text-white text-xl font-semibold">Perfil</h2>
                </div>

                <div class="grid md:grid-cols-2 gap-5">

                    <div>
                        <label class="text-xs text-neutral-400 uppercase block mb-2">Nombre</label>
                        <input type="text"
                               disabled
                               value="{{ auth()->user()->name }}"
                               class="w-full bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">
                    </div>

                    <div>
                        <label class="text-xs text-neutral-400 uppercase block mb-2">Correo</label>
                        <input type="text"
                               disabled
                               value="{{ auth()->user()->email }}"
                               class="w-full bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">
                    </div>

                </div>

            </div>



            {{-- CAMBIAR CONTRASEÑA --}}
            <div class="bg-black/60 border border-blue-500/20 rounded-2xl p-6">

                <div class="flex items-center gap-3 mb-5">
                    <span class="material-symbols-outlined text-blue-400">lock</span>
                    <h2 class="text-white text-xl font-semibold">Seguridad</h2>
                </div>

                <form action="{{ route('ajustes.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <input type="password" name="current_password" placeholder="Contraseña actual"
                        class="w-full bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="password" name="password" placeholder="Nueva contraseña"
                        class="w-full bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="password" name="password_confirmation" placeholder="Confirmar nueva contraseña"
                        class="w-full bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <button class="px-5 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-white">
                        Guardar contraseña
                    </button>

                </form>

            </div>



            {{-- EMPRESAS --}}
            <div class="bg-black/60 border border-blue-500/20 rounded-2xl p-6">

                <div class="flex justify-between items-center mb-5">

                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-400">business</span>
                        <h2 class="text-white text-xl font-semibold">Empresas</h2>
                    </div>

                </div>

                {{-- CREAR --}}
                <form action="{{ route('ajustes.empresa.store') }}" method="POST" class="grid md:grid-cols-2 gap-4 mb-6">
                    @csrf

                    <input type="text" name="nombre" placeholder="Nombre empresa"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="text" name="nit" placeholder="NIT"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="text" name="telefono" placeholder="Teléfono"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="email" name="email" placeholder="Correo"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="text" name="direccion" placeholder="Dirección"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white md:col-span-2">

                    <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3 md:col-span-2">
                        Crear Empresa
                    </button>
                </form>


                {{-- LISTADO --}}
                <div class="space-y-3">

                    @foreach($empresas as $empresa)

                    <div class="bg-neutral-900 rounded-xl p-4 flex justify-between items-center">

                        <div>
                            <p class="text-white font-semibold">{{ $empresa->nombre }}</p>
                            <p class="text-neutral-400 text-sm">{{ $empresa->nit }}</p>
                        </div>

                        <form action="{{ route('ajustes.empresa.destroy',$empresa) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button onclick="return confirm('Eliminar empresa?')"
                                class="text-red-400 hover:text-red-300">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>

                    </div>

                    @endforeach

                </div>

            </div>



            {{-- USUARIOS --}}
            <div class="bg-black/60 border border-blue-500/20 rounded-2xl p-6">

                <div class="flex items-center gap-3 mb-5">
                    <span class="material-symbols-outlined text-blue-400">group</span>
                    <h2 class="text-white text-xl font-semibold">Usuarios</h2>
                </div>


                {{-- CREAR --}}
                <form action="{{ route('ajustes.usuario.store') }}" method="POST" class="grid md:grid-cols-2 gap-4 mb-6">
                    @csrf

                    <input type="text" name="name" placeholder="Nombre"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="email" name="email" placeholder="Correo"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <input type="password" name="password" placeholder="Contraseña"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">

                    <select name="role"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white">
                        <option value="admin">Admin</option>
                        <option value="almacenista">Almacenista</option>
                    </select>

                    <select name="empresa_id"
                        class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-3 text-white md:col-span-2">
                        <option value="">Sin empresa</option>

                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}">
                                {{ $empresa->nombre }}
                            </option>
                        @endforeach

                    </select>

                    <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3 md:col-span-2">
                        Crear Usuario
                    </button>

                </form>


                {{-- LISTA --}}
                <div class="space-y-3">

                    @foreach($users as $user)

                    <div class="bg-neutral-900 rounded-xl p-4 flex justify-between items-center">

                        <div>
                            <p class="text-white font-semibold">{{ $user->name }}</p>
                            <p class="text-neutral-400 text-sm">{{ $user->email }}</p>
                            <p class="text-blue-400 text-xs mt-1">{{ $user->role }}</p>
                        </div>

                        @if(auth()->id() != $user->id)

                        <form action="{{ route('ajustes.usuario.destroy',$user) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button onclick="return confirm('Eliminar usuario?')"
                                class="text-red-400 hover:text-red-300">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>

                        @endif

                    </div>

                    @endforeach

                </div>

            </div>

        </div>



        {{-- DERECHA --}}
        <div class="space-y-6">


            {{-- SISTEMA --}}
            <div class="bg-black/60 border border-blue-500/20 rounded-2xl p-6">

                <div class="flex items-center gap-3 mb-5">
                    <span class="material-symbols-outlined text-blue-400">monitoring</span>
                    <h2 class="text-white text-lg font-semibold">Sistema</h2>
                </div>

                <div class="space-y-3 text-sm">

                    <div class="flex justify-between">
                        <span class="text-neutral-400">Versión</span>
                        <span class="text-white">2.0</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-neutral-400">Usuarios</span>
                        <span class="text-white">{{ $users->count() }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-neutral-400">Empresas</span>
                        <span class="text-white">{{ $empresas->count() }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-neutral-400">Estado</span>
                        <span class="text-green-400">Activo</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection