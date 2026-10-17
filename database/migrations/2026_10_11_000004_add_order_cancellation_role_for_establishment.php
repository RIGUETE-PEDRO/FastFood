<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLE_NAME = 'pedidos_cancelar';
    private const GROUP_NAME = 'Estabelecimento';

    public function up(): void
    {
        $now = now();

        DB::table('roles')->insertOrIgnore([
            'nome' => self::ROLE_NAME,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $groupId = DB::table('tipo_usuarios')
            ->where('descricao', self::GROUP_NAME)
            ->value('id');
        $roleId = DB::table('roles')
            ->where('nome', self::ROLE_NAME)
            ->value('id');

        if (!$groupId || !$roleId) {
            return;
        }

        DB::table('SecureKey_tipo_usuario')->updateOrInsert(
            [
                'tipo_usuario_id' => $groupId,
                'role_id' => $roleId,
            ],
            [
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        $roleId = DB::table('roles')
            ->where('nome', self::ROLE_NAME)
            ->value('id');

        if (!$roleId) {
            return;
        }

        DB::table('SecureKey_tipo_usuario')
            ->where('role_id', $roleId)
            ->delete();

        DB::table('roles')
            ->where('id', $roleId)
            ->delete();
    }
};
