<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Categoria; // <-- Importamos el modelo Categoria
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        // BUSCADOR (Corregido: categoria -> categoria_id)
        if ($request->search) {
            $query->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('codigo', 'like', '%' . $request->search . '%')
                  ->orWhere('categoria_id', 'like', '%' . $request->search . '%');
        }

        // FILTRO CATEGORIA (Corregido: categoria -> categoria_id)
        if ($request->categoria && $request->categoria != 'todos') {
            $query->where('categoria_id', $request->categoria);
        }

        $productos = $query->latest()->paginate(8);

        // KPIs
        $totalProductos = Producto::count();
        $alertasStock = Producto::whereColumn('stock', '<=', 'stock_minimo')->count();
        $valorInventario = Producto::selectRaw('SUM(precio_compra * stock) as total')->value('total');
        
        // CORREGIDO: Traemos los nombres de la tabla categorias, no de productos
        $categorias = Categoria::pluck('nombre', 'id'); 

        return view('dashboard.inventario.index', compact(
            'productos',
            'totalProductos',
            'alertasStock',
            'valorInventario',
            'categorias'
        ));
    }

    public function ajustarStock(Request $request)
    {
        $data = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo' => 'required|in:sumar,restar',
            'cantidad' => 'required|numeric|min:1'
        ]);

        $producto = Producto::findOrFail($data['producto_id']);

        if ($data['tipo'] === 'sumar') {
            $producto->stock += $data['cantidad'];
            $tipoMovimiento = 'entrada';
        } else {
            if ($producto->stock < $data['cantidad']) {
                return back()->withErrors([
                    'cantidad' => 'No puedes dejar el stock en negativo'
                ]);
            }
            $producto->stock -= $data['cantidad'];
            $tipoMovimiento = 'salida';
        }

        $producto->save();

        Movimiento::create([
            'producto_id' => $producto->id,
            'user_id' => auth()->id(),
            'tipo' => $tipoMovimiento,
            'cantidad' => $data['cantidad'],
            'descripcion' => 'Ajuste manual de inventario'
        ]);

        return back()->with('success', 'Stock actualizado correctamente');
    }
}
