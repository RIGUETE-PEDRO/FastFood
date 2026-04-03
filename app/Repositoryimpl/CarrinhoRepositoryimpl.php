<?php

namespace App\Repositoryimpl;


use App\Models\CarrinhoModel;
use App\Models\CidadeModel;
use App\Models\EnderecoModel;
use App\Models\ItemPedidoModel;
use App\Models\MesaModel;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Repository\CarrinhoRepository;
use Illuminate\Support\Collection;

class CarrinhoRepositoryimpl implements CarrinhoRepository
{
    public  function pegarMesaSelecionada($statusPermitidos)
    {
        return MesaModel::query()
            ->whereIn('status', $statusPermitidos)
            ->orderBy('numero_da_mesa')
            ->get();
    }

    public function pegarEnderecosDoUsuario($usuarioId)
    {
        return EnderecoModel::with('cidade')
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function pegarMesaPorId(int $id): ?MesaModel
    {
        return MesaModel::find($id);
    }

    public function buscarProdutoPorId(int $produtoId): ?ProdutoModel
    {
        return ProdutoModel::find($produtoId);
    }

    public function buscarItemCarrinhoPorUsuarioEProduto(int $usuarioId, int $produtoId): ?CarrinhoModel
    {
        return CarrinhoModel::where('usuario_id', $usuarioId)
            ->where('produto_id', $produtoId)
            ->first();
    }

    public function criarItemCarrinho(array $dados): CarrinhoModel
    {
        return CarrinhoModel::create($dados);
    }

    public function buscarItemCarrinhoComProduto(int $id, int $usuarioId): ?CarrinhoModel
    {
        return CarrinhoModel::where('id', $id)
            ->where('usuario_id', $usuarioId)
            ->with('produto')
            ->first();
    }

    public function buscarItemCarrinho(int $id, int $usuarioId): ?CarrinhoModel
    {
        return CarrinhoModel::where('id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();
    }

    public function listarCidades()
    {
        return CidadeModel::all();
    }

    public function cidadeExiste(int $cidadeId): bool
    {
        return CidadeModel::whereKey($cidadeId)->exists();
    }

    public function buscarEnderecoPorIdEUsuario(int $enderecoId, int $usuarioId): ?EnderecoModel
    {
        return EnderecoModel::where('id', $enderecoId)
            ->where('usuario_id', $usuarioId)
            ->first();
    }

    public function criarEndereco(array $dados): EnderecoModel
    {
        return EnderecoModel::create($dados);
    }

    public function quantidadeEnderecosUsuario(int $usuarioId): int
    {
        return EnderecoModel::where('usuario_id', $usuarioId)->count();
    }

    public function listarItensSelecionadosCarrinho(int $usuarioId): Collection
    {
        return CarrinhoModel::where('selecionado', true)
            ->where('usuario_id', $usuarioId)
            ->get();
    }

    public function criarItemPedido(array $dados): ItemPedidoModel
    {
        return ItemPedidoModel::create($dados);
    }

    public function calcularTotalAbertoMesa(int $mesaId): float
    {
        return (float) ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->get()
            ->sum(function ($item) {
                return ((float) $item->preco_unitario) * ((int) $item->quantidade);
            });
    }

    public function removerItensSelecionadosCarrinho(int $usuarioId): void
    {
        CarrinhoModel::where('usuario_id', $usuarioId)
            ->where('selecionado', true)
            ->delete();
    }

    public function somarValorSelecionadoCarrinho(int $usuarioId): float
    {
        return (float) CarrinhoModel::where('usuario_id', $usuarioId)
            ->where('selecionado', true)
            ->sum('preco_total');
    }

    public function criarPedido(array $dados): PedidoModel
    {
        return PedidoModel::create($dados);
    }
}
