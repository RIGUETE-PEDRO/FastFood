<?php

namespace App\Http\Controllers;

use App\Enum\StatusPedidos;
use App\Models\Pedido;
use App\Services\PedidosFeitosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class PedidosFeitosController extends Controller
{
    public function __construct(private PedidosFeitosService $service)
    {
    }

    public function verPedidosAdmin()
    {
        $usuario = session('usuario_logado');
        $primeiroNome = $usuario ? explode(' ', trim($usuario->nome))[0] : null;

        $pedidosCollection = $this->service->listarPedidos();

        $pedidos = $pedidosCollection->map(function (Pedido $pedido) {
            $statusEnum = StatusPedidos::tryFrom((int) $pedido->status) ?? StatusPedidos::PENDENTE;

            $pedido->status_enum = $statusEnum;
            $pedido->status_label = $this->service->rotulo($statusEnum);
            $pedido->next_status = $this->service->proximoStatus($statusEnum);

            return $pedido;
        });

        $contagens = $pedidos->groupBy(fn ($pedido) => $pedido->status_enum->value ?? 0)->map->count();
        $dashboardCards = [
            [
                'label' => 'Pedidos pendentes',
                'valor' => $contagens->get(StatusPedidos::PENDENTE->value, 0),
                'accent' => 'card-resumo--pendente',
            ],
            [
                'label' => 'Em preparo',
                'valor' => $contagens->get(StatusPedidos::EM_PREPARO->value, 0),
                'accent' => 'card-resumo--preparo',
            ],
            [
                'label' => 'A caminho',
                'valor' => $contagens->get(StatusPedidos::A_CAMINHO->value, 0),
                'accent' => 'card-resumo--expedicao',
            ],
            [
                'label' => 'Entregues',
                'valor' => $contagens->get(StatusPedidos::ENTREGUE->value, 0),
                'accent' => 'card-resumo--entregue',
            ],
        ];

        $statusOptions = $this->service->opcoesStatus();
        $statusLabels = collect($statusOptions)->pluck('label', 'value')->toArray();
        $statusTimeline = array_map(function (StatusPedidos $status) {
            return [
                'enum' => $status,
                'value' => $status->value,
                'label' => $this->service->rotulo($status),
            ];
        }, array_values(array_filter(StatusPedidos::cases(), fn (StatusPedidos $status) => $status !== StatusPedidos::CANCELADO)));

        $pedidosPorStatus = [
            'abertos' => $pedidos->filter(fn (Pedido $pedido) => in_array($pedido->status_enum, [StatusPedidos::PENDENTE, StatusPedidos::EM_PREPARO, StatusPedidos::A_CAMINHO], true))->values(),
            'finalizados' => $pedidos->filter(fn (Pedido $pedido) => in_array($pedido->status_enum, [StatusPedidos::ENTREGUE, StatusPedidos::CANCELADO], true))->values(),
        ];

        return view('Admin.Pedidos', [
            'pedidos' => $pedidos,
            'statusOptions' => $statusOptions,
            'statusTimeline' => $statusTimeline,
            'statusLabels' => $statusLabels,
            'pedidosPorStatus' => $pedidosPorStatus,
            'dashboardCards' => $dashboardCards,
            'totalPedidos' => $pedidosCollection->count(),
            'usuario' => $usuario,
            'nomeUsuario' => $primeiroNome,
            'tipoUsuario' => $usuario?->tipo_descricao ?? null,
        ]);
    }

    public function atualizarStatus(Request $request, Pedido $pedido): JsonResponse|RedirectResponse
    {
        $dados = $request->validate([
            'status' => ['required', Rule::in(array_column($this->service->opcoesStatus(), 'value'))],
        ]);

        $novoStatus = StatusPedidos::from((int) $dados['status']);

        $pedidoAtualizado = $this->service->atualizarStatus($pedido, $novoStatus);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Status atualizado com sucesso.',
                'pedido' => $pedidoAtualizado,
            ]);
        }

        return redirect()->back()->with('sucesso', 'Status atualizado para ' . $this->service->rotulo($novoStatus) . '.');
    }

    public function avancarStatus(Pedido $pedido): RedirectResponse
    {
        $statusAtual = StatusPedidos::tryFrom((int) $pedido->status) ?? StatusPedidos::PENDENTE;
        $proximo = $this->service->proximoStatus($statusAtual);

        if (!$proximo) {
            return redirect()->back()->with('erro', 'O pedido já está no status final.');
        }

        $this->service->atualizarStatus($pedido, $proximo);

        return redirect()->back()->with('sucesso', 'Status avançado para ' . $this->service->rotulo($proximo) . '.');
    }
}