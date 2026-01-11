<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use App\Services\PedidoService;
use App\Services\GenericBase;

class ProdutosController extends Controller
{
    public function adicionarAoCarrinho(Request $request)
    {

        $genericBase = new GenericBase();
        $produtoId = $request->input('produto_id');
        $quantidade = $request->input('quantidade', 1);
        $observacao = $request->input('observacao');
        $preco = $request->input('preco');
        $usuario = $genericBase->pegarUsuarioLogado();

        $pedidoService = new PedidoService();
        $pedidoService->adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco);

        return redirect(url()->previous())
            ->with('success', 'Produto adicionado ao carrinho com sucesso!');
    }

    public function verCarrinho()
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        $carrinhoItems = $genericBase->pegarItensCarrinho($usuarioLogado['id']);

        return view('Carrinho', [
            'usuario' => $usuarioLogado,
            'carrinho' => $carrinhoItems,
        ]);
    }

    public function removerDoCarrinho($id)
    {
        $pedidoService = new PedidoService();
        $pedidoService->removerProdutoDoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', 'Produto removido do carrinho com sucesso!');
    }
}
