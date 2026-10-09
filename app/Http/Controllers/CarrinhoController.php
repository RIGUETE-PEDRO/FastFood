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
        $request->validate([
            'produto_id' => 'required|integer|exists:produtos,id',
            'quantidade' => 'required|integer|min:1|max:99',
            'observacao' => 'nullable|string|max:500',
            'personalizacao' => 'nullable|array',
            'personalizacao.tipo' => 'nullable|in:pizza,porcao',
            'personalizacao.modo' => 'nullable|in:inteira,meia_a_meia',
            'personalizacao.tamanho' => 'nullable|in:P,M,G,GG,XG',
            'personalizacao.borda' => 'nullable|in:sem_borda,com_borda',
            'personalizacao.borda_id' => 'nullable|integer|min:0',
            'personalizacao.sabor_2_id' => 'nullable|integer|min:1',
            'personalizacao.tipo_porcao' => 'nullable|in:inteira,meia',
        ]);

        $usuarioLogado =  $this->genericBase->hasLogado();

        try {
            $this->carrinhoService->adicionarProdutoAoCarrinho(
                $usuarioLogado,
                $request->input('produto_id'),
                $request->input('quantidade'),
                $request->input('observacao'),
                $request->input('personalizacao')
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->withErrors(['personalizacao' => $e->getMessage()]);
        }

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

        $resultado = $this->carrinhoService->escolherMesa($request);

        if (!($resultado['status'] ?? false)) {
            return redirect()->route('carrinho')
                ->with($resultado['tipo'] ?? 'error', $resultado['mensagem'] ?? ErroMensagens::ERRO_PROCESSAR);
        }

        return redirect()->route('pedidos')
            ->with($resultado['tipo'] ?? 'success', $resultado['mensagem'] ?? PassMensagens::PEDIDO_REALIZADO_SUCESSO);
    }

    public function atualizarQuantidade(Request $request, $id)
    {
        $request->validate([
            'acao' => 'nullable|in:mais,menos',
            'quantidade' => 'sometimes|required|integer|min:1|max:99',
        ]);

        try {
            $atualizou = $this->carrinhoService->atualizarQuantidadeProdutoNoCarrinho($request, $id);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('carrinho')->with('error', $e->getMessage());
        }

        if (!$atualizou) {
            return redirect()->route('carrinho')
                ->with('error', 'A quantidade deve ficar entre 1 e 99 e o produto precisa estar disponível.');
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


    public function toggleSelecionar(Request $request, $id)
    {
        $this->genericBase->hasLogado();
        $resultado = $this->carrinhoService->toggleSelecionarProdutoNoCarrinho($request, $id);

        if ($request->expectsJson()) {
            return response()->json($resultado);
        }

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
        $resultado = $this->carrinhoService->validarCarrinhoAntesRegistrarPedido($request);

        if (!($resultado['status'] ?? false)) {
            return redirect()->route('carrinho')
                ->with($resultado['tipo'] ?? 'error', $resultado['mensagem'] ?? ErroMensagens::ERRO_PROCESSAR);
        }

        return redirect()->route('pedidos')
            ->with($resultado['tipo'] ?? 'success', $resultado['mensagem'] ?? PassMensagens::PEDIDO_REALIZADO_SUCESSO);
    }

    public function selecionarCidade(Request $request)
    {
        $this->genericBase->hasLogado();
        $cidades = $this->carrinhoService->selecionarCidade($request);

        return redirect()->back()->with('cidades', $cidades);
    }
}
