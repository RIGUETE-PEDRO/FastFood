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
}
