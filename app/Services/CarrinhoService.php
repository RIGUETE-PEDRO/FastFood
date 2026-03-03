<?php

namespace App\Services;

use App\Enum\Pagamento;
use App\Enum\StatusPedidos as EnumsStatusPedidos;
use App\Models\Carrinho;
use App\Models\Endereco;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Pedido;
use App\Models\Cidade;
use App\Models\FormaPagamento;
use App\Models\ItemPedido;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Models\Mesa;
use Illuminate\Support\Facades\Log;

class CarrinhoService
{
    public function adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco)
    {
        $GenericBase = new GenericBase();
        $quantidadeNormalizada = $quantidade;


        $usuarioId = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        if (!$usuarioId) {
            throw new \InvalidArgumentException("Usuário inválido" . ErroMensagens::PRECISA_ESTA_LOGADO);
        }

        $produto = Produto::findOrFail($produtoId);
        $precoUnitario = (float) $produto->preco;
        $precoTotal = $quantidadeNormalizada * $precoUnitario;

        if ($GenericBase->findByProdutosIsUsuario($produtoId, $usuarioId)) {
            $itemCarrinho = Carrinho::where('usuario_id', $usuarioId)
                ->where('produto_id', $produtoId)
                ->first();

            if ($itemCarrinho) {
                $itemCarrinho->quantidade += $quantidadeNormalizada;
                $itemCarrinho->preco_total += $precoTotal;
                $itemCarrinho->save();
            }
        } else {
            Carrinho::create([
                'usuario_id' => $usuarioId,
                'produto_id' => $produtoId,
                'quantidade' => $quantidadeNormalizada,
                'observacao' => $observacao ?? '',
                'preco_total' => $precoTotal,
            ]);
        }
    }

    public function removerProdutoDoCarrinho($id)
    {
        if (!Auth::check()) {
            return;
        }

        $itemCarrinho = Carrinho::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->first();
        if ($itemCarrinho) {
            $itemCarrinho->delete();
        }
    }

    public function atualizarQuantidadeProdutoNoCarrinho(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $itemCarrinho = Carrinho::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->with('produto')
            ->firstOrFail();

        // Se clicou em + ou -
        if ($request->filled('acao')) {

            if ($request->acao === 'mais') {
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

        // Atualiza preço total
        $precoUnitario = (float) $itemCarrinho->produto->preco;
        $itemCarrinho->preco_total = $itemCarrinho->quantidade * $precoUnitario;

        $itemCarrinho->save();

        return back();
    }

    public function selecionarCidade()
    {
        $cidade = Cidade::all();

        return $cidade;
    }

    public function toggleSelecionarProdutoNoCarrinho($id)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $itemCarrinho = Carrinho::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        $itemCarrinho->selecionado = !$itemCarrinho->selecionado;
        $itemCarrinho->save();

        return back();
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
            $endereco = Endereco::where('id', $opcaoEndereco)
                ->where('usuario_id', $usuarioId)
                ->first();

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

        if (!$cidadeId || !Cidade::whereKey($cidadeId)->exists()) {
            Session::flash('checkout.modal', 'enderecoNovoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::NAO_ENCONTRAMOS_ENDERECO,
            ];
        }

        $endereco = Endereco::create([
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

        $quantidadeEnderecos = Endereco::where('usuario_id', $usuarioId)->count();
        if ($quantidadeEnderecos <= 1) {
            Session::flash('checkout.modal', 'enderecoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Mantenha pelo menos um endereço cadastrado.',
            ];
        }

        $endereco = Endereco::where('id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

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

        $carrinhoItems = Carrinho::where('selecionado', true)
            ->where('usuario_id', $usuarioId)
            ->get();


        foreach ($carrinhoItems as $item) {
            $precoUnitario = $item->preco_total / $item->quantidade;

            ItemPedido::create([
                'usuario_id' => $usuarioId,
                'produto_id' => $item->produto_id,
                'quantidade' => $item->quantidade,
                'preco_unitario' => $precoUnitario,
                'status_da_comanda' => 'em_aberto',
                'pago_em' => null,
                'pedido_id' => $resultado,
                'mesa_id' => $mesaId,
            ]);
        }

        if ($mesaId) {
            $mesa = Mesa::find($mesaId);
            if ($mesa) {
                $totalAberto = (float) ItemPedido::query()
                    ->where('mesa_id', $mesaId)
                    ->where('status_da_comanda', 'em_aberto')
                    ->get()
                    ->sum(function ($item) {
                        return ((float) $item->preco_unitario) * ((int) $item->quantidade);
                    });

                $mesa->preco = $totalAberto;
                $mesa->status = 'Ocupada';
                $mesa->save();
            }
        }

        Carrinho::where('usuario_id', $usuarioId)->where('selecionado', true)->delete();

        Session::forget('checkout.mesa_id');
        Session::forget('checkout.endereco_id');
        Session::forget('checkout.tipo_entrega');
        Session::forget('checkout.pagamento');
        Session::forget('checkout.modal');
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

        $mesaId = Session::get('checkout.mesa_id');
        $tipoEntrega = $request->input('tipo_entrega') ?? Session::get('checkout.tipo_entrega');
        if (!$tipoEntrega) {
            $tipoEntrega = $mesaId ? 'retirar' : 'entrega';
        }

        $usuarioId = Auth::id();

        $valor_total = Carrinho::where('usuario_id', $usuarioId)
            ->where('selecionado', true)
            ->sum('preco_total');

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

        if ($tipoEntrega === 'retirar' && !$mesaId) {
            Session::flash('checkout.modal', 'mesaModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Selecione uma mesa para retirada.',
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
            $pedido = Pedido::create([
                'usuario_id' => $usuarioId,
                'endereco_id' => $enderecoId,
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

    public function selecionarMesaNoCarrinho($id){
        if (!Auth::check()) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => ErroMensagens::PRECISA_ESTA_LOGADO,
            ];
        }

        $mesa = Mesa::find($id);
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
        ];

        if (!in_array($statusNormalizado, $statusPermitidos, true)) {
            Session::flash('checkout.modal', 'mesaModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Mesa indisponível para retirada.',
            ];
        }

        Session::put('checkout.mesa_id', $mesa->id);
        Session::put('checkout.tipo_entrega', 'retirar');
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
}
