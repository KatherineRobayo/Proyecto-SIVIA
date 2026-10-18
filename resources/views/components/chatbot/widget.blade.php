<div
    x-data="{
        open: false,
        loading: false,
        message: '',
        activePrinciples: [],
        messages: [
            { role: 'assistant', content: '¡Hola! Soy el asistente informativo de SIVIA. ¿En qué puedo ayudarte?' }
        ],

        async sendMessage() {
            const text = this.message.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', content: text });
            this.message = '';
            this.loading = true;

            try {
                const history = this.messages
                    .slice(0, -1)
                    .slice(-6)
                    .map(item => ({ role: item.role, content: item.content }));

                const response = await fetch('{{ route('chatbot.message') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: text,
                        history: history
                    })
                });

                const data = await response.json().catch(() => ({}));
                let reply = data.reply;

                if (!reply) {
                    reply = 'El servidor no devolvió una respuesta. Inténtalo de nuevo.';
                }

                if (data.principio && !this.activePrinciples.includes(data.principio)) {
                    this.activePrinciples.push(data.principio);
                }

                this.messages.push({ role: 'assistant', content: reply });
            } catch (error) {
                this.messages.push({ role: 'assistant', content: 'No pude conectarme con el asistente. Intenta nuevamente.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    const container = this.$refs.messages;
                    if (container) container.scrollTop = container.scrollHeight;
                });
            }
        }
    }"
    class="fixed bottom-24 right-6 z-[9999]"
>
    {{-- Ventana del chatbot --}}
    <div
        id="chatbot-window"
        x-show="open"
        x-transition
        @click.outside="open = false"
        class="mb-4 flex w-[350px] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-[#262626] bg-[#171717] shadow-2xl"
        style="display: none; height: min(36rem, calc(100dvh - 11.5rem));"
    >
        {{-- Encabezado --}}
        <div class="flex shrink-0 items-center justify-between border-b border-[#262626] bg-[#0A0A0A] px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#3B82F6] text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6m5-4a8 8 0 11-16 0 8 8 0 0116 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-[#F5F5F5]">Asistente SIVIA</p>
                    <p class="text-xs text-[#A3A3A3]">Información sobre SIVIA</p>
                </div>
            </div>
            <button @click="open = false" type="button" class="rounded-lg p-2 text-[#A3A3A3] transition hover:bg-[#262626] hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Mensajes --}}
        <div x-ref="messages" class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain p-4">
            <template x-for="(item, index) in messages" :key="index">
                <div class="flex" :class="item.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed whitespace-pre-line"
                         :class="item.role === 'user' ? 'rounded-br-md bg-[#3B82F6] text-white' : 'rounded-bl-md bg-[#262626] text-[#F5F5F5]'"
                         x-text="item.content"></div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="rounded-2xl rounded-bl-md bg-[#262626] px-4 py-3 text-sm text-[#A3A3A3]">Escribiendo...</div>
            </div>
        </div>

        {{-- Barra de Principios (CON TOOLTIPS) --}}
        <div class="shrink-0 border-t border-[#262626] bg-[#0A0A0A] px-4 py-2">
            <p class="text-[10px] text-center text-[#737373] mb-1.5 uppercase tracking-wider font-semibold">Tu Estilo de Negocio</p>
            <div class="flex justify-center gap-1.5 flex-wrap">
                <template x-for="i in 12" :key="i">
                    <div class="h-2 w-2 rounded-full transition-all duration-300 cursor-help"
                         :title="
                            i === 1 ? 'Principio 1: Organizo y dirijo mi propia vida' :
                            i === 2 ? 'Principio 2: La vida es la gran apuesta del siglo XXI' :
                            i === 3 ? 'Principio 3: Edifico un estilo propio de vida' :
                            i === 4 ? 'Principio 4: Determino mi vida y aprendo de ella' :
                            i === 5 ? 'Principio 5: Me valoro y cuido mi salud' :
                            i === 6 ? 'Principio 6: Voy más allá de mí' :
                            i === 7 ? 'Principio 7: Me perfecciono a través de la tecnología' :
                            i === 8 ? 'Principio 8: Vivo y contribuyo a la paz' :
                            i === 9 ? 'Principio 9: Soy libre, autónomo y responsable' :
                            i === 10 ? 'Principio 10: Forjo mi personalidad e identidad' :
                            i === 11 ? 'Principio 11: Realizo actos transformadores' :
                            'Principio 12: Exploro sentimientos positivos'
                         "
                         :class="activePrinciples.includes(i) ? 'bg-gradient-to-br from-[#60A5FA] to-[#3B82F6] shadow-[0_0_5px_rgba(59,130,246,0.5)] scale-110' : 'bg-[#262626]'"></div>
                </template>
            </div>
        </div>

        {{-- Entrada --}}
        <form @submit.prevent="sendMessage()" class="shrink-0 border-t border-[#262626] bg-[#0A0A0A] p-3">
            <div class="flex items-end gap-2">
                <textarea x-model="message" @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()" maxlength="500" rows="1" placeholder="Escribe tu pregunta..." class="min-h-[42px] flex-1 resize-none rounded-xl border border-[#262626] bg-[#171717] px-3 py-2.5 text-sm text-[#F5F5F5] outline-none placeholder:text-[#737373] focus:border-[#3B82F6]"></textarea>
                <button type="submit" :disabled="loading || !message.trim()" class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-xl bg-[#3B82F6] text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-40">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <p class="mt-2 text-[10px] text-[#737373]">El asistente responde únicamente sobre SIVIA.</p>
        </form>
    </div>

    {{-- Botón flotante --}}
    <button @click="open = !open" type="button" class="relative flex h-[52px] w-[52px] items-center justify-center rounded-full bg-gradient-to-br from-[#60A5FA] via-[#3B82F6] to-[#1D4ED8] text-white shadow-lg shadow-blue-950/50 ring-1 ring-white/30 transition duration-200 hover:scale-105 hover:shadow-xl hover:shadow-blue-950/60 focus:outline-none focus:ring-2 focus:ring-[#93C5FD] focus:ring-offset-2 focus:ring-offset-[#0A0A0A]">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 drop-shadow-sm" viewBox="0 0 32 32" fill="none">
            <path d="M16 4.5v3" stroke="white" stroke-width="2" stroke-linecap="round"/>
            <circle cx="16" cy="4" r="2" fill="#DBEAFE"/>
            <rect x="6" y="9" width="20" height="16" rx="6" fill="#EFF6FF"/>
            <path d="M6 14H4.5a2 2 0 0 0 0 4H6m20-4h1.5a2 2 0 0 1 0 4H26" stroke="#DBEAFE" stroke-width="2"/>
            <circle cx="12" cy="16" r="1.6" fill="#2563EB"/>
            <circle cx="20" cy="16" r="1.6" fill="#2563EB"/>
            <path d="M12.5 20h7" stroke="#2563EB" stroke-width="1.7" stroke-linecap="round"/>
            <path d="m25.5 6 .6 1.6 1.6.6-1.6.6-.6 1.6-.6-1.6-1.6-.6 1.6-.6.6-1.6Z" fill="#FDE68A"/>
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span x-show="!open" class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full border-2 border-[#1D4ED8] bg-white px-0.5 text-[8px] font-extrabold leading-none text-[#1D4ED8]">IA</span>
    </button>
</div>