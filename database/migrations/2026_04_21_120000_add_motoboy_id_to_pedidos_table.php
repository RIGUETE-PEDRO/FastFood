<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('motoboy_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->timestamp('motoboy_vinculado_em')
                ->nullable()
                ->after('motoboy_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('motoboy_id');
            $table->dropColumn('motoboy_vinculado_em');
        });
    }
};
