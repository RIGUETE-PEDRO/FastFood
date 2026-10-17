<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLE_NAMES = [
        'gerenciamento_bordas',
        'pedido_whatsapp',
    ];

    public function up(): void
    {
        $now = now();

        DB::table('roles')->insertOrIgnore([
            ['nome' => 'gerenciamento_bordas', 'created_at' => $now, 'updated_at' => $now],
            ['nome' => 'pedido_whatsapp', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $administradorId = DB::table('tipo_usuarios')
            ->where('descricao', 'Administrador')
            ->value('id');

        if (!$administradorId) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('nome', self::ROLE_NAMES)
            ->pluck('id');

        DB::table('SecureKey_tipo_usuario')->insertOrIgnore(
            $roleIds->map(fn ($roleId) => [
                'tipo_usuario_id' => $administradorId,
                'role_id' => $roleId,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        $roleIds = DB::table('roles')
            ->whereIn('nome', self::ROLE_NAMES)
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            return;
        }

        DB::table('SecureKey_tipo_usuario')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('id', $roleIds)->delete();
    }
};
