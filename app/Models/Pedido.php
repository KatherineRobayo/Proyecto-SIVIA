<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'numero_pedido',
        'estado',
        'descripcion',
        'total',
        'metodo_pago',
        'fecha_entrega_esperada',
        'fecha_entrega_real',
    ];

    protected $casts = [
        'fecha_entrega_esperada' => 'datetime',
        'fecha_entrega_real' => 'datetime',
    ];

    /**
     * Relación con la empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con detalles del pedido
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    /**
     * Generar número único de pedido
     */
    public static function generarNumeroPedido(): string
    {
        $ultima = static::orderBy('id', 'desc')->first();
        $numero = ($ultima ? $ultima->id : 0) + 1;
        return 'PED-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular total del pedido
     */
    public function calcularTotal(): float
    {
        return $this->detalles()->sum('subtotal');
    }
}
