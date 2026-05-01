<?php

namespace App\Services;

use App\Models\PedidoModel;
use App\Repository\AdminRepository;

/**
 * Serviço para gerenciar Pedidos com Auditoria automática
 */
class PedidoComAuditoriaService
{
    protected AdminRepository $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * Cria novo pedido e registra na auditoria
     */
    public function criarPedido(array $dados): PedidoModel
    {
        // Cria o pedido
        $pedido = PedidoModel::create($dados);

        // Registra na auditoria
        AuditoriaService::registrarCriacao(
            'pedido',
            [
                'id' => $pedido->id,
                'usuario_id' => $pedido->usuario_id,
                'valor_total' => $pedido->valor_total,
                'status' => $pedido->status,
            ]
        );

        return $pedido;
    }

    /**
     * Atualiza pedido e registra na auditoria
     */
    public function atualizarPedido(int $pedidoId, array $dadosNovos): PedidoModel
    {
        $pedido = PedidoModel::findOrFail($pedidoId);
        $dadosAntigos = $pedido->only(array_keys($dadosNovos));

        // Atualiza
        $pedido->update($dadosNovos);

        // Registra na auditoria
        AuditoriaService::registrarAtualizacao(
            'pedido',
            $dadosAntigos,
            $dadosNovos
        );

        return $pedido;
    }

    /**
     * Muda status do pedido com auditoria
     */
    public function mudarStatusPedido(
        int $pedidoId,
        string $statusNovo,
        string $motivo = ''
    ): PedidoModel {
        $pedido = PedidoModel::findOrFail($pedidoId);
        $statusAntigo = $pedido->status;

        // Atualiza o status
        $pedido->update(['status' => $statusNovo]);

        // Registra na auditoria
        AuditoriaService::registrarMudancaStatusPedido(
            $pedidoId,
            $statusAntigo,
            $statusNovo,
            $motivo
        );

        return $pedido;
    }

    /**
     * Registra um pagamento de pedido
     */
    public function registrarPagamento(
        int $pedidoId,
        string $statusPagamento,
        array $dadosPagamento
    ): PedidoModel {
        $pedido = PedidoModel::findOrFail($pedidoId);

        // Atualiza o pedido
        $pedido->update([
            'status' => 'pagamento_processado',
            'tipo_pagamento_id' => $dadosPagamento['tipo_pagamento_id'] ?? null,
        ]);

        // Registra na auditoria
        AuditoriaService::registrarPagamento(
            $pedidoId,
            $statusPagamento,
            [
                'tipo' => $dadosPagamento['tipo'] ?? 'desconhecido',
                'valor' => $dadosPagamento['valor'] ?? 0,
                'referencia' => $dadosPagamento['referencia'] ?? null,
            ]
        );

        return $pedido;
    }

    /**
     * Exclui pedido e registra na auditoria
     */
    public function excluirPedido(int $pedidoId): void
    {
        $pedido = PedidoModel::findOrFail($pedidoId);

        $dadosPedido = $pedido->toArray();

        // Exclui
        $pedido->delete();

        // Registra na auditoria
        AuditoriaService::registrarExclusao(
            'pedido',
            $dadosPedido
        );
    }

    /**
     * Visualiza pedido (auditoria passiva)
     */
    public function visualizarPedido(int $pedidoId): PedidoModel
    {
        $pedido = PedidoModel::findOrFail($pedidoId);

        // Registra visualização
        AuditoriaService::registrarVisualizacao('pedido', $pedidoId);

        return $pedido;
    }
}
