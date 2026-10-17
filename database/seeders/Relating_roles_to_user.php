<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Relating_roles_to_user extends Seeder
{
    public function run(): void
    {
        $rolesPorGrupo = [
            'SecureKey' => ['SecureKey'],
            'Estabelecimento' => [
                'pedidos_cancelar',
            ],
            'Administrador' => [
                'admin',
                'dashbord',
                'pedidos',
                'gerenciamento_funcionarios',
                'gerenciamento_produtos',
                'gerenciamento_bordas',
                'pedido_whatsapp',
                'entregas',
                'mesas',
                'garcom',
            ],
            'Entregador' => [
                'entregas',
                'pedidos_visualizar',
            ],
            'garçom' => [
                'garcom',
                'mesas',
                'pedidos_visualizar',
            ],
        ];

        foreach ($rolesPorGrupo as $descricaoGrupo => $nomesRoles) {
            $grupoId = DB::table('tipo_usuarios')
                ->where('descricao', $descricaoGrupo)
                ->value('id');

            if (!$grupoId) {
                continue;
            }

            $roles = DB::table('roles')
                ->whereIn('nome', $nomesRoles)
                ->pluck('id');

            foreach ($roles as $roleId) {
                DB::table('SecureKey_tipo_usuario')->updateOrInsert(
                    [
                        'tipo_usuario_id' => $grupoId,
                        'role_id' => $roleId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
