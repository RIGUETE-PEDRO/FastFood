<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesa_fechamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->integer('numero_da_mesa');
            $table->decimal('total_pago', 10, 2)->default(0.00);
            $table->unsignedInteger('total_itens')->default(0);
            $table->json('formas_pagamento')->nullable();
            $table->json('pagamentos_resumo')->nullable();
            $table->timestamp('fechado_em')->nullable();
            $table->timestamps();
            $table->index('fechado_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesa_fechamentos');
    }
};
