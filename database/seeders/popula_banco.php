<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class popula_banco extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_pagamento')->insert([
            [
                'tipo_pagamento' => 'Cartão de Crédito',
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'tipo_pagamento' => 'Cartão de Débito',
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'tipo_pagamento' => 'Pix',
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'tipo_pagamento' => 'Dinheiro',
                'created_at' => now(),
                'updated_at' => now(),
                
            ],
        ]);

        DB::table('usuarios')->insert([
            [
                'nome' => 'Administrador',
                'email' => 'admin@gmail.com',
                'senha' => bcrypt('admin'),
                'telefone' => '1234567890',
                'tipo_usuario_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'nome' => 'Cliente',
                'email' => 'cliente@gmail.com',
                'senha' => bcrypt('cliente'),
                'telefone' => '0987654321',
                'tipo_usuario_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),

            ]
        ]);

        DB::table('produtos')->insert([
            [
                'nome' => 'x-egg',
                'descricao' => 'Descrição do Produto Exemplo 1',
                'preco' => 19.99,
                'categoria_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'disponivel' => true,
                'imagem_url' => 'produto_695a88a9ebbfa.png',
            ],
            [
                'nome' => 'x-burger',
                'descricao' => 'Descrição do Produto Exemplo 2',
                'preco' => 29.99,
                'categoria_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'disponivel' => true,
                'imagem_url' => 'produto_695a88a9ebbfa.png',
            ],
            [
                'nome' => 'salada',
                'descricao' => 'Descrição do Produto Exemplo 3',
                'preco' => 39.99,
                'categoria_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
                'disponivel' => true,
                'imagem_url' => 'personPadrao.svg',
            ],
        ]);

        DB::table('status')->insert([
            [
                'status' => 'Pendente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Em Preparação',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Em Trânsito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Entregue',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status' => 'Recusado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
