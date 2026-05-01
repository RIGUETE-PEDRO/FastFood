<?php

namespace App\Repositoryimpl;

use App\Models\KeyClockAuditoriaModel;
use App\Repository\AuditoriaRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditoriaRepositoryimpl implements AuditoriaRepository
{
    public function buscar(array $filtros = [], int $porPagina = 20): LengthAwarePaginator
    {
        $query = KeyClockAuditoriaModel::with('usuario');

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

        $ordemData = strtolower((string) ($filtros['ordem_data'] ?? 'desc'));
        if (!in_array($ordemData, ['asc', 'desc'], true)) {
            $ordemData = 'desc';
        }

        return $query
            ->orderBy('created_at', $ordemData)
            ->paginate($porPagina)
            ->appends($filtros);
    }
}
