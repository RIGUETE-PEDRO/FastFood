<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;
use App\Services\SecureKeyService;
use App\Services\AuditoriaConsultaService;

class SecureKeyController
{
    protected GenericBase $genericBase;
    protected SecureKeyService $SecureKeyService;
    protected AuditoriaConsultaService $auditoriaConsultaService;

    public function __construct(
        GenericBase              $genericBase,
        SecureKeyService         $SecureKeyService,
        AuditoriaConsultaService $auditoriaConsultaService
    )
    {
        $this->genericBase = $genericBase;
        $this->SecureKeyService = $SecureKeyService;
        $this->auditoriaConsultaService = $auditoriaConsultaService;
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

    public function auditoria(Request $request)
    {
        $filtros = $request->only(['filtro', 'valor', 'ordem_data', 'data_inicio', 'data_fim']);
        $auditorias = $this->auditoriaConsultaService->listar($filtros, 20);

        if ($request->ajax()) {
            return response()->json([
                'total' => $auditorias->total(),
                'rowsHtml' => view('Admin.partials.SecureKey_auditoria_rows', [
                    'auditorias' => $auditorias,
                ])->render(),
                'statsHtml' => view('Admin.partials.SecureKey_auditoria_stats', [
                    'auditorias' => $auditorias,
                ])->render(),
            ]);
        }

        return view('Admin.SecureKey_auditoria', [
            'auditorias' => $auditorias,
            'filtro' => $filtros['filtro'] ?? null,
            'valor' => $filtros['valor'] ?? null,
            'ordem_data' => $filtros['ordem_data'] ?? 'desc',
            'data_inicio' => $filtros['data_inicio'] ?? null,
            'data_fim' => $filtros['data_fim'] ?? null,
        ]);
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
