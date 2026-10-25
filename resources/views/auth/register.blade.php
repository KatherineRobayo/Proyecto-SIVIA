@extends('layouts.app')

@section('title', 'KitchenFlow | Registrar Personal')

@section('content')

    {{-- [QoL4 Golden Stardust Background] --}}
<x-golden-stardust />

{{-- ═
     Hero background
--}}
<div class="fixed inset-0 z-0 opacity-20 pointer-events-none">
    <img src="{{ asset('images/hero-kitchen.webp') }}"
         class="w-full h-full object-cover"
         alt=""
         onerror="this.style.display='none'">
    <div class="absolute inset-0 bg-gradient-to-b from-background via-transparent to-background"></div>
</div>

{{-- Decoraciom de glows --}}
<div class="fixed bottom-0 right-0 w-64 h-64 bg-blue-500/5 blur-[120px] rounded-full pointer-events-none z-0"></div>
<div class="fixed top-0 left-0 w-96 h-96 bg-blue-900/5 blur-[150px] rounded-full pointer-events-none z-0"></div>

{{-- 
     MAIN
--}}
<main class="relative z-10 min-h-screen flex items-center justify-center
             px-4 py-12">

    <div class="w-full max-w-6xl flex flex-col md:flex-row gap-12 items-stretch">

        {{-- ═
             Columna izquierda — Brand + value props
        --}}
        <section class="flex-1 flex flex-col justify-center space-y-8">
            <div>
                <h1 class="text-blue-500 font-headline text-5xl font-bold italic tracking-tight
                           drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
                    Sivia Pos
                </h1>
                <p class="text-blue-400/80 font-headline text-xl mt-2 italic">
                    Management Suite
                </p>
            </div>

            <div class="space-y-6">
                <h2 class="text-white font-headline text-4xl leading-tight">
                    Bienvenido al <span class="text-primary-container">sistema</span>
                    de Gestión Integral.
                </h2>
                <p class="text-neutral-300 text-lg max-w-md">
                    Crea una cuenta de cliente para realizar pedidos a las empresas
                    registradas en SIVIA.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-neutral-800 border border-blue-900/20 rounded-lg">
                    <span class="material-symbols-outlined text-blue-500 mb-2 block">dashboard</span>
                    <h3 class="text-blue-400 font-bold">Dashboard Inteligente</h3>
                    <p class="text-xs text-neutral-400 mt-1">Métricas en tiempo real</p>
                </div>
                <div class="p-4 bg-neutral-800 border border-blue-900/20 rounded-lg">
                    <span class="material-symbols-outlined text-blue-500 mb-2 block">inventory_2</span>
                    <h3 class="text-blue-400 font-bold">Control de Stock</h3>
                    <p class="text-xs text-neutral-400 mt-1">Seguimiento automatizado</p>
                </div>
            </div>

            {{-- Link de acceso --}}
            <div>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 text-sm text-blue-500/70
                          hover:text-blue-400 transition-colors font-serif">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Volver al inicio de sesión
                </a>
            </div>
        </section>

        {{-- ══════════════════════════════════════════
             COLUMNA DERECHA — Formulario de registro
        ═══════════════════════════════════════════ --}}
        <section class="flex-1">
            <div class="bg-neutral-900/80 backdrop-blur-md border border-blue-900/30
                        p-8 md:p-12 rounded-xl shadow-2xl relative overflow-hidden">

                {{-- línea azul superior --}}
                <div class="absolute top-0 left-0 w-full h-1
                            bg-gradient-to-r from-transparent via-blue-500 to-transparent"></div>

                <header class="mb-10 text-center">
                    <h2 class="text-2xl font-headline font-bold text-white
                               tracking-widest uppercase">
                        Crear cuenta de cliente
                    </h2>
                    <div class="w-12 h-px bg-blue-500 mx-auto mt-2"></div>
                    <p class="text-xs text-neutral-500 mt-3 italic">
                        El registro público crea cuentas sin permisos administrativos
                    </p>
                </header>

                {{-- ── errores de validacion (bloque general) ── --}}
                @if($errors->any())
                    <div class="mb-6 px-4 py-3 bg-error-container/20 border border-error/30
                                rounded-lg text-error text-sm font-serif space-y-1">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- ── Mensaje de exito ── --}}
                @if(session('success'))
                    <div class="mb-6 px-4 py-3 bg-green-900/20 border border-green-700/40
                                rounded-lg text-green-400 text-sm font-serif">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    {{-- nombre completo --}}
                    <div class="space-y-2">
                        <label for="name"
                               class="block text-sm font-bold text-blue-400/80
                                      tracking-wide uppercase">
                            Nombre completo
                        </label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                                         text-neutral-500 group-focus-within:text-blue-500 transition-colors">
                                person
                            </span>
                            <input id="name"
                                   name="name"
                                   type="text"
                                   value="{{ old('name') }}"
                                   required
                                   autocomplete="name"
                                   placeholder="Juan Pérez"
                                   class="w-full bg-neutral-800 text-white
                                          pl-12 pr-4 py-4 rounded-lg outline-none
                                          border {{ $errors->has('name') ? 'border-error/60' : 'border-blue-900/20' }}
                                          focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                          transition-all placeholder:text-neutral-600">
                        </div>
                        @error('name')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Correo electrónico --}}
                    <div class="space-y-2">
                        <label for="email"
                               class="block text-sm font-bold text-blue-400/80
                                      tracking-wide uppercase">
                            Correo electrónico
                        </label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                                         text-neutral-500 group-focus-within:text-blue-500 transition-colors">
                                mail
                            </span>
                            <input id="email"
                                   name="email"
                                   type="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="email"
                                   placeholder="empleado@kitchenflow.com"
                                   class="w-full bg-neutral-800 text-white
                                          pl-12 pr-4 py-4 rounded-lg outline-none
                                          border {{ $errors->has('email') ? 'border-error/60' : 'border-blue-900/20' }}
                                          focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                          transition-all placeholder:text-neutral-600">
                        </div>
                        @error('email')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="space-y-2">
                        <label for="password"
                               class="block text-sm font-bold text-blue-400/80
                                      tracking-wide uppercase">
                            Contraseña
                        </label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                                         text-neutral-500 group-focus-within:text-blue-500 transition-colors">
                                lock
                            </span>
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••••••"
                                   class="w-full bg-neutral-800 text-white
                                          pl-12 pr-12 py-4 rounded-lg outline-none
                                          border {{ $errors->has('password') ? 'border-error/60' : 'border-blue-900/20' }}
                                          focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                          transition-all placeholder:text-neutral-600">
                            <button type="button"
                                    id="togglePassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2
                                           text-neutral-500 hover:text-blue-400 transition-colors">
                                <span class="material-symbols-outlined" id="eyeIconPwd">visibility</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-neutral-500 italic mt-1 px-1">
                            Mínimo 8 caracteres · incluir mayúscula, número y símbolo
                        </p>
                        @error('password')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirmar contraseña --}}
                    <div class="space-y-2">
                        <label for="password_confirmation"
                               class="block text-sm font-bold text-blue-400/80
                                      tracking-wide uppercase">
                            Confirmar contraseña
                        </label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                                         text-neutral-500 group-focus-within:text-blue-500 transition-colors">
                                lock_reset
                            </span>
                            <input id="password_confirmation"
                                   name="password_confirmation"
                                   type="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••••••"
                                   class="w-full bg-neutral-800 text-white
                                          pl-12 pr-4 py-4 rounded-lg outline-none
                                          border border-blue-900/20
                                          focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                          transition-all placeholder:text-neutral-600">
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-4">
                        <button type="submit"
                                class="w-full bg-primary-container text-on-primary font-headline font-bold
                                       py-4 px-6 rounded-lg gold-glow
                                       hover:bg-blue-500-tint active:scale-[0.98] transition-all
                                       flex items-center justify-center gap-2 group">
                            <span>REGISTRAR USUARIO</span>
                            <span class="material-symbols-outlined
                                         group-hover:translate-x-1 transition-transform">
                                arrow_forward
                            </span>
                        </button>
                    </div>

                    {{-- Link a login --}}
                    <div class="text-center pt-6 border-t border-blue-900/10">
                        <p class="text-neutral-400 text-sm">
                            ¿Ya tienes acceso?
                            <a href="{{ route('login') }}"
                               class="text-blue-500 font-bold hover:text-blue-400 transition-colors">
                                Iniciar sesión
                            </a>
                        </p>
                    </div>

                </form>
            </div>

            {{-- Footer links --}}
            <footer class="mt-8 flex justify-center gap-6 text-[10px] uppercase tracking-widest text-neutral-600">
                <span class="hover:text-blue-500 transition-colors cursor-default">Política de privacidad</span>
                <span class="hover:text-blue-500 transition-colors cursor-default">Términos de uso</span>
            </footer>
        </section>

    </div>
</main>

@endsection

@push('scripts')
<script>
    (function () {
        const btn   = document.getElementById('togglePassword');
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIconPwd');
        if (!btn || !input || !icon) return;
        btn.addEventListener('click', function () {
            const hidden = input.type === 'password';
            input.type   = hidden ? 'text' : 'password';
            icon.textContent = hidden ? 'visibility_off' : 'visibility';
        });
    })();
</script>
@endpush
