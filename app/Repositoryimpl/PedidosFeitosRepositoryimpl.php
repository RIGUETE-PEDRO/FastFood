<?php

namespace App\Repositoryimpl;

use App\Models\PedidoModel;
use Illuminate\Support\Collection;

class PedidosFeitosRepositoryimpl
{
    public function listarPedidos(): Collection
    {
        return PedidoModel::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
        ])->orderByDesc('created_at')->get();
    }

    public function salvarStatus(PedidoModel $pedido, int $status): PedidoModel
    {
        $pedido->status = $status;
        $pedido->save();

        return $pedido->fresh([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
        ]);
    }
}
