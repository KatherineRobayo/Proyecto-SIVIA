@extends('layouts.smart-order')

@section('title', 'Pago simulado | SIVIA')

@section('content')
<main class="min-h-screen bg-neutral-950 px-4 py-10 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">
        <header class="rounded-3xl border border-blue-900/30 bg-neutral-900/80 p-6 sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">{{ $order->empresa->nombre }}</p>
            <h1 class="mt-2 font-serif text-3xl font-bold sm:text-4xl">Pago simulado</h1>
            <p class="mt-3 text-neutral-400">
                Pedido <span class="font-semibold text-white">{{ $order->numero_pedido }}</span>
            </p>
        </header>

        @if (session('payment_error'))
            <p class="mt-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200" role="alert">
                {{ session('payment_error') }}
            </p>
        @endif

        <section class="mt-6 rounded-2xl border border-neutral-800 bg-neutral-900/80 p-5 sm:p-7">
            <div class="flex items-center justify-between gap-4 border-b border-neutral-800 pb-5">
                <span class="text-neutral-300">Total</span>
                <span class="text-2xl font-bold text-blue-300">${{ number_format((float) $order->total, 2, ',', '.') }}</span>
            </div>

            <form id="simulated-payment-form" method="POST" action="{{ route('smart.order.pay') }}" class="mt-6">
                @csrf
                <fieldset>
                    <legend class="font-semibold">Elige un método de pago</legend>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach (['tarjeta' => 'Tarjeta', 'nequi' => 'Nequi', 'daviplata' => 'Daviplata'] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-neutral-700 bg-neutral-950 px-4 py-4 transition hover:border-blue-400/50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-500/10">
                                <input type="radio" name="payment_method" value="{{ $value }}" required @checked(old('payment_method') === $value) class="h-5 w-5 accent-blue-500">
                                <span class="font-semibold">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                @error('payment_method')
                    <p class="mt-3 text-sm text-red-300" role="alert">Selecciona una de las opciones de pago.</p>
                @enderror

                <p class="mt-5 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-sm leading-relaxed text-amber-100">
                    Esta es una simulación local. No se realizará ningún cobro ni se enviarán datos a una entidad financiera.
                </p>

                <button id="simulated-pay-button" type="submit" class="mt-6 w-full rounded-xl bg-blue-500 px-5 py-4 text-lg font-bold text-neutral-950 transition hover:bg-blue-400">
                    Pagar ${{ number_format((float) $order->total, 2, ',', '.') }}
                </button>
            </form>
        </section>
    </div>
</main>
<script>
    const simulatedPaymentForm = document.getElementById('simulated-payment-form');
    let paymentProcessing = false;
    simulatedPaymentForm?.addEventListener('submit', (event) => {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedMethod || paymentProcessing) {
            return;
        }

        event.preventDefault();
        paymentProcessing = true;
        const button = document.getElementById('simulated-pay-button');
        button.disabled = true;
        button.textContent = 'Procesando pago simulado…';
        window.setTimeout(() => simulatedPaymentForm.submit(), 1000);
    });
</script>
@endsection
