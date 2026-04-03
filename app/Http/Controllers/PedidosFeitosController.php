<?php

namespace App\Http\Controllers;

use App\Enum\StatusPedidos as EnumsStatusPedidos;
use App\Mensagens\PassMensagens;
use App\Models\Pedido;
use App\Services\GenericBase;
use App\Services\PedidosFeitosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class PedidosFeitosController extends Controller
{
    protected GenericBase $genericBase;
    protected PedidosFeitosService $service;

    public function __construct(GenericBase $genericBase, PedidosFeitosService $service)
    {
        $this->genericBase = $genericBase;
        $this->service = $service;
    }

    public function verPedidosAdmin()
    {
        return view('Admin.Pedidos', $this->montarDadosPainel());
    }

    public function atualizarStatus(Request $request, Pedido $pedido): JsonResponse|RedirectResponse
    {
        $dados = $request->validate([
            'status' => ['required', Rule::in(array_column($this->service->opcoesStatus(), 'value'))],
        ]);

        $novoStatus = EnumsStatusPedidos::from((int) $dados['status']);

        $pedidoAtualizado = $this->service->atualizarStatus($pedido, $novoStatus);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => PassMensagens::ATUALIZADO_STATUS,
                'pedido' => $pedidoAtualizado,
            ]);
        }

        return redirect()->back()->with('sucesso', PassMensagens::ATUALIZADO_STATUS . ' ' . $this->service->rotulo($novoStatus) . '.');
    }

    public function avancarStatus(Pedido $pedido): RedirectResponse
    {
        // Nesta rota o Route Model Binding retorna o model sem atributos "derivados" (ex: status_enum).
        // O status persistido fica em $pedido->status.
        $statusAtual = EnumsStatusPedidos::tryFrom((int) $pedido->status) ?? EnumsStatusPedidos::PENDENTE;
        $proximo = $this->service->proximoStatus($statusAtual);

        if (!$proximo) {
            return redirect()->back()->with('erro', PassMensagens::ATUALIZADO_STATUS_FINAL);
        }

        $this->service->atualizarStatus($pedido, $proximo);

        return redirect()->back()->with('sucesso', PassMensagens::STATUS_AVANCADO . ' ' . $this->service->rotulo($proximo) . '.');
    }

    public function pollResumo(Request $request): JsonResponse
    {
        $snapshot = Pedido::query()
            ->select(['id', 'status', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Pedido $pedido) => [
                'id' => (int) $pedido->id,
                'status' => (int) $pedido->status,
                'updated_at' => optional($pedido->updated_at)?->toIso8601String(),
            ])
            ->values();

        $checksum = md5($snapshot->toJson());

        if (!$request->boolean('full')) {
            return response()->json([
                'checksum' => $checksum,
                'total' => $snapshot->count(),
            ]);
        }

        $dados = $this->montarDadosPainel();

        return response()->json([
            'checksum' => $dados['realtimeChecksum'] ?? $checksum,
            'total' => $dados['totalPedidos'] ?? $snapshot->count(),
            'totalLabel' => ($dados['totalPedidos'] ?? $snapshot->count()) . ' pedidos ativos',
            'resumoHtml' => view('Admin.partials.pedidos-resumo-cards', [
                'dashboardCards' => $dados['dashboardCards'],
            ])->render(),
            'listaHtml' => view('Admin.partials.pedidos-lista', [
                'pedidos' => $dados['pedidos'],
                'pedidosPorStatus' => $dados['pedidosPorStatus'],
                'statusOptions' => $dados['statusOptions'],
                'statusTimeline' => $dados['statusTimeline'],
                'statusLabels' => $dados['statusLabels'],
            ])->render(),
        ]);
    }

    private function montarDadosPainel(): array
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $primeiroNome = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : null;

        $pedidosCollection = $this->service->listarPedidos();

        $pedidos = $pedidosCollection->map(function (Pedido $pedido) {
            $statusEnum = EnumsStatusPedidos::tryFrom((int) $pedido->status) ?? EnumsStatusPedidos::PENDENTE;

            $pedido->status_enum = $statusEnum;
            $pedido->status_label = $this->service->rotulo($statusEnum);
            $pedido->next_status = $this->service->proximoStatus($statusEnum);

            return $pedido;
        });

        $contagens = $pedidos->groupBy(fn ($pedido) => $pedido->status_enum->value ?? 0)->map->count();
        $dashboardCards = [
            [
                'label' => 'Pedidos pendentes',
                'valor' => $contagens->get(EnumsStatusPedidos::PENDENTE->value, 0),
                'accent' => 'card-resumo--pendente',
            ],
            [
                'label' => 'Em preparo',
                'valor' => $contagens->get(EnumsStatusPedidos::EM_PREPARO->value, 0),
                'accent' => 'card-resumo--preparo',
            ],
            [
                'label' => 'A caminho',
                'valor' => $contagens->get(EnumsStatusPedidos::A_CAMINHO->value, 0),
                'accent' => 'card-resumo--expedicao',
            ],
            [
                'label' => 'Entregues',
                'valor' => $contagens->get(EnumsStatusPedidos::ENTREGUE->value, 0),
                'accent' => 'card-resumo--entregue',
            ],
        ];

        $statusOptions = $this->service->opcoesStatus();
        $statusLabels = collect($statusOptions)->pluck('label', 'value')->toArray();
        $statusTimeline = array_map(function (EnumsStatusPedidos $status) {
            return [
                'enum' => $status,
                'value' => $status->value,
                'label' => $this->service->rotulo($status),
            ];
        }, array_values(array_filter(EnumsStatusPedidos::cases(), fn (EnumsStatusPedidos $status) => $status !== EnumsStatusPedidos::CANCELADO)));

        $pedidosPorStatus = [
            'abertos' => $pedidos->filter(fn (Pedido $pedido) => in_array($pedido->status_enum, [EnumsStatusPedidos::PENDENTE, EnumsStatusPedidos::EM_PREPARO, EnumsStatusPedidos::A_CAMINHO], true))->values(),
            'finalizados' => $pedidos->filter(fn (Pedido $pedido) => in_array($pedido->status_enum, [EnumsStatusPedidos::ENTREGUE, EnumsStatusPedidos::CANCELADO], true))->values(),
        ];

        $realtimeChecksum = md5($pedidosCollection
            ->map(fn (Pedido $pedido) => [
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
