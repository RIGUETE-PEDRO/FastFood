<?php

namespace App\Repository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SecureKeyAuditoriaModel;

interface AuditoriaRepository
{
    public function registrar(array $dados): SecureKeyAuditoriaModel;

    public function buscar(array $filtros = [], int $porPagina = 20): LengthAwarePaginator;

    public function estatisticas(array $filtros = []): array;

    public function aplicarFiltros(Builder $query, array $filtros): void;

}
