<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('item_pedido', 'valor_pago')) {
                $table->decimal('valor_pago', 8, 2)->default(0.00)->after('preco_unitario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            if (Schema::hasColumn('item_pedido', 'valor_pago')) {
                $table->dropColumn('valor_pago');
            }
        });
    }
};
