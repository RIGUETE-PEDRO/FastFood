<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\table;

class Criando_dados_empresa extends Seeder
{
    public function run(): void
    {
        DB::table('dados_empresa')->insert([
            ['Informacao' => 'Nome da Empresa', 'Valor' => null],
            ['Informacao' => 'Rua', 'Valor' => null],
            ['Informacao' => 'Telefone', 'Valor' => null],
            ['Informacao' => 'Email', 'Valor' => null],
            ['Informacao' => 'CNPJ', 'Valor' => null],
            ['Informacao' => 'Horário de Funcionamento', 'Valor' => null],
            ['Informacao' => 'Redes Sociais', 'Valor' => null],
            ['Informacao' => 'Numero', 'Valor' => null],
            ['Informacao' => 'CEP', 'Valor' => null],
            ['Informacao' => 'Bairro', 'Valor' => null],
            ['Informacao' => 'Cidade', 'Valor' => null],
            ['Informacao' => 'Estado', 'Valor' => null],
            ['Informacao' => 'Msg_comanda', 'Valor' => null],

        ]);
    }
}
