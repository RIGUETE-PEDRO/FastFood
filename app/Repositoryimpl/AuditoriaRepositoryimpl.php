<?php

namespace App\Repositoryimpl;

use App\Models\SecureKeyAuditoriaModel;
use App\Repository\AuditoriaRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuditoriaRepositoryimpl implements AuditoriaRepository
{
    public function buscar(array $filtros = [], int $porPagina = 20): LengthAwarePaginator
    {
        $query = SecureKeyAuditoriaModel::with('usuario');
        $this->aplicarFiltros($query, $filtros);

        $ordemData = strtolower((string) ($filtros['ordem_data'] ?? 'desc'));
        if (!in_array($ordemData, ['asc', 'desc'], true)) {
            $ordemData = 'desc';
        }

        return $query
            ->orderBy('created_at', $ordemData)
            ->paginate($porPagina)
            ->appends($filtros);
    }

    public function estatisticas(array $filtros = []): array
    {
        $query = SecureKeyAuditoriaModel::query();
        $this->aplicarFiltros($query, $filtros);

        $ultimaAcao = (clone $query)
            ->latest('created_at')
            ->first(['acao', 'created_at']);

        return [
            'total' => (clone $query)->count(),
            'usuarios_ativos' => (clone $query)
                ->whereNotNull('usuario_id')
                ->distinct('usuario_id')
                ->count('usuario_id'),
            'tipos_acao' => (clone $query)
                ->whereNotNull('acao')
                ->distinct('acao')
                ->count('acao'),
            'ultima_acao' => $ultimaAcao,
        ];
    }

    public function aplicarFiltros(Builder $query, array $filtros): void
    {
        $filtro = $filtros['filtro'] ?? null;
        $valor = trim((string) ($filtros['valor'] ?? ''));

        if ($filtro && $valor !== '') {
            if ($filtro === 'acao') {
                $query->where('acao', 'like', "%{$valor}%");
            } elseif ($filtro === 'usuario') {
                $query->whereHas('usuario', function ($q) use ($valor) {
                    $q->where('nome', 'like', "%{$valor}%")
                        ->orWhere('email', 'like', "%{$valor}%");
                });
            } elseif ($filtro === 'recurso') {
                $query->where('recurso', 'like', "%{$valor}%");
            }
        }

        if (!empty($filtros['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['data_inicio']);
        }

        if (!empty($filtros['data_fim'])) {
            $query->whereDate('created_at', '<=', $filtros['data_fim']);
        }
    }
}
