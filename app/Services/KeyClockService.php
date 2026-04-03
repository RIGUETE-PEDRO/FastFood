<?php

namespace App\Services;

use App\Models\UsuarioModel;
use App\Repositoryimpl\KeyClockRepositoryimpl;

class KeyClockService
{
    public function __construct(private KeyClockRepositoryimpl $repository)
    {

    }

    public function hasRole(UsuarioModel $usuario, string $roleName): bool
    {
        return $this->repository->hasRole($usuario, $roleName);
    }

    public function getAllGrupos()
    {
        return $this->repository->getAllGrupos();
    }

    public function createRole($roleName)
    {
        $this->repository->createRole((string) $roleName);
    }

    public function getAllRoles()
    {
        return $this->repository->getAllRoles();
    }

    public function getRolesPorGrupos(array $grupoIds): array
    {
        return $this->repository->getRolesPorGrupos($grupoIds);
    }

    public function adicionarRoleAoGrupo(int $tipoUsuarioId, int $roleId): void
    {
        $this->repository->adicionarRoleAoGrupo($tipoUsuarioId, $roleId);
    }

    public function removerRoleDoGrupo(int $tipoUsuarioId, int $roleId): void
    {
        $this->repository->removerRoleDoGrupo($tipoUsuarioId, $roleId);
    }
}
