<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Relating_roles_to_user extends Seeder
{

    public function run(): void
    {
        DB::table('SecureKey_tipo_usuario')->insert([
            [
                'created_at' => now(),
                'tipo_usuario_id' => 5,
                'role_id' => 3
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 1
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 4
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 5
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 6
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 7
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 8
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 9
            ],
            [
                'created_at' => now(),
                'tipo_usuario_id' => 3,
                'role_id' => 10
            ]
            ]);
    }
}
