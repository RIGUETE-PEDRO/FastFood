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

            return;
        }

        if (!Schema::hasTable('SecureKey_auditoria')) {
            Schema::create('SecureKey_auditoria', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('acao', 50)->index();
                $table->string('recurso', 100)->index();
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
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('SecureKey_auditoria') && !Schema::hasTable('key_clock_auditoria')) {
            Schema::rename('SecureKey_auditoria', 'key_clock_auditoria');
        }
    }
};
