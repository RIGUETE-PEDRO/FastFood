<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;
use App\Services\SecureKeyService;

class SecureKeyController
{
    protected GenericBase $genericBase;
    protected SecureKeyService $SecureKeyService;

    public function __construct(
        GenericBase              $genericBase,
        SecureKeyService         $SecureKeyService
    )
    {
        $this->genericBase = $genericBase;
        $this->SecureKeyService = $SecureKeyService;
    }

    public function index()
    {
        return view('Admin.SecureKey');
    }

    public function grupo()
    {
        $grupos = $this->SecureKeyService->getAllGrupos();
        $roles = $this->SecureKeyService->getAllRoles();
        $rolesPorGrupo = $this->SecureKeyService->getRolesPorGrupos($grupos->pluck('id')->all());

        return view('Admin.SecureKey_grupo', compact('grupos', 'roles', 'rolesPorGrupo'));
    }

    public function permissoes()
    {
        $roleName = request()->input('role_name');
        if ($roleName) {
            $this->SecureKeyService->createRole($roleName);
        }
        return view('Admin.SecureKey_permissoes');
    }

    public function adicionarRoleGrupo(Request $request, int $grupo)
    {
        $dados = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $this->SecureKeyService->adicionarRoleAoGrupo($grupo, (int) $dados['role_id']);

        return response()->json(['ok' => true]);
    }

    public function removerRoleGrupo(int $grupo, int $role)
    {
        $this->SecureKeyService->removerRoleDoGrupo($grupo, $role);

        return response()->json(['ok' => true]);
    }
}
