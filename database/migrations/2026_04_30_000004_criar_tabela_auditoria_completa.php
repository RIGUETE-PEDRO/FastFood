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
        // Verifica se a tabela já existe
        if (!Schema::hasTable('key_clock_auditoria')) {
            Schema::create('key_clock_auditoria', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('acao', 50)->index(); // criar, atualizar, excluir, login, logout, etc
                $table->string('recurso', 100)->index(); // pedido, usuario, produto, etc
                $table->ipAddress('ip')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('detalhes')->nullable();
                $table->timestamps();

                // Índices para busca rápida
                $table->index(['usuario_id', 'created_at']);
                $table->index(['acao', 'created_at']);
                $table->index(['recurso', 'created_at']);

                // Foreign key
                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('usuarios')
                    ->onDelete('set null');
            });
        } else {
            // Se a tabela já existe, adiciona colunas que possam estar faltando
            Schema::table('key_clock_auditoria', function (Blueprint $table) {
                if (!Schema::hasColumn('key_clock_auditoria', 'ip')) {
                    $table->ipAddress('ip')->nullable()->after('user_agent');
                }

                if (!Schema::hasColumn('key_clock_auditoria', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('recurso');
                }

                if (!Schema::hasColumn('key_clock_auditoria', 'detalhes')) {
                    $table->json('detalhes')->nullable()->after('user_agent');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_clock_auditoria');
    }
};
