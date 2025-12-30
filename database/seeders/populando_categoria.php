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
        // Inserir dados na tabela categoria
        DB::table('categoria_produto')->insert([
            ['nome' => 'Lanches', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Pizzas', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Porções', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Bebidas', 'created_at' => now(), 'updated_at' => now()],
        ]);


    }
}
