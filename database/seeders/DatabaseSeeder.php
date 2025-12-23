<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Inserir tipos de usuário padrão
        DB::table('tipo_usuarios')->insert([
            ['descricao' => 'Cliente', 'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'Estabelecimento', 'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'Entregador', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
