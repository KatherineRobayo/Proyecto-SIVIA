<x-guest-layout>

    {{-- encabezado --}}
    <div class="mb-8 text-center field-group">
        {{-- Ícono animado --}}
        <div class="relative inline-flex items-center justify-center w-16 h-16
                    rounded-full bg-blue-500/10 border border-blue-500/20 mb-4
                    animate-pulse-border">
            <span class="material-symbols-outlined text-3xl text-blue-500">mark_email_unread</span>
            {{-- punto pulsante --}}
            <span class="absolute top-1 right-1 w-3 h-3 rounded-full bg-blue-400
                         animate-ping opacity-75"></span>
            <span class="absolute top-1 right-1 w-3 h-3 rounded-full bg-blue-500"></span>
        </div>

        <h2 class="text-xl font-headline font-bold text-white tracking-wide uppercase">
            Verifica tu Correo
        </h2>
        <div class="w-10 h-px bg-blue-500/50 mx-auto mt-2"></div>
        <p class="text-sm text-gray-400 mt-3 leading-relaxed max-w-xs mx-auto">
            Te enviamos un enlace de verificación. Revisa tu
            bandeja de entrada antes de continuar.
        </p>
    </div>
    {{-- alerta para link reenviado --}}
    @if (session('status') == 'verification-link-sent')
        <div class="field-group mb-6 flex items-start gap-3
                    px-4 py-3 rounded-lg
                    bg-green-900/20 border border-green-700/30">
            <span class="material-symbols-outlined text-green-400 mt-0.5 flex-shrink-0 text-xl">
                check_circle
            </span>
            <p class="text-sm text-green-400">
                Nuevo enlace enviado. Revisa tu correo.
            </p>
        </div>
    @endif

    {{-- info card --}}
    <div class="field-group mb-6 px-4 py-4 rounded-lg
                bg-neutral-800 border border-outline-variant/30">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-500/70 mt-0.5 flex-shrink-0">
                info
            </span>
            <p class="text-xs text-gray-400 leading-relaxed">
                Si no ves el correo, revisa la carpeta de spam.
                El enlace expira en <span class="text-blue-400 font-bold">60 minutos</span>.
            </p>
        </div>
    </div>

    {{-- acciones --}}
    <div class="space-y-3">

        {{-- reenviar verificación --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    data-submit-btn
                    class="btn-shimmer relative w-full overflow-hidden
                           bg-primary-container text-on-primary-container
                           font-headline font-bold py-3.5 rounded-lg
                           hover:bg-primary-fixed active:scale-[0.98]
                           transition-all duration-300
                           flex items-center justify-center gap-2 field-group">
                <span class="material-symbols-outlined text-lg">forward_to_inbox</span>
                <span data-btn-text class="transition-opacity duration-200">
                    Reenviar enlace de verificación
                </span>
                <svg data-btn-spinner class="hidden w-4 h-4 animate-spin absolute right-4"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>
        </form>

        {{-- separador --}}
        <div class="flex items-center gap-3 field-group">
            <div class="flex-1 h-px bg-outline-variant/30"></div>
            <span class="text-[10px] text-outline uppercase tracking-widest">o</span>
            <div class="flex-1 h-px bg-outline-variant/30"></div>
        </div>

        {{-- cerrar sesion --}}
        <form method="POST" action="{{ route('logout') }}" class="field-group">
            @csrf
            <button type="submit"
                    class="w-full py-3 rounded-lg
                           border border-outline-variant/40
                           text-on-surface-variant hover:text-on-surface
                           hover:border-outline-variant/70
                           text-sm font-serif transition-all duration-200
                           flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-lg">logout</span>
                Cerrar sesión
            </button>
        </form>

    </div>

</x-guest-layout>