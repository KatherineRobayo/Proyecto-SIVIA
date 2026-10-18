<x-guest-layout>

    {{-- ecabezado --}}
    <div class="mb-8 text-center field-group">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full
                    bg-blue-500/10 border border-blue-500/30 mb-4">
            <span class="material-symbols-outlined text-2xl text-blue-500">lock_reset</span>
        </div>
        <h2 class="text-xl font-headline font-bold text-white tracking-wide uppercase">
            Nueva Contraseña
        </h2>
        <div class="w-10 h-px bg-blue-500/50 mx-auto mt-2"></div>
        <p class="text-sm text-gray-400 mt-3">
            Define tu nueva clave de acceso al sistema.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- Token oculto --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- email --}}
        <div class="field-group space-y-2 mb-5">
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
                       value="{{ old('email', $request->email) }}"
                       required
                       autofocus
                       autocomplete="username"
                       class="kf-input w-full bg-neutral-800 text-white
                              border border-gray-700
                              focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                              placeholder:text-gray-500">
            </div>
            @error('email')
                <p class="text-xs text-error mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- nueva contraseña --}}
        <div class="field-group space-y-2 mb-5">
            <label for="password"
                   class="block text-xs font-bold uppercase tracking-widest text-blue-400/80">
                Nueva contraseña
            </label>
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                             text-outline group-focus-within:text-blue-500 transition-colors duration-200">
                    lock
                </span>
                <input id="password"
                       name="password"
                       type="password"
                       required
                       autocomplete="new-password"
                       placeholder="••••••••"
                       class="kf-input w-full bg-neutral-800 text-white
                              border border-gray-700
                              focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                              placeholder:text-gray-500">
                <button type="button"
                        data-toggle-password="password"
                        class="absolute right-4 top-1/2 -translate-y-1/2
                               text-outline hover:text-blue-400 transition-colors duration-200">
                    <span class="material-symbols-outlined text-xl"
                          style="transition: transform 0.2s ease;">
                        visibility
                    </span>
                </button>
            </div>
            {{-- indicador de fortaleza --}}
            <div id="strength-bar" class="h-0.5 w-0 rounded-full bg-blue-500 mt-1
                                           transition-all duration-500"></div>
            <p id="strength-label"
               class="text-[10px] text-outline/60 mt-0.5 tracking-wide hidden">
            </p>
            @error('password')
                <p class="text-xs text-error mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- confirmar contraseña --}}
        <div class="field-group space-y-2 mb-7">
            <label for="password_confirmation"
                   class="block text-xs font-bold uppercase tracking-widest text-blue-400/80">
                Confirmar contraseña
            </label>
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2
                             text-outline group-focus-within:text-blue-500 transition-colors duration-200">
                    lock_clock
                </span>
                <input id="password_confirmation"
                       name="password_confirmation"
                       type="password"
                       required
                       autocomplete="new-password"
                       placeholder="••••••••"
                       class="kf-input w-full bg-neutral-800 text-white
                            border border-gray-700
                            focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                            placeholder:text-gray-500">
                <button type="button"
                        data-toggle-password="password_confirmation"
                        class="absolute right-4 top-1/2 -translate-y-1/2
                               text-outline hover:text-blue-400 transition-colors duration-200">
                    <span class="material-symbols-outlined text-xl"
                          style="transition: transform 0.2s ease;">
                        visibility
                    </span>
                </button>
            </div>
            @error('password_confirmation')
                <p class="text-xs text-error mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>
        {{--  formuladrio Submit --}}
        <div class="field-group">
            <button type="submit"
                    data-submit-btn
                    class="btn-shimmer relative w-full overflow-hidden
                           bg-primary-container text-on-primary-container
                           font-headline font-bold py-3.5 rounded-lg
                           hover:bg-primary-fixed active:scale-[0.98]
                           transition-all duration-300
                           flex items-center justify-center gap-2">
                <span data-btn-text class="transition-opacity duration-200">
                    Restablecer contraseña
                </span>
                <span class="material-symbols-outlined text-lg">check_circle</span>
                <svg data-btn-spinner class="hidden w-4 h-4 animate-spin absolute right-4"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>
        </div>

    </form>

    {{-- JS: indicador de fortaleza de contraseña --}}
    <script>
        (function () {
            const input  = document.getElementById('password');
            const bar    = document.getElementById('strength-bar');
            const label  = document.getElementById('strength-label');
            if (!input || !bar || !label) return;

            const levels = [
                { pct: '15%',  color: '#ef4444', text: 'Muy débil'  },
                { pct: '35%',  color: '#f97316', text: 'Débil'      },
                { pct: '60%',  color: '#eab308', text: 'Moderada'   },
                { pct: '80%',  color: '#a3e635', text: 'Fuerte'     },
                { pct: '100%', color: '#22c55e', text: 'Muy fuerte' },
            ];

            function score(pwd) {
                let s = 0;
                if (pwd.length >= 8)                       s++;
                if (pwd.length >= 12)                      s++;
                if (/[A-Z]/.test(pwd))                     s++;
                if (/[0-9]/.test(pwd))                     s++;
                if (/[@$!%*?&_#^~\-]/.test(pwd))           s++;
                return Math.min(s, levels.length - 1);
            }

            input.addEventListener('input', function () {
                const pwd = input.value;
                if (!pwd) {
                    bar.style.width = '0';
                    label.classList.add('hidden');
                    return;
                }
                const lvl        = levels[score(pwd)];
                bar.style.width  = lvl.pct;
                bar.style.background = lvl.color;
                label.textContent    = lvl.text;
                label.style.color    = lvl.color;
                label.classList.remove('hidden');
            });
        })();
    </script>

</x-guest-layout>
