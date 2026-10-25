<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Empresa;
use App\Models\Movimiento;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SmartOrderController extends Controller
{
    private const BUSINESS_SESSION_KEY = 'smart_order.empresa_id';
    private const CART_SESSION_KEY = 'smart_order.cart';
    private const TOKEN_SESSION_KEY = 'smart_order.confirmation_token';
    private const RECEIPT_SESSION_KEY = 'smart_order.receipt';

    public function index(Request $request): View
    {
        $selectedBusinessId = $request->session()->get(self::BUSINESS_SESSION_KEY);

        if ($selectedBusinessId !== null) {
            $validatedBusinessId = filter_var($selectedBusinessId, FILTER_VALIDATE_INT);

            if ($validatedBusinessId === false || $validatedBusinessId < 1 || ! Empresa::whereKey($validatedBusinessId)->exists()) {
                $request->session()->forget(self::BUSINESS_SESSION_KEY);
                $this->forgetOrderState($request);
            }
        }

        $businesses = Empresa::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'direccion', 'logo']);

        return view('smart-order', [
            'businesses' => $businesses,
            'smartOrderError' => session('smart_order_error'),
        ]);
    }

    public function selectBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
        ]);

        $newBusinessId = (int) $validated['empresa_id'];
        if ((int) $request->session()->get(self::BUSINESS_SESSION_KEY, 0) !== $newBusinessId) {
            $this->forgetOrderState($request);
        }
        $request->session()->put(self::BUSINESS_SESSION_KEY, $newBusinessId);

        return redirect()->route('smart.order.catalog');
    }

    public function catalog(Request $request): View|RedirectResponse
    {
        $businessId = $request->session()->get(self::BUSINESS_SESSION_KEY);

        $validatedBusinessId = filter_var($businessId, FILTER_VALIDATE_INT);

        if ($validatedBusinessId === false || $validatedBusinessId < 1) {
            return redirect()
                ->route('smart.order')
                ->with('smart_order_error', 'Selecciona un negocio antes de consultar su catálogo.');
        }

        $business = Empresa::find($validatedBusinessId);

        if (! $business) {
            $request->session()->forget(self::BUSINESS_SESSION_KEY);

            return redirect()
                ->route('smart.order')
                ->with('smart_order_error', 'El negocio seleccionado ya no está disponible. Elige otro para continuar.');
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'categoria' => [
                'nullable',
                'integer',
                Rule::exists('categorias', 'id')->where('empresa_id', $business->id),
            ],
        ]);

        try {
            $productsQuery = $business->productos()
                ->where('productos.empresa_id', $business->id)
                ->where('productos.estado', 'activo')
                ->where('productos.stock', '>', 0)
                ->with('categoria')
                ->orderBy('productos.nombre');

            if (! empty($validated['search'])) {
                $search = trim($validated['search']);
                $productsQuery->where(function ($query) use ($search): void {
                    $query->where('productos.nombre', 'like', "%{$search}%")
                        ->orWhereHas('categoria', fn ($categories) => $categories->where('nombre', 'like', "%{$search}%"));
                });
            }

            if (! empty($validated['categoria'])) {
                $productsQuery->where('productos.categoria_id', $validated['categoria']);
            }

            $products = $productsQuery->paginate(12)->withQueryString();
            $categories = $business->categorias()->orderBy('nombre')->get(['id', 'nombre']);
        } catch (QueryException $exception) {
            Log::error('No se pudo cargar el catálogo de pedidos inteligentes.', [
                'empresa_id' => $business->id,
                'exception' => $exception,
            ]);

            return view('smart-order', [
                'businesses' => collect(),
                'selectedBusiness' => $business,
                'catalogError' => 'No fue posible cargar el catálogo en este momento. Intenta de nuevo más tarde.',
                'products' => collect(),
                'categories' => collect(),
                'cartSummary' => [
                    'quantity' => array_sum($this->sessionCart($request)),
                    'total' => 0,
                ],
            ]);
        }

        $business = Empresa::find($validatedBusinessId);

        if (! $business) {
            $request->session()->forget(self::BUSINESS_SESSION_KEY);
            $this->forgetOrderState($request);

            return redirect()
                ->route('smart.order')
                ->with('smart_order_error', 'El negocio seleccionado ya no está disponible. Elige otro para continuar.');
        }

        $cartSummary = $this->cartSummary($request, $business);

        return view('smart-order', [
            'businesses' => collect(),
            'selectedBusiness' => $business,
            'products' => $products,
            'categories' => $categories,
            'cartSummary' => $cartSummary,
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $business = $this->selectedBusiness($request);

        if (! $business) {
            return $this->businessSelectionRedirect();
        }

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')
                    ->where('empresa_id', $business->id)
                    ->where('estado', 'activo'),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $product = $business->productos()
            ->where('productos.estado', 'activo')
            ->findOrFail($validated['product_id']);
        $cart = $this->sessionCart($request);
        $key = (string) $product->id;
        $quantity = (int) ($cart[$key] ?? 0) + (int) $validated['quantity'];

        if ($quantity > 999 || $quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => $quantity > $product->stock
                    ? "Solo hay {$product->stock} unidades disponibles de {$product->nombre}."
                    : 'La cantidad máxima por producto es 999 unidades.',
            ]);
        }

        $cart[$key] = $quantity;
        $request->session()->put(self::CART_SESSION_KEY, $cart);
        $request->session()->forget(self::TOKEN_SESSION_KEY);

        return redirect()
            ->route('smart.order.catalog')
            ->with('success', "{$product->nombre} se añadió a Tu Pedido.");
    }

    public function cart(Request $request): View|RedirectResponse
    {
        $business = $this->selectedBusiness($request);

        if (! $business) {
            return $this->businessSelectionRedirect();
        }

        return view('smart-order-cart', [
            'selectedBusiness' => $business,
            'cartSummary' => $this->cartSummary($request, $business),
        ]);
    }

    public function updateCartItem(Request $request, int $producto): RedirectResponse
    {
        $business = $this->selectedBusiness($request);

        if (! $business) {
            return $this->businessSelectionRedirect();
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        $cart = $this->sessionCart($request);
        if (! array_key_exists((string) $producto, $cart)) {
            return redirect()->route('smart.order.cart')
                ->with('order_error', 'Ese producto no forma parte de Tu Pedido.');
        }

        $product = $business->productos()
            ->where('productos.estado', 'activo')
            ->findOrFail($producto);
        $quantity = (int) $validated['quantity'];

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Solo hay {$product->stock} unidades disponibles de {$product->nombre}.",
            ]);
        }

        $cart[(string) $product->id] = $quantity;
        $request->session()->put(self::CART_SESSION_KEY, $cart);
        $request->session()->forget(self::TOKEN_SESSION_KEY);

        return redirect()->route('smart.order.cart')->with('success', 'La cantidad del pedido se actualizó.');
    }

    public function removeFromCart(Request $request, int $producto): RedirectResponse
    {
        if (! $this->selectedBusiness($request)) {
            return $this->businessSelectionRedirect();
        }

        $cart = $this->sessionCart($request);
        unset($cart[(string) $producto]);
        $request->session()->put(self::CART_SESSION_KEY, $cart);
        $request->session()->forget(self::TOKEN_SESSION_KEY);

        return redirect()->route('smart.order.cart')->with('success', 'Producto eliminado de Tu Pedido.');
    }

    public function clearCart(Request $request): RedirectResponse
    {
        if (! $this->selectedBusiness($request)) {
            return $this->businessSelectionRedirect();
        }

        $request->session()->forget([self::CART_SESSION_KEY, self::TOKEN_SESSION_KEY]);

        return redirect()->route('smart.order.cart')->with('success', 'Tu Pedido está vacío.');
    }

    public function review(Request $request): View|RedirectResponse
    {
        $business = $this->selectedBusiness($request);

        if (! $business) {
            return $this->businessSelectionRedirect();
        }

        $summary = $this->cartSummary($request, $business);

        if ($summary['items']->isEmpty() || $summary['hasIssues']) {
            return redirect()->route('smart.order.cart')
                ->with('order_error', 'Revisa los productos y las existencias antes de confirmar.');
        }

        $token = $request->session()->get(self::TOKEN_SESSION_KEY);
        if (! is_string($token) || ! Str::isUuid($token)) {
            $token = (string) Str::uuid();
            $request->session()->put(self::TOKEN_SESSION_KEY, $token);
        }

        return view('smart-order-review', [
            'selectedBusiness' => $business,
            'cartSummary' => $summary,
            'confirmationToken' => $token,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $business = $this->selectedBusiness($request);

        if (! $business) {
            return $this->businessSelectionRedirect();
        }

        $validated = $request->validate([
            'confirmation_token' => ['required', 'uuid'],
        ]);
        $token = $validated['confirmation_token'];
        $expectedToken = $request->session()->get(self::TOKEN_SESSION_KEY);
        $receipt = $request->session()->get(self::RECEIPT_SESSION_KEY, []);

        if (is_array($receipt) && ($receipt['token'] ?? null) === $token) {
            return redirect()->route('smart.order.confirmed');
        }

        if (! is_string($expectedToken) || ! hash_equals($expectedToken, $token)) {
            return redirect()->route('smart.order.cart')
                ->with('order_error', 'La confirmación expiró. Revisa nuevamente Tu Pedido.');
        }

        if (! Schema::hasColumn('pedidos', 'smart_order_token')) {
            Log::error('No está aplicada la migración de idempotencia de SmartOrder.');

            return redirect()->route('smart.order.review')
                ->with('order_error', 'No se puede confirmar todavía. El sistema requiere una actualización de base de datos.');
        }

        $cart = $this->sessionCart($request);
        if ($cart === []) {
            return redirect()->route('smart.order.cart')->with('order_error', 'Tu Pedido está vacío.');
        }

        $actor = $request->user();
        if ($actor && ($actor->role !== User::ROLE_CUSTOMER || ! $actor->activo)) {
            abort(403, 'Esta cuenta no puede confirmar pedidos de cliente.');
        }

        try {
            $order = DB::transaction(function () use ($business, $cart, $token, $actor): Pedido {
                Empresa::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();

                $existingOrder = Pedido::query()->where('smart_order_token', $token)->first();
                if ($existingOrder) {
                    if ((int) $existingOrder->empresa_id !== $business->id) {
                        throw ValidationException::withMessages([
                            'confirmation_token' => 'El pedido de esta confirmación pertenece a otro negocio.',
                        ]);
                    }

                    return $existingOrder;
                }

                $products = Producto::forEmpresa($business->id)
                    ->whereIn('id', array_keys($cart))
                    ->where('estado', 'activo')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy(fn (Producto $product): string => (string) $product->id);

                if ($products->count() !== count($cart)) {
                    throw ValidationException::withMessages([
                        'products' => 'Uno o más productos ya no están disponibles en este negocio.',
                    ]);
                }

                foreach ($cart as $productId => $quantity) {
                    if ($quantity < 1 || $products[$productId]->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'products' => "No hay existencias suficientes de {$products[$productId]->nombre}.",
                        ]);
                    }
                }

                $total = 0.0;
                $subtotals = [];
                foreach ($cart as $productId => $quantity) {
                    $subtotal = round($quantity * (float) $products[$productId]->precio_venta, 2);
                    if ($subtotal > 99_999_999.99 || $total + $subtotal > 99_999_999.99) {
                        throw ValidationException::withMessages([
                            'products' => 'El total del pedido supera el máximo permitido.',
                        ]);
                    }
                    $subtotals[$productId] = $subtotal;
                    $total += $subtotal;
                }

                $order = Pedido::create([
                    'empresa_id' => $business->id,
                    'user_id' => null,
                    'cliente_id' => $actor?->id,
                    'numero_pedido' => Pedido::generarNumeroPedido($business->id),
                    'estado' => 'pendiente',
                    'metodo_pago' => 'pendiente',
                    'total' => 0,
                    'stock_reserved' => true,
                    'smart_order_token' => $token,
                ]);

                foreach ($cart as $productId => $quantity) {
                    $product = $products[$productId];
                    $subtotal = $subtotals[$productId];

                    $product->decrement('stock', $quantity);

                    Movimiento::create([
                        'producto_id' => $product->id,
                        'user_id' => $actor?->id,
                        'tipo' => 'salida',
                        'cantidad' => $quantity,
                        'descripcion' => "Reserva del pedido {$order->numero_pedido}",
                    ]);

                    DetallePedido::create([
                        'empresa_id' => $business->id,
                        'pedido_id' => $order->id,
                        'producto_id' => $product->id,
                        'cantidad' => $quantity,
                        'precio' => $product->precio_venta,
                        'subtotal' => $subtotal,
                    ]);
                }

                $order->update(['total' => $total]);

                return $order;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (QueryException $exception) {
            try {
                $order = Pedido::query()->where('smart_order_token', $token)->first();
            } catch (QueryException $lookupException) {
                Log::error('No se pudo comprobar si el pedido SmartOrder ya había sido creado.', [
                    'empresa_id' => $business->id,
                    'exception' => $lookupException,
                ]);

                Log::error('Falló la confirmación de un pedido SmartOrder.', [
                    'empresa_id' => $business->id,
                    'exception' => $exception,
                ]);

                return redirect()->route('smart.order.review')
                    ->with('order_error', 'No fue posible confirmar el pedido. No se reservó inventario; intenta de nuevo.');
            }

            if (! $order) {
                Log::error('Falló la confirmación de un pedido SmartOrder.', [
                    'empresa_id' => $business->id,
                    'exception' => $exception,
                ]);

                return redirect()->route('smart.order.review')
                    ->with('order_error', 'No fue posible confirmar el pedido. No se reservó inventario; intenta de nuevo.');
            }
        }

        $this->storeReceipt($request, $order);

        return redirect()->route('smart.order.confirmed');
    }

    public function confirmed(Request $request): View|RedirectResponse
    {
        $receipt = $request->session()->get(self::RECEIPT_SESSION_KEY);
        if (! is_array($receipt) || empty($receipt['id']) || empty($receipt['token'])) {
            return redirect()->route('smart.order.catalog')
                ->with('order_error', 'No hay una confirmación reciente para mostrar.');
        }

        $order = Pedido::query()
            ->whereKey($receipt['id'])
            ->where('smart_order_token', $receipt['token'])
            ->with(['empresa', 'detalles.producto'])
            ->first();

        if (! $order) {
            $request->session()->forget(self::RECEIPT_SESSION_KEY);

            return redirect()->route('smart.order.catalog')
                ->with('order_error', 'No encontramos el pedido confirmado. Contacta al negocio si el problema continúa.');
        }

        return view('smart-order-confirmed', compact('order'));
    }

    private function selectedBusiness(Request $request): ?Empresa
    {
        $businessId = filter_var(
            $request->session()->get(self::BUSINESS_SESSION_KEY),
            FILTER_VALIDATE_INT
        );

        if ($businessId === false || $businessId < 1) {
            return null;
        }

        $business = Empresa::find($businessId);
        if (! $business) {
            $request->session()->forget(self::BUSINESS_SESSION_KEY);
            $this->forgetOrderState($request);
        }

        return $business;
    }

    private function businessSelectionRedirect(): RedirectResponse
    {
        return redirect()->route('smart.order')
            ->with('smart_order_error', 'Selecciona un negocio antes de continuar.');
    }

    private function sessionCart(Request $request): array
    {
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);
        if (! is_array($cart)) {
            return [];
        }

        $validCart = [];
        foreach ($cart as $productId => $quantity) {
            $id = filter_var($productId, FILTER_VALIDATE_INT);
            $count = filter_var($quantity, FILTER_VALIDATE_INT);

            if ($id !== false && $id > 0 && $count !== false && $count > 0 && $count <= 999) {
                $validCart[(string) $id] = $count;
            }
        }

        if ($validCart !== $cart) {
            $request->session()->put(self::CART_SESSION_KEY, $validCart);
        }

        return $validCart;
    }

    private function cartSummary(Request $request, Empresa $business): array
    {
        $cart = $this->sessionCart($request);
        $products = $business->productos()
            ->whereIn('productos.id', array_keys($cart))
            ->get()
            ->keyBy(fn (Producto $product): string => (string) $product->id);

        $items = collect();
        $total = 0.0;
        $hasIssues = false;

        foreach ($cart as $productId => $quantity) {
            $product = $products->get((string) $productId);
            $available = $product && $product->estado === 'activo' && $product->stock >= $quantity;
            $price = $product ? (float) $product->precio_venta : 0.0;
            $subtotal = $price * $quantity;
            if ($available) {
                $total += $subtotal;
            } else {
                $hasIssues = true;
            }

            $items->push([
                'productId' => (int) $productId,
                'product' => $product,
                'quantity' => $quantity,
                'unitPrice' => $price,
                'subtotal' => $subtotal,
                'available' => (bool) $available,
            ]);
        }

        return [
            'items' => $items,
            'total' => round($total, 2),
            'hasIssues' => $hasIssues,
            'quantity' => array_sum($cart),
        ];
    }

    private function forgetOrderState(Request $request): void
    {
        $request->session()->forget([
            self::CART_SESSION_KEY,
            self::TOKEN_SESSION_KEY,
            self::RECEIPT_SESSION_KEY,
        ]);
    }

    private function storeReceipt(Request $request, Pedido $order): void
    {
        $request->session()->put(self::RECEIPT_SESSION_KEY, [
            'id' => $order->id,
            'token' => $order->smart_order_token,
        ]);
        $request->session()->forget([self::CART_SESSION_KEY, self::TOKEN_SESSION_KEY]);
    }
}
