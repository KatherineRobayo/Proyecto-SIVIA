<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'cliente_id',
        'numero_pedido',
        'estado',
        'descripcion',
        'total',
        'metodo_pago',
        'fecha_entrega_esperada',
        'fecha_entrega_real',
        'stock_reserved',
        'smart_order_token',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha_entrega_esperada' => 'datetime',
        'fecha_entrega_real' => 'datetime',
        'stock_reserved' => 'boolean',
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

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Relación con detalles del pedido
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function venta(): HasOne
    {
        return $this->hasOne(Venta::class);
    }

    /**
     * Generar número único de pedido
     */
    public static function generarNumeroPedido(?int $empresaId = null): string
    {
        if ($empresaId !== null) {
            Empresa::query()->whereKey($empresaId)->lockForUpdate()->firstOrFail();
        }

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

    public function scopeForEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }
}
