<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar coluna no_carrousel na tabela produtos se não existir
        if (!Schema::hasColumn('produtos', 'no_carrousel')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->tinyInteger('no_carrousel')->default(0)->after('disponivel')->nullable(false);
            });
        }
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            if (Schema::hasColumn('produtos', 'no_carrousel')) {
                $table->dropColumn('no_carrousel');
            }
        });
    }
};

