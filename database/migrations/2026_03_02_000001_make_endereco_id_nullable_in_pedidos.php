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
        Schema::table('pedidos', function (Blueprint $table) {
            // Para permitir retirada na mesa sem endereço.
            // Recria FK para aceitar NULL e usar nullOnDelete.
            $table->dropForeign(['endereco_id']);
            $table->unsignedBigInteger('endereco_id')->nullable()->change();
            $table->foreign('endereco_id')->references('id')->on('endereco')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['endereco_id']);
            $table->unsignedBigInteger('endereco_id')->nullable(false)->change();
            $table->foreign('endereco_id')->references('id')->on('endereco');
        });
    }
};
