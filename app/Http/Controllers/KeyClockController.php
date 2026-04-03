<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;
use App\Services\KeyClockService;

class KeyClockController
{
    protected GenericBase $genericBase;
    protected KeyClockService $keyClockService;

     public function __construct(GenericBase $genericBase, KeyClockService $keyClockService)
    {
        $this->genericBase = $genericBase;
        $this->keyClockService = $keyClockService;
    }

    public function index()
    {
        return view('Admin.keyclock');
    }

    public function grupo()
    {
        $grupos = $this->keyClockService->getAllGrupos();
        $roles = $this->keyClockService->getAllRoles();
        $rolesPorGrupo = $this->keyClockService->getRolesPorGrupos($grupos->pluck('id')->all());

        return view('Admin.keyclock_grupo', compact('grupos', 'roles', 'rolesPorGrupo'));
    }

    public function permissoes()
    {
        $roleName = request()->input('role_name');
        if ($roleName) {
            $this->keyClockService->createRole($roleName);
        }
        return view('Admin.keyclock_permissoes');
    }

    public function auditoria()
    {
        return view('Admin.keyclock_auditoria');
    }

    public function adicionarRoleGrupo(Request $request, int $grupo)
    {
        $dados = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $this->keyClockService->adicionarRoleAoGrupo($grupo, (int) $dados['role_id']);

        return response()->json(['ok' => true]);
    }

    public function removerRoleGrupo(int $grupo, int $role)
    {
        $this->keyClockService->removerRoleDoGrupo($grupo, $role);

        return response()->json(['ok' => true]);
    }
}
