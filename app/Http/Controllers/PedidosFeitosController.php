<?php

namespace App\Http\Controllers;

use App\Enum\StatusPedidos as EnumsStatusPedidos;
use App\Mensagens\PassMensagens;
use App\Models\Pedido;
use App\Models\PedidoModel;
use App\Services\GenericBase;
use App\Services\PedidoService;
use App\Services\PedidosFeitosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class PedidosFeitosController extends Controller
{
    protected GenericBase $genericBase;
    protected PedidosFeitosService $pedidosFeitosService;
    protected PedidoService $pedidosService;

    public function __construct(GenericBase $genericBase, PedidosFeitosService $pedidosFeitosService, PedidoService $pedidosService)
    {
        $this->genericBase = $genericBase;
        $this->pedidosFeitosService = $pedidosFeitosService;
        $this->pedidosService = $pedidosService;
    }

    public function verPedidosAdmin()
    {
        return view('Admin.Pedidos', $this->pedidosService->montarDadosPainel());
    }

    public function atualizarStatus(Request $request, PedidoModel $pedido): JsonResponse|RedirectResponse
    {
        $dados = $request->validate([
            'status' => ['required', Rule::in(array_column($this->pedidosFeitosService->opcoesStatus(), 'value'))],
        ]);

        $novoStatus = EnumsStatusPedidos::from((int) $dados['status']);

        $pedidoAtualizado = $this->pedidosFeitosService->atualizarStatus($pedido, $novoStatus);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => PassMensagens::ATUALIZADO_STATUS,
                'pedido' => $pedidoAtualizado,
            ]);
        }

        return redirect()->back()->with('sucesso', PassMensagens::ATUALIZADO_STATUS . ' ' . $this->pedidosFeitosService->rotulo($novoStatus) . '.');
    }

    public function avancarStatus(PedidoModel $pedido): RedirectResponse
    {

        $statusAtual = EnumsStatusPedidos::tryFrom((int) $pedido->status) ?? EnumsStatusPedidos::PENDENTE;
        $proximo = $this->pedidosFeitosService->proximoStatus($statusAtual);

        if (!$proximo) {
            return redirect()->back()->with('erro', PassMensagens::ATUALIZADO_STATUS_FINAL);
        }

        $this->pedidosFeitosService->atualizarStatus($pedido, $proximo);

        return redirect()->back()->with('sucesso', PassMensagens::STATUS_AVANCADO . ' ' . $this->pedidosFeitosService->rotulo($proximo) . '.');
    }

    public function gerarCumpom(PedidoModel $pedido)
    {
        $cupom = $this->pedidosFeitosService->gerarCumpom($pedido->id);
        $dadosEmpresa = $this->pedidosFeitosService->PegarDadosEmpresa();
        return view('Admin.Notinha', [
            'cupom' => $cupom,
            'dadosEmpresa' => $dadosEmpresa,
            'pedido' => $pedido
        ]);
    }



    public function somNovoPedido(): BinaryFileResponse
    {
        return response()->file(
            app_path('sons/jekyll85-60s-telephone-bells-196165_sd1qeg9h.mp3'),
            [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'private, max-age=86400',
            ]
        );
    }
}
