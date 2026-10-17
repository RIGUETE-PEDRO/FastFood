<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleIds = DB::table('roles')
            ->where('nome', 'user')
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            return;
        }

        DB::table('SecureKey_tipo_usuario')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('id', $roleIds)->delete();
    }

    public function down(): void
    {
        $now = now();

        DB::table('roles')->insertOrIgnore([
            'nome' => 'user',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
