<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use App\Services\PedidoService;
use App\Services\GenericBase;
use Illuminate\Support\Facades\Auth;

class ProdutosController extends Controller
{
    public function adicionarAoCarrinho(Request $request)
    {
        $produtoId = $request->input('produto_id');
        $quantidade = $request->input('quantidade', 1);
        $observacao = $request->input('observacao');
        $preco = $request->input('preco');

        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login para adicionar ao carrinho.');
        }

        $pedidoService = new PedidoService();
        $pedidoService->adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco);

        return redirect(url()->previous())
            ->with('success', 'Produto adicionado ao carrinho com sucesso!');
    }

    public function verCarrinho()
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login para ver o carrinho.');
        }

        $genericBase = new GenericBase();
        $carrinhoItems = $genericBase->pegarItensCarrinho($usuario->id);

        return view('Carrinho', [
            'usuario' => $usuario,
            'carrinho' => $carrinhoItems,
        ]);
    }

    public function removerDoCarrinho($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $pedidoService = new PedidoService();
        $pedidoService->removerProdutoDoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', 'Produto removido do carrinho com sucesso!');
    }

    public function atualizarQuantidade(Request $request, $id)
    {
        $pedidoService = new PedidoService();
        $pedidoService->atualizarQuantidadeProdutoNoCarrinho($request, $id);

        return redirect()->route('carrinho')
            ->with('success', 'Quantidade do produto atualizada com sucesso!');
    }

    public function toggleSelecionar(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $pedidoService = new PedidoService();
        $pedidoService->toggleSelecionarProdutoNoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', 'Item do carrinho atualizado com sucesso!');
    }
}
