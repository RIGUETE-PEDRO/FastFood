<?php

namespace App\Http\Controllers;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Models\FormaPagamento;
use App\Models\ItemPedido;
use App\Services\GenericBase;
use App\Services\MesasService;
use Illuminate\Http\Request;


class MesaController extends Controller
{
    protected GenericBase $genericBase;
    protected MesasService $mesasService;


    public function __construct(GenericBase $genericBase, MesasService $mesasService)
    {
        $this->genericBase = $genericBase;
        $this->mesasService = $mesasService;
    }


    public function Mesa()
    {

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        $mesas = $this->mesasService->pegarMesas();

        return view('Admin.Mesa', ['usuario' => $usuarioLogado, 'mesas' => $mesas]);
    }

    public function cadastrarMesa(Request $request)
    {
        if ($request->input('numero_da_mesa') < 1) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
        }
        $response = $this->mesasService->cadastrarMesa($request);
        if ($response) {
            return $response;
        }

        return redirect()->back()->with('success', PassMensagens::MESA_CADASTRADA_SUCESSO);
    }

    public function ListarMesa(Request $request)
    {
        $mesas = $this->mesasService->pegarMesas();
        return view('Admin.Mesa', ['mesas' => $mesas]);
    }

    public function removerMesa(Request $request)
    {

        $id = $request->input('mesa_id');


        if (!$id) {
            return redirect()->back()->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $this->mesasService->removerMesa($id);

        return redirect()->route('mesas.index')->with('success', PassMensagens::MESA_REMOVIDA_SUCESSO);
    }

    public function atualizarMesa(Request $request)
    {
        $response = $this->mesasService->atualizarMesa($request);
        if ($response) {
            return $response;
        }

        return redirect()->back()->with('success', PassMensagens::MESA_ATUALIZADA_SUCESSO);
    }

    public function detalhesMesa($id)
    {
        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();

        $mesasService = $this->mesasService;
        $mesa = $mesasService->pegarMesaPorId($id);

        if (!$mesa) {
            return redirect()->route('mesas.index')->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $itensAbertos = $mesasService->pegarItensContaMesa($id, false);
        $itensPagos = $mesasService->pegarItensContaMesa($id, true);
        $totalAberto = $mesasService->calcularTotalItens($itensAbertos);

        $formasPagamento = FormaPagamento::query()->orderBy('tipo_pagamento')->get();

        return view('Admin.DetalhesMesa', [
            'usuario' => $usuarioLogado,
            'mesa' => $mesa,
            'itensAbertos' => $itensAbertos,
            'itensPagos' => $itensPagos,
            'totalAberto' => $totalAberto,
            'formasPagamento' => $formasPagamento,
        ]);
    }

    public function abaterItensContaMesa(Request $request, $id)
    {
        $itemIds = $request->input('item_ids', []);
        $pagamentoMetodo = (string) $request->input('pagamento_metodo');
        $valorPagamentoRaw = (string) $request->input('valor_pagamento_total');
        $quantidades = (array) $request->input('quantidades', []);
        $metodosPermitidos = ['cartao_credito', 'cartao_debito', 'pix', 'dinheiro'];

        if ($pagamentoMetodo === '' || !in_array($pagamentoMetodo, $metodosPermitidos, true)) {
            return redirect()->back()->with('error', 'Selecione uma forma de pagamento para abater.');
        }

        $parseValor = function (string $raw): ?float {
            $raw = trim($raw);
            if ($raw === '') {
                return null;
            }

            // Remove tudo que não for dígito, ponto ou vírgula.
            $raw = preg_replace('/[^0-9\.,]/', '', $raw) ?? '';
            if ($raw === '') {
                return null;
            }

            // Se tiver vírgula, assume vírgula como decimal (pt-BR) e remove pontos de milhar.
            if (str_contains($raw, ',')) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            }

            if (!is_numeric($raw)) {
                return null;
            }

            $v = (float) $raw;
            return $v > 0 ? $v : null;
        };

        $valorPagamento = $parseValor($valorPagamentoRaw);
        if ($valorPagamento === null) {
            return redirect()->back()->with('error', 'Digite o valor total do pagamento para abater.');
        }

        $itens = ItemPedido::query()
            ->where('mesa_id', $id)
            ->where('status_da_comanda', 'em_aberto')
            ->whereIn('id', (array) $itemIds)
            ->get(['id', 'preco_unitario', 'quantidade', 'valor_pago']);

        if ($itens->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum item válido selecionado para abater.');
        }

        $totalCalculado = 0.0;
        foreach ($itens as $item) {
            $qtdMax = (int) $item->quantidade;
            $qtd = (int) ($quantidades[$item->id] ?? 0);
            if ($qtd < 1 || $qtd > $qtdMax) {
                return redirect()->back()->with('error', 'Informe uma quantidade válida para cada item selecionado.');
            }

            $unit = (float) $item->preco_unitario;
            $lineTotal = $unit * $qtd;
            $valorPago = (float) ($item->valor_pago ?? 0);

            // Se existe valor_pago, este item deve ser unidade (qtdMax=1). Considera apenas o restante.
            if ($valorPago > 0) {
                if ($qtdMax !== 1 || $qtd !== 1) {
                    return redirect()->back()->with('error', 'Para itens com pagamento parcial, abata a quantidade 1 (unidade).');
                }

                $restante = $unit - $valorPago;
                if ($restante <= 0) {
                    continue;
                }
                $totalCalculado += $restante;
            } else {
                $totalCalculado += $lineTotal;
            }
        }

        if (round($valorPagamento, 2) > round($totalCalculado, 2)) {
            return redirect()->back()->with('error', 'O valor digitado não pode ser maior que o total selecionado.');
        }

        $mesasService = new MesasService();

        $erro = $mesasService->abaterPorValor($id, (array) $itemIds, $quantidades, (float) $valorPagamento, $pagamentoMetodo);
        if ($erro) {
            if (str_starts_with($erro, 'warn:')) {
                return redirect()->back()->with('success', substr($erro, 5));
            }

            return redirect()->back()->with('error', $erro);
        }

        return redirect()->back()->with('success', 'Itens abatidos (pagos) com sucesso.');
    }
}
