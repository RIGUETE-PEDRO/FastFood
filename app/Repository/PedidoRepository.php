<?php

namespace App\Repository;

use Illuminate\Support\Collection;

interface PedidoRepository
{
    public function filtrarPedidosDataNome($data, $nome): Collection;

    public function listarParaChecksum(): Collection;

    public function pegarPedidosDoUsuario(int $usuarioId): Collection;

    public function buscarDadosEmpresa(): Collection;
}