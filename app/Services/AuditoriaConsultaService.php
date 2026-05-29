<?php

namespace App\Services;

use App\Repository\AuditoriaRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditoriaConsultaService
{
    private const FILTROS_PERMITIDOS = ['acao', 'usuario', 'recurso'];

    public function __construct(private AuditoriaRepository $repository)
    {
    }

    public function listar(array $filtros = [], int $porPagina = 20): LengthAwarePaginator
    {
        return $this->repository->buscar($this->normalizarFiltros($filtros), $porPagina);
    }

    public function estatisticas(array $filtros = []): array
    {
        return $this->repository->estatisticas($this->normalizarFiltros($filtros));
    }

    public function normalizarFiltros(array $filtros = []): array
    {
        $filtro = $filtros['filtro'] ?? null;
        if (!in_array($filtro, self::FILTROS_PERMITIDOS, true)) {
            $filtro = null;
        }

        $ordemData = strtolower((string) ($filtros['ordem_data'] ?? 'desc'));
        if (!in_array($ordemData, ['asc', 'desc'], true)) {
            $ordemData = 'desc';
        }

        $dataInicio = $this->normalizarData($filtros['data_inicio'] ?? null);
        $dataFim = $this->normalizarData($filtros['data_fim'] ?? null);

        if ($dataInicio && $dataFim && $dataInicio > $dataFim) {
            [$dataInicio, $dataFim] = [$dataFim, $dataInicio];
        }

        $filtrosNormalizados = [
            'filtro' => $filtro,
            'valor' => trim((string) ($filtros['valor'] ?? '')),
            'ordem_data' => $ordemData,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ];

        if (!$filtrosNormalizados['filtro']) {
            $filtrosNormalizados['valor'] = '';
        }

        return $filtrosNormalizados;
    }

    private function normalizarData(?string $data): ?string
    {
        if (!$data || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return null;
        }

        [$ano, $mes, $dia] = array_map('intval', explode('-', $data));

        return checkdate($mes, $dia, $ano) ? $data : null;
    }
}
