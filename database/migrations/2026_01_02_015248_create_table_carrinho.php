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
        Schema::create('carrinho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->integer('quantidade');
            $table->text('observacao')->nullable();
            $table->decimal('preco_total', 8, 2);
            $table->timestamps();
            $table->boolean('selecionado')->default(false);
            $table->boolean("deleted")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrinho');
    }
};
