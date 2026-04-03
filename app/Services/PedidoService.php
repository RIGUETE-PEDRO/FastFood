<?php

namespace App\Services;

use App\Models\Pedido;
use App\Enums\EnumsStatusPedidos;
use App\App\Enum\StatusPedidos;
use App\Enum\StatusPedidos as EnumStatusPedidos;
use Illuminate\Validation\Rules\Enum;

class PedidoService
{
    protected EnumStatusPedidos $enumsStatusPedidos;
    protected GenericBase $genericBase;
    protected PedidosFeitosService $service;

    public function __construct(GenericBase $genericBase, PedidosFeitosService $service, EnumStatusPedidos $enumsStatusPedidos)
    {
        $this->genericBase = $genericBase;
        $this->service = $service;
        $this->enumsStatusPedidos = $enumsStatusPedidos;
    }

    public function checksumBasico(): array
    {
        $snapshot = Pedido::query()
            ->select(['id', 'status', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($pedido) => [
                'id' => (int) $pedido->id,
                'status' => (int) $pedido->status,
                'updated_at' => optional($pedido->updated_at)?->toIso8601String(),
            ])
            ->values();

        return [
            'checksum' => md5($snapshot->toJson()),
            'total' => $snapshot->count(),
        ];
    }

    public function dadosResumo(): array
    {
        $dados = $this->montarDadosPainel();

        return [
            'checksum' => $dados['realtimeChecksum'],
            'total' => $dados['totalPedidos'],
            'dashboardCards' => $dados['dashboardCards'],
            'pedidos' => $dados['pedidos'],
            'pedidosPorStatus' => $dados['pedidosPorStatus'],
            'statusOptions' => $dados['statusOptions'],
            'statusTimeline' => $dados['statusTimeline'],
            'statusLabels' => $dados['statusLabels'],
        ];
    }

    public function pegarPedidosDoUsuario($usuarioId)
    {
        $pedidos = Pedido::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento'
        ])
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();

        return $pedidos;
    }

    public function montarDadosPainel(): array
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $primeiroNome = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : null;

        $pedidosCollection = $this->service->listarPedidos();

        $pedidos = $pedidosCollection->map(function (Pedido $pedido) {
            $statusEnum = $this->enumsStatusPedidos::tryFrom((int) $pedido->status) ?? $this->enumsStatusPedidos::PENDENTE;

            $pedido->status_enum = $statusEnum;
            $pedido->status_label = $this->service->rotulo($statusEnum);
            $pedido->next_status = $this->service->proximoStatus($statusEnum);

            return $pedido;
        });

        $contagens = $pedidos->groupBy(fn($pedido) => $pedido->status_enum->value ?? 0)->map->count();
        $dashboardCards = [
            [
                'label' => 'Pedidos pendentes',
                'valor' => $contagens->get($this->enumsStatusPedidos::PENDENTE->value, 0),
                'accent' => 'card-resumo--pendente',
            ],
            [
                'label' => 'Em preparo',
                'valor' => $contagens->get($this->enumsStatusPedidos::EM_PREPARO->value, 0),
                'accent' => 'card-resumo--preparo',
            ],
            [
                'label' => 'A caminho',
                'valor' => $contagens->get($this->enumsStatusPedidos::A_CAMINHO->value, 0),
                'accent' => 'card-resumo--expedicao',
            ],
            [
                'label' => 'Entregues',
                'valor' => $contagens->get($this->enumsStatusPedidos::ENTREGUE->value, 0),
                'accent' => 'card-resumo--entregue',
            ],
        ];

        $statusOptions = $this->service->opcoesStatus();
        $statusLabels = collect($statusOptions)->pluck('label', 'value')->toArray();
        $statusTimeline = array_map(function (EnumStatusPedidos $status) {
            return [
                'enum' => $status,
                'value' => $status->value,
                'label' => $this->service->rotulo($status),
            ];
        }, array_values(array_filter($this->enumsStatusPedidos::cases(), fn($status) => $status !== $this->enumsStatusPedidos::CANCELADO)));

        $pedidosPorStatus = [
            'abertos' => $pedidos->filter(fn(Pedido $pedido) => in_array($pedido->status_enum, [$this->enumsStatusPedidos::PENDENTE, $this->enumsStatusPedidos::EM_PREPARO, $this->enumsStatusPedidos::A_CAMINHO], true))->values(),
            'finalizados' => $pedidos->filter(fn(Pedido $pedido) => in_array($pedido->status_enum, [$this->enumsStatusPedidos::ENTREGUE, $this->enumsStatusPedidos::CANCELADO], true))->values(),
        ];

        $realtimeChecksum = md5($pedidosCollection
            ->map(fn(Pedido $pedido) => [
                'id' => (int) $pedido->id,
                'status' => (int) $pedido->status,
                'updated_at' => optional($pedido->updated_at)?->toIso8601String(),
            ])
            ->values()
            ->toJson());

        return [
            'pedidos' => $pedidos,
            'statusOptions' => $statusOptions,
            'statusTimeline' => $statusTimeline,
            'statusLabels' => $statusLabels,
            'pedidosPorStatus' => $pedidosPorStatus,
            'dashboardCards' => $dashboardCards,
            'totalPedidos' => $pedidosCollection->count(),
            'realtimeChecksum' => $realtimeChecksum,
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $usuarioLogado?->tipo_descricao ?? null,
        ];
    }
}
