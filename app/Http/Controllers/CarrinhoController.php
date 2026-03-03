<?php

namespace App\Http\Controllers;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use Illuminate\Http\Request;
use App\Services\CarrinhoService;
use App\Services\GenericBase;
use App\Models\Endereco;
use App\Models\Cidade;
use App\Models\Mesa;
use Error;
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

        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->adicionarProdutoAoCarrinho($usuarioLogado, $produtoId, $quantidade, $observacao, $preco);

        return redirect(url()->previous())
            ->with('success', PassMensagens::PRODUTO_ADICIONADO_SUCESSO);
    }

    public function verCarrinho()
    {
        $statusPermitidos = [
            'disponivel',
            'Disponivel',
            'Disponível',
            'reservada',
            'Reservada',
        ];

        $mesa = Mesa::query()
            ->whereIn('status', $statusPermitidos)
            ->orderBy('numero_da_mesa')
            ->get();
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoItems = $genericBase->pegarItensCarrinho($usuarioLogado->id);

        $enderecos = Endereco::with('cidade')
            ->where('usuario_id', $usuarioLogado->id)
            ->orderByDesc('created_at')
            ->get();

        $cidades = Cidade::orderBy('nome')->get();

        return view('Carrinho', [
            'usuario' => $usuarioLogado,
            'carrinho' => $carrinhoItems,
            'enderecos' => $enderecos,
            'enderecoSelecionadoId' => session('checkout.endereco_id'),
            'cidades' => $cidades,
            'mesas' => $mesa
        ]);
    }

    public function removerDoCarrinho($id)
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->removerProdutoDoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', PassMensagens::REMOVER_CARRINHO_SUCESSO);
    }


    public function selecionarMesa(Request $request)
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $mesaId = $request->input('mesa_id');
        if (!$mesaId) {
            return redirect()->route('carrinho')->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->selecionarMesaNoCarrinho($mesaId);

        if (!($resultado['status'] ?? false)) {
            return redirect()->route('carrinho')->with('error', $resultado['mensagem'] ?? 'Mesa inválida.');
        }

        return redirect()->route('carrinho')
            ->with('success', PassMensagens::MESA_SELECIONADA_SUCESSO);
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
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->deletarEnderecoUsuario($id);

        return redirect()->route('carrinho')->with($resultado['tipo'], $resultado['mensagem']);
    }


    public function toggleSelecionar(Request $request, $id)
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $carrinhoService->toggleSelecionarProdutoNoCarrinho($id);

        return redirect()->route('carrinho');
    }

    public function pegarEndereco(Request $request)
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
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
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $enderecoId = $request->endereco_id
            ?? session('checkout.endereco_id')
            ?? $request->endereco_opcao;



        $carrinhoService = new CarrinhoService();
        $resultado = $carrinhoService->registraPedido($request , $enderecoId);

        if (!($resultado['status'] ?? false)) {
            return redirect()->route('carrinho')->with($resultado['tipo'] ?? 'error', $resultado['mensagem'] ?? ErroMensagens::ERRO_PROCESSAR);
        }

        $pedidoId = $resultado['pedido_id'] ?? null;
        if ($pedidoId) {
            $carrinhoService->limparCarrinhoAposPedido($request, $pedidoId);
        }

        return redirect()->route('pedidos')->with($resultado['tipo'] ?? 'success', $resultado['mensagem'] ?? PassMensagens::PEDIDO_REALIZADO_SUCESSO);
    }

    public function selecionarCidade(Request $request)
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = new CarrinhoService();
        $cidades = $carrinhoService->selecionarCidade($request);

        return redirect()->back()->with('cidades', $cidades);
    }
}
