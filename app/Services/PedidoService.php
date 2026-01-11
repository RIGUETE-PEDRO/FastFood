<?php

namespace App\Services;

use App\Models\Carrinho;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PedidoService
{
    public function adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco)
    {
        $GenericBase = new GenericBase();
        $quantidadeNormalizada = $quantidade;


        $usuarioId = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        if (!$usuarioId) {
            throw new \InvalidArgumentException('Usuário inválido para adicionar ao carrinho.');
        }

        $produto = Produto::findOrFail($produtoId);
        $precoUnitario = (float) $produto->preco;
        $precoTotal = $quantidadeNormalizada * $precoUnitario;

        if ($GenericBase->findByProdutosIsUsuario($produtoId, $usuarioId, $precoTotal)) {
            $itemCarrinho = Carrinho::where('usuario_id', $usuarioId)
                ->where('produto_id', $produtoId)
                ->first();

            if ($itemCarrinho) {
                $itemCarrinho->quantidade += $quantidadeNormalizada;
                $itemCarrinho->preco_total += $precoTotal;
                $itemCarrinho->save();
            }
        } else {
            Carrinho::create([
                'usuario_id' => $usuarioId,
                'produto_id' => $produtoId,
                'quantidade' => $quantidadeNormalizada,
                'observacao' => $observacao ?? '',
                'preco_total' => $precoTotal,
            ]);
        }
    }

    public function removerProdutoDoCarrinho($id)
    {
        if (!Auth::check()) {
            return;
        }

        $itemCarrinho = Carrinho::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->first();
        if ($itemCarrinho) {
            $itemCarrinho->delete();
        }
    }

    public function atualizarQuantidadeProdutoNoCarrinho(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $itemCarrinho = Carrinho::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->with('produto')
            ->firstOrFail();

        // Se clicou em + ou -
        if ($request->filled('acao')) {

            if ($request->acao === 'mais') {
                $itemCarrinho->quantidade++;
            }

            if ($request->acao === 'menos' && $itemCarrinho->quantidade > 1) {
                $itemCarrinho->quantidade--;
            }
        } elseif ($request->filled('quantidade')) {

            $quantidadeNormalizada = max(1, (int) $request->quantidade);
            $itemCarrinho->quantidade = $quantidadeNormalizada;
        }

        // Atualiza preço total
        $precoUnitario = (float) $itemCarrinho->produto->preco;
        $itemCarrinho->preco_total = $itemCarrinho->quantidade * $precoUnitario;

        $itemCarrinho->save();

        return back();
    }
}
