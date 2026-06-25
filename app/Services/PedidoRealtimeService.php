<?php

namespace App\Services;

use App\Events\PedidosAtualizados;
use Illuminate\Support\Facades\Log;

class PedidoRealtimeService
{
    public function __construct(private PedidoService $pedidoService)
    {
    }

    public function payload(?int $pedidoId = null): array
    {
        $dados = $this->pedidoService->checksumBasico();

        return [
            'pedidoId' => $pedidoId,
            'checksum' => $dados['checksum'],
            'total' => $dados['total'],
            'pendentes' => $dados['pendentes'],
            'ultimoPendenteId' => $dados['ultimoPendenteId'],
            'totalLabel' => $dados['total'] . ' pedidos ativos',
            'emittedAt' => now()->toIso8601String(),
        ];
    }

    public function broadcast(?int $pedidoId = null): void
    {
        try {
            event(new PedidosAtualizados($this->payload($pedidoId)));
        } catch (\Throwable $e) {
            Log::warning('Nao foi possivel enviar atualizacao de pedidos por WebSocket.', [
                'pedido_id' => $pedidoId,
                'exception' => $e,
            ]);
        }
    }
}
