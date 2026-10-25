<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedidos') && ! Schema::hasColumn('pedidos', 'smart_order_token')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->uuid('smart_order_token')->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'smart_order_token')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->dropUnique(['smart_order_token']);
                $table->dropColumn('smart_order_token');
            });
        }
    }
};
