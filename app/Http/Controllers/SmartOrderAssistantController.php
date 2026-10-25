<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Producto;
use App\Models\User;
use App\Services\SmartOrderAssistantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class SmartOrderAssistantController extends Controller
{
    private const BUSINESS_SESSION_KEY = 'smart_order.empresa_id';

    private const CART_SESSION_KEY = 'smart_order.cart';

    private const PROPOSAL_SESSION_KEY = 'smart_order.assistant_proposal';

    private const RESULT_SESSION_KEY = 'smart_order.assistant_result';

    public function index(Request $request): View|RedirectResponse
    {
        $this->assertCustomerOrGuest($request);
        $business = $this->selectedBusiness($request);
        if (! $business) {
            return redirect()->route('smart.order')
                ->with('smart_order_error', 'Selecciona un negocio antes de abrir el asistente.');
        }

        $result = $request->session()->get(self::RESULT_SESSION_KEY);
        if (is_array($result)) {
            $result['items'] = $this->presentItems($business, $result['items'] ?? []);
            $result['alternatives'] = $this->presentAlternatives($business, $result['alternative_ids'] ?? []);
            $result['issues'] = collect($result['items'])
                ->filter(fn (array $item): bool => ! $item['available'])
                ->pluck('issue')
                ->filter()
                ->values()
                ->all();
            if ($result['issues'] !== []) {
                $result['can_accept'] = false;
            }
        } else {
            $result = null;
        }

        return view('smart-order-assistant', [
            'selectedBusiness' => $business,
            'assistantResult' => $result,
            'assistantEnabled' => (bool) config('smart_order_assistant.enabled'),
        ]);
    }

    public function interpret(Request $request, SmartOrderAssistantService $assistant): RedirectResponse
    {
        $this->assertCustomerOrGuest($request);
        $business = $this->selectedBusiness($request);
        if (! $business) {
            return redirect()->route('smart.order')
                ->with('smart_order_error', 'Selecciona un negocio antes de abrir el asistente.');
        }

        $maxLength = max(1, (int) config('smart_order_assistant.max_input_chars', 700));
        $validated = $request->validate([
            'request' => ['required', 'string', 'max:'.$maxLength],
        ]);
        $customerRequest = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $validated['request']) ?? '');
        if ($customerRequest === '') {
            throw ValidationException::withMessages(['request' => 'Escribe qué productos necesitas.']);
        }

        $request->session()->forget([self::PROPOSAL_SESSION_KEY, self::RESULT_SESSION_KEY]);
        if (! config('smart_order_assistant.enabled')) {
            return redirect()->route('smart.order.assistant')
                ->with('assistant_error', 'El asistente de pedidos no está habilitado. Puedes continuar con el catálogo y Tu Pedido.');
        }
        if (! config('chatbot.gemini.api_key')) {
            return redirect()->route('smart.order.assistant')
                ->with('assistant_error', 'El asistente no tiene un proveedor configurado. Puedes continuar con el catálogo y Tu Pedido.');
        }

        try {
            $catalog = Producto::forEmpresa($business->id)
                ->where('estado', 'activo')
                ->with('categoria:id,nombre')
                ->orderBy('nombre')
                ->limit(max(1, (int) config('smart_order_assistant.catalog_limit', 200)))
                ->get(['id', 'empresa_id', 'categoria_id', 'nombre', 'stock']);

            $interpretation = $assistant->interpret($customerRequest, $catalog);
            $items = $this->presentItems($business, $interpretation['items']);
            $alternatives = $this->presentAlternatives($business, $interpretation['alternatives']);
            $issues = collect($items)
                ->filter(fn (array $item): bool => ! $item['available'])
                ->pluck('issue')
                ->filter()
                ->values()
                ->all();
            $canAccept = ! $interpretation['needs_clarification']
                && $interpretation['items'] !== []
                && $issues === [];
            if ($issues !== []) {
                $message = 'No hay existencias suficientes para una o más cantidades. Revisa la disponibilidad y corrige tu solicitud.';
                $clarification = null;
            } elseif ($alternatives !== []) {
                $message = 'No encontré una coincidencia exacta. Estas opciones similares sí existen en el catálogo; escribe cuál prefieres y cuántas unidades necesitas.';
                $clarification = null;
            } elseif ($interpretation['needs_clarification']) {
                $message = 'No pude identificar con seguridad todos los productos o cantidades. Especifica el nombre del producto y cuántas unidades necesitas.';
                $clarification = null;
            } elseif ($canAccept) {
                $message = 'Esta propuesta usa productos reales del catálogo seleccionado. Revisa las cantidades antes de añadirla a Tu Pedido.';
                $clarification = null;
            } else {
                $message = 'No encontré una propuesta clara con productos disponibles de este negocio. Prueba con el nombre del producto y la cantidad.';
                $clarification = null;
            }

            $result = [
                'message' => $message,
                'needs_clarification' => $interpretation['needs_clarification'],
                'clarification' => $clarification,
                'items' => $items,
                'alternatives' => $alternatives,
                'alternative_ids' => $interpretation['alternatives'],
                'issues' => $issues,
                'can_accept' => $canAccept,
            ];

            if ($canAccept) {
                $token = (string) Str::uuid();
                $request->session()->put(self::PROPOSAL_SESSION_KEY, [
                    'token' => $token,
                    'empresa_id' => $business->id,
                    'items' => $interpretation['items'],
                ]);
                $result['proposal_token'] = $token;
            }

            $request->session()->put(self::RESULT_SESSION_KEY, $result);
        } catch (RuntimeException $exception) {
            $errorCode = preg_match('/^[a-z0-9_]+$/', $exception->getMessage()) === 1
                ? $exception->getMessage()
                : 'assistant_unavailable';
            Log::warning('No se pudo interpretar una solicitud de SmartOrder.', [
                'empresa_id' => $business->id,
                'code' => $errorCode,
            ]);

            return redirect()->route('smart.order.assistant')
                ->with('assistant_error', 'No se pudo interpretar la solicitud en este momento. Inténtalo otra vez o continúa con el catálogo.');
        }

        return redirect()->route('smart.order.assistant');
    }

    public function accept(Request $request): RedirectResponse
    {
        $this->assertCustomerOrGuest($request);
        $business = $this->selectedBusiness($request);
        if (! $business) {
            return redirect()->route('smart.order')
                ->with('smart_order_error', 'Selecciona un negocio antes de continuar.');
        }

        $validated = $request->validate(['proposal_token' => ['required', 'uuid']]);
        $proposal = $request->session()->get(self::PROPOSAL_SESSION_KEY);
        if (! is_array($proposal)
            || ($proposal['token'] ?? null) !== $validated['proposal_token']
            || (int) ($proposal['empresa_id'] ?? 0) !== $business->id
            || ! is_array($proposal['items'] ?? null)
            || $proposal['items'] === []) {
            return redirect()->route('smart.order.assistant')
                ->with('assistant_error', 'La propuesta expiró o no corresponde a este negocio. Vuelve a escribir tu solicitud.');
        }

        $cart = $this->validatedSessionCart($request);
        $merged = $cart;
        foreach ($proposal['items'] as $item) {
            if (! is_array($item)
                || ! is_int($item['product_id'] ?? null)
                || ! is_int($item['quantity'] ?? null)
                || $item['quantity'] < 1
                || $item['quantity'] > 999) {
                return redirect()->route('smart.order.assistant')
                    ->with('assistant_error', 'La propuesta no es válida. Vuelve a escribir tu solicitud.');
            }

            $key = (string) $item['product_id'];
            $merged[$key] = ($merged[$key] ?? 0) + $item['quantity'];
            if ($merged[$key] > 999) {
                return redirect()->route('smart.order.assistant')
                    ->with('assistant_error', 'La cantidad supera el máximo permitido. Corrige tu solicitud.');
            }
        }

        $products = $business->productos()
            ->where('productos.estado', 'activo')
            ->whereIn('productos.id', array_keys($merged))
            ->get(['id', 'nombre', 'stock'])
            ->keyBy(fn (Producto $product): string => (string) $product->id);

        if ($products->count() !== count($merged)) {
            return redirect()->route('smart.order.assistant')
                ->with('assistant_error', 'Un producto de Tu Pedido ya no pertenece a este negocio o dejó de estar disponible. Revisa el catálogo antes de continuar.');
        }

        foreach ($merged as $productId => $quantity) {
            $product = $products[$productId];
            if ($quantity > $product->stock) {
                return redirect()->route('smart.order.assistant')
                    ->with('assistant_error', "No hay existencias suficientes de {$product->nombre}. Ajusta las cantidades en Tu Pedido.");
            }
        }

        $request->session()->put(self::CART_SESSION_KEY, $merged);
        $request->session()->forget([self::PROPOSAL_SESSION_KEY, self::RESULT_SESSION_KEY]);

        return redirect()->route('smart.order.cart')
            ->with('success', 'La propuesta se añadió a Tu Pedido. Revisa las cantidades y el total antes de confirmar.');
    }

    private function selectedBusiness(Request $request): ?Empresa
    {
        $businessId = filter_var($request->session()->get(self::BUSINESS_SESSION_KEY), FILTER_VALIDATE_INT);
        if ($businessId === false || $businessId < 1) {
            $request->session()->forget([
                self::BUSINESS_SESSION_KEY,
                self::CART_SESSION_KEY,
                self::PROPOSAL_SESSION_KEY,
                self::RESULT_SESSION_KEY,
            ]);

            return null;
        }

        $business = Empresa::find($businessId);
        if (! $business) {
            $request->session()->forget([
                self::BUSINESS_SESSION_KEY,
                self::CART_SESSION_KEY,
                self::PROPOSAL_SESSION_KEY,
                self::RESULT_SESSION_KEY,
            ]);
        }

        return $business;
    }

    /**
     * @return list<array{product_id: int, quantity: int, name: string, stock: int, available: bool, issue: string|null}>
     */
    private function presentItems(Empresa $business, mixed $items): array
    {
        if (! is_array($items) || $items === []) {
            return [];
        }

        $ids = collect($items)
            ->filter(fn ($item): bool => is_array($item) && is_int($item['product_id'] ?? null))
            ->pluck('product_id')
            ->unique()
            ->values();
        $products = $business->productos()
            ->whereIn('productos.id', $ids)
            ->get(['id', 'nombre', 'stock', 'estado'])
            ->keyBy('id');

        return collect($items)->map(function (array $item) use ($products): array {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $product = $products->get($productId);
            $issue = null;
            $available = $product !== null && $product->estado === 'activo' && $quantity > 0 && $product->stock >= $quantity;
            if (! $product || $product->estado !== 'activo') {
                $issue = 'El producto dejó de estar disponible en este negocio.';
            } elseif ($product->stock < $quantity) {
                $issue = "Solo hay {$product->stock} unidades disponibles.";
            }

            return [
                'product_id' => $productId,
                'quantity' => $quantity,
                'name' => $product?->nombre ?? 'Producto no disponible',
                'stock' => (int) ($product?->stock ?? 0),
                'available' => $available,
                'issue' => $issue,
            ];
        })->values()->all();
    }

    /**
     * @return list<array{product_id: int, name: string, stock: int}>
     */
    private function presentAlternatives(Empresa $business, mixed $productIds): array
    {
        if (! is_array($productIds)) {
            return [];
        }

        $ids = collect($productIds)
            ->filter(fn ($id): bool => is_int($id))
            ->unique()
            ->values();
        if ($ids->isEmpty()) {
            return [];
        }

        return $business->productos()
            ->where('productos.estado', 'activo')
            ->where('productos.stock', '>', 0)
            ->whereIn('productos.id', $ids)
            ->orderBy('productos.nombre')
            ->get(['id', 'nombre', 'stock'])
            ->map(fn (Producto $product): array => [
                'product_id' => (int) $product->id,
                'name' => $product->nombre,
                'stock' => (int) $product->stock,
            ])
            ->all();
    }

    private function validatedSessionCart(Request $request): array
    {
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);
        if (! is_array($cart)) {
            throw ValidationException::withMessages(['cart' => 'Tu Pedido no es válido. Revisa el carrito antes de continuar.']);
        }

        $validated = [];
        foreach ($cart as $productId => $quantity) {
            $id = filter_var($productId, FILTER_VALIDATE_INT);
            $count = filter_var($quantity, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1 || $count === false || $count < 1 || $count > 999) {
                throw ValidationException::withMessages(['cart' => 'Tu Pedido contiene cantidades no válidas. Revísalo antes de continuar.']);
            }
            $validated[(string) $id] = $count;
        }

        return $validated;
    }

    private function assertCustomerOrGuest(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user === null || ($user->role === User::ROLE_CUSTOMER && $user->activo),
            403,
            'Esta cuenta no puede utilizar el asistente de pedidos de cliente.'
        );
    }
}
