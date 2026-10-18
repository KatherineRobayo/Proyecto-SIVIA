<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 
    public function up(): void
{
    Schema::table('productos', function (Blueprint $table) {
        $table->decimal('precio_compra',10,2)->default(0)->after('categoria');
        $table->decimal('precio_venta',10,2)->default(0)->after('precio_compra');
        $table->dropColumn('precio');
    });
}

public function down(): void
{
    Schema::table('productos', function (Blueprint $table) {
        $table->decimal('precio',10,2)->default(0);
        $table->dropColumn(['precio_compra','precio_venta']);
    });
}
};
