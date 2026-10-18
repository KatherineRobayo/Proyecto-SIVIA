<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::latest()->get();
        return view('dashboard.productos.index', compact('productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','min:3','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]+$/'],
            'codigo' => ['nullable','string','max:50'],
            'categoria' => ['required'],
            'precio_compra' => ['required','numeric','min:0'],
            'precio_venta' => ['required','numeric','min:0'],
            'iva' => ['required','numeric','min:0','max:100'],
            'stock' => ['required','integer','min:0'],
            'stock_minimo' => ['required','integer','min:0'],
            'descripcion' => ['nullable','max:255'],
            'imagen' => ['nullable','image','mimes:jpg,png,jpeg','max:2048']
        ]);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto = Producto::create($data);

        Movimiento::create([
            'producto_id' => $producto->id,
            'user_id' => auth()->id(),
            'tipo' => 'entrada',
            'cantidad' => $producto->stock,
            'descripcion' => 'Producto creado'
        ]);

        return back()->with('success', 'Producto creado');
    }

    public function update(Request $request, Producto $producto)
    {
        $stockAnterior = $producto->stock;

        $data = $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'nullable|string',
            'categoria' => 'nullable|string',
            'precio_compra' => 'required|numeric',
            'precio_venta' => 'required|numeric',
            'iva' => 'required|numeric',
            'stock' => 'required|integer',
            'stock_minimo' => 'required|integer',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image'
        ]);

        $producto->update($data);

        if ($data['stock'] > $stockAnterior) {

            Movimiento::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => 'entrada',
                'cantidad' => $data['stock'] - $stockAnterior,
                'descripcion' => 'Stock aumentado desde productos'
            ]);

        } elseif ($data['stock'] < $stockAnterior) {

            Movimiento::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => 'salida',
                'cantidad' => $stockAnterior - $data['stock'],
                'descripcion' => 'Stock reducido desde productos'
            ]);

        } else {

            Movimiento::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => 'entrada',
                'cantidad' => 0,
                'descripcion' => 'Producto actualizado'
            ]);
        }

        return back()->with('success', 'Producto actualizado');
    }

    public function destroy(Producto $producto)
    {
        Movimiento::create([
            'producto_id' => $producto->id,
            'user_id' => auth()->id(),
            'tipo' => 'salida',
            'cantidad' => $producto->stock,
            'descripcion' => 'Producto eliminado'
        ]);

        $producto->delete();

        return back()->with('success', 'Producto eliminado');
    }
}
