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
        Schema::create('item_pedido', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->decimal('preco_unitario', 8, 2);
            $table->integer('quantidade');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->boolean("deleted")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pedido');
    }
};
