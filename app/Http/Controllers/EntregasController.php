<?php

namespace App\Http\Controllers;

use App\Enum\StatusPedidos as EnumsStatusPedidos;
use App\Models\PedidoModel;
use App\Services\PedidosFeitosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EntregasController extends Controller
{
    public function __construct(private PedidosFeitosService $pedidosFeitosService)
    {
    }

    public function index()
    {
        $usuarioLogado = Auth::user();
        $nomeUsuario = $usuarioLogado?->nome ? explode(' ', trim($usuarioLogado->nome))[0] : 'Entregador';

        $pedidosEntrega = $this->pedidosFeitosService->listarPedidos()
            ->filter(function ($pedido) {
                return !is_null($pedido->endereco_id);
            })
            ->map(function ($pedido) {
                $statusEnum = EnumsStatusPedidos::tryFrom((int) $pedido->status) ?? EnumsStatusPedidos::PENDENTE;
                $pedido->status_enum = $statusEnum;
                $pedido->status_label = $this->pedidosFeitosService->rotulo($statusEnum);
                return $pedido;
            })
            ->values();

        $pedidosAbertos = $pedidosEntrega
            ->filter(fn($pedido) => in_array($pedido->status_enum, [EnumsStatusPedidos::PENDENTE, EnumsStatusPedidos::EM_PREPARO, EnumsStatusPedidos::A_CAMINHO], true))
            ->filter(fn($pedido) => is_null($pedido->motoboy_id))
            ->values();

        $pedidosFinalizados = $pedidosEntrega
            ->filter(fn($pedido) => in_array($pedido->status_enum, [EnumsStatusPedidos::ENTREGUE, EnumsStatusPedidos::CANCELADO], true))
            ->values();

        $pedidosAceitos = $pedidosEntrega
            ->filter(fn($pedido) => !is_null($pedido->motoboy_id))
            ->filter(fn($pedido) => in_array($pedido->status_enum, [EnumsStatusPedidos::PENDENTE, EnumsStatusPedidos::EM_PREPARO, EnumsStatusPedidos::A_CAMINHO], true))
            ->sortByDesc(fn($pedido) => $pedido->motoboy_vinculado_em ?? $pedido->updated_at)
            ->values();

        return view('Admin.Entregas', [
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $nomeUsuario,
            'pedidosAbertos' => $pedidosAbertos,
            'pedidosFinalizados' => $pedidosFinalizados,
            'pedidosAceitos' => $pedidosAceitos,
            'totalPedidosEntrega' => $pedidosEntrega->count(),
        ]);
    }

    public function aceitar(PedidoModel $pedido): RedirectResponse
    {
        $usuarioLogado = Auth::user();
        $tipoMensagem = 'success';
        $mensagem = 'Entrega aceita com sucesso.';

        if (!$usuarioLogado) {
            $tipoMensagem = 'error';
            $mensagem = 'Usuário não autenticado.';
        } else {
            $statusAtual = EnumsStatusPedidos::tryFrom((int) $pedido->status) ?? EnumsStatusPedidos::PENDENTE;
            $usuarioId = (int) ($usuarioLogado->id ?? 0);

            $pedidoNaoEntrega = is_null($pedido->endereco_id);
            $statusInvalidoParaAceite = $statusAtual !== EnumsStatusPedidos::A_CAMINHO;
            $vinculadoOutroMotoboy = (int) ($pedido->motoboy_id ?? 0) !== 0 && (int) $pedido->motoboy_id !== $usuarioId;
            $jaVinculadoAoMesmoMotoboy = (int) ($pedido->motoboy_id ?? 0) === $usuarioId;

            if ($pedidoNaoEntrega) {
                $tipoMensagem = 'error';
                $mensagem = 'Esse pedido não é de entrega.';
            } elseif ($statusInvalidoParaAceite) {
                $tipoMensagem = 'error';
                $mensagem = 'Motoboy só pode aceitar quando o pedido estiver na etapa de entrega.';
            } elseif ($vinculadoOutroMotoboy) {
                $tipoMensagem = 'error';
                $mensagem = 'Esse pedido já foi aceito por outro motoboy.';
            } elseif ($jaVinculadoAoMesmoMotoboy) {
                $mensagem = 'Esse pedido já está com você.';
            } else {
                $pedido->motoboy_id = $usuarioId;
                $pedido->motoboy_vinculado_em = now();
                $pedido->status = EnumsStatusPedidos::A_CAMINHO->value;
                $pedido->save();
            }
        }

        return redirect()->back()->with($tipoMensagem, $mensagem);
    }

    public function finalizar(PedidoModel $pedido): RedirectResponse
    {
        $usuarioLogado = Auth::user();
        $tipoMensagem = 'success';
        $mensagem = 'Entrega finalizada com sucesso.';

        if (!$usuarioLogado) {
            $tipoMensagem = 'error';
            $mensagem = 'Usuário não autenticado.';
        } else {
            $statusAtual = EnumsStatusPedidos::tryFrom((int) $pedido->status) ?? EnumsStatusPedidos::PENDENTE;
            $usuarioId = (int) ($usuarioLogado->id ?? 0);

            $pedidoNaoEntrega = is_null($pedido->endereco_id);
            $naoEstaComMotoboyAtual = (int) ($pedido->motoboy_id ?? 0) !== $usuarioId;
            $statusInvalidoParaFinalizar = $statusAtual !== EnumsStatusPedidos::A_CAMINHO;

            if ($pedidoNaoEntrega) {
                $tipoMensagem = 'error';
                $mensagem = 'Esse pedido não é de entrega.';
            } elseif ($naoEstaComMotoboyAtual) {
                $tipoMensagem = 'error';
                $mensagem = 'Você só pode finalizar pedidos vinculados a você.';
            } elseif ($statusInvalidoParaFinalizar) {
                $tipoMensagem = 'error';
                $mensagem = 'Só é possível finalizar pedidos em entrega.';
            } else {
                $pedido->status = EnumsStatusPedidos::ENTREGUE->value;
                $pedido->save();
            }
        }

        return redirect()->back()->with($tipoMensagem, $mensagem);
    }
}
