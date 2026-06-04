<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesa_fechamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('mesa_fechamentos', 'produtos_resumo')) {
                $table->json('produtos_resumo')->nullable()->after('pagamentos_resumo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mesa_fechamentos', function (Blueprint $table) {
            if (Schema::hasColumn('mesa_fechamentos', 'produtos_resumo')) {
                $table->dropColumn('produtos_resumo');
            }
        });
    }
};
