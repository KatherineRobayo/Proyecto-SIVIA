@extends('layouts.smart-order')

@section('title', 'Asistente de pedidos | SIVIA')

@section('content')
<main class="min-h-screen bg-neutral-950 px-4 py-10 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('smart.order.catalog') }}" class="inline-flex items-center gap-2 text-sm text-neutral-400 transition hover:text-blue-300">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            Volver al catálogo
        </a>

        <header class="mt-8 rounded-3xl border border-blue-900/30 bg-neutral-900/80 p-6 sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">{{ $selectedBusiness->nombre }}</p>
            <h1 class="mt-2 font-serif text-3xl font-bold sm:text-4xl">Asistente de pedidos</h1>
            <p class="mt-3 max-w-2xl leading-relaxed text-neutral-400">
                Escribe lo que necesitas. Revisaremos únicamente los productos de este negocio y podrás comprobar la propuesta antes de añadirla a Tu Pedido.
            </p>
        </header>

        @if (session('assistant_error'))
            <p class="mt-6 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-200" role="alert">
                {{ session('assistant_error') }}
            </p>
        @endif
        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <section class="mt-6 rounded-2xl border border-neutral-800 bg-neutral-900/80 p-5 sm:p-7">
            @if (! $assistantEnabled)
                <p class="mb-5 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-200">
                    El asistente aún no está habilitado. Puedes seguir usando el catálogo y Tu Pedido.
                </p>
            @endif
            <form method="POST" action="{{ route('smart.order.assistant.interpret') }}" id="assistant-form">
                @csrf
                <label for="assistant-request" class="block font-semibold text-white">¿Qué productos necesitas?</label>
                <p class="mt-1 text-sm text-neutral-400">
                    Por ejemplo: «Quiero dos panes integrales y tres jugos de mango».
                </p>
                <textarea id="assistant-request" name="request" rows="4" maxlength="{{ config('smart_order_assistant.max_input_chars', 700) }}" required
                          placeholder="Escribe los productos y las cantidades que buscas"
                          @disabled(! $assistantEnabled)
                          class="mt-4 w-full resize-y rounded-xl border border-blue-900/40 bg-neutral-950 px-4 py-3 text-white placeholder:text-neutral-600 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50">{{ old('request') }}</textarea>
                <div class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                    <button type="button" id="voice-button" @disabled(! $assistantEnabled)
                            class="flex min-h-20 items-center justify-center gap-3 rounded-2xl border-2 border-blue-400/60 bg-blue-500/15 px-6 py-5 text-xl font-bold text-blue-100 shadow-lg shadow-blue-950/30 transition hover:bg-blue-500/25 disabled:cursor-not-allowed disabled:opacity-50 sm:min-w-56">
                        <span class="text-3xl" aria-hidden="true">🎤</span>
                        <span id="voice-button-label">Hablar</span>
                    </button>
                    <p class="text-xs leading-relaxed text-neutral-500">La propuesta no reserva existencias ni confirma el pedido.</p>
                </div>
                <p id="voice-status" class="mt-3 hidden rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-neutral-300" role="status" aria-live="polite"></p>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <button id="assistant-submit" type="submit" @disabled(! $assistantEnabled)
                            class="inline-flex justify-center gap-2 rounded-xl bg-blue-500 px-5 py-3 font-bold text-neutral-950 transition hover:bg-blue-400 disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="material-symbols-outlined text-lg" aria-hidden="true">auto_awesome</span>
                        Interpretar solicitud
                    </button>
                </div>
            </form>
        </section>

        @if ($assistantResult)
            <section class="mt-6 rounded-2xl border border-blue-500/25 bg-neutral-900/90 p-5 sm:p-7" aria-live="polite">
                <h2 class="font-serif text-xl font-bold">Esto entendí</h2>
                <p class="mt-3 leading-relaxed text-neutral-300" data-assistant-message>{{ $assistantResult['message'] }}</p>
                <button type="button" id="speak-summary" class="mt-4 rounded-xl border border-blue-500/30 px-4 py-2.5 text-sm font-semibold text-blue-200 transition hover:bg-blue-500/10">
                    🔊 Escuchar resumen
                </button>

                @if (! empty($assistantResult['clarification']))
                    <p class="mt-4 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-100">
                        {{ $assistantResult['clarification'] }}
                    </p>
                @endif

                @if (! empty($assistantResult['items']))
                    <ul class="mt-5 divide-y divide-neutral-800 rounded-xl border border-neutral-800">
                        @foreach ($assistantResult['items'] as $item)
                            <li class="flex flex-wrap items-center justify-between gap-3 p-4" data-assistant-item="{{ $item['quantity'] }} {{ $item['name'] }}">
                                <div>
                                    <p class="font-semibold text-white">{{ $item['name'] }}</p>
                                    <p class="mt-1 text-sm text-neutral-400">
                                        Cantidad: {{ $item['quantity'] }}
                                        @if ($item['available'])
                                            · {{ $item['stock'] }} disponibles
                                        @endif
                                    </p>
                                    @if ($item['issue'])
                                        <p class="mt-1 text-sm text-amber-200">{{ $item['issue'] }}</p>
                                    @endif
                                </div>
                                @if ($item['available'])
                                    <span class="rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-200">
                                        Disponible
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($assistantResult['alternatives']))
                    <div class="mt-5">
                        <h3 class="font-semibold text-white">Opciones similares disponibles</h3>
                        <ul class="mt-3 divide-y divide-neutral-800 rounded-xl border border-neutral-800">
                            @foreach ($assistantResult['alternatives'] as $alternative)
                                <li class="flex flex-wrap items-center justify-between gap-3 p-4">
                                    <span class="font-medium">{{ $alternative['name'] }}</span>
                                    <span class="text-sm text-neutral-400">{{ $alternative['stock'] }} disponibles</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-sm text-neutral-400">Escribe el nombre de la opción que prefieres y la cantidad. Aún no se ha añadido al pedido.</p>
                    </div>
                @endif

                @if (! empty($assistantResult['issues']))
                    <div class="mt-4 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm text-amber-100" role="alert">
                        @foreach ($assistantResult['issues'] as $issue)
                            <p>{{ $issue }}</p>
                        @endforeach
                        <p class="mt-2">Corrige la solicitud antes de añadir productos.</p>
                    </div>
                @endif

                @if (! empty($assistantResult['can_accept']) && ! empty($assistantResult['proposal_token']))
                    <form method="POST" action="{{ route('smart.order.assistant.accept') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="proposal_token" value="{{ $assistantResult['proposal_token'] }}">
                        <button type="submit" class="w-full rounded-xl bg-blue-500 px-5 py-3 font-bold text-neutral-950 transition hover:bg-blue-400 sm:w-auto">
                            Añadir propuesta a Tu Pedido
                        </button>
                    </form>
                @elseif (! empty($assistantResult['needs_clarification']))
                    <p class="mt-5 text-sm text-amber-200">Aclara tu solicitud en el cuadro de arriba para recibir una propuesta completa.</p>
                @endif
            </section>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('smart.order.cart') }}" class="rounded-xl border border-blue-500/30 px-4 py-3 text-sm font-semibold text-blue-200 transition hover:bg-blue-500/10">
                Ir a Tu Pedido
            </a>
            <a href="{{ route('smart.order.catalog') }}" class="rounded-xl border border-neutral-700 px-4 py-3 text-sm font-semibold text-neutral-300 transition hover:border-blue-400/40">
                Continuar con el catálogo
            </a>
        </div>
    </div>
</main>
<script>
    const assistantForm = document.getElementById('assistant-form');
    const assistantTextarea = document.getElementById('assistant-request');
    const assistantButton = document.getElementById('assistant-submit');
    const voiceButton = document.getElementById('voice-button');
    const voiceButtonLabel = document.getElementById('voice-button-label');
    const voiceStatus = document.getElementById('voice-status');
    const showVoiceStatus = (message) => {
        voiceStatus.textContent = message;
        voiceStatus.classList.remove('hidden');
    };

    assistantForm?.addEventListener('submit', () => {
        assistantButton.disabled = true;
        assistantButton.textContent = 'Interpretando solicitud…';
    });

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (voiceButton && ! SpeechRecognition) {
        voiceButton.disabled = true;
        showVoiceStatus('Este navegador no permite dictar pedidos. Puedes escribir tu solicitud en el cuadro de texto.');
    }

    voiceButton?.addEventListener('click', () => {
        if (! SpeechRecognition) {
            return;
        }

        const recognition = new SpeechRecognition();
        recognition.lang = 'es-CO';
        recognition.interimResults = true;
        recognition.continuous = false;

        voiceButton.disabled = true;
        voiceButtonLabel.textContent = 'Te escuchamos…';
        showVoiceStatus('Habla ahora. Cuando termines, podrás revisar el texto antes de enviarlo.');

        let finalTranscript = '';
        let receivedTranscript = false;
        recognition.onresult = (event) => {
            let interimTranscript = '';
            for (let index = event.resultIndex; index < event.results.length; index += 1) {
                const transcript = event.results[index][0].transcript;
                if (event.results[index].isFinal) {
                    finalTranscript += transcript;
                } else {
                    interimTranscript += transcript;
                }
            }

            assistantTextarea.value = `${finalTranscript}${interimTranscript}`.trim();
            receivedTranscript = finalTranscript.trim() !== '';
        };

        recognition.onerror = (event) => {
            const message = event.error === 'not-allowed' || event.error === 'service-not-allowed'
                ? 'No se concedió permiso para usar el micrófono. Puedes escribir tu solicitud.'
                : 'No pudimos reconocer la voz. Inténtalo otra vez o escribe tu solicitud.';
            showVoiceStatus(message);
        };

        recognition.onend = () => {
            voiceButton.disabled = false;
            voiceButtonLabel.textContent = 'Hablar';
            if (receivedTranscript && assistantTextarea.value.trim() !== '') {
                showVoiceStatus('Texto transcrito. Revísalo y pulsa «Interpretar solicitud» cuando esté listo.');
                assistantTextarea.focus();
            }
        };

        try {
            recognition.start();
        } catch {
            voiceButton.disabled = false;
            voiceButtonLabel.textContent = 'Hablar';
            showVoiceStatus('No se pudo iniciar el micrófono. Puedes escribir tu solicitud.');
        }
    });

    document.getElementById('speak-summary')?.addEventListener('click', () => {
        if (! ('speechSynthesis' in window)) {
            showVoiceStatus('Este navegador no permite leer el resumen en voz alta.');
            return;
        }

        const itemLines = [...document.querySelectorAll('[data-assistant-item]')]
            .map((item) => item.dataset.assistantItem)
            .filter(Boolean);
        const summary = itemLines.length > 0
            ? `Entendí que quieres: ${itemLines.join('. ')}.`
            : document.querySelector('[data-assistant-message]')?.textContent.trim();

        if (summary) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(summary);
            utterance.lang = 'es-CO';
            window.speechSynthesis.speak(utterance);
        }
    });
</script>
@endsection
