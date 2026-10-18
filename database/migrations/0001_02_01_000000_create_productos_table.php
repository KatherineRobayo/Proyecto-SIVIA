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
    Schema::create('productos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('empresa_id')->constrained();
        $table->foreignId('categoria_id')->nullable()->constrained();

        $table->string('codigo')->nullable();
        $table->string('nombre');

        $table->enum('tipo', ['simple','compuesto'])->default('simple');

        $table->integer('stock')->default(0);
        $table->integer('stock_minimo')->default(5);

        $table->decimal('precio_compra',10,2)->default(0);
        $table->decimal('precio_venta',10,2);

        $table->string('imagen')->nullable();

        $table->timestamps();
    });
}
   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
