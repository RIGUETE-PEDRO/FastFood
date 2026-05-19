<?php

namespace App\Repository;

use Illuminate\Support\Collection;

interface PedidosFeitosRepository
{
  public function buscarPedidoPorId($pedidoId);

  public function buscarDadosEmpresa(): Collection;
}
