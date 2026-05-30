<?php

namespace App\Services;

use App\Enum\StatusPedidos as EnumStatusPedidos;
use App\Models\PedidoModel;
use App\Models\UsuarioModel;
use App\Repository\PedidoRepository;

use Illuminate\Http\Request;

class PedidoService
{
    protected GenericBase $genericBase;
    protected PedidosFeitosService $service;
    protected PedidoRepository $repository;

    public function __construct(GenericBase $genericBase, PedidosFeitosService $service, PedidoRepository $repository)
    {
        $this->genericBase = $genericBase;
        $this->service = $service;
        $this->repository = $repository;
    }

    public function filtrarPedidosDataNome(Request $request){
        $data = $request->dia;
        $nome = $request->cliente;

        return $this->repository->filtrarPedidosDataNome($data, $nome);
    }

    public function checksumBasico(): array
    {
        $snapshot = $this->repository->listarParaChecksum()
            ->map(fn($pedido) => [
                'id' => (int) $pedido->id,
                'status' => (int) $pedido->status,
                'updated_at' => optional($pedido->updated_at)?->toIso8601String(),
            ])
            ->values();
        $pendentes = $snapshot->where('status', EnumStatusPedidos::PENDENTE->value)->values();

        return [
            'checksum' => md5($snapshot->toJson()),
            'total' => $snapshot->count(),
            'pendentes' => $pendentes->count(),
            'ultimoPendenteId' => $pendentes->max('id'),
        ];
    }

    public function dadosResumo(): array
    {
        $dados = $this->montarDadosPainel();

        return [
            'checksum' => $dados['realtimeChecksum'],
            'total' => $dados['totalPedidos'],
            'pendentes' => $dados['pedidosPendentes'],
            'ultimoPendenteId' => $dados['ultimoPedidoPendenteId'],
            'dashboardCards' => $dados['dashboardCards'],
            'pedidos' => $dados['pedidos'],
            'pedidosPorStatus' => $dados['pedidosPorStatus'],
            'statusOptions' => $dados['statusOptions'],
            'statusTimeline' => $dados['statusTimeline'],
            'statusLabels' => $dados['statusLabels'],
        ];
    }

    public function pegarPedidosDoUsuario(UsuarioModel|int $usuario)
    {

        $usuarioId = $usuario instanceof UsuarioModel
            ? (int) $usuario->id
            : (int) $usuario;

        $pedidos = $this->repository->pegarPedidosDoUsuario($usuarioId);

        return $pedidos;
    }

    public function montarDadosPainel(): array
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $primeiroNome = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : null;

        $pedidosCollection = $this->service->listarPedidos();

        $pedidos = $pedidosCollection->map(function (PedidoModel $pedido) {
            $statusEnum = EnumStatusPedidos::tryFrom((int) $pedido->status) ?? EnumStatusPedidos::PENDENTE;

            $pedido->status_enum = $statusEnum;
            $pedido->status_label = $this->service->rotulo($statusEnum);
            $pedido->next_status = $this->service->proximoStatus($statusEnum);

            return $pedido;
        });

        $contagens = $pedidos->groupBy(fn($pedido) => $pedido->status_enum->value ?? 0)->map->count();
        $dashboardCards = [
            [
                'label' => 'Pedidos pendentes',
                'valor' => $contagens->get(EnumStatusPedidos::PENDENTE->value, 0),
                'accent' => 'card-resumo--pendente',
            ],
            [
                'label' => 'Em preparo',
                'valor' => $contagens->get(EnumStatusPedidos::EM_PREPARO->value, 0),
                'accent' => 'card-resumo--preparo',
            ],
            [
                'label' => 'A caminho',
                'valor' => $contagens->get(EnumStatusPedidos::A_CAMINHO->value, 0),
                'accent' => 'card-resumo--expedicao',
            ],
            [
                'label' => 'Entregues',
                'valor' => $contagens->get(EnumStatusPedidos::ENTREGUE->value, 0),
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
        }, array_values(array_filter(EnumStatusPedidos::cases(), fn($status) => $status !== EnumStatusPedidos::CANCELADO)));

        $pedidosPorStatus = [
            'abertos' => $pedidos->filter(fn(PedidoModel $pedido) => in_array($pedido->status_enum, [EnumStatusPedidos::PENDENTE, EnumStatusPedidos::EM_PREPARO, EnumStatusPedidos::A_CAMINHO], true))->values(),
            'finalizados' => $pedidos->filter(fn(PedidoModel $pedido) => in_array($pedido->status_enum, [EnumStatusPedidos::ENTREGUE, EnumStatusPedidos::CANCELADO], true))->values(),
        ];

        $realtimeChecksum = md5($pedidosCollection
            ->map(fn(PedidoModel $pedido) => [
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
            'pedidosPendentes' => $contagens->get(EnumStatusPedidos::PENDENTE->value, 0),
            'ultimoPedidoPendenteId' => optional($pedidos->where('status_enum', EnumStatusPedidos::PENDENTE)->sortByDesc('id')->first())->id,
            'realtimeChecksum' => $realtimeChecksum,
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $usuarioLogado?->tipo_descricao ?? null,
        ];
    }
}
