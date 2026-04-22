<?php

namespace App\Repositoryimpl;

use App\Enum\StatusPedidos;
use App\Models\ItemPedidoModel;
use App\Models\MesaModel;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Repository\GarcomRepository;



class GarcomRepositoryimpl implements GarcomRepository
{
    public function adicionarProdutoAoPedido($produtoId, $mesaId, $quantidade, $usuarioLogado)
    {
        $usuarioId = $usuarioLogado->usuario_id ?? $usuarioLogado->id ?? null;

        $pedidoIdAberto = ItemPedidoModel::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->whereNotNull('pedido_id')
            ->latest('id')
            ->value('pedido_id');

        $pedido = null;
        if ($pedidoIdAberto) {
            $pedido = PedidoModel::query()
                ->where('id', $pedidoIdAberto)
                ->whereNotIn('status', [
                    StatusPedidos::ENTREGUE->value,
                    StatusPedidos::CANCELADO->value,
                ])
                ->first();
        }

        if (!$pedido) {
            $pedido = PedidoModel::create([
                'usuario_id' => $usuarioId,
                'status' => StatusPedidos::PENDENTE->value,
                'valor_total' => 0,
            ]);
        }

        $produto = ProdutoModel::findOrFail($produtoId);
        $quantidade = max(1, (int) $quantidade);
        $precoUnitario = (float) ($produto->preco ?? 0);

        $itemPedido = ItemPedidoModel::where('pedido_id', $pedido->id)
            ->where('produto_id', $produtoId)
            ->first();

        if ($itemPedido) {
            $itemPedido->quantidade += $quantidade;
            $itemPedido->save();
        } else {
            ItemPedidoModel::create([
                'pedido_id' => $pedido->id,
                'produto_id' => $produtoId,
                'quantidade' => $quantidade,
                'usuario_id' => $usuarioId,
                'mesa_id' => $mesaId,
                'preco_unitario' => $precoUnitario,
            ]);
        }

        $pedido->valor_total = (float) ($pedido->valor_total ?? 0) + ($precoUnitario * $quantidade);
        $pedido->save();

        $mesa = MesaModel::find($mesaId);
        if ($mesa) {
            $totalMesa = (float) ItemPedidoModel::query()
                ->where('mesa_id', $mesaId)
                ->where('status_da_comanda', 'em_aberto')
                ->get()
                ->sum(function ($item) {
                    $total = ((float) $item->preco_unitario) * ((int) $item->quantidade);
                    $pago = (float) ($item->valor_pago ?? 0);
                    $restante = $total - $pago;
                    return $restante > 0 ? $restante : 0;
                });

            $mesa->preco = $totalMesa;
            if ($totalMesa > 0) {
                $mesa->status = 'Ocupada';
            }
            $mesa->save();
        }

        return true;
    }

}
