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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('numero_pedido')->unique();
            $table->string('estado')->default('pendiente'); // pendiente, confirmado, enviado, entregado, cancelado
            $table->text('descripcion')->nullable();
            
            $table->decimal('total', 10, 2)->default(0);
            $table->string('metodo_pago')->default('pendiente');
            
            $table->dateTime('fecha_entrega_esperada')->nullable();
            $table->dateTime('fecha_entrega_real')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
