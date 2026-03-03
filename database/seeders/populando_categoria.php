<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class populando_categoria extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorias (idempotente)
        $categorias = ['Lanches', 'Pizzas', 'Porções', 'Bebidas'];
        foreach ($categorias as $nome) {
            DB::table('categoria_produto')->updateOrInsert(
                ['nome' => $nome],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }


    }
}
