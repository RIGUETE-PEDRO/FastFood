<?php

namespace App\Repositoryimpl;

use App\Models\PedidoModel;
use App\Repository\PedidosFeitosRepository;
use Illuminate\Support\Collection;

class PedidosFeitosRepositoryimpl implements PedidosFeitosRepository
{
    public function listarPedidos(): Collection
    {
        return PedidoModel::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
            'motoboy',
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
            'motoboy',
        ]);
    }

    public function buscarPedidoPorId($pedidoId): ?PedidoModel
    {
        return PedidoModel::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
            'motoboy',
        ])->find($pedidoId);
    }
}
