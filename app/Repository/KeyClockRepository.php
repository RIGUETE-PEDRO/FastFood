<?php

namespace App\Repository;

use App\Models\UsuarioModel;

interface KeyClockRepository
{
    public function hasRole(UsuarioModel $usuario, string $roleName): bool;

    public function getAllGrupos();

    public function createRole(string $roleName): void;

    public function getAllRoles();

    public function getRolesPorGrupos(array $grupoIds): array;

    public function adicionarRoleAoGrupo(int $tipoUsuarioId, int $roleId): void;

    public function removerRoleDoGrupo(int $tipoUsuarioId, int $roleId): void;
}
