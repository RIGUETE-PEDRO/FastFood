<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            if (Schema::hasColumn('item_pedido', 'usuario_id')) {
                // tentar dropar foreign key segura
                try {
                    $table->dropForeign(['usuario_id']);
                } catch (\Throwable $e) {
                    // ignore se não existir
                }
                $table->dropColumn('usuario_id');
            }

            if (Schema::hasColumn('item_pedido', 'mesa_id')) {
                try {
                    $table->dropForeign(['mesa_id']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('mesa_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('mesa_id')->nullable();
            // notar: recriar chaves estrangeiras manualmente se necessário
        });
    }
};
