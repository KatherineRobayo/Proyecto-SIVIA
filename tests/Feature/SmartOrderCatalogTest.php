<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartOrderCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_select_a_business_and_see_only_its_available_products(): void
    {
        $businessA = Empresa::create(['nombre' => 'Tienda A']);
        $businessB = Empresa::create(['nombre' => 'Tienda B']);
        $category = Categoria::create([
            'empresa_id' => $businessA->id,
            'nombre' => 'Bebidas',
        ]);

        $available = $this->product($businessA, 'Producto disponible', [
            'categoria_id' => $category->id,
        ]);
        $this->product($businessA, 'Sin existencias', ['stock' => 0]);
        $this->product($businessA, 'Inactivo', ['estado' => 'inactivo']);
        $this->product($businessB, 'Producto de otra tienda');

        $this->from(route('smart.order'))
            ->post(route('smart.order.business'), ['empresa_id' => $businessA->id])
            ->assertRedirect(route('smart.order.catalog'))
            ->assertSessionHas('smart_order.empresa_id', $businessA->id);

        $this->get(route('smart.order.catalog', ['empresa_id' => $businessB->id]))
            ->assertOk()
            ->assertSee('Tienda A')
            ->assertSee('Producto disponible')
            ->assertSee('Bebidas')
            ->assertDontSee('Sin existencias')
            ->assertDontSee('Inactivo')
            ->assertDontSee('Producto de otra tienda');

        $this->assertSame($businessA->id, $available->fresh()->empresa_id);
    }

    public function test_catalog_search_and_category_filter_are_scoped_to_selected_business(): void
    {
        $businessA = Empresa::create(['nombre' => 'Tienda A']);
        $businessB = Empresa::create(['nombre' => 'Tienda B']);
        $categoryA = Categoria::create(['empresa_id' => $businessA->id, 'nombre' => 'Frutas']);
        $categoryB = Categoria::create(['empresa_id' => $businessB->id, 'nombre' => 'Frutas']);
        $this->product($businessA, 'Manzana', ['categoria_id' => $categoryA->id]);
        $this->product($businessA, 'Pan');
        $this->product($businessB, 'Manzana de B', ['categoria_id' => $categoryB->id]);

        $this->withSession(['smart_order.empresa_id' => $businessA->id])
            ->get(route('smart.order.catalog', ['search' => 'Manzana', 'categoria' => $categoryA->id]))
            ->assertOk()
            ->assertSee('Manzana')
            ->assertDontSee('Pan')
            ->assertDontSee('Manzana de B');

        $this->withSession(['smart_order.empresa_id' => $businessA->id])
            ->get(route('smart.order.catalog', ['categoria' => $categoryB->id]))
            ->assertSessionHasErrors('categoria');
    }

    public function test_catalog_without_a_selected_business_returns_to_business_selection(): void
    {
        $this->get(route('smart.order.catalog'))
            ->assertRedirect(route('smart.order'))
            ->assertSessionHas('smart_order_error');

        $this->get(route('smart.order'))
            ->assertOk()
            ->assertSee('Selecciona un negocio antes de consultar su catálogo.');
    }

    public function test_catalog_clears_a_selected_business_that_no_longer_exists(): void
    {
        $this->withSession(['smart_order.empresa_id' => 987654])
            ->get(route('smart.order.catalog'))
            ->assertRedirect(route('smart.order'))
            ->assertSessionMissing('smart_order.empresa_id')
            ->assertSessionHas('smart_order_error');
    }

    private function product(Empresa $business, string $name, array $attributes = []): Producto
    {
        return Producto::create(array_merge([
            'empresa_id' => $business->id,
            'codigo' => null,
            'nombre' => $name,
            'precio_compra' => 5,
            'precio_venta' => 10,
            'iva' => 0,
            'stock' => 4,
            'stock_minimo' => 1,
            'estado' => 'activo',
        ], $attributes));
    }
}
