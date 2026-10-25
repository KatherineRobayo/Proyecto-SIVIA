<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Categoria;
use App\Models\Movimiento;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MultiCompanyHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_cannot_read_or_modify_another_companys_product(): void
    {
        $companyA = $this->company('A');
        $companyB = $this->company('B');
        $adminA = $this->user(User::ROLE_COMPANY_ADMIN, $companyA);
        $productA = $this->product($companyA, 'A-1', 'Producto A');
        $productB = $this->product($companyB, 'B-1', 'Producto B');

        $this->actingAs($adminA)
            ->get(route('productos.index'))
            ->assertOk()
            ->assertSee('Producto A')
            ->assertDontSee('Producto B');

        $this->actingAs($adminA)
            ->put(route('productos.update', $productB), [
                'nombre' => 'Modificado',
                'codigo' => 'B-1',
                'precio_compra' => 5,
                'precio_venta' => 10,
                'iva' => 0,
                'stock' => 10,
                'stock_minimo' => 2,
            ])
            ->assertNotFound();

        $this->assertSame('Producto B', $productB->fresh()->nombre);
        $this->assertSame($productA->id, $productA->fresh()->id);
    }

    public function test_employee_cannot_use_admin_product_actions_or_access_another_companys_order(): void
    {
        $companyA = $this->company('A');
        $companyB = $this->company('B');
        $employeeA = $this->user(User::ROLE_EMPLOYEE, $companyA);
        $productB = $this->product($companyB, 'B-2', 'Producto B');
        $orderB = Pedido::create([
            'empresa_id' => $companyB->id,
            'user_id' => null,
            'numero_pedido' => 'PED-B-1',
            'estado' => 'pendiente',
            'total' => 10,
            'metodo_pago' => 'pendiente',
        ]);

        $this->actingAs($employeeA)
            ->post(route('productos.store'), [
                'nombre' => 'No autorizado',
                'precio_compra' => 1,
                'precio_venta' => 2,
                'iva' => 0,
                'stock' => 1,
                'stock_minimo' => 0,
            ])
            ->assertRedirect();

        $this->actingAs($employeeA)
            ->get(route('pedidos.show', $orderB))
            ->assertNotFound();

        $this->actingAs($employeeA)
            ->get(route('productos.index'))
            ->assertOk()
            ->assertDontSee('Producto B');
        $this->actingAs($employeeA)->get(route('caja.index'))->assertOk();
        $this->assertDatabaseMissing('productos', ['nombre' => 'No autorizado']);
        $this->assertNotNull($productB->fresh());
    }

    public function test_company_admin_cannot_manage_users_from_another_company(): void
    {
        $companyA = $this->company('Users A');
        $companyB = $this->company('Users B');
        $adminA = $this->user(User::ROLE_COMPANY_ADMIN, $companyA);
        $employeeB = $this->user(User::ROLE_EMPLOYEE, $companyB);

        $this->actingAs($adminA)
            ->put(route('ajustes.usuario.update', $employeeB), ['name' => 'Cross-company edit'])
            ->assertForbidden();

        $this->actingAs($adminA)
            ->delete(route('ajustes.usuario.destroy', $employeeB))
            ->assertForbidden();

        $this->assertSame('Empleado', $employeeB->fresh()->name);
        $this->assertTrue($employeeB->fresh()->activo);
    }

    public function test_customer_without_company_can_browse_and_place_a_reserved_order(): void
    {
        $company = $this->company('Customer shop');
        $customer = $this->user(User::ROLE_CUSTOMER);
        $product = $this->product($company, 'SKU-C1', 'Customer product', 20, 8);

        $this->actingAs($customer)
            ->get(route('catalogo.clientes.productos', $company))
            ->assertOk()
            ->assertSee('Customer product');

        $this->actingAs($customer)
            ->post(route('catalogo.clientes.pedidos.store', $company), [
                'productos' => [
                    ['id' => $product->id, 'cantidad' => 2, 'precio' => 0],
                ],
            ])
            ->assertRedirect(route('catalogo.clientes.empresas'));

        $order = Pedido::query()->sole();
        $this->assertNull($customer->empresa_id);
        $this->assertSame($customer->id, $order->cliente_id);
        $this->assertNull($order->user_id);
        $this->assertSame($company->id, $order->empresa_id);
        $this->assertTrue($order->stock_reserved);
        $this->assertSame('40.00', $order->total);
        $this->assertSame(6, $product->fresh()->stock);
        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id' => $order->id,
            'producto_id' => $product->id,
            'precio' => 20,
            'cantidad' => 2,
        ]);
        $this->assertDatabaseHas('movimientos', [
            'producto_id' => $product->id,
            'user_id' => $customer->id,
            'tipo' => 'salida',
            'cantidad' => 2,
        ]);
    }

    public function test_customer_order_rejects_a_product_from_a_different_company(): void
    {
        $companyA = $this->company('A');
        $companyB = $this->company('B');
        $customer = $this->user(User::ROLE_CUSTOMER);
        $productB = $this->product($companyB, 'SKU-B2', 'Product B');

        $this->actingAs($customer)
            ->from(route('catalogo.clientes.productos', $companyA))
            ->post(route('catalogo.clientes.pedidos.store', $companyA), [
                'productos' => [
                    ['id' => $productB->id, 'cantidad' => 1],
                ],
            ])
            ->assertSessionHasErrors('productos.0.id');

        $this->assertSame(0, Pedido::count());
        $this->assertSame(10, $productB->fresh()->stock);
    }

    public function test_employee_cannot_manage_categories_but_company_admin_categories_are_tenant_scoped(): void
    {
        $companyA = $this->company('Category A');
        $companyB = $this->company('Category B');
        $adminA = $this->user(User::ROLE_COMPANY_ADMIN, $companyA);
        $employeeA = $this->user(User::ROLE_EMPLOYEE, $companyA);
        $categoryB = Categoria::create([
            'empresa_id' => $companyB->id,
            'nombre' => 'Categoría B',
        ]);

        $this->actingAs($employeeA)
            ->post(route('categorias.store'), ['nombre' => 'No autorizado'])
            ->assertRedirect();

        $this->actingAs($adminA)
            ->put(route('categorias.update', $categoryB), ['nombre' => 'Modificada'])
            ->assertNotFound();

        $this->assertSame('Categoría B', $categoryB->fresh()->nombre);
        $this->actingAs($adminA)
            ->post(route('categorias.store'), ['nombre' => 'Categoría A'])
            ->assertRedirect();
        $this->assertDatabaseHas('categorias', [
            'empresa_id' => $companyA->id,
            'nombre' => 'Categoría A',
        ]);
    }

    public function test_employee_sale_uses_server_price_and_records_stock_movement(): void
    {
        $company = $this->company('Sale company');
        $employee = $this->user(User::ROLE_EMPLOYEE, $company);
        $product = $this->product($company, 'SKU-SALE', 'Sale product', 12, 5);

        $this->actingAs($employee)
            ->post(route('ventas.store'), [
                'metodo_pago' => 'tarjeta',
                'productos' => [
                    ['id' => $product->id, 'cantidad' => 2, 'precio' => 0],
                ],
            ])
            ->assertRedirect(route('ventas.index'));

        $sale = Venta::query()->sole();
        $this->assertSame('24.00', $sale->total);
        $this->assertSame($employee->id, $sale->user_id);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseHas('detalle_ventas', [
            'empresa_id' => $company->id,
            'venta_id' => $sale->id,
            'producto_id' => $product->id,
            'cantidad' => 2,
            'precio' => 12,
        ]);
        $this->assertDatabaseHas('movimientos', [
            'producto_id' => $product->id,
            'user_id' => $employee->id,
            'tipo' => 'salida',
            'cantidad' => 2,
        ]);
    }

    public function test_fulfilling_customer_order_creates_one_sale_without_double_decrementing_reserved_stock(): void
    {
        $company = $this->company('Order fulfillment');
        $customer = $this->user(User::ROLE_CUSTOMER);
        $employee = $this->user(User::ROLE_EMPLOYEE, $company);
        $product = $this->product($company, 'SKU-ORDER', 'Order product', 8, 5);

        $this->actingAs($customer)->post(route('catalogo.clientes.pedidos.store', $company), [
            'productos' => [['id' => $product->id, 'cantidad' => 2]],
        ])->assertRedirect();

        $order = Pedido::query()->sole();
        $this->actingAs($employee)->post(route('pedidos.cambiarEstado', $order), [
            'estado' => 'entregado',
            'metodo_pago' => 'efectivo',
        ])->assertRedirect();

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseHas('ventas', [
            'pedido_id' => $order->id,
            'empresa_id' => $company->id,
            'user_id' => $employee->id,
            'cliente_id' => $customer->id,
            'total' => 16,
        ]);
        $this->assertSame(1, Venta::where('pedido_id', $order->id)->count());
        $this->assertSame($customer->id, $order->fresh()->cliente_id);
    }

    public function test_cancelling_a_customer_order_releases_reserved_stock(): void
    {
        $company = $this->company('Cancel order');
        $customer = $this->user(User::ROLE_CUSTOMER);
        $employee = $this->user(User::ROLE_EMPLOYEE, $company);
        $product = $this->product($company, 'SKU-CANCEL', 'Reserved product', 8, 5);

        $this->actingAs($customer)->post(route('catalogo.clientes.pedidos.store', $company), [
            'productos' => [['id' => $product->id, 'cantidad' => 2]],
        ])->assertRedirect();

        $order = Pedido::query()->sole();
        $this->actingAs($employee)->post(route('pedidos.cambiarEstado', $order), [
            'estado' => 'cancelado',
        ])->assertRedirect();

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertFalse($order->fresh()->stock_reserved);
        $this->assertEquals(2, Movimiento::where('producto_id', $product->id)->where('tipo', 'entrada')->sum('cantidad'));
        $this->assertEquals(2, Movimiento::where('producto_id', $product->id)->where('tipo', 'salida')->sum('cantidad'));
        $this->assertSame(0, Venta::count());
    }

    public function test_inventory_adjustment_records_actor_and_product_company(): void
    {
        $companyA = $this->company('Inventory A');
        $companyB = $this->company('Inventory B');
        $employeeA = $this->user(User::ROLE_EMPLOYEE, $companyA);
        $productA = $this->product($companyA, 'SKU-INV-A', 'Inventory A product');
        $productB = $this->product($companyB, 'SKU-INV-B', 'Inventory B product');

        $this->actingAs($employeeA)->post(route('productos.ajustarStock'), [
            'producto_id' => $productA->id,
            'tipo' => 'sumar',
            'cantidad' => 3,
        ])->assertRedirect();

        $this->assertSame(13, $productA->fresh()->stock);
        $this->assertSame(10, $productB->fresh()->stock);
        $this->assertDatabaseHas('movimientos', [
            'producto_id' => $productA->id,
            'user_id' => $employeeA->id,
            'tipo' => 'entrada',
            'cantidad' => 3,
        ]);

        $this->actingAs($employeeA)->post(route('productos.ajustarStock'), [
            'producto_id' => $productB->id,
            'tipo' => 'restar',
            'cantidad' => 1,
        ])->assertSessionHasErrors('producto_id');
        $this->assertSame(10, $productB->fresh()->stock);
    }

    public function test_customer_cannot_access_administration_or_point_of_sale(): void
    {
        $customer = $this->user(User::ROLE_CUSTOMER);

        $this->actingAs($customer)->get(route('ajustes.index'))->assertRedirect();
        $this->actingAs($customer)->get(route('caja.index'))->assertRedirect();
        $this->actingAs($customer)->post(route('ajustes.empresa.store'), [
            'nombre' => 'Unauthorized',
        ])->assertRedirect();

        $this->assertDatabaseMissing('empresas', ['nombre' => 'Unauthorized']);
    }

    public function test_global_admin_can_create_companies_and_company_administrators(): void
    {
        $globalAdmin = $this->user(User::ROLE_GLOBAL_ADMIN);

        $this->actingAs($globalAdmin)->post(route('ajustes.empresa.store'), [
            'nombre' => 'Nueva empresa',
        ])->assertRedirect();
        $company = Empresa::where('nombre', 'Nueva empresa')->sole();

        $this->actingAs($globalAdmin)->post(route('ajustes.usuario.store'), [
            'name' => 'Administrador empresa',
            'email' => 'tenant-admin@example.test',
            'password' => 'SafePassword123',
            'role' => User::ROLE_COMPANY_ADMIN,
            'empresa_id' => $company->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'tenant-admin@example.test',
            'role' => User::ROLE_COMPANY_ADMIN,
            'empresa_id' => $company->id,
        ]);

        $this->actingAs($globalAdmin)->post(route('ajustes.usuario.store'), [
            'name' => 'Empleado no autorizado por global',
            'email' => 'employee@example.test',
            'password' => 'SafePassword123',
            'role' => User::ROLE_EMPLOYEE,
            'empresa_id' => $company->id,
        ])->assertSessionHasErrors('role');
    }

    public function test_legacy_roles_are_resolved_to_their_compatible_roles(): void
    {
        $company = $this->company('Legacy tenant');
        DB::table('users')->insert([
            [
                'name' => 'Legacy global',
                'email' => 'legacy-global@example.test',
                'role' => 'admin',
                'empresa_id' => null,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Legacy operator',
                'email' => 'legacy-operator@example.test',
                'role' => 'almacenista',
                'empresa_id' => $company->id,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $legacyGlobal = User::where('email', 'legacy-global@example.test')->firstOrFail();
        $legacyOperator = User::where('email', 'legacy-operator@example.test')->firstOrFail();

        $this->assertTrue($legacyGlobal->isAdminGlobal());
        $this->assertSame(User::ROLE_EMPLOYEE, $legacyOperator->role);
        $this->actingAs($legacyOperator)->get(route('inventario.index'))->assertOk();
    }

    public function test_inventory_and_reports_only_include_the_authenticated_company(): void
    {
        $companyA = $this->company('Report A');
        $companyB = $this->company('Report B');
        $adminA = $this->user(User::ROLE_COMPANY_ADMIN, $companyA);
        $productA = $this->product($companyA, 'SKU-RA', 'Product Report A');
        $this->product($companyB, 'SKU-RB', 'Product Report B');
        Venta::create([
            'empresa_id' => $companyA->id,
            'user_id' => $adminA->id,
            'total' => 15,
            'metodo_pago' => 'efectivo',
        ]);
        Venta::create([
            'empresa_id' => $companyB->id,
            'total' => 300,
            'metodo_pago' => 'efectivo',
        ]);
        Movimiento::create([
            'producto_id' => $productA->id,
            'user_id' => $adminA->id,
            'tipo' => 'entrada',
            'cantidad' => 1,
        ]);

        $this->actingAs($adminA)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertSee('Product Report A')
            ->assertDontSee('Product Report B');

        $this->actingAs($adminA)
            ->get(route('reportes.index'))
            ->assertOk()
            ->assertSee('$15.00')
            ->assertDontSee('$300.00');
    }

    private function company(string $name): Empresa
    {
        return Empresa::create(['nombre' => $name]);
    }

    private function user(string $role, ?Empresa $company = null): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'SafePassword123',
            'role' => $role,
            'empresa_id' => $company?->id,
            'activo' => true,
        ]);
    }

    private function product(Empresa $company, string $code, string $name, int $price = 10, int $stock = 10): Producto
    {
        return Producto::create([
            'empresa_id' => $company->id,
            'codigo' => $code,
            'nombre' => $name,
            'precio_compra' => 5,
            'precio_venta' => $price,
            'stock' => $stock,
            'stock_minimo' => 1,
            'iva' => 0,
            'estado' => 'activo',
        ]);
    }
}
