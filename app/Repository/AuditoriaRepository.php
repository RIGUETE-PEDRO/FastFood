<?php

namespace App\Repository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditoriaRepository
{
    public function buscar(array $filtros = [], int $porPagina = 20): LengthAwarePaginator;
}
