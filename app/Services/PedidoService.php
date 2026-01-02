<?php

namespace App\Services;

use App\Models\Pedido;

class PedidoService
{
    public function adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco)
    {


        $precoTotal = $quantidade * $preco;


        Pedido::create([
            'usuario_id' => $usuario['id'],
            'produto_id' => $produtoId,
            'quantidade' => $quantidade,
            'observacao' => $observacao ?? '',
            'preco_total' => $precoTotal,
        ]);


    }
}
