<x-guest-layout>

    {{-- encabezado --}}
    <div class="mb-8 text-center field-group">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full
                    bg-blue-500/10 border border-blue-500/30 mb-4">
            <span class="material-symbols-outlined text-2xl text-blue-500">key</span>
        </div>
        <h2 class="text-xl font-headline font-bold text-on-surface tracking-wide uppercase">
            Recuperar Acceso
        </h2>
        <div class="w-10 h-px bg-blue-500/50 mx-auto mt-2"></div>
        <p class="text-sm text-on-surface-variant mt-3 leading-relaxed max-w-xs mx-auto">
            Ingresa tu correo y te enviaremos un enlace
            para restablecer tu contraseña.
        </p>
    </div>

    {{-- status de sesion (exito al enviar el link) --}}
    @if (session('status'))
        <div class="field-group mb-6 flex items-start gap-3
                    px-4 py-3 rounded-lg
                    bg-green-900/20 border border-green-700/30">
            <span class="material-symbols-outlined text-green-400 mt-0.5 flex-shrink-0">
                check_circle
            </span>
            <p class="text-sm text-green-400">{{ session('status') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- email --}}
        <div class="field-group space-y-2 mb-6">
            <label for="email"
                   class="block text-xs font-bold uppercase tracking-widest text-blue-400/80">
                Correo electrónico
            </label>
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                             text-outline group-focus-within:text-blue-500 transition-colors duration-200">
                    mail
                </span>
                <input id="email"
                       name="email"
                       type="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="email"
                       placeholder="admin@kitchenflow.com"
                       class="w-full !bg-neutral-900 !text-white
                                pl-12 pr-4 py-3 rounded-lg
                                border border-neutral-700
                                placeholder:text-gray-400
                                focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                transition-all duration-200">
            </div>
            @error('email')
                <p class="text-xs text-error mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- acciones --}}
        <div class="field-group space-y-3">

            {{-- submit --}}
            <button type="submit"
                    data-submit-btn
                    class="btn-shimmer relative w-full overflow-hidden
                           bg-primary-container text-on-primary-container
                           font-headline font-bold py-3.5 rounded-lg
                           hover:bg-primary-fixed active:scale-[0.98]
                           transition-all duration-300
                           flex items-center justify-center gap-2">
                <span data-btn-text class="transition-opacity duration-200">
                    Enviar enlace de recuperación
                </span>
                <span class="material-symbols-outlined text-lg">send</span>
                <svg data-btn-spinner class="hidden w-4 h-4 animate-spin absolute right-4"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>

            {{-- volver al login --}}
            <div class="text-center pt-2">
                <a href="{{ route('login') }}"
                   class="text-xs text-outline hover:text-blue-400
                          transition-colors duration-200 uppercase tracking-widest
                          inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Volver al inicio de sesión
                </a>
            </div>

        </div>

    </form>

</x-guest-layout>