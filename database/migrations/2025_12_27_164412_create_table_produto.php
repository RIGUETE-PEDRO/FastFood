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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('categoria_id')->constrained('categoria_produto');
            $table->decimal('preco', 10, 2);
            $table->text('descricao')->nullable();
            $table->string('imagem_url')->nullable();
            $table->string('ingredientes')->nullable();
            $table->boolean('disponivel')->default(true);
            $table->timestamps();
            $table->boolean("deleted")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_produto');
    }
};
