<?php

namespace App\Http\Controllers;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use Illuminate\Http\Request;
use App\Services\CarrinhoService;
use App\Services\GenericBase;
use App\Models\Endereco;
use App\Models\Cidade;
use Error;
use Illuminate\Support\Facades\Auth;
use Mockery\Generator\StringManipulation\Pass\Pass;
use PHPUnit\Runner\ResultCache\ResultCache;

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
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco);

        return redirect(url()->previous())
            ->with('success', PassMensagens::PRODUTO_ADICIONADO_SUCESSO);
    }

    public function verCarrinho()
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
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
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->removerProdutoDoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', PassMensagens::REMOVER_CARRINHO_SUCESSO);
    }



    public function atualizarQuantidade(Request $request, $id)
    {
        $carrinhoService = new CarrinhoService();
        $atualizou = $carrinhoService->atualizarQuantidadeProdutoNoCarrinho($request, $id);

            if (!$atualizou) {
                return redirect()->route('carrinho')
                    ->with('error', ErroMensagens::QUANTIDADE_MINIMA);
            }else{
                return redirect()->route('carrinho')
                    ->with('success', PassMensagens::QUANTIDADE_ATUALIZADA_SUCESSO);
            }
    }



    public function deletarEndereco($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->deletarEnderecoUsuario($id);

        return redirect()->route('carrinho')->with($resultado['tipo'], $resultado['mensagem']);
    }


    public function toggleSelecionar(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->toggleSelecionarProdutoNoCarrinho($id);

        return redirect()->route('carrinho');
    }

    public function pegarEndereco(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
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

    public function registrarPedido(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $enderecoId = $request->endereco_id
            ?? session('checkout.endereco_id')
            ?? $request->endereco_opcao;



        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->registraPedido($request , $enderecoId);
        if($resultado != null){
            $carrinhoService->limparCarrinhoAposPedido($request,$resultado);
        }

        $redirect = redirect()->route('pedidos');

        return $redirect->with($resultado);
    }

    public function selecionarCidade(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $cidades = $carrinhoService->selecionarCidade($request);

        return redirect()->back()->with('cidades', $cidades);
    }
}
