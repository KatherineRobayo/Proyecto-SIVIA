<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pedidos', 'stock_reserved')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->boolean('stock_reserved')->default(false);
            });
        }

        if (! Schema::hasColumn('ventas', 'pedido_id')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->unsignedBigInteger('pedido_id')->nullable();
            });
        }

        if (! Schema::hasIndex('ventas', ['empresa_id', 'pedido_id'], 'unique')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->unique(['empresa_id', 'pedido_id'], 'ventas_empresa_pedido_unique');
            });
        }

        if (! collect(Schema::getForeignKeys('ventas'))->contains(
            fn (array $foreignKey): bool => $foreignKey['columns'] === ['pedido_id', 'empresa_id']
        )) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->foreign(['pedido_id', 'empresa_id'], 'ventas_pedido_empresa_foreign')
                    ->references(['id', 'empresa_id'])
                    ->on('pedidos')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ventas', 'pedido_id')
            && DB::table('ventas')->whereNotNull('pedido_id')->exists()) {
            throw new RuntimeException('No se puede retirar el vínculo porque ya hay ventas asociadas a pedidos.');
        }

        if (Schema::hasColumn('pedidos', 'stock_reserved')
            && DB::table('pedidos')->where('stock_reserved', true)->exists()) {
            throw new RuntimeException('No se puede retirar stock_reserved mientras existan pedidos con reservas.');
        }

        if (Schema::hasColumn('ventas', 'pedido_id')) {
            foreach (Schema::getForeignKeys('ventas') as $foreignKey) {
                if ($foreignKey['columns'] === ['pedido_id', 'empresa_id']) {
                    Schema::table('ventas', fn (Blueprint $table) => $table->dropForeign($foreignKey['name']));
                }
            }

            foreach (Schema::getIndexes('ventas') as $index) {
                if ($index['unique'] && $index['columns'] === ['empresa_id', 'pedido_id']) {
                    Schema::table('ventas', fn (Blueprint $table) => $table->dropUnique($index['name']));
                }
            }

            Schema::table('ventas', fn (Blueprint $table) => $table->dropColumn('pedido_id'));
        }

        if (Schema::hasColumn('pedidos', 'stock_reserved')) {
            Schema::table('pedidos', fn (Blueprint $table) => $table->dropColumn('stock_reserved'));
        }
    }
};
