<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('key_clock_auditoria') && !Schema::hasTable('SecureKey_auditoria')) {
            Schema::rename('key_clock_auditoria', 'SecureKey_auditoria');
        }

        if (!Schema::hasTable('SecureKey_auditoria')) {
            Schema::create('SecureKey_auditoria', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('acao', 50);
                $table->string('recurso', 100);
                $table->ipAddress('ip')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('detalhes')->nullable();
                $table->timestamps();

                $table->index(['usuario_id', 'created_at']);
                $table->index(['acao', 'created_at']);
                $table->index(['recurso', 'created_at']);

                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('usuarios')
                    ->nullOnDelete();
            });

            return;
        }

        Schema::table('SecureKey_auditoria', function (Blueprint $table) {
            if (!Schema::hasColumn('SecureKey_auditoria', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('SecureKey_auditoria', 'acao')) {
                $table->string('acao', 50)->after('usuario_id');
            }

            if (!Schema::hasColumn('SecureKey_auditoria', 'recurso')) {
                $table->string('recurso', 100)->after('acao');
            }

            if (!Schema::hasColumn('SecureKey_auditoria', 'ip')) {
                $table->ipAddress('ip')->nullable()->after('recurso');
            }

            if (!Schema::hasColumn('SecureKey_auditoria', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip');
            }

            if (!Schema::hasColumn('SecureKey_auditoria', 'detalhes')) {
                $table->json('detalhes')->nullable()->after('user_agent');
            }

            if (!Schema::hasColumn('SecureKey_auditoria', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // Nao remove a tabela para preservar o historico de auditoria.
    }
};
