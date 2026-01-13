<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CarrinhoService;
use App\Services\GenericBase;
use App\Models\Endereco;
use App\Models\Cidade;
use Illuminate\Support\Facades\Auth;


class CarrinhoController extends Controller
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

        $carrinhoService = new CarrinhoService();
        $carrinhoService->adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco);

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

        $enderecos = Endereco::with('cidade')
            ->where('usuario_id', $usuario->id)
            ->orderByDesc('created_at')
            ->get();

        $cidades = Cidade::orderBy('nome')->get();

        return view('Carrinho', [
            'usuario' => $usuario,
            'carrinho' => $carrinhoItems,
            'enderecos' => $enderecos,
            'enderecoSelecionadoId' => session('checkout.endereco_id'),
            'cidades' => $cidades,
        ]);
    }

    public function removerDoCarrinho($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->removerProdutoDoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', 'Produto removido do carrinho com sucesso!');
    }



    public function atualizarQuantidade(Request $request, $id)
    {
        $carrinhoService = new CarrinhoService();
        $carrinhoService->atualizarQuantidadeProdutoNoCarrinho($request, $id);

        return redirect()->route('carrinho')
            ->with('success', 'Quantidade do produto atualizada com sucesso!');
    }



    public function deletarEndereco($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->deletarEnderecoUsuario($id);

        return redirect()->route('carrinho')->with($resultado['tipo'], $resultado['mensagem']);
    }


    public function toggleSelecionar(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->toggleSelecionarProdutoNoCarrinho($id);

        return redirect()->route('carrinho');
    }

    public function pegarEndereco(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->pegarEnderecoUsuario($request);

        if ($request->expectsJson()) {
            $statusCode = $resultado['status'] ? 200 : 422;
            return response()->json($resultado, $statusCode);
        }

        $redirect = redirect()->route('carrinho');

        if (!$resultado['status']) {
            $redirect = $redirect->withInput();
        }

        return $redirect->with($resultado['tipo'], $resultado['mensagem']);
    }

    public function registrarPagamento(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->registrarPagamento($request);

        $redirect = redirect()->route('pedido');

        if (!$resultado['status']) {
            $redirect = redirect()->route('carrinho')->withInput();
        }

        return $redirect->with($resultado['tipo'], $resultado['mensagem']);
    }

    public function selecionarCidade(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', 'Você precisa fazer login.');
        }

        $carrinhoService = new CarrinhoService();
        $cidades = $carrinhoService->selecionarCidade($request);

        return redirect()->back()->with('cidades', $cidades);
    }
}
