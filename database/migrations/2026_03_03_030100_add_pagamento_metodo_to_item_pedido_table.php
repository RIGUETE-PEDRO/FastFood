<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('item_pedido', 'pagamento_metodo')) {
                $table->string('pagamento_metodo')->nullable()->after('pago_em');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            if (Schema::hasColumn('item_pedido', 'pagamento_metodo')) {
                $table->dropColumn('pagamento_metodo');
            }
        });
    }
};
