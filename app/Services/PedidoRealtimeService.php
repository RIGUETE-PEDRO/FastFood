<?php

namespace App\Services;

use App\Events\PedidosAtualizados;
use Illuminate\Support\Facades\Log;

class PedidoRealtimeService
{
    private const CSRF_PLACEHOLDER = '__FLASHFOOD_CSRF_TOKEN__';

    public function __construct(private PedidoService $pedidoService)
    {
    }

    public function payload(?int $pedidoId = null): array
    {
        $dados = $this->pedidoService->dadosResumo();

        return [
            'pedidoId' => $pedidoId,
            'checksum' => $dados['checksum'],
            'total' => $dados['total'],
            'pendentes' => $dados['pendentes'],
            'ultimoPendenteId' => $dados['ultimoPendenteId'],
            'totalLabel' => $dados['total'] . ' pedidos ativos',
            'resumoHtml' => $this->trocarTokenCsrfPorMarcador(view('Admin.partials.pedidos-resumo-cards', [
                'dashboardCards' => $dados['dashboardCards'],
            ])->render()),
            'listaHtml' => $this->trocarTokenCsrfPorMarcador(view('Admin.partials.pedidos-lista', [
                'pedidos' => $dados['pedidos'],
                'pedidosPorStatus' => $dados['pedidosPorStatus'],
                'statusOptions' => $dados['statusOptions'],
                'statusTimeline' => $dados['statusTimeline'],
                'statusLabels' => $dados['statusLabels'],
            ])->render()),
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

    private function trocarTokenCsrfPorMarcador(string $html): string
    {
        return preg_replace(
            '/(<input\b(?=[^>]*\bname=["\']_token["\'])(?=[^>]*\btype=["\']hidden["\'])[^>]*\bvalue=)(["\'])(.*?)\2/i',
            '$1$2' . self::CSRF_PLACEHOLDER . '$2',
            $html
        ) ?? $html;
    }
}
