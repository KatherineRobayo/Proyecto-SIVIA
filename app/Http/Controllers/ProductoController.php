<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index()
    {
        $empresaId = $this->empresaId();
        $productos = Producto::forEmpresa($empresaId)
            ->with('categoria')
            ->latest()
            ->get();
        $categorias = Categoria::query()->where('empresa_id', $empresaId)->orderBy('nombre')->get();

        return view('dashboard.productos.index', compact('productos', 'categorias'));
    }

    public function store(Request $request)
    {
        $this->authorizeCompanyAdmin();
        $empresaId = $this->empresaId();
        $data = $this->validatedProductData($request, $empresaId);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        DB::transaction(function () use ($data, $empresaId): void {
            $producto = Producto::create($data + [
                'empresa_id' => $empresaId,
                'tipo' => 'simple',
                'estado' => 'activo',
            ]);

            if ($producto->stock > 0) {
                Movimiento::create([
                    'producto_id' => $producto->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'entrada',
                    'cantidad' => $producto->stock,
                    'descripcion' => 'Existencia inicial del producto',
                ]);
            }

        });

        return back()->with('success', 'Producto creado');
    }

    public function update(Request $request, Producto $producto)
    {
        $this->authorizeCompanyAdmin();
        $this->assertProductBelongsToActor($producto);
        $stockAnterior = $producto->stock;
        $data = $this->validatedProductData($request, (int) $producto->empresa_id, $producto);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        DB::transaction(function () use ($producto, $data, $stockAnterior): void {
            $producto->update($data);
            $delta = $producto->stock - $stockAnterior;

            if ($delta !== 0) {
                Movimiento::create([
                    'producto_id' => $producto->id,
                    'user_id' => auth()->id(),
                    'tipo' => $delta > 0 ? 'entrada' : 'salida',
                    'cantidad' => abs($delta),
                    'descripcion' => 'Ajuste de stock desde la edición del producto',
                ]);
            }
        });

        return back()->with('success', 'Producto actualizado');
    }

    public function destroy(Producto $producto)
    {
        $this->authorizeCompanyAdmin();
        $this->assertProductBelongsToActor($producto);
        $producto->update(['estado' => 'inactivo']);

        return back()->with('success', 'Producto desactivado; se conservó su historial.');
    }

    private function validatedProductData(Request $request, int $empresaId, ?Producto $producto = null): array
    {
        $codigoUnique = Rule::unique('productos', 'codigo')->where('empresa_id', $empresaId);
        if ($producto) {
            $codigoUnique->ignore($producto->id);
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255', $codigoUnique],
            'categoria_id' => [
                'nullable',
                'integer',
                Rule::exists('categorias', 'id')->where('empresa_id', $empresaId),
            ],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'iva' => ['required', 'numeric', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
        ]);

        if (($data['codigo'] ?? null) === '') {
            $data['codigo'] = null;
        }

        return $data;
    }

    private function empresaId(): int
    {
        $user = auth()->user();
        abort_unless(
            $user?->activo
                && $user->empresa_id
                && in_array($user->role, [\App\Models\User::ROLE_COMPANY_ADMIN, \App\Models\User::ROLE_EMPLOYEE], true),
            403,
            'Esta operación requiere un usuario operativo asociado a una empresa.'
        );

        return (int) $user->empresa_id;
    }

    private function authorizeCompanyAdmin(): void
    {
        abort_unless(
            auth()->user()?->activo
                && auth()->user()?->role === \App\Models\User::ROLE_COMPANY_ADMIN,
            403
        );
    }

    private function assertProductBelongsToActor(Producto $producto): void
    {
        abort_unless((int) $producto->empresa_id === $this->empresaId(), 404);
    }
}
