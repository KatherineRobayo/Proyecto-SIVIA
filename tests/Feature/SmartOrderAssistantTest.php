<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Movimiento;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Services\Chatbot\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class SmartOrderAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('smart_order_assistant.enabled', true);
        config()->set('chatbot.gemini.api_key', 'test-key-not-a-real-secret');
    }

    public function test_button_route_shows_assistant_and_interprets_a_valid_proposal_without_creating_an_order(): void
    {
        $business = $this->business();
        $product = $this->product($business, 'Pan integral', 8, 5);
        $this->mockGemini([
            'needs_clarification' => false,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'alternatives' => [],
        ]);
        $this->withSession(['smart_order.empresa_id' => $business->id]);

        $this->get(route('smart.order.catalog'))
            ->assertOk()
            ->assertSee('✨ ¿Necesitas ayuda para pedir?')
            ->assertSee(route('smart.order.assistant'));
        $this->get(route('smart.order.assistant'))->assertOk();
        $this->post(route('smart.order.assistant.interpret'), [
            'request' => 'Quiero dos panes integrales',
        ])->assertRedirect(route('smart.order.assistant'));

        $this->get(route('smart.order.assistant'))
            ->assertOk()
            ->assertSee('Esta propuesta usa productos reales del catálogo seleccionado.')
            ->assertSee('Pan integral')
            ->assertSee('Sí, confirmar y pagar');
        $this->assertSame(0, Pedido::count());
        $this->assertSame(0, Movimiento::count());
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_customer_must_accept_before_ai_proposal_is_added_to_existing_cart(): void
    {
        $business = $this->business();
        $product = $this->product($business, 'Jugo', 4, 7);
        $this->mockGemini([
            'needs_clarification' => false,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'alternatives' => [],
        ]);
        $this->withSession([
            'smart_order.empresa_id' => $business->id,
            'smart_order.cart' => [(string) $product->id => 1],
        ])->post(route('smart.order.assistant.interpret'), ['request' => 'Quiero dos jugos'])
            ->assertRedirect();

        $this->assertSame(1, session('smart_order.cart')[(string) $product->id]);
        $token = session('smart_order.assistant_proposal.token');
        $this->post(route('smart.order.assistant.accept'), ['proposal_token' => $token])
            ->assertRedirect(route('smart.order.cart'));
        $this->assertSame(3, session('smart_order.cart')[(string) $product->id]);
        $this->get(route('smart.order.cart'))->assertOk()->assertSee('$12,00');
        $this->assertSame(0, Pedido::count());
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_ambiguous_request_requires_clarification_and_cannot_be_accepted(): void
    {
        $business = $this->business();
        $this->mockGemini([
            'needs_clarification' => true,
            'items' => [],
            'alternatives' => [],
        ]);
        $this->withSession(['smart_order.empresa_id' => $business->id])
            ->post(route('smart.order.assistant.interpret'), ['request' => 'Quiero pan'])
            ->assertRedirect();

        $this->get(route('smart.order.assistant'))
            ->assertOk()
            ->assertSee('No pude identificar con seguridad todos los productos o cantidades.')
            ->assertDontSee('Añadir propuesta a Tu Pedido');
        $this->assertNull(session('smart_order.assistant_proposal'));
    }

    public function test_model_cannot_propose_another_business_product_or_accept_stale_stock(): void
    {
        $businessA = $this->business('Tienda A');
        $businessB = $this->business('Tienda B');
        $otherProduct = $this->product($businessB, 'Producto B', 10, 4);
        $this->mockGemini([
            'needs_clarification' => false,
            'items' => [['product_id' => $otherProduct->id, 'quantity' => 1]],
            'alternatives' => [],
        ]);
        $this->withSession(['smart_order.empresa_id' => $businessA->id])
            ->post(route('smart.order.assistant.interpret'), ['request' => 'Quiero producto B'])
            ->assertRedirect();

        $this->assertNull(session('smart_order.assistant_proposal'));
        $this->assertSame(0, Pedido::count());
        $this->assertSame(4, $otherProduct->fresh()->stock);
    }

    public function test_invalid_model_output_and_provider_failure_show_safe_messages_without_side_effects(): void
    {
        $business = $this->business();
        $this->mockGeminiResponse('not valid json');
        $this->withSession(['smart_order.empresa_id' => $business->id])
            ->post(route('smart.order.assistant.interpret'), ['request' => 'Quiero algo'])
            ->assertRedirect()
            ->assertSessionHas('assistant_error', 'No se pudo interpretar la solicitud en este momento. Inténtalo otra vez o continúa con el catálogo.');

        $this->assertSame(0, Pedido::count());
        $this->assertSame(0, Movimiento::count());

        $this->mock(GeminiClient::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andThrow(new RuntimeException('gemini_timeout'));
        });
        $this->post(route('smart.order.assistant.interpret'), ['request' => 'Quiero algo'])
            ->assertRedirect()
            ->assertSessionHas('assistant_error');
    }

    public function test_rejects_overlong_input_and_invalid_proposal_token(): void
    {
        $business = $this->business();
        $this->withSession(['smart_order.empresa_id' => $business->id])
            ->post(route('smart.order.assistant.interpret'), [
                'request' => str_repeat('a', config('smart_order_assistant.max_input_chars') + 1),
            ])->assertSessionHasErrors('request');

        $this->post(route('smart.order.assistant.accept'), ['proposal_token' => 'bad-token'])
            ->assertSessionHasErrors('proposal_token');
    }

    public function test_acceptance_rechecks_stock_for_combined_cart_quantities(): void
    {
        $business = $this->business();
        $product = $this->product($business, 'Agua', 2, 3);
        $token = (string) Str::uuid();
        $this->withSession([
            'smart_order.empresa_id' => $business->id,
            'smart_order.cart' => [(string) $product->id => 2],
            'smart_order.assistant_proposal' => [
                'token' => $token,
                'empresa_id' => $business->id,
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
            ],
        ]);

        $this->post(route('smart.order.assistant.accept'), ['proposal_token' => $token])
            ->assertRedirect(route('smart.order.assistant'))
            ->assertSessionHas('assistant_error');
        $this->assertSame(2, session('smart_order.cart')[(string) $product->id]);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(0, Pedido::count());
    }

    public function test_non_customer_account_cannot_use_the_customer_assistant(): void
    {
        $business = $this->business();
        $admin = User::create([
            'name' => 'Admin empresa',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'SafePassword123',
            'role' => User::ROLE_COMPANY_ADMIN,
            'empresa_id' => $business->id,
            'activo' => true,
        ]);

        $this->actingAs($admin)->withSession(['smart_order.empresa_id' => $business->id])
            ->get(route('smart.order.assistant'))
            ->assertForbidden();
        $this->actingAs($admin)
            ->post(route('smart.order.assistant.interpret'), ['request' => 'Quiero un producto'])
            ->assertForbidden();
    }

    public function test_similar_alternatives_are_limited_to_real_available_products_and_need_customer_choice(): void
    {
        $business = $this->business();
        $alternative = $this->product($business, 'Jugo de naranja', 6, 4);
        $this->mockGemini([
            'needs_clarification' => true,
            'items' => [],
            'alternatives' => [$alternative->id],
        ]);
        $this->withSession(['smart_order.empresa_id' => $business->id])
            ->post(route('smart.order.assistant.interpret'), ['request' => 'Quiero jugo de mandarina'])
            ->assertRedirect();

        $this->get(route('smart.order.assistant'))
            ->assertOk()
            ->assertSee('Opciones similares disponibles')
            ->assertSee('Jugo de naranja')
            ->assertDontSee('Añadir propuesta a Tu Pedido');
        $this->assertSame(0, Pedido::count());
        $this->assertSame(4, $alternative->fresh()->stock);
    }

    private function mockGemini(array $payload): void
    {
        $this->mockGeminiResponse(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function mockGeminiResponse(string $response): void
    {
        $this->mock(GeminiClient::class, function ($mock) use ($response): void {
            $mock->shouldReceive('generate')->once()->andReturn($response);
        });
    }

    private function business(string $name = 'Tienda IA'): Empresa
    {
        return Empresa::create(['nombre' => $name]);
    }

    private function product(Empresa $business, string $name, int $price, int $stock): Producto
    {
        return Producto::create([
            'empresa_id' => $business->id,
            'codigo' => null,
            'nombre' => $name,
            'precio_compra' => 1,
            'precio_venta' => $price,
            'iva' => 0,
            'stock' => $stock,
            'stock_minimo' => 1,
            'estado' => 'activo',
        ]);
    }
    protected function tearDown(): void 
    {
        // Restaurar los manejadores de errores de PHP para evitar el 'Risky' de PHPUnit
        restore_error_handler();
        restore_exception_handler();

        parent::tearDown();
    }
}
