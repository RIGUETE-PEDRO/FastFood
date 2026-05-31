<?php

namespace App\Repository;

use Illuminate\Support\Collection;
use App\Models\PedidoModel;

interface PedidosFeitosRepository
{
    public function listarPedidos();

    public function salvarStatus(PedidoModel $pedido, int $status);

    public function buscarPedidoPorId($pedidoId);

    public function buscarDadosEmpresa();
}
