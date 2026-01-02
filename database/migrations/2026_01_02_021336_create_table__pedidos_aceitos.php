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
        Schema::create('pedidos_aceitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedido');
            $table->foreignId('endereco_id')->constrained('endereco');
            $table->foreignId('status_id')->constrained('status_pedido');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_aceitos');
    }
};
