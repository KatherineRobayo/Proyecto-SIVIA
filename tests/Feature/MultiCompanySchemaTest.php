<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Empresa;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiCompanySchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_roles_enforce_company_membership_rules(): void
    {
        $empresa = Empresa::create(['nombre' => 'Empresa A']);

        $globalAdmin = User::create([
            'name' => 'Global',
            'email' => 'global@example.test',
            'password' => 'password',
            'role' => User::ROLE_GLOBAL_ADMIN,
        ]);
        $companyAdmin = User::create([
            'name' => 'Admin A',
            'email' => 'admin-a@example.test',
            'password' => 'password',
            'role' => User::ROLE_COMPANY_ADMIN,
            'empresa_id' => $empresa->id,
        ]);
        $employee = User::create([
            'name' => 'Empleado A',
            'email' => 'employee-a@example.test',
            'password' => 'password',
            'role' => User::ROLE_EMPLOYEE,
            'empresa_id' => $empresa->id,
        ]);
        $customer = User::create([
            'name' => 'Cliente',
            'email' => 'customer@example.test',
            'password' => 'password',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->assertNull($globalAdmin->empresa_id);
        $this->assertSame($empresa->id, $companyAdmin->empresa_id);
        $this->assertSame($empresa->id, $employee->empresa_id);
        $this->assertNull($customer->empresa_id);

        $this->expectException(\InvalidArgumentException::class);
        User::create([
            'name' => 'Invalid',
            'email' => 'invalid@example.test',
            'password' => 'password',
            'role' => User::ROLE_EMPLOYEE,
        ]);
    }

    public function test_product_codes_are_unique_within_each_company(): void
    {
        $empresaA = Empresa::create(['nombre' => 'Empresa A']);
        $empresaB = Empresa::create(['nombre' => 'Empresa B']);

        $this->createProduct($empresaA, 'SKU-1');
        $this->createProduct($empresaB, 'SKU-1');

        $this->expectException(QueryException::class);
        $this->createProduct($empresaA, 'SKU-1');
    }

    public function test_sale_details_cannot_reference_another_companys_product(): void
    {
        $empresaA = Empresa::create(['nombre' => 'Empresa A']);
        $empresaB = Empresa::create(['nombre' => 'Empresa B']);
        $productA = $this->createProduct($empresaA, 'A-1');
        $productB = $this->createProduct($empresaB, 'B-1');
        $sale = Venta::create([
            'empresa_id' => $empresaA->id,
            'total' => 10,
            'metodo_pago' => 'efectivo',
        ]);

        $this->expectException(QueryException::class);
        DetalleVenta::create([
            'empresa_id' => $empresaA->id,
            'venta_id' => $sale->id,
            'producto_id' => $productB->id,
            'cantidad' => 1,
            'precio' => 10,
            'subtotal' => 10,
        ]);
    }

    public function test_order_details_cannot_reference_another_companys_product(): void
    {
        $empresaA = Empresa::create(['nombre' => 'Empresa A']);
        $empresaB = Empresa::create(['nombre' => 'Empresa B']);
        $productB = $this->createProduct($empresaB, 'B-2');
        $order = Pedido::create([
            'empresa_id' => $empresaA->id,
            'numero_pedido' => 'PED-000001',
            'total' => 10,
            'metodo_pago' => 'pendiente',
        ]);

        $this->expectException(QueryException::class);
        DetallePedido::create([
            'empresa_id' => $empresaA->id,
            'pedido_id' => $order->id,
            'producto_id' => $productB->id,
            'cantidad' => 1,
            'precio' => 10,
            'subtotal' => 10,
        ]);
    }

    public function test_product_category_must_belong_to_the_same_company(): void
    {
        $empresaA = Empresa::create(['nombre' => 'Empresa A']);
        $empresaB = Empresa::create(['nombre' => 'Empresa B']);
        $categoryB = Categoria::create([
            'empresa_id' => $empresaB->id,
            'nombre' => 'Categoría B',
        ]);

        $this->expectException(QueryException::class);
        Producto::create([
            'empresa_id' => $empresaA->id,
            'categoria_id' => $categoryB->id,
            'nombre' => 'Producto cruzado',
            'codigo' => 'CROSS-1',
            'precio_venta' => 10,
        ]);
    }

    private function createProduct(Empresa $empresa, string $codigo): Producto
    {
        return Producto::create([
            'empresa_id' => $empresa->id,
            'codigo' => $codigo,
            'nombre' => 'Producto ' . $codigo,
            'precio_venta' => 10,
            'estado' => 'activo',
        ]);
    }
}
