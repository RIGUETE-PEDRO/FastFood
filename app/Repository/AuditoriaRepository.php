<?php

namespace App\Repository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
interface AuditoriaRepository
{
   public function buscar(array $filtros = [], int $porPagina = 20);

    public function estatisticas(array $filtros = []);

    public function aplicarFiltros(Builder $query, array $filtros);

}
