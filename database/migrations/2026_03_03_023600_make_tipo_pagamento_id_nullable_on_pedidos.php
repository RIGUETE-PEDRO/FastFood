<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pedidos') || !Schema::hasColumn('pedidos', 'tipo_pagamento_id')) {
            return;
        }

        // Drop FK (name should be pedidos_tipo_pagamento_id_foreign in default Laravel naming)
        try {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropForeign(['tipo_pagamento_id']);
            });
        } catch (Throwable $e) {
            // If it was already dropped or named differently, continue.
        }

        // Make the column nullable without requiring doctrine/dbal.
        DB::statement('ALTER TABLE `pedidos` MODIFY `tipo_pagamento_id` BIGINT UNSIGNED NULL');

        // Recreate FK constraint with NULL on delete behavior.
        Schema::table('pedidos', function (Blueprint $table) {
            $table
                ->foreign('tipo_pagamento_id')
                ->references('id')
                ->on('tipo_pagamento')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('pedidos') || !Schema::hasColumn('pedidos', 'tipo_pagamento_id')) {
            return;
        }

        try {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropForeign(['tipo_pagamento_id']);
            });
        } catch (Throwable $e) {
            // ignore
        }

        DB::statement('ALTER TABLE `pedidos` MODIFY `tipo_pagamento_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('pedidos', function (Blueprint $table) {
            $table
                ->foreign('tipo_pagamento_id')
                ->references('id')
                ->on('tipo_pagamento');
        });
    }
};
