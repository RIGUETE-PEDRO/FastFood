<?php

namespace App\Http\Controllers;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use Illuminate\Http\Request;
use App\Services\CarrinhoService;
use App\Services\GenericBase;

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

        $usuarioLogado =  $this->genericBase->hasLogado();

        $this->carrinhoService->adicionarProdutoAoCarrinho($usuarioLogado, $produtoId, $quantidade, $observacao, $preco);

        return redirect(url()->previous())
            ->with('success', PassMensagens::PRODUTO_ADICIONADO_SUCESSO);
    }


    public function verCarrinho()
    {
        $DadosPedido = $this->carrinhoService->PegardadosPedido();

        return view('Carrinho', [
            'usuario' => $DadosPedido['usuario'],
            'carrinho' => $DadosPedido['carrinho'],
            'enderecos' => $DadosPedido['enderecos'],
            'enderecoSelecionadoId' => session('checkout.endereco_id'),
            'cidades' => $DadosPedido['cidades'],
            'mesas' => $DadosPedido['mesa']
        ]);
    }

    public function removerDoCarrinho($id)
    {
        $this->genericBase->hasLogado();


        $this->carrinhoService->removerProdutoDoCarrinho($id);

        return redirect()->route('carrinho')
            ->with('success', PassMensagens::REMOVER_CARRINHO_SUCESSO);
    }


    public function selecionarMesa(Request $request)
    {

        $this->genericBase->hasLogado();

        $this->carrinhoService->escolherMesa($request);

        return redirect()->route('pedidos')->with($pedidoResultado['tipo'] ?? 'success', $pedidoResultado['mensagem'] ?? PassMensagens::PEDIDO_REALIZADO_SUCESSO);
    }

    public function atualizarQuantidade(Request $request, $id)
    {
        $carrinhoService = $this->carrinhoService;
        $atualizou = $carrinhoService->atualizarQuantidadeProdutoNoCarrinho($request, $id);

        if (!$atualizou) {
            return redirect()->route('carrinho')
                ->with('error', ErroMensagens::QUANTIDADE_MINIMA);
        } else {
            return redirect()->route('carrinho')
                ->with('success', PassMensagens::QUANTIDADE_ATUALIZADA_SUCESSO);
        }
    }

    public function deletarEndereco($id)
    {
        $this->genericBase->hasLogado();


        $carrinhoService = $this->carrinhoService;
        $resultado = $carrinhoService->deletarEnderecoUsuario($id);

        return redirect()->route('carrinho')->with($resultado['tipo'], $resultado['mensagem']);
    }


    public function toggleSelecionar($id)
    {
        $this->genericBase->hasLogado();
        $this->carrinhoService->toggleSelecionarProdutoNoCarrinho($id);

        return redirect()->route('carrinho');
    }

    public function pegarEndereco(Request $request)
    {
        $this->genericBase->hasLogado();

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

        $this->genericBase->hasLogado();
        $this->carrinhoService->validarCarrinhoAntesRegistrarPedido($request);

        return redirect()->route('pedidos')->with($resultado['tipo'] ?? 'success', $resultado['mensagem'] ?? PassMensagens::PEDIDO_REALIZADO_SUCESSO);
    }

    public function selecionarCidade(Request $request)
    {
        $this->genericBase->hasLogado();
        $cidades = $this->carrinhoService->selecionarCidade($request);

        return redirect()->back()->with('cidades', $cidades);
    }
}
