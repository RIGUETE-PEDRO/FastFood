<?php

namespace App\Services;

use App\Enum\StatusPedidos as EnumsStatusPedidos;
use App\Models\PedidoModel;
use App\Repositoryimpl\PedidosFeitosRepositoryimpl;
use Illuminate\Support\Collection;

class PedidosFeitosService
{
    public function __construct(private PedidosFeitosRepositoryimpl $repository)
    {
    }

    /**
     * Lista pedidos com todas as relações necessárias para o painel administrativo.
     */
    public function listarPedidos(): Collection
    {
        return $this->repository->listarPedidos();
    }

    /**
     * Atualiza o status do pedido.
     */
    public function atualizarStatus(PedidoModel $pedido, EnumsStatusPedidos $status): PedidoModel
    {
        return $this->repository->salvarStatus($pedido, $status->value);
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
