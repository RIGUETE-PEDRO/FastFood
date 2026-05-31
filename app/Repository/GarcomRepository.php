<?php

namespace App\Repository;

interface GarcomRepository
{
    public function adicionarProdutoAoPedido($produtoId, $mesaId, $quantidade, $usuarioLogado);

    public function adicionarProdutosAoPedido(array $itens, int $mesaId, $usuarioLogado): bool;
}
