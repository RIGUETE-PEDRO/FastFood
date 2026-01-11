<?php

namespace App\Services;

use App\Models\Carrinho;
use App\Models\Produto;


class PedidoService
{
    public function adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco)
    {

        $quantidadeNormalizada = max(1, (int) ($quantidade ?: 1));

        $usuarioId = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        if (!$usuarioId) {
            throw new \InvalidArgumentException('Usuário inválido para adicionar ao carrinho.');
        }

        $produto = Produto::findOrFail($produtoId);
        $precoUnitario = (float) $produto->preco;
        $precoTotal = $quantidadeNormalizada * $precoUnitario;

        Carrinho::create([
            'usuario_id' => $usuarioId,
            'produto_id' => $produtoId,
            'quantidade' => $quantidadeNormalizada,
            'observacao' => $observacao ?? '',
            'preco_total' => $precoTotal,
        ]);


    }

    public function removerProdutoDoCarrinho($id)
    {
        $itemCarrinho = Carrinho::find($id);
        if ($itemCarrinho) {
            $itemCarrinho->delete();
        }
    }
}
