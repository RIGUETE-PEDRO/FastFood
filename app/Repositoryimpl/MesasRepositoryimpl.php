<?php

namespace App\Repositoryimpl;

use App\Enum\StatusPedidos;
use App\Models\FormaPagamentoModel;
use App\Models\ItemPedidoModel;
use App\Models\MesaModel;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use Illuminate\Support\Collection;

class MesasRepositoryimpl
{
    public function pegarPedidoIdsDaMesa(int $mesaId): Collection
    {
        return ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->whereNotNull('pedido_id')
            ->pluck('pedido_id')
            ->unique()
            ->values();
    }

    public function finalizarPedidosPorIds(Collection $pedidoIds): void
    {
        PedidoModel::query()
            ->whereIn('id', $pedidoIds)
            ->whereNotIn('status', [
                StatusPedidos::ENTREGUE->value,
                StatusPedidos::CANCELADO->value,
            ])
            ->update(['status' => StatusPedidos::ENTREGUE->value]);
    }

    public function listarMesas()
    {
        return MesaModel::all();
    }

    public function listarFormasPagamento()
    {
        return FormaPagamentoModel::query()->orderBy('tipo_pagamento')->get();
    }

    public function pegarMesaPorId(int $id): ?MesaModel
    {
        return MesaModel::find($id);
    }

    public function pegarProdutosDisponiveis(array $excluirProdutoIds = []): Collection
    {
        $query = ProdutoModel::query()
            ->where('disponivel', true)
            ->orderBy('nome');

        if (!empty($excluirProdutoIds)) {
            $query->whereNotIn('id', $excluirProdutoIds);
        }

        return $query->get();
    }

    public function pegarItensContaMesa(int $mesaId, bool $pago): Collection
    {
        $status = $pago ? 'pago' : 'em_aberto';

        return ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', $status)
            ->with('produto')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function listarItensAbertosMesa(int $mesaId): Collection
    {
        return ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->get();
    }

    public function pegarItensAbertosSelecionados(int $mesaId, array $itemIds): Collection
    {
        return ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->whereIn('id', $itemIds)
            ->get(['id', 'preco_unitario', 'quantidade', 'valor_pago']);
    }

    public function pegarItensParaAbatimento(int $mesaId, array $itemIds): Collection
    {
        return ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->whereIn('id', $itemIds)
            ->orderBy('id')
            ->get();
    }

    public function pegarItemAbertoDaMesa(int $mesaId, int $itemId): ?ItemPedidoModel
    {
        return ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->where('id', $itemId)
            ->first();
    }

    public function salvarItem(ItemPedidoModel $item): void
    {
        $item->save();
    }

    public function removerItem(ItemPedidoModel $item): void
    {
        $item->delete();
    }

    public function atualizarTotaisPedido(int $pedidoId): void
    {
        $pedido = PedidoModel::find($pedidoId);
        if (!$pedido) {
            return;
        }

        $total = (float) ItemPedidoModel::query()
            ->where('pedido_id', $pedidoId)
            ->selectRaw('COALESCE(SUM(preco_unitario * quantidade), 0) as total')
            ->value('total');

        $qtdItens = (int) ItemPedidoModel::query()
            ->where('pedido_id', $pedidoId)
            ->count();

        $pedido->valor_total = $total;
        if ($qtdItens === 0) {
            $pedido->status = StatusPedidos::CANCELADO->value;
        }

        $pedido->save();
    }

    public function criarItemPedido(array $dados): ItemPedidoModel
    {
        return ItemPedidoModel::create($dados);
    }

    public function existemItensAbertosMesa(int $mesaId): bool
    {
        return ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->exists();
    }

    public function desvincularMesaItensPagos(int $mesaId): void
    {
        ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'pago')
            ->update(['mesa_id' => null]);
    }

    public function existeNumeroMesa(int $numeroMesa, ?int $ignorarId = null): bool
    {
        $query = MesaModel::where('numero_da_mesa', $numeroMesa);

        if ($ignorarId !== null) {
            $query->where('id', '!=', $ignorarId);
        }

        return $query->exists();
    }

    public function criarMesa(array $dados): MesaModel
    {
        return MesaModel::create($dados);
    }

    public function removerMesa(int $id): void
    {
        $mesa = MesaModel::find($id);
        if ($mesa) {
            $mesa->delete();
        }
    }

    public function pegarProdutosMesa(int $id)
    {
        return ProdutoModel::where('mesa_id', $id)->get();
    }
}
