<?php

namespace App\Repository;

interface GarcomRepository
{
    public function adicionarProdutoAoPedido($produtoId, $mesaId, $quantidade, $usuarioLogado);
}
