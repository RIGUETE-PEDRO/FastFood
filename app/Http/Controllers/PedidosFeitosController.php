<?php

namespace App\Http\Controllers;

use App\Enum\StatusPedidos as EnumsStatusPedidos;
use App\Mensagens\PassMensagens;
use App\Models\Pedido;
use App\Models\PedidoModel;
use App\Models\ConfiguracaoImpressao;
use App\Roles\Roles;
use App\Services\GenericBase;
use App\Services\PedidoService;
use App\Services\PedidosFeitosService;
use App\Services\SecureKeyService;
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
        return view('Admin.Pedidos', array_merge($this->pedidosService->montarDadosPainel(), [
            'configuracoesImpressao' => ConfiguracaoImpressao::atuais(),
            'podeGerenciarPedidos' => app(SecureKeyService::class)->hasRole(auth()->user(), Roles::PEDIDOS),
            'podeCancelarPedidos' => app(SecureKeyService::class)->hasRole(auth()->user(), Roles::PEDIDOS_CANCELAR),
        ]));
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

        if ($statusAtual === EnumsStatusPedidos::PENDENTE
            && $proximo === EnumsStatusPedidos::EM_PREPARO
            && (ConfiguracaoImpressao::atuais()['autoPrintOnAccept'] ?? false)) {
            $cupom = $this->pedidosFeitosService->gerarCumpom($pedido->id);
            return view('Admin.Notinha', [
                'cupom' => $cupom,
                'dadosEmpresa' => $this->pedidosFeitosService->PegarDadosEmpresa(),
                'pedido' => $pedido->fresh(),
                'configuracoesImpressao' => ConfiguracaoImpressao::atuais(),
                'imprimirAutomaticamente' => true,
            ]);
        }

        return redirect()->back()->with('sucesso', PassMensagens::STATUS_AVANCADO . ' ' . $this->pedidosFeitosService->rotulo($proximo) . '.');
    }

    public function cancelar(PedidoModel $pedido): RedirectResponse
    {
        $this->pedidosFeitosService->cancelarPedido($pedido);

        return redirect()->back()->with('sucesso', 'Pedido cancelado com sucesso.');
    }

    public function gerarCumpom(PedidoModel $pedido)
    {
        $cupom = $this->pedidosFeitosService->gerarCumpom($pedido->id);
        $dadosEmpresa = $this->pedidosFeitosService->PegarDadosEmpresa();
        return view('Admin.Notinha', [
            'cupom' => $cupom,
            'dadosEmpresa' => $dadosEmpresa,
            'pedido' => $pedido,
            'configuracoesImpressao' => ConfiguracaoImpressao::atuais(),
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
