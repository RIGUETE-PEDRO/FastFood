<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;


class popula_status extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      DB::table('status_pedido')->insert([
        [
          'descricao' => 'Pendente',
          'created_at' => now(),
          'updated_at' => now(),
      ],[
          'descricao' => 'Em Preparação',
          'created_at' => now(),
          'updated_at' => now(),
      ],[
          'descricao' => 'Em Trânsito',
          'created_at' => now(),
          'updated_at' => now(),
      ],[
          'descricao' => 'Entregue',
          'created_at' => now(),
          'updated_at' => now(),
      ],[
          'descricao' => 'Cancelado',
          'created_at' => now(),
          'updated_at' => now(),
      ]
      ]);
    }
}
