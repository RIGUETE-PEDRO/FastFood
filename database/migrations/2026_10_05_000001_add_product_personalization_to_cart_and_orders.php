<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->json('precos_por_tamanho')->nullable();
            $table->boolean('aceita_meia_por')->default(false);
            $table->decimal('preco_meia_por', 10, 2)->nullable();
        });

        Schema::table('carrinho', function (Blueprint $table) {
            $table->json('personalizacao')->nullable();
        });

        Schema::table('item_pedido', function (Blueprint $table) {
            $table->json('personalizacao')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            $table->dropColumn('personalizacao');
        });

        Schema::table('carrinho', function (Blueprint $table) {
            $table->dropColumn('personalizacao');
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['precos_por_tamanho', 'aceita_meia_por', 'preco_meia_por']);
        });
    }
};
