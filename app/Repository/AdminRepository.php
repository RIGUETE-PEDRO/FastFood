<?php

namespace App\Repository;

use Carbon\Carbon;
use Illuminate\Support\Collection;

interface AdminRepository
{
    public function buscarFuncionarios($searchTerm);

    public function totalVendasNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): float;

    public function totalPedidosNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): int;

    public function contagemStatusNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): Collection;

    public function produtoMaisVendidoNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): ?object;

    public function topProdutosNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo, int $limite = 5): Collection;
}
