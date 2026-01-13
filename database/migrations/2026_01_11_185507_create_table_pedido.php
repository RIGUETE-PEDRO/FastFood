<?php

use App\Models\Carrinho;
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
            $table->foreignId('status')->constrained('status')->default(1);
            $table->foreignId('tipo_pagamento_id')->constrained('tipo_pagamento');
            $table->foreignId('endereco_id')->constrained('endereco');
            $table->string('observacoes_pagamento')->nullable();
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
