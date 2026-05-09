<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Create_roles extends Seeder
{

    public function run(): void
    {
        DB::table('roles')->insert([
                [
                    'nome' => 'admin',
                    'created_at' => now()],
                [
                    'nome' => 'user',
                    'created_at' => now()
                ],
                [
                    'nome' => 'keyclock',
                    'created_at' => now()
                ],
                [
                    'nome' => 'dashbord',
                    'created_at' => now()
                ],
                [
                    'nome' => 'pedidos',
                    'created_at' => now()
                ],
                [
                    'nome' => 'gerenciamento_funcionarios',
                    'created_at' => now()
                ],
                [
                    'nome' => 'gerenciamento_produtos',
                    'created_at' => now()
                ],
                [
                    'nome' => 'cardapio',
                    'created_at' => now()
                ],
                [
                    'nome' => 'entregas',
                    'created_at' => now()
                ],
                [
                    'nome' => 'mesas',
                    'created_at' => now()
                ],
                [
                    'nome' => 'garcom',
                    'created_at' => now()
                ]
            ]


        );
    }
}



