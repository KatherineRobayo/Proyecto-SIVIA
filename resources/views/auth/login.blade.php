@extends('layouts.app')

@section('title', 'SIVIA POS | Acceso')

@section('content')

<x-golden-stardust />

<main class="relative min-h-screen flex items-center justify-center metallic-grid px-6">

    {{-- glow decorativo --}}
    <div class="absolute -top-24 -left-24 w-64 h-64 bg-blue-500/10 blur-[100px] rounded-full"></div>
    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-blue-500/10 blur-[100px] rounded-full"></div>

    <div class="w-full max-w-6xl grid md:grid-cols-2 gap-10 items-center">

        {{-- 🔹 IZQUIERDA (LOGO / TEXTO) --}}
        <div class="hidden md:flex flex-col justify-center items-start space-y-6">

            <h1 class="text-5xl font-bold italic text-blue-500 font-serif">
                SIVIA POS
            </h1>

            <p class="text-on-surface-variant max-w-md">
                Sistema inteligente de ventas e inventario.  
                Control total de tu negocio en tiempo real.
            </p>

            <div class="w-16 h-16 flex items-center justify-center rounded-full
                        bg-surface-container-high border border-blue-500/30
                        shadow-[0_0_15px_rgba(59,130,246,0.3)]">
                <span class="material-symbols-outlined text-4xl text-blue-500">
                    point_of_sale
                </span>
            </div>

        </div>

        {{-- 🔹 DERECHA (FORMULARIO ORIGINAL) --}}
        <div class="w-full max-w-md mx-auto">

            <div class="relative bg-surface-container-lowest border border-outline-variant/50
                        rounded-xl overflow-hidden shadow-2xl backdrop-blur-sm">

                {{-- línea superior --}}
                <div class="absolute top-0 left-0 w-full h-1
                            bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-60"></div>

                <div class="p-8 md:p-12">

                    {{-- branding --}}
                    <div class="flex flex-col items-center mb-10 text-center">
                        <div class="w-16 h-16 mb-6 flex items-center justify-center rounded-full
                                    bg-surface-container-high border border-blue-500/30
                                    shadow-[0_0_15px_rgba(59,130,246,0.3)]">
                            <span class="material-symbols-outlined text-4xl text-blue-500">
                                point_of_sale
                            </span>
                        </div>

                        <h1 class="font-headline text-3xl font-bold italic tracking-tight
                                   text-blue-500">
                            SIVIA POS
                        </h1>

                        <p class="text-on-surface-variant text-sm mt-2">
                            Sistema Inteligente
                        </p>
                    </div>

                    {{-- FORMULARIO ORIGINAL --}}
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        {{-- EMAIL --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-widest text-blue-400/80 ml-1">
                                Correo electrónico
                            </label>

                            <div class="relative group">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                                             text-on-surface-variant group-focus-within:text-blue-500">
                                    mail
                                </span>

                                <input type="email" name="email" required
                                       class="w-full !bg-neutral-900 !text-white
                                            pl-12 py-3 rounded-lg outline-none
                                            border border-neutral-700
                                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                            placeholder:text-gray-400 transition-all duration-300">
                            </div>
                        </div>

                        {{-- PASSWORD --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-widest text-blue-400/80 ml-1">
                                Contraseña
                            </label>

                            <div class="relative group">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                                             text-on-surface-variant group-focus-within:text-blue-500">
                                    lock
                                </span>

                                <input id="password" type="password" name="password" required
                                       class="w-full !bg-neutral-900 !text-white
                                            pl-12 py-3 rounded-lg outline-none
                                            border border-neutral-700
                                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                            placeholder:text-gray-400 transition-all duration-300">

                                <button type="button" id="togglePassword"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-blue-500">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </div>
                        </div>

                        {{-- REMEMBER --}}
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="remember"
                                   class="w-4 h-4 rounded bg-surface-container-high text-blue-500">
                            <span class="text-xs text-on-surface-variant">
                                Mantener sesión iniciada
                            </span>
                        </div>

                        {{-- BOTÓN ORIGINAL (solo azul) --}}
                        <button type="submit"
                                class="w-full group relative overflow-hidden
                                       bg-blue-500 hover:bg-blue-400
                                       text-black font-bold py-4 rounded-lg
                                       shadow-lg shadow-blue-500/20
                                       transition-all duration-500">

                            <span class="relative z-10 flex items-center justify-center gap-2">
                                Ingresar
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </span>

                            <div class="absolute inset-0
                                        bg-gradient-to-r from-white/0 via-white/20 to-white/0
                                        -translate-x-full group-hover:translate-x-full
                                        transition-transform duration-700"></div>
                        </button>

                    </form>

                </div>

                {{-- línea inferior --}}
                <div class="h-1.5 bg-gradient-to-r from-blue-900/40 via-blue-500/40 to-blue-900/40"></div>

            </div>

        </div>

    </div>

</main>

@endsection