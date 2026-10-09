<?php

namespace App\Services;

use App\Enum\Pagamento;
use App\Enum\StatusPedidos as EnumsStatusPedidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Repository\CarrinhoRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarrinhoService
{
    protected GenericBase $genericBase;
    protected CarrinhoRepository $carrinhoRepository;
    protected PersonalizacaoProdutoService $personalizacaoProdutoService;


    public function __construct(GenericBase $genericBase, CarrinhoRepository $carrinhoRepository, PersonalizacaoProdutoService $personalizacaoProdutoService)
    {
        $this->genericBase = $genericBase;
        $this->carrinhoRepository = $carrinhoRepository;
        $this->personalizacaoProdutoService = $personalizacaoProdutoService;
    }

    public function validarCarrinhoAntesRegistrarPedido(Request $request)
    {
        $enderecoId = $request->endereco_id
            ?? session('checkout.endereco_id')
            ?? $request->endereco_opcao;

        return $this->registrarPedidoCompleto($request, $enderecoId);
    }

    public function escolherMesa(Request $request)
    {
        $mesaId = $request->input('mesa_id');
        if (!$mesaId) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::SEM_ID_MESA,
            ];
        }

        $resultado = $this->selecionarMesaNoCarrinho($mesaId);

        if (!($resultado['status'] ?? false)) {
            return $resultado;
        }

        return $this->registrarPedidoCompleto($request, null);
    }

    public function pegarDadosPedido()
    {
        $statusPermitidos = ['disponivel', 'reservada', 'ocupada'];

        $mesa = $this->carrinhoRepository->pegarMesaSelecionada($statusPermitidos);

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();

        $carrinhoItems = $this->genericBase->pegarItensCarrinho($usuarioLogado->id);
        $enderecos = $this->carrinhoRepository->pegarEnderecosDoUsuario($usuarioLogado->id);
        $cidades = $this->genericBase->findByCidade();

        return [
            'usuario' => $usuarioLogado,
            'carrinho' => $carrinhoItems,
            'enderecos' => $enderecos,
            'cidades' => $cidades,
            'mesa' => $mesa,
        ];
    }

    public function adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $configuracao = null)
    {
        $quantidadeNormalizada = filter_var($quantidade, FILTER_VALIDATE_INT);
        if ($quantidadeNormalizada === false || $quantidadeNormalizada < 1 || $quantidadeNormalizada > 99) {
            throw new \InvalidArgumentException('A quantidade deve ficar entre 1 e 99.');
        }

        $usuarioId = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        if (!$usuarioId) {
            throw new \InvalidArgumentException("Usuário inválido" . ErroMensagens::PRECISA_ESTA_LOGADO);
        }

        $produto = $this->carrinhoRepository->buscarProdutoPorId((int) $produtoId);
        if (!$produto) {
            throw new \InvalidArgumentException('Produto não encontrado.');
        }

        $cotacao = $this->personalizacaoProdutoService->cotar($produto, $configuracao);
        $personalizacao = $cotacao['personalizacao'];
        $precoUnitario = $cotacao['preco_unitario'];
        $precoTotal = round($quantidadeNormalizada * $precoUnitario, 2);
        $observacao = trim((string) ($observacao ?? ''));

        $itemCarrinho = $this->carrinhoRepository->buscarItemCarrinhoPorUsuarioEProduto(
            (int) $usuarioId,
            (int) $produtoId,
            $personalizacao,
            $observacao
        );

        if ($itemCarrinho) {
            if ($itemCarrinho->quantidade + $quantidadeNormalizada > 99) {
                throw new \InvalidArgumentException('A quantidade máxima por item é 99.');
            }
            $itemCarrinho->quantidade += $quantidadeNormalizada;
            $itemCarrinho->preco_total = round($itemCarrinho->quantidade * $precoUnitario, 2);
            $itemCarrinho->save();
        } else {
            $this->carrinhoRepository->criarItemCarrinho([
                'usuario_id' => $usuarioId,
                'produto_id' => $produtoId,
                'quantidade' => $quantidadeNormalizada,
                'observacao' => $observacao ?? '',
                'preco_total' => $precoTotal,
                'personalizacao' => $personalizacao,
            ]);
        }
    }

    public function removerProdutoDoCarrinho($id)
    {
        if (!Auth::check()) {
            return;
        }

        $itemCarrinho = $this->carrinhoRepository->buscarItemCarrinho((int) $id, (int) Auth::id());
        if ($itemCarrinho) {
            $itemCarrinho->delete();
        }
    }

    public function atualizarQuantidadeProdutoNoCarrinho(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $itemCarrinho = $this->carrinhoRepository->buscarItemCarrinhoComProduto((int) $id, (int) Auth::id());
        if (!$itemCarrinho) {
            abort(404);
        }

        // Se clicou em + ou -
        if ($request->filled('acao')) {

            if ($request->acao === 'mais') {
                if ($itemCarrinho->quantidade >= 99) {
                    return false;
                }
                $itemCarrinho->quantidade++;
            }

            if ($request->acao === 'menos') {
                $itemCarrinho->quantidade--;
                //se for menor que 1, não atualiza e retorna erro
                if ($itemCarrinho->quantidade < 1) {

                    return false;
                }
            }
        } elseif ($request->filled('quantidade')) {

            $quantidadeNormalizada = max(1, (int) $request->quantidade);
            $itemCarrinho->quantidade = $quantidadeNormalizada;
        }

        try {
            $cotacao = $this->personalizacaoProdutoService->cotar($itemCarrinho->produto, $itemCarrinho->personalizacao);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        $itemCarrinho->personalizacao = $cotacao['personalizacao'];
        $itemCarrinho->preco_total = round($itemCarrinho->quantidade * $cotacao['preco_unitario'], 2);

        $itemCarrinho->save();

        return back();
    }

    public function selecionarCidade()
    {
        $cidade = $this->carrinhoRepository->listarCidades();

        return $cidade;
    }

    public function toggleSelecionarProdutoNoCarrinho(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $itemCarrinho = $this->carrinhoRepository->buscarItemCarrinho((int) $id, (int) Auth::id());
        if (!$itemCarrinho) {
            abort(404);
        }

        $itemCarrinho->selecionado = $request->boolean('ativo');
        $itemCarrinho->save();

        return [
            'status' => true,
            'selecionado' => (bool) $itemCarrinho->selecionado,
        ];
    }


    public function pegarEnderecoUsuario(Request $request)
    {
        $usuarioId = Auth::id();

        if (!$usuarioId) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::NAO_LOGADO_ENDERECO,
            ];
        }

        $opcaoEndereco = $request->input('endereco_opcao');

        if ($opcaoEndereco && $opcaoEndereco !== 'novo') {
            $endereco = $this->carrinhoRepository->buscarEnderecoPorIdEUsuario((int) $opcaoEndereco, (int) $usuarioId);

            if (!$endereco) {
                Session::flash('checkout.modal', 'enderecoModal');
                return [
                    'status' => false,
                    'tipo' => 'error',
                    'mensagem' => ErroMensagens::NAO_ENCONTRAMOS_ENDERECO,
                ];
            }

            Session::put('checkout.endereco_id', $endereco->id);
            Session::put('checkout.cidade_id', optional($endereco->cidade)->id);
            Session::put('checkout.tipo_entrega', 'entrega');
            Session::forget('checkout.mesa_id');
            Session::flash('checkout.modal', 'pagamentoModal');

            return [
                'status' => true,
                'tipo' => 'success',
                'mensagem' => "endereco " . PassMensagens::SELECIONADO_SUCESSO,
                'endereco' => [
                    'id' => $endereco->id,
                    'logradouro' => $endereco->logradouro,
                    'numero' => $endereco->numero,
                    'bairro' => $endereco->bairro,
                    'complemento' => $endereco->complemento,
                    'cidade' => optional($endereco->cidade)->nome,
                ],
            ];
        }

        $bairro = trim((string) $request->input('bairro'));
        $rua = trim((string) $request->input('rua'));
        $cidadeId = $request->input('cidade_id');

        if ($bairro === '' || $rua === '') {
            Session::flash('checkout.modal', 'enderecoNovoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::NAO_ENCONTRAMOS_ENDERECO,
            ];
        }

        if (!$cidadeId || !$this->carrinhoRepository->cidadeExiste((int) $cidadeId)) {
            Session::flash('checkout.modal', 'enderecoNovoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::NAO_ENCONTRAMOS_ENDERECO,
            ];
        }

        $endereco = $this->carrinhoRepository->criarEndereco([
            'usuario_id'  => $usuarioId,
            'logradouro'  => $rua,
            'numero'      => $request->input('numero'),
            'complemento' => $request->input('complemento'),
            'bairro'      => $bairro,
            'cidade_id'   => $cidadeId,
        ]);

        Session::put('checkout.endereco_id', $endereco->id);
        Session::put('checkout.cidade_id', $cidadeId);
        Session::put('checkout.tipo_entrega', 'entrega');
        Session::forget('checkout.mesa_id');
        Session::flash('checkout.modal', 'pagamentoModal');

        return [
            'status' => true,
            'tipo' => 'success',
            'mensagem' => "Endereço " . PassMensagens::CADASTRADO_SUCESSO,
            'endereco' => [
                'id' => $endereco->id,
                'logradouro' => $endereco->logradouro,
                'numero' => $endereco->numero,
                'bairro' => $endereco->bairro,
                'complemento' => $endereco->complemento,
                'cidade' => optional($endereco->cidade)->nome,
            ],
        ];
    }


    public function deletarEnderecoUsuario($id)
    {
        $usuarioId = Auth::id();

        if (!$usuarioId) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Faça login para gerenciar seus endereços.',
            ];
        }

        $quantidadeEnderecos = $this->carrinhoRepository->quantidadeEnderecosUsuario((int) $usuarioId);
        if ($quantidadeEnderecos <= 1) {
            Session::flash('checkout.modal', 'enderecoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Mantenha pelo menos um endereço cadastrado.',
            ];
        }

        $endereco = $this->carrinhoRepository->buscarEnderecoPorIdEUsuario((int) $id, (int) $usuarioId);

        if (!$endereco) {
            Session::flash('checkout.modal', 'enderecoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::ENDEREÇO_NAO_ENCONTRADO . ' para exclusão.',
            ];
        }

        $eraSelecionado = (string) Session::get('checkout.endereco_id') === (string) $endereco->id;

        $endereco->delete();

        if ($eraSelecionado) {
            Session::forget('checkout.endereco_id');
        }

        Session::flash('checkout.modal', 'enderecoModal');

        return [
            'status' => true,
            'tipo' => 'success',
            'mensagem' => "Endereço " . PassMensagens::DELETE_SUCESSO,
        ];
    }

    public function limparCarrinhoAposPedido($request, $resultado)
    {
        $usuarioId = Auth::id();

        $mesaId = Session::get('checkout.mesa_id');

        $carrinhoItems = $this->carrinhoRepository->listarItensSelecionadosCarrinho((int) $usuarioId);


        foreach ($carrinhoItems as $item) {
            $precoUnitario = $item->preco_total / $item->quantidade;

            $this->carrinhoRepository->criarItemPedido([
                'produto_id' => $item->produto_id,
                'quantidade' => $item->quantidade,
                'preco_unitario' => $precoUnitario,
                'status_da_comanda' => 'em_aberto',
                'pago_em' => null,
                'pedido_id' => $resultado,
                'observacao' => $item->observacao,
                'personalizacao' => $item->personalizacao,
            ]);
        }

        $this->carrinhoRepository->removerItensSelecionadosCarrinho((int) $usuarioId);

        Session::forget('checkout.mesa_id');
        Session::forget('checkout.endereco_id');
        Session::forget('checkout.tipo_entrega');
        Session::forget('checkout.pagamento');
        Session::forget('checkout.modal');
    }

    private function registrarPedidoCompleto(Request $request, $enderecoId): array
    {
        try {
            return DB::transaction(function () use ($request, $enderecoId) {
                $resultado = $this->registraPedido($request, $enderecoId);

                if (!($resultado['status'] ?? false)) {
                    return $resultado;
                }

                $pedidoId = $resultado['pedido_id'] ?? null;
                if ($pedidoId) {
                    $this->limparCarrinhoAposPedido($request, $pedidoId);
                }

                return $resultado;
            });
        } catch (\Throwable $e) {
            Log::error('Erro ao finalizar o pedido completo', [
                'usuario_id' => Auth::id(),
                'tipo_entrega' => $request->input('tipo_entrega') ?? Session::get('checkout.tipo_entrega'),
                'mesa_id' => Session::get('checkout.mesa_id'),
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Erro ao finalizar o pedido. Tente novamente.',
            ];
        }
    }


    public function registraPedido($request, $enderecoId)
    {
        if (!Auth::check()) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::PRECISA_ESTA_LOGADO,
            ];
        }

        $tipoEntregaInformado = $request->input('tipo_entrega');
        $tipoEntrega = $tipoEntregaInformado ?? Session::get('checkout.tipo_entrega');
        $mesaId = Session::get('checkout.mesa_id');

        if ($tipoEntrega === 'retirar') {
            $tipoEntrega = 'mesa';
        }

        if ($tipoEntregaInformado === 'mesa' && !$request->filled('mesa_id')) {
            $mesaId = null;
            Session::forget('checkout.mesa_id');
        }

        if (!$tipoEntrega) {
            $tipoEntrega = $mesaId ? 'mesa' : 'entrega';
        }

        if (!in_array($tipoEntrega, ['mesa', 'entrega'], true)) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Tipo de pedido inválido.',
            ];
        }

        if ($tipoEntrega === 'mesa') {
            $enderecoId = null;
            Session::forget('checkout.endereco_id');
            Session::forget('checkout.cidade_id');
        }

        $usuarioId = Auth::id();

        try {
            $precosMudaram = $this->recalcularItensSelecionados((int) $usuarioId);
        } catch (\InvalidArgumentException $e) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => $e->getMessage(),
            ];
        }

        if ($precosMudaram) {
            return [
                'status' => false,
                'tipo' => 'warning',
                'mensagem' => 'O preço de um ou mais itens foi atualizado. Confira o carrinho e confirme o pedido novamente.',
            ];
        }

        $valor_total = $this->carrinhoRepository->somarValorSelecionadoCarrinho((int) $usuarioId);

        if ($valor_total <= 0) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Selecione pelo menos um produto para finalizar.',
            ];
        }

        if ($tipoEntrega === 'entrega' && !$enderecoId) {
            Session::flash('checkout.modal', 'enderecoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Selecione um endereço para entrega.',
            ];
        }

        if ($tipoEntrega === 'mesa' && !$mesaId) {
            Session::flash('checkout.modal', 'mesaModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::SEM_ID_MESA,
            ];
        }

        $pagamentoenum = null;
        if ($tipoEntrega === 'entrega') {
            $pagamentoMetodo = $request->input('pagamento_metodo');
            if (!$pagamentoMetodo) {
                Session::flash('checkout.modal', 'pagamentoModal');
                return [
                    'status' => false,
                    'tipo' => 'error',
                    'mensagem' => 'Selecione uma forma de pagamento.',
                ];
            }

            $pagamentoenum = Pagamento::fromString($pagamentoMetodo);
        }


        try {
            $pedido = $this->carrinhoRepository->criarPedido([
                'usuario_id' => $usuarioId,
                'endereco_id' => $enderecoId,
                'mesa_id' => $tipoEntrega === 'mesa' ? $mesaId : null,
                'tipo_pagamento_id' => $pagamentoenum ? $pagamentoenum->value : null,
                'observacoes_pagamento' => $tipoEntrega === 'entrega' ? $request->input('observacoes_pagamento') : null,
                'valor_total' => $valor_total,
                'status' => EnumsStatusPedidos::PENDENTE->value
            ]);

            return [
                'status' => true,
                'tipo' => 'success',
                'mensagem' => PassMensagens::PEDIDO_REALIZADO_SUCESSO,
                'pedido_id' => $pedido->id,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao registrar o pedido', [
                'usuario_id' => $usuarioId,
                'endereco_id' => $enderecoId,
                'mesa_id' => $mesaId,
                'tipo_entrega' => $tipoEntrega,
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Erro ao registrar o pedido. Verifique os dados e tente novamente.',
            ];
        }
    }

    public function selecionarMesaNoCarrinho($id)
    {
        if (!Auth::check()) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::PRECISA_ESTA_LOGADO,
            ];
        }

        $mesa = $this->carrinhoRepository->pegarMesaPorId((int) $id);
        if (!$mesa) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Mesa não encontrada.',
            ];
        }

        $status = (string) $mesa->status;
        $statusNormalizado = mb_strtolower($status);
        $statusPermitidos = [
            'disponivel',
            'disponível',
            'reservada',
            'ocupada',
        ];

        if (!in_array($statusNormalizado, $statusPermitidos, true)) {
            Session::flash('checkout.modal', 'mesaModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Mesa indisponivel para receber pedido.',
            ];
        }

        Session::put('checkout.mesa_id', $mesa->id);
        Session::put('checkout.tipo_entrega', 'mesa');
        Session::forget('checkout.endereco_id');
        Session::forget('checkout.cidade_id');
        Session::forget('checkout.pagamento');
        Session::forget('checkout.modal');

        return [
            'status' => true,
            'tipo' => 'success',
            'mensagem' => PassMensagens::MESA_SELECIONADA_SUCESSO,
        ];
    }

    private function recalcularItensSelecionados(int $usuarioId): bool
    {
        $precosMudaram = false;
        foreach ($this->carrinhoRepository->listarItensSelecionadosCarrinho($usuarioId) as $item) {
            if (!$item->produto) {
                throw new \InvalidArgumentException('Um produto do carrinho não está mais disponível. Remova-o e adicione outro.');
            }

            $cotacao = $this->personalizacaoProdutoService->cotar($item->produto, $item->personalizacao);
            $item->personalizacao = $cotacao['personalizacao'];
            $novoTotal = round($item->quantidade * $cotacao['preco_unitario'], 2);
            if (round((float) $item->preco_total, 2) !== $novoTotal) {
                $precosMudaram = true;
            }
            $item->preco_total = $novoTotal;
            $item->save();
        }

        return $precosMudaram;
    }
}
//576 linhas
