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
            if (!Schema::hasColumn('pedidos', 'mesa_id')) {
                $table->unsignedBigInteger('mesa_id')->nullable()->after('endereco_id');
                $table->foreign('mesa_id')
                    ->references('id')
                    ->on('mesas')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'mesa_id')) {
                $table->dropForeign(['mesa_id']);
                $table->dropColumn('mesa_id');
            }
        });
    }
};
