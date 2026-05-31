<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesa_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->foreignId('mesa_fechamento_id')->nullable()->constrained('mesa_fechamentos')->nullOnDelete();
            $table->string('pagamento_metodo');
            $table->decimal('valor', 10, 2)->default(0.00);
            $table->timestamp('pago_em')->nullable();
            $table->timestamps();

            $table->index(['mesa_id', 'mesa_fechamento_id']);
            $table->index('pago_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesa_pagamentos');
    }
};
