<?php

namespace App\Repositoryimpl;

use App\Models\Usuario;
use App\Models\UsuarioModel;
use Illuminate\Support\Facades\DB;

class KeyClockRepositoryimpl
{
    public function hasRole(UsuarioModel $usuario, string $roleName): bool
    {
        $role = DB::table('roles')->where('nome', $roleName)->first();

        if (!$role) {
            return false;
        }
        return DB::table('keyclock_tipo_usuario as ktu')
            ->join('roles as r', 'r.id', '=', 'ktu.role_id')
            ->where('ktu.tipo_usuario_id', $usuario->tipo_usuario_id)
            ->where('r.nome', $roleName)
            ->exists();
    }

	public function getAllGrupos()
	{
		return DB::table('tipo_usuarios')
			->select('id', 'descricao as nome')
			->orderBy('descricao')
			->get();
	}

	public function createRole(string $roleName): void
	{
		$existingRole = DB::table('roles')->where('nome', $roleName)->first();

		if (!$existingRole) {
			DB::table('roles')->insert([
				'nome' => $roleName,
				'created_at' => now(),
				'updated_at' => now(),
			]);
		}
	}

	public function getAllRoles()
	{
		return DB::table('roles')
			->select('id', 'nome')
			->orderBy('nome')
			->get();
	}

	public function getRolesPorGrupos(array $grupoIds): array
	{
		if (empty($grupoIds)) {
			return [];
		}

		return DB::table('keyclock_tipo_usuario as ktu')
			->join('roles as r', 'r.id', '=', 'ktu.role_id')
			->whereIn('ktu.tipo_usuario_id', $grupoIds)
			->select('ktu.tipo_usuario_id as grupo_id', 'r.id as role_id', 'r.nome')
			->orderBy('r.nome')
			->get()
			->groupBy('grupo_id')
			->map(fn ($items) => $items
				->map(fn ($item) => [
					'id' => $item->role_id,
					'nome' => $item->nome,
				])
				->values()
				->all())
			->toArray();
	}

	public function adicionarRoleAoGrupo(int $tipoUsuarioId, int $roleId): void
	{
		DB::table('keyclock_tipo_usuario')->updateOrInsert(
			[
				'tipo_usuario_id' => $tipoUsuarioId,
				'role_id' => $roleId,
			],
			[
				'updated_at' => now(),
				'created_at' => now(),
			]
		);
	}

	public function removerRoleDoGrupo(int $tipoUsuarioId, int $roleId): void
	{
		DB::table('keyclock_tipo_usuario')
			->where('tipo_usuario_id', $tipoUsuarioId)
			->where('role_id', $roleId)
			->delete();
	}
}
