<?php

namespace App\Repositoryimpl;

use App\Models\PedidoModel;

class PedidoRepositoryimpl
{
    public function listarParaChecksum()
    {
        return PedidoModel::query()
            ->select(['id', 'status', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function pegarPedidosDoUsuario(int $usuarioId)
    {
        return PedidoModel::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
        ])
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();
    }
}
