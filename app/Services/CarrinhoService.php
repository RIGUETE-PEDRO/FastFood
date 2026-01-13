<?php

namespace App\Services;

use App\Enum\Pagamento;
use App\Enum\StatusPedidos;
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

class CarrinhoService
{
    public function adicionarProdutoAoCarrinho($usuario, $produtoId, $quantidade, $observacao, $preco)
    {
        $GenericBase = new GenericBase();
        $quantidadeNormalizada = $quantidade;


        $usuarioId = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        if (!$usuarioId) {
            throw new \InvalidArgumentException('Usuário inválido para adicionar ao carrinho.');
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

            if ($request->acao === 'menos' && $itemCarrinho->quantidade > 1) {
                $itemCarrinho->quantidade--;
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

    public function selecionarCidade(Request $request)
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
                'mensagem' => 'Faça login para selecionar um endereço.',
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
                    'mensagem' => 'Não encontramos o endereço selecionado. Escolha outro ou cadastre um novo.',
                ];
            }

            Session::put('checkout.endereco_id', $endereco->id);
            Session::put('checkout.cidade_id', optional($endereco->cidade)->id);
            Session::flash('checkout.modal', 'pagamentoModal');

            return [
                'status' => true,
                'tipo' => 'success',
                'mensagem' => 'Endereço selecionado com sucesso!',
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
                'mensagem' => 'Informe pelo menos bairro e rua para cadastrar um novo endereço.',
            ];
        }

        if (!$cidadeId || !Cidade::whereKey($cidadeId)->exists()) {
            Session::flash('checkout.modal', 'enderecoNovoModal');
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Selecione uma cidade válida.',
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
        Session::flash('checkout.modal', 'pagamentoModal');

        return [
            'status' => true,
            'tipo' => 'success',
            'mensagem' => 'Endereço cadastrado com sucesso!',
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
                'mensagem' => 'Endereço não encontrado para exclusão.',
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
            'mensagem' => 'Endereço excluído com sucesso.',
        ];
    }

    public function limparCarrinhoAposPedido($request,$resultado){
        $usuarioId = Auth::id();

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
                'pedido_id' => $resultado,
            ]);
        }

        Carrinho::where('usuario_id', $usuarioId)->where('selecionado', true)->delete();


    }
    

    public function registraPedido($request , $enderecoId)
    {
        if (!Auth::check()) {
            return [
                'status' => false,
                'tipo' => 'error',
                'mensagem' => 'Você precisa fazer login.',
            ];
        }

        $usuarioId = Auth::id();

        $valor_total = Carrinho::where('usuario_id', $usuarioId)
            ->where('selecionado', true)
            ->sum('preco_total');

        $pagamentoMetodo = $request->input('pagamento_metodo');
        $pagamentoenum = Pagamento::fromString($pagamentoMetodo);


    try{
        $pedido = Pedido::create([
            'usuario_id' => $usuarioId,
            'endereco_id' => $enderecoId,
            'tipo_pagamento_id' => $pagamentoenum->value,
            'observacoes_pagamento' => $request->input('observacoes_pagamento'),
            'valor_total' => $valor_total,
            'status' => StatusPedidos::PENDENTE->value
        ]);

        $resposta = $pedido->id;
        return $resposta;

    } catch (\Exception $e) {
        throw new \Exception('Erro ao registrar o pedido.');
    }
    $resposta = false;

    return $resposta;

    }




}
