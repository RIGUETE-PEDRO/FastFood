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
        Schema::table('sessions', function (Blueprint $table) {
            // Verificar se a FK já não existe antes de criar
            if (!Schema::hasColumn('sessions', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            }

            // Tentar adicionar FK de forma segura
            try {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('usuarios')
                    ->onDelete('set null');
            } catch (\Throwable $e) {
                // Se já existir, ignora
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'user_id')) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                    // ignore se não existir
                }
            }
        });
    }
};
