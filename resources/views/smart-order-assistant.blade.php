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
                Cuéntame qué necesitas por voz o por escrito. Revisaremos únicamente los productos de este negocio y te leeré la propuesta antes de confirmar.
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
            <div id="assistant-conversation" class="mb-5 space-y-3" aria-live="polite">
                @if ($assistantResult)
                    <div class="max-w-[90%] rounded-2xl rounded-bl-md border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm leading-relaxed text-neutral-200">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-blue-300">SIVIA</p>
                        <p data-assistant-message>{{ $assistantResult['message'] }}</p>
                    </div>
                @endif
            </div>
            <form method="POST" action="{{ route('smart.order.assistant.interpret') }}" id="assistant-form">
                @csrf
                <label for="assistant-request" class="sr-only">Escribe o dicta tu pedido</label>
                <textarea id="assistant-request" name="request" rows="3" maxlength="{{ config('smart_order_assistant.max_input_chars', 700) }}" required
                          placeholder="Por ejemplo: Quiero dos panes integrales y tres jugos de mango"
                          @disabled(! $assistantEnabled)
                          class="w-full resize-y rounded-2xl border border-blue-900/40 bg-neutral-950 px-4 py-3 text-white placeholder:text-neutral-600 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50">{{ old('request') }}</textarea>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" id="voice-button" @disabled(! $assistantEnabled)
                            class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-blue-400/40 bg-blue-500/10 text-lg text-blue-200 transition hover:border-blue-300/70 hover:bg-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                            aria-label="Hablar con el asistente">
                        <span class="material-symbols-outlined" aria-hidden="true">mic</span>
                    </button>
                    <p class="text-xs text-neutral-500">Escribe y pulsa Enter, o dicta: se enviará automáticamente al terminar.</p>
                </div>
                <p id="voice-status" class="mt-3 hidden rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-3 text-sm text-neutral-300" role="status" aria-live="polite"></p>
            </form>
        </section>

        @if ($assistantResult)
            <section class="mt-6 rounded-2xl border border-blue-500/25 bg-neutral-900/90 p-5 sm:p-7" aria-live="polite">
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
                        <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($assistantResult['alternatives'] as $alternative)
                                <li class="rounded-xl border border-neutral-800 bg-neutral-950/70 p-3">
                                    <p class="font-medium text-white">{{ $alternative['name'] }}</p>
                                    <p class="mt-1 text-xs text-neutral-400">{{ $alternative['stock'] }} disponibles</p>
                                    <div class="mt-3 flex items-center justify-between gap-2">
                                        <label class="sr-only" for="alternative-quantity-{{ $alternative['product_id'] }}">Cantidad de {{ $alternative['name'] }}</label>
                                        <input id="alternative-quantity-{{ $alternative['product_id'] }}" type="number" min="1" max="{{ $alternative['stock'] }}" value="1"
                                               class="w-20 rounded-lg border border-neutral-700 bg-neutral-900 px-2 py-1.5 text-sm text-white focus:border-blue-500 focus:outline-none">
                                        <button type="button" data-assistant-alternative="{{ $alternative['name'] }}" data-quantity-input="alternative-quantity-{{ $alternative['product_id'] }}"
                                                class="rounded-lg border border-blue-500/30 px-3 py-1.5 text-xs font-semibold text-blue-200 transition hover:bg-blue-500/10">
                                            Elegir opción
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-xs text-neutral-500">El precio se confirma con los valores vigentes del catálogo.</p>
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
                    <form method="POST" action="{{ route('smart.order.assistant.accept') }}" id="assistant-accept-form" class="mt-5">
                        @csrf
                        <input type="hidden" name="proposal_token" value="{{ $assistantResult['proposal_token'] }}">
                        <button id="btn-confirmar-pago" type="submit" class="w-full rounded-xl bg-blue-500 px-5 py-3 font-bold text-neutral-950 transition hover:bg-blue-400 sm:w-auto">
                            Sí, confirmar y pagar
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
    const voiceButton = document.getElementById('voice-button');
    const voiceButtonLabel = document.getElementById('voice-button-label');
    const voiceStatus = document.getElementById('voice-status');
    const assistantConversation = document.getElementById('assistant-conversation');
    const csrfToken = assistantForm?.querySelector('input[name="_token"]')?.value;
    const acceptForm = document.getElementById('assistant-accept-form');
    const acceptButton = document.getElementById('btn-confirmar-pago');

    acceptButton?.addEventListener('click', (event) => {
        event.preventDefault();
        handleAssistantAccept(event);
    });
    acceptForm?.addEventListener('submit', handleAssistantAccept);

    const showVoiceStatus = (message) => {
        if (!voiceStatus) return;
        voiceStatus.textContent = message;
        voiceStatus.classList.remove('hidden');
    };

    const appendUserBubble = (message) => {
        if (!message || !assistantConversation) return;
        const bubble = document.createElement('p');
        bubble.className = 'ml-auto max-w-[85%] rounded-2xl rounded-br-md border border-blue-500/20 bg-blue-500/10 px-4 py-3 text-sm text-blue-100';
        bubble.textContent = message;
        assistantConversation.append(bubble);
    };

    const pendingRequest = sessionStorage.getItem('smart-order-assistant-request');
    if (pendingRequest) {
        appendUserBubble(pendingRequest);
        sessionStorage.removeItem('smart-order-assistant-request');
    }

    assistantForm?.addEventListener('submit', () => {
        const request = assistantTextarea.value.trim();
        if (request) sessionStorage.setItem('smart-order-assistant-request', request);
        showVoiceStatus('Entendido. Estoy preparando tu propuesta…');
    });

    assistantTextarea?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && ! event.shiftKey && ! event.isComposing) {
            event.preventDefault();
            assistantForm.requestSubmit();
        }
    });

    const assistantMessage = document.querySelector('[data-assistant-message]');
    if (assistantMessage && 'speechSynthesis' in window) {
        const items = [...document.querySelectorAll('[data-assistant-item]')]
            .map((item) => item.dataset.assistantItem)
            .filter(Boolean);
        const alternatives = [...document.querySelectorAll('[data-assistant-alternative]')]
            .map((option) => option.dataset.assistantAlternative)
            .filter(Boolean);
        const spokenText = [
            assistantMessage.textContent.trim(),
            items.length ? `Entendí que quieres: ${items.join('. ')}.` : '',
            alternatives.length ? `Puedes elegir entre: ${alternatives.join(' o ')}.` : '',
        ].filter(Boolean).join(' ');
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(spokenText);
        utterance.lang = 'es-CO';
        utterance.onerror = () => showVoiceStatus('No se pudo reproducir la respuesta en voz alta; puedes leerla en pantalla.');
        window.speechSynthesis.speak(utterance);
    }

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
        if (voiceButtonLabel) voiceButtonLabel.textContent = 'Te escuchamos…';
        showVoiceStatus('Habla ahora. Enviaremos tu solicitud al terminar.');

        let finalTranscript = '';
        let submitted = false;
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
        };

        recognition.onerror = (event) => {
            const message = event.error === 'not-allowed' || event.error === 'service-not-allowed'
                ? 'No se concedió permiso para usar el micrófono. Puedes escribir tu solicitud.'
                : 'No pudimos reconocer la voz. Inténtalo otra vez o escribe tu solicitud.';
            showVoiceStatus(message);
        };

        recognition.onend = () => {
            voiceButton.disabled = false;
            if (voiceButtonLabel) voiceButtonLabel.textContent = 'Hablar';
            if (!submitted && finalTranscript.trim() !== '') {
                submitted = true;
                showVoiceStatus('Solicitud recibida. Estoy preparando tu propuesta…');
                assistantForm.requestSubmit();
            } else if (!finalTranscript.trim()) {
                showVoiceStatus('No alcanzamos a escuchar una solicitud. Inténtalo otra vez o escríbela.');
            }
        };

        try {
            recognition.start();
        } catch {
            voiceButton.disabled = false;
            if (voiceButtonLabel) voiceButtonLabel.textContent = 'Hablar';
            showVoiceStatus('No se pudo iniciar el micrófono. Puedes escribir tu solicitud.');
        }
    });

    document.querySelectorAll('[data-assistant-alternative]').forEach((button) => {
        button.addEventListener('click', () => {
            const quantityInput = document.getElementById(button.dataset.quantityInput);
            const quantity = Number(quantityInput.value);
            const maximum = Number(quantityInput.max);
            if (!Number.isInteger(quantity) || quantity < 1 || quantity > maximum) {
                showVoiceStatus(`Elige una cantidad entre 1 y ${maximum}.`);
                quantityInput.focus();
                return;
            }

            assistantTextarea.value = `Quiero ${quantity} ${button.dataset.assistantAlternative}`;
            assistantForm.requestSubmit();
        });
    });

    async function handleAssistantAccept(event) {
        event.preventDefault();
        const button = acceptForm.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Confirmando disponibilidad…';

        const cartUrl = @json(route('smart.order.cart'));
        const reviewUrl = @json(route('smart.order.review'));
        const confirmUrl = @json(route('smart.order.confirm'));
        const paymentUrl = @json(route('smart.order.payment'));
        const cartPath = new URL(cartUrl, window.location.href).pathname;
        const reviewPath = new URL(reviewUrl, window.location.href).pathname;
        const paymentPath = new URL(paymentUrl, window.location.href).pathname;

        try {
            const acceptResponse = await fetch(acceptForm.action, {
                method: 'POST',
                body: new FormData(acceptForm),
                credentials: 'same-origin',
                headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!acceptResponse.ok || !acceptResponse.redirected
                || new URL(acceptResponse.url).pathname !== cartPath) {
                const responseHtml = await acceptResponse.text();
                const responseDocument = new DOMParser().parseFromString(responseHtml, 'text/html');
                const serverMessage = responseDocument.querySelector('[role="alert"]')?.textContent.trim();
                throw new Error(serverMessage || 'No se pudo validar la propuesta. Revisa las existencias e inténtalo otra vez.');
            }

            const reviewResponse = await fetch(reviewUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!reviewResponse.ok || reviewResponse.redirected
                || new URL(reviewResponse.url).pathname !== reviewPath) {
                throw new Error('No se pudo revisar el pedido. Tu propuesta permanece en el carrito.');
            }

            const reviewHtml = await reviewResponse.text();
            const reviewDocument = new DOMParser().parseFromString(reviewHtml, 'text/html');
            const confirmationForm = reviewDocument.getElementById('smart-order-confirmation-token');
            const confirmationToken = confirmationForm?.querySelector('input[name="confirmation_token"]')?.value;
            const reviewCsrf = confirmationForm?.querySelector('input[name="_token"]')?.value || csrfToken;
            if (!confirmationToken || !reviewCsrf) {
                throw new Error('No se pudo generar la confirmación segura del pedido.');
            }

            const confirmationData = new FormData();
            for (const [name, value] of [['_token', reviewCsrf], ['confirmation_token', confirmationToken]]) {
                confirmationData.set(name, value);
            }
            const confirmationResponse = await fetch(confirmUrl, {
                method: 'POST',
                body: confirmationData,
                credentials: 'same-origin',
                headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!confirmationResponse.ok || !confirmationResponse.redirected
                || new URL(confirmationResponse.url).pathname !== paymentPath) {
                const confirmationHtml = await confirmationResponse.text();
                const confirmationDocument = new DOMParser().parseFromString(confirmationHtml, 'text/html');
                const serverMessage = confirmationDocument.querySelector('[role="alert"]')?.textContent.trim();
                throw new Error(serverMessage || 'No se pudo confirmar el pedido. Revisa el resumen e inténtalo otra vez.');
            }

            window.location.href = paymentUrl;
        } catch (error) {
            showVoiceStatus(error.message || 'No se pudo confirmar el pedido. Inténtalo otra vez.');
            button.disabled = false;
            button.textContent = 'Sí, confirmar y pagar';
        }
    }
</script>
@endsection
