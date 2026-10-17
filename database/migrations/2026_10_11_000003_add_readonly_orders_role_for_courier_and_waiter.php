<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const VIEW_ROLE = 'pedidos_visualizar';
    private const GROUP_NAMES = ['Entregador', 'garçom'];

    public function up(): void
    {
        $now = now();

        DB::table('roles')->insertOrIgnore([
            'nome' => self::VIEW_ROLE,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $groupIds = DB::table('tipo_usuarios')
            ->whereIn('descricao', self::GROUP_NAMES)
            ->pluck('id');

        if ($groupIds->isEmpty()) {
            return;
        }

        $pedidosRoleId = DB::table('roles')->where('nome', 'pedidos')->value('id');
        if ($pedidosRoleId) {
            DB::table('SecureKey_tipo_usuario')
                ->whereIn('tipo_usuario_id', $groupIds)
                ->where('role_id', $pedidosRoleId)
                ->delete();
        }

        $viewRoleId = DB::table('roles')->where('nome', self::VIEW_ROLE)->value('id');

        foreach ($groupIds as $groupId) {
            DB::table('SecureKey_tipo_usuario')->updateOrInsert(
                [
                    'tipo_usuario_id' => $groupId,
                    'role_id' => $viewRoleId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        $viewRoleId = DB::table('roles')->where('nome', self::VIEW_ROLE)->value('id');

        if ($viewRoleId) {
            DB::table('SecureKey_tipo_usuario')->where('role_id', $viewRoleId)->delete();
            DB::table('roles')->where('id', $viewRoleId)->delete();
        }

        $garcomId = DB::table('tipo_usuarios')
            ->where('descricao', 'garçom')
            ->value('id');
        $pedidosRoleId = DB::table('roles')->where('nome', 'pedidos')->value('id');

        if ($garcomId && $pedidosRoleId) {
            DB::table('SecureKey_tipo_usuario')->updateOrInsert(
                [
                    'tipo_usuario_id' => $garcomId,
                    'role_id' => $pedidosRoleId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
};
