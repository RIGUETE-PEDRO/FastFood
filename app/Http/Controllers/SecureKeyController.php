<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;
use App\Services\KeyClockService;
use App\Services\AuditoriaConsultaService;

class KeyClockController
{
    protected GenericBase $genericBase;
    protected KeyClockService $keyClockService;
    protected AuditoriaConsultaService $auditoriaConsultaService;

    public function __construct(
        GenericBase $genericBase,
        KeyClockService $keyClockService,
        AuditoriaConsultaService $auditoriaConsultaService
    )
    {
        $this->genericBase = $genericBase;
        $this->keyClockService = $keyClockService;
        $this->auditoriaConsultaService = $auditoriaConsultaService;
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

    public function auditoria(Request $request)
    {
        $filtros = $request->only(['filtro', 'valor', 'ordem_data', 'data_inicio', 'data_fim']);
        $auditorias = $this->auditoriaConsultaService->listar($filtros, 20);

        if ($request->ajax()) {
            return response()->json([
                'total' => $auditorias->total(),
                'rowsHtml' => view('Admin.partials.keyclock_auditoria_rows', [
                    'auditorias' => $auditorias,
                ])->render(),
                'statsHtml' => view('Admin.partials.keyclock_auditoria_stats', [
                    'auditorias' => $auditorias,
                ])->render(),
            ]);
        }

        return view('Admin.keyclock_auditoria', [
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

        $this->keyClockService->adicionarRoleAoGrupo($grupo, (int) $dados['role_id']);

        return response()->json(['ok' => true]);
    }

    public function removerRoleGrupo(int $grupo, int $role)
    {
        $this->keyClockService->removerRoleDoGrupo($grupo, $role);

        return response()->json(['ok' => true]);
    }
}
