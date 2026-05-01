<?php

namespace App\Services;

use App\Repository\AuditoriaRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditoriaConsultaService
{
    public function __construct(private AuditoriaRepository $repository)
    {
    }

    public function listar(array $filtros = [], int $porPagina = 20): LengthAwarePaginator
    {
        $filtrosNormalizados = [
            'filtro' => $filtros['filtro'] ?? null,
            'valor' => $filtros['valor'] ?? null,
            'ordem_data' => $filtros['ordem_data'] ?? 'desc',
            'data_inicio' => $filtros['data_inicio'] ?? null,
            'data_fim' => $filtros['data_fim'] ?? null,
        ];

        return $this->repository->buscar($filtrosNormalizados, $porPagina);
    }
}
