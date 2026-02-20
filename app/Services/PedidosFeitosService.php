<?php

namespace App\Services;

use App\Enum\StatusPedidos as EnumsStatusPedidos;
use App\Models\Pedido;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;

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
    public function atualizarStatus(Pedido $pedido, EnumsStatusPedidos $status): Pedido
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
    public function proximoStatus(EnumsStatusPedidos $status): ?EnumsStatusPedidos
    {
        return match ($status) {
            EnumsStatusPedidos::PENDENTE => EnumsStatusPedidos::EM_PREPARO,
            EnumsStatusPedidos::EM_PREPARO => EnumsStatusPedidos::A_CAMINHO,
            EnumsStatusPedidos::A_CAMINHO => EnumsStatusPedidos::ENTREGUE,
            default => null,
        };
    }

    /**
     * Retorna o rótulo amigável do status.
     */
    public function rotulo(EnumsStatusPedidos $status): string
    {
        return match ($status) {
            EnumsStatusPedidos::PENDENTE => 'Pendente',
            EnumsStatusPedidos::EM_PREPARO => 'Em preparo',
            EnumsStatusPedidos::A_CAMINHO => 'A caminho',
            EnumsStatusPedidos::ENTREGUE => 'Entregue',
            EnumsStatusPedidos::CANCELADO => 'Cancelado',
        };
    }

    /**
     * Lista de status disponíveis com rótulos.
     */
    public function opcoesStatus(): array
    {
        return array_map(function (EnumsStatusPedidos $status) {
            return [
                'value' => $status->value,
                'label' => $this->rotulo($status),
            ];
        }, EnumsStatusPedidos::cases());
    }
}
