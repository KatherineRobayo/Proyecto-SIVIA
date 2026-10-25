<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->validateExistingData();
        $this->ensureCompatibilityColumns();

        $this->dropForeignKeys('users', ['empresa_id']);
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true);
            }
            $table->foreign('empresa_id')->references('id')->on('empresas')->restrictOnDelete();
            $table->string('role')->default('cliente')->change();
        });

        Schema::table('productos', function (Blueprint $table) {
            if (! Schema::hasColumn('productos', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }
            if (! Schema::hasColumn('productos', 'estado')) {
                $table->string('estado')->default('activo');
            }
        });

        if (! Schema::hasIndex('categorias', ['id', 'empresa_id'], 'unique')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->unique(['id', 'empresa_id'], 'categorias_id_empresa_unique');
            });
        }

        $this->dropForeignKeys('productos', ['categoria_id']);
        $this->dropUniqueIndexes('productos', ['codigo']);
        Schema::table('productos', function (Blueprint $table) {
            if (! Schema::hasIndex('productos', ['id', 'empresa_id'], 'unique')) {
                $table->unique(['id', 'empresa_id'], 'productos_id_empresa_unique');
            }
            if (! Schema::hasIndex('productos', ['empresa_id', 'codigo'], 'unique')) {
                $table->unique(['empresa_id', 'codigo'], 'productos_empresa_codigo_unique');
            }
            if (! $this->hasForeignKey('productos', ['categoria_id', 'empresa_id'])) {
                $table->foreign(['categoria_id', 'empresa_id'], 'productos_categoria_empresa_foreign')
                    ->references(['id', 'empresa_id'])
                    ->on('categorias')
                    ->restrictOnDelete();
            }
        });

        $this->dropForeignKeys('ventas', ['user_id']);
        Schema::table('ventas', function (Blueprint $table) {
            if (! Schema::hasColumn('ventas', 'cliente_id')) {
                $table->foreignId('cliente_id')->nullable();
            }
            if (Schema::hasColumn('ventas', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasIndex('ventas', ['id', 'empresa_id'], 'unique')) {
                $table->unique(['id', 'empresa_id'], 'ventas_id_empresa_unique');
            }
        });
        if (! $this->hasForeignKey('ventas', ['cliente_id'])) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->foreign('cliente_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        $this->dropUniqueIndexes('pedidos', ['numero_pedido']);
        $this->dropForeignKeys('pedidos', ['empresa_id']);
        $this->dropForeignKeys('pedidos', ['user_id']);
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'cliente_id')) {
                $table->foreignId('cliente_id')->nullable();
            }
            if (! Schema::hasIndex('pedidos', ['numero_pedido', 'empresa_id'], 'unique')) {
                $table->unique(['numero_pedido', 'empresa_id'], 'pedidos_numero_empresa_unique');
            }
            if (! Schema::hasIndex('pedidos', ['id', 'empresa_id'], 'unique')) {
                $table->unique(['id', 'empresa_id'], 'pedidos_id_empresa_unique');
            }
            if (! $this->hasForeignKey('pedidos', ['empresa_id'])) {
                $table->foreign('empresa_id')->references('id')->on('empresas')->restrictOnDelete();
            }
            $table->unsignedBigInteger('user_id')->nullable()->change();
            if (! $this->hasForeignKey('pedidos', ['user_id'])) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            }
        });
        if (! $this->hasForeignKey('pedidos', ['cliente_id'])) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->foreign('cliente_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        $this->dropForeignKeys('movimientos', ['producto_id']);
        $this->dropForeignKeys('movimientos', ['user_id']);
        Schema::table('movimientos', function (Blueprint $table) {
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            if (! $this->hasForeignKey('movimientos', ['user_id'])) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        $this->addTenantToDetails('detalle_ventas', 'venta_id', 'ventas');
        $this->addTenantToDetails('detalle_pedidos', 'pedido_id', 'pedidos');
        $this->replaceDetailProductForeignKey('detalle_ventas');
        $this->replaceDetailProductForeignKey('detalle_pedidos');

        $this->dropForeignKeys('detalle_ventas', ['venta_id']);
        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (! $this->hasForeignKey('detalle_ventas', ['venta_id', 'empresa_id'])) {
                $table->foreign(['venta_id', 'empresa_id'], 'detalle_ventas_venta_empresa_foreign')
                    ->references(['id', 'empresa_id'])
                    ->on('ventas')
                    ->cascadeOnDelete();
            }
        });

        $this->dropForeignKeys('detalle_pedidos', ['pedido_id']);
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            if (! $this->hasForeignKey('detalle_pedidos', ['pedido_id', 'empresa_id'])) {
                $table->foreign(['pedido_id', 'empresa_id'], 'detalle_pedidos_pedido_empresa_foreign')
                    ->references(['id', 'empresa_id'])
                    ->on('pedidos')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Esta migración de reconciliación no es reversible sin riesgo de pérdida de integridad.');
    }

    private function addTenantToDetails(string $tableName, string $parentKey, string $parentTable): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable();
            }
        });

        DB::table($tableName)
            ->join($parentTable, "{$parentTable}.id", '=', "{$tableName}.{$parentKey}")
            ->whereNull("{$tableName}.empresa_id")
            ->select("{$tableName}.id as id", "{$parentTable}.empresa_id as tenant_empresa_id")
            ->orderBy("{$tableName}.id")
            ->chunkById(500, function ($details) use ($tableName): void {
                foreach ($details as $detail) {
                    DB::table($tableName)
                        ->where('id', $detail->id)
                        ->update(['empresa_id' => $detail->tenant_empresa_id]);
                }
            }, "{$tableName}.id", 'id');

        $unresolvedRows = DB::table($tableName)->whereNull('empresa_id')->count();

        if ($unresolvedRows > 0) {
            throw new RuntimeException("No se pudo asignar empresa a {$unresolvedRows} registros de {$tableName}; revise relaciones antes de reintentar.");
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->unsignedBigInteger('empresa_id')->nullable(false)->change();
            if (! Schema::hasIndex($tableName, ['empresa_id'])) {
                $table->index('empresa_id', "{$tableName}_empresa_id_index");
            }
        });
    }

    private function replaceDetailProductForeignKey(string $tableName): void
    {
        $crossCompanyRows = DB::table($tableName)
            ->join('productos', 'productos.id', '=', "{$tableName}.producto_id")
            ->whereColumn("{$tableName}.empresa_id", '!=', 'productos.empresa_id')
            ->count();

        if ($crossCompanyRows > 0) {
            throw new RuntimeException("Se encontraron {$crossCompanyRows} detalles en {$tableName} asociados a productos de otra empresa; corrija la integridad antes de continuar.");
        }

        $this->dropForeignKeys($tableName, ['producto_id']);
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! $this->hasForeignKey($tableName, ['producto_id', 'empresa_id'])) {
                $table->foreign(['producto_id', 'empresa_id'], "{$tableName}_producto_empresa_foreign")
                    ->references(['id', 'empresa_id'])
                    ->on('productos')
                    ->restrictOnDelete();
            }
        });
    }

    private function dropForeignKeys(string $tableName, array $columns): void
    {
        $foreignKeys = array_filter(
            Schema::getForeignKeys($tableName),
            fn (array $foreignKey): bool => $foreignKey['columns'] === $columns
        );

        foreach ($foreignKeys as $foreignKey) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropForeign($foreignKey['name']));
        }
    }

    private function hasForeignKey(string $tableName, array $columns): bool
    {
        foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
            if ($foreignKey['columns'] === $columns) {
                return true;
            }
        }

        return false;
    }

    private function dropUniqueIndexes(string $tableName, array $columns): void
    {
        $indexes = array_filter(
            Schema::getIndexes($tableName),
            fn (array $index): bool => $index['unique'] && $index['columns'] === $columns
        );

        foreach ($indexes as $index) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropUnique($index['name']));
        }
    }

    private function validateExistingData(): void
    {
        if (Schema::hasColumn('users', 'empresa_id')) {
            $this->assertNoRows(
                DB::table('users')
                    ->leftJoin('empresas', 'empresas.id', '=', 'users.empresa_id')
                    ->whereNotNull('users.empresa_id')
                    ->whereNull('empresas.id'),
                'Hay usuarios asociados a empresas inexistentes.'
            );
        }

        if (! Schema::hasColumn('productos', 'empresa_id')) {
            throw new RuntimeException('La tabla productos no tiene empresa_id. Asigne cada producto a una empresa antes de migrar.');
        }
        $this->assertNoRows(
            DB::table('productos')
                ->leftJoin('empresas', 'empresas.id', '=', 'productos.empresa_id')
                ->whereNull('empresas.id'),
            'Hay productos sin una empresa válida.'
        );

        if (Schema::hasColumn('productos', 'categoria_id')) {
            $this->assertNoRows(
                DB::table('productos')
                    ->join('categorias', 'categorias.id', '=', 'productos.categoria_id')
                    ->whereNotNull('productos.categoria_id')
                    ->whereColumn('productos.empresa_id', '!=', 'categorias.empresa_id'),
                'Hay productos asignados a categorías de otra empresa.'
            );
        }

        if (Schema::hasColumn('productos', 'codigo')) {
            $duplicateCodes = DB::table('productos')
                ->select('empresa_id', 'codigo')
                ->whereNotNull('codigo')
                ->groupBy('empresa_id', 'codigo')
                ->havingRaw('COUNT(*) > 1');
            $this->assertNoRows($duplicateCodes, 'Hay códigos de producto repetidos dentro de una misma empresa.');
        }

        foreach ([
            ['detalle_ventas', 'venta_id', 'ventas'],
            ['detalle_pedidos', 'pedido_id', 'pedidos'],
        ] as [$details, $parentKey, $parent]) {
            $this->assertNoRows(
                DB::table($details)
                    ->leftJoin($parent, "{$parent}.id", '=', "{$details}.{$parentKey}")
                    ->whereNull("{$parent}.id"),
                "Hay detalles huérfanos en {$details}; no se puede derivar su empresa."
            );

            $this->assertNoRows(
                DB::table($details)
                    ->leftJoin('productos', 'productos.id', '=', "{$details}.producto_id")
                    ->whereNull('productos.id'),
                "Hay productos inexistentes referenciados por {$details}."
            );

            $this->assertNoRows(
                DB::table($details)
                    ->join($parent, "{$parent}.id", '=', "{$details}.{$parentKey}")
                    ->join('productos', 'productos.id', '=', "{$details}.producto_id")
                    ->whereColumn("{$parent}.empresa_id", '!=', 'productos.empresa_id'),
                "Hay detalles en {$details} asociados a productos de otra empresa."
            );

            if (Schema::hasColumn($details, 'empresa_id')) {
                $this->assertNoRows(
                    DB::table($details)
                        ->join($parent, "{$parent}.id", '=', "{$details}.{$parentKey}")
                        ->whereNotNull("{$details}.empresa_id")
                        ->whereColumn("{$details}.empresa_id", '!=', "{$parent}.empresa_id"),
                    "Hay detalles en {$details} con una empresa diferente a la de su cabecera."
                );
            }
        }
    }

    private function ensureCompatibilityColumns(): void
    {
        if (! Schema::hasColumn('users', 'empresa_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
            });
        }

        Schema::table('productos', function (Blueprint $table) {
            if (! Schema::hasColumn('productos', 'categoria_id')) {
                $table->unsignedBigInteger('categoria_id')->nullable();
            }
            if (! Schema::hasColumn('productos', 'iva')) {
                $table->decimal('iva', 5, 2)->default(0);
            }
        });
    }

    private function assertNoRows($query, string $message): void
    {
        $count = $query->count();

        if ($count > 0) {
            throw new RuntimeException("{$message} Registros afectados: {$count}.");
        }
    }
};
