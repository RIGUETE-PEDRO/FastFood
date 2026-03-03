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

        // Tipos de usuário (idempotente: não duplica por causa do UNIQUE)
        $tipos = ['Cliente', 'Estabelecimento', 'Administrador', 'Entregador'];
        foreach ($tipos as $descricao) {
            DB::table('tipo_usuarios')->updateOrInsert(
                ['descricao' => $descricao],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->call(populando_categoria::class);

        $this->call(popular_municipios::class);
        $this->call(popula_banco::class);
    }
}

