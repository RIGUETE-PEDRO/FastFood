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
        Schema::table('item_pedido', function (Blueprint $table) {
            $table->foreignId('mesa_id')
                ->nullable()
                ->after('usuario_id')
                ->constrained('mesas')
                ->nullOnDelete();

            $table->index(['mesa_id', 'status_da_comanda']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            $table->dropIndex(['mesa_id', 'status_da_comanda']);
            $table->dropConstrainedForeignId('mesa_id');
        });
    }
};
