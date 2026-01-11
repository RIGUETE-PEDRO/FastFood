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
            $table->timestamps();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->decimal('valor_total', 8, 2);
            $table->string('status')->default('Pendente');
            $table->foreignId('tipo_pagamento_id')->constrained('tipo_pagamento');
            $table->foreignId('endereco_id')->constrained('endereco');
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
