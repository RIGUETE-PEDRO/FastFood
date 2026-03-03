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


class CarrinhoController extends Controller
{
    protected GenericBase $genericBase;
    protected CarrinhoService $carrinhoService;


    public function __construct(GenericBase $genericBase, CarrinhoService $carrinhoService)
    {
        $this->genericBase = $genericBase;
        $this->carrinhoService = $carrinhoService;
    }



    public function adicionarAoCarrinho(Request $request)
    {
        $produtoId = $request->input('produto_id');
        $quantidade = $request->input('quantidade', 1);
        $observacao = $request->input('observacao');
        $preco = $request->input('preco');


        $usuarioLogado =  $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }


        $this->carrinhoService->adicionarProdutoAoCarrinho($usuarioLogado, $produtoId, $quantidade, $observacao, $preco);

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

        $usuarioLogado =    $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoItems = $this->genericBase->pegarItensCarrinho($usuarioLogado->id);

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
        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $this->carrinhoService->removerProdutoDoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', PassMensagens::REMOVER_CARRINHO_SUCESSO);
    }


    public function selecionarMesa(Request $request)
    {

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $mesaId = $request->input('mesa_id');
        if (!$mesaId) {
            return redirect()->route('carrinho')->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $carrinhoService = $this->carrinhoService;
        $resultado = $carrinhoService->selecionarMesaNoCarrinho($mesaId);

        if (!($resultado['status'] ?? false)) {
            return redirect()->route('carrinho')->with('error', $resultado['mensagem'] ?? 'Mesa inválida.');
        }

        $pedidoResultado = $carrinhoService->registraPedido($request, null);
        if (!($pedidoResultado['status'] ?? false)) {
            return redirect()->route('carrinho')->with($pedidoResultado['tipo'] ?? 'error', $pedidoResultado['mensagem'] ?? ErroMensagens::ERRO_PROCESSAR);
        }

        $pedidoId = $pedidoResultado['pedido_id'] ?? null;
        if ($pedidoId) {
            $carrinhoService->limparCarrinhoAposPedido($request, $pedidoId);
        }

        return redirect()->route('pedidos')->with($pedidoResultado['tipo'] ?? 'success', $pedidoResultado['mensagem'] ?? PassMensagens::PEDIDO_REALIZADO_SUCESSO);
    }

    public function atualizarQuantidade(Request $request, $id)
    {
        $carrinhoService = $this->carrinhoService;
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
        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = $this->carrinhoService;
        $resultado = $carrinhoService->deletarEnderecoUsuario($id);

        return redirect()->route('carrinho')->with($resultado['tipo'], $resultado['mensagem']);
    }


    public function toggleSelecionar(Request $request, $id)
    {

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }


        $this->carrinhoService->toggleSelecionarProdutoNoCarrinho($id);

        return redirect()->route('carrinho');
    }

    public function pegarEndereco(Request $request)
    {

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $carrinhoService = $this->carrinhoService;
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

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $enderecoId = $request->endereco_id
            ?? session('checkout.endereco_id')
            ?? $request->endereco_opcao;



        $carrinhoService = $this->carrinhoService;
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

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        if (!$usuarioLogado) {
            return redirect()->route('login.form')->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }


        $cidades = $this->carrinhoService->selecionarCidade($request);

        return redirect()->back()->with('cidades', $cidades);
    }
}
