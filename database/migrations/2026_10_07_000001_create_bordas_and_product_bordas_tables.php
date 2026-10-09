<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bordas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100)->unique();
            $table->json('adicionais_por_tamanho')->nullable();
            $table->boolean('ativa')->default(true);
            $table->boolean('padrao')->default(false);
            $table->timestamps();
        });

        Schema::create('produto_borda', function (Blueprint $table) {
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->foreignId('borda_id')->constrained('bordas')->cascadeOnDelete();
            $table->primary(['produto_id', 'borda_id']);
        });

        $now = now();
        $defaultId = DB::table('bordas')->insertGetId([
            'nome' => 'Borda tradicional',
            'adicionais_por_tamanho' => json_encode(['P' => 7, 'M' => 7, 'G' => 7, 'GG' => 7, 'XG' => 13]),
            'ativa' => true,
            'padrao' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $pizzaCategoryIds = DB::table('categoria_produto')->get(['id', 'nome'])
            ->filter(fn ($categoria) => in_array(mb_strtolower(Str::ascii(trim((string) $categoria->nome))), ['pizza', 'pizzas'], true))
            ->pluck('id');

        if ($pizzaCategoryIds->isNotEmpty()) {
            $productIds = DB::table('produtos')->whereIn('categoria_id', $pizzaCategoryIds)->pluck('id');
            foreach ($productIds as $productId) {
                DB::table('produto_borda')->insert([
                    'produto_id' => $productId,
                    'borda_id' => $defaultId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_borda');
        Schema::dropIfExists('bordas');
    }
};
