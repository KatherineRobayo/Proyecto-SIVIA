<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedidos') && ! Schema::hasColumn('pedidos', 'pagado')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->boolean('pagado')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'pagado')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->dropColumn('pagado');
            });
        }
    }
};
