<?php

namespace App\Repositoryimpl;

use App\Enum\StatusPedidos;
use App\Models\ItemPedidoModel;
use App\Models\MesaModel;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Repository\GarcomRepository;
use Illuminate\Support\Facades\DB;



class GarcomRepositoryimpl implements GarcomRepository
{
    public function adicionarProdutoAoPedido($produtoId, $mesaId, $quantidade, $usuarioLogado)
    {
        return $this->adicionarProdutosAoPedido([[
            'produto_id' => $produtoId,
            'quantidade' => $quantidade,
        ]], (int) $mesaId, $usuarioLogado);
    }

    public function adicionarProdutosAoPedido(array $itens, int $mesaId, $usuarioLogado): bool
    {
        $usuarioId = $usuarioLogado->usuario_id ?? $usuarioLogado->id ?? null;

        return DB::transaction(function () use ($itens, $mesaId, $usuarioId) {
            $pedidoIdAberto = ItemPedidoModel::query()
                ->whereHas('pedido', function ($q) use ($mesaId) {
                    $q->where('mesa_id', $mesaId);
                })
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
                    'mesa_id' => $mesaId,
                    'status' => StatusPedidos::PENDENTE->value,
                    'valor_total' => 0,
                ]);
            }

            $itensNormalizados = collect($itens)
                ->map(fn ($item) => [
                    'produto_id' => (int) ($item['produto_id'] ?? 0),
                    'quantidade' => max(1, (int) ($item['quantidade'] ?? 1)),
                ])
                ->filter(fn ($item) => $item['produto_id'] > 0)
                ->groupBy('produto_id')
                ->map(fn ($grupo, $produtoId) => [
                    'produto_id' => (int) $produtoId,
                    'quantidade' => (int) $grupo->sum('quantidade'),
                ])
                ->values();

            if ($itensNormalizados->isEmpty()) {
                return false;
            }

            $produtos = ProdutoModel::query()
                ->whereIn('id', $itensNormalizados->pluck('produto_id')->all())
                ->get()
                ->keyBy('id');

            $totalAdicionado = 0.0;

            foreach ($itensNormalizados as $item) {
                $produto = $produtos->get($item['produto_id']);
                if (!$produto) {
                    continue;
                }

                $precoUnitario = (float) ($produto->preco ?? 0);
                $quantidade = (int) $item['quantidade'];

                $itemPedido = ItemPedidoModel::query()
                    ->where('pedido_id', $pedido->id)
                    ->where('produto_id', $produto->id)
                    ->where('status_da_comanda', 'em_aberto')
                    ->where(function ($query) {
                        $query->whereNull('valor_pago')
                            ->orWhere('valor_pago', '<=', 0);
                    })
                    ->first();

                if ($itemPedido) {
                    $itemPedido->quantidade += $quantidade;
                    $itemPedido->save();
                } else {
                    ItemPedidoModel::create([
                        'pedido_id' => $pedido->id,
                        'produto_id' => $produto->id,
                        'quantidade' => $quantidade,
                        'preco_unitario' => $precoUnitario,
                        'status_da_comanda' => 'em_aberto',
                    ]);
                }

                $totalAdicionado += $precoUnitario * $quantidade;
            }

            $pedido->valor_total = (float) ($pedido->valor_total ?? 0) + $totalAdicionado;
            $pedido->save();

            $mesa = MesaModel::find($mesaId);
            if ($mesa) {
                $totalMesa = (float) ItemPedidoModel::query()
                    ->whereHas('pedido', function ($q) use ($mesaId) {
                        $q->where('mesa_id', $mesaId);
                    })
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
        });
    }

}
