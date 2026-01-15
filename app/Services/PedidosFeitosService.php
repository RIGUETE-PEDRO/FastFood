<?php

namespace App\Services;

use App\Enum\StatusPedidos;
use App\Models\Pedido;
use Illuminate\Support\Collection;

class PedidosFeitosService
{
    /**
     * Lista pedidos com todas as relações necessárias para o painel administrativo.
     */
    public function listarPedidos(): Collection
    {
        return Pedido::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
        ])->orderByDesc('created_at')->get();
    }

    /**
     * Atualiza o status do pedido.
     */
    public function atualizarStatus(Pedido $pedido, StatusPedidos $status): Pedido
    {
        $pedido->status = $status->value;
        $pedido->save();

        return $pedido->fresh([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
        ]);
    }

    /**
     * Retorna o próximo status válido para avançar o pedido.
     */
    public function proximoStatus(StatusPedidos $status): ?StatusPedidos
    {
        return match ($status) {
            StatusPedidos::PENDENTE => StatusPedidos::EM_PREPARO,
            StatusPedidos::EM_PREPARO => StatusPedidos::A_CAMINHO,
            StatusPedidos::A_CAMINHO => StatusPedidos::ENTREGUE,
            default => null,
        };
    }

    /**
     * Retorna o rótulo amigável do status.
     */
    public function rotulo(StatusPedidos $status): string
    {
        return match ($status) {
            StatusPedidos::PENDENTE => 'Pendente',
            StatusPedidos::EM_PREPARO => 'Em preparo',
            StatusPedidos::A_CAMINHO => 'A caminho',
            StatusPedidos::ENTREGUE => 'Entregue',
            StatusPedidos::CANCELADO => 'Cancelado',
        };
    }

    /**
     * Lista de status disponíveis com rótulos.
     */
    public function opcoesStatus(): array
    {
        return array_map(function (StatusPedidos $status) {
            return [
                'value' => $status->value,
                'label' => $this->rotulo($status),
            ];
        }, StatusPedidos::cases());
    }
}