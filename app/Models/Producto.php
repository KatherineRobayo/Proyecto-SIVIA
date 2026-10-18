<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'codigo',
        'categoria',
        'precio_compra',
        'precio_venta',
        'iva',
        'stock',
        'stock_minimo',
        'descripcion',
        'imagen',
        'estado'
    ];
}