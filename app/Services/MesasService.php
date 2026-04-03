<?php

namespace App\Services;

use App\Enum\StatusPedidos;
use App\Models\Mesa;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Models\FormaPagamento;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;

class MesasService extends GenericBase
{
    private function normalizarMetodoPagamento(?string $metodo): ?string
    {
        $metodo = trim((string) $metodo);
        if ($metodo === '') {
            return null;
        }

        return match ($metodo) {
            'cartao_credito', 'cartao_debito', 'cartao' => 'cartao',
            default => $metodo,
        };
    }

    private function combinarMetodosPagamento(?string $existente, ?string $novo): ?string
    {
        $novoNormalizado = $this->normalizarMetodoPagamento($novo);
        if ($novoNormalizado === null) {
            return $existente;
        }

        $partes = collect(preg_split('/\s*\+\s*/', (string) $existente) ?: [])
            ->map(fn($m) => $this->normalizarMetodoPagamento($m))
            ->filter(fn($m) => !empty($m))
            ->values()
            ->all();

        if (!in_array($novoNormalizado, $partes, true)) {
            $partes[] = $novoNormalizado;
        }

        return implode(' + ', $partes);
    }

    private function finalizarPedidosDaMesa(int $mesaId): void
    {
        $pedidoIds = ItemPedido::query()
            ->where('mesa_id', $mesaId)
            ->whereNotNull('pedido_id')
            ->pluck('pedido_id')
            ->unique()
            ->values();

        if ($pedidoIds->isEmpty()) {
            return;
        }

        Pedido::query()
            ->whereIn('id', $pedidoIds)
            ->whereNotIn('status', [
                StatusPedidos::ENTREGUE->value,
                StatusPedidos::CANCELADO->value,
            ])
            ->update(['status' => StatusPedidos::ENTREGUE->value]);
    }

    public function pegarMesas()
    {
        return Mesa::all();
    }

    public function pegarDetalhesMesa($id)
    {
        $mesa = $this->pegarMesaPorId($id);
        if (!$mesa) {
            return redirect()->route('mesas.index')->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $itensAbertos = $this->pegarItensContaMesa($id, false);
        $itensPagos = $this->pegarItensContaMesa($id, true);
        $totalAberto = $this->calcularTotalItens($itensAbertos);

        $formasPagamento = FormaPagamento::query()->orderBy('tipo_pagamento')->get();

        return [
            'mesa' => $mesa,
            'itensAbertos' => $itensAbertos,
            'itensPagos' => $itensPagos,
            'totalAberto' => $totalAberto,
            'formasPagamento' => $formasPagamento,
        ];
    }

    public function pegarMesaPorId($id): ?Mesa
    {
        return Mesa::find($id);
    }

    public function pegarProdutosDisponiveis(array $excluirProdutoIds = []): Collection
    {
        $query = Produto::query()
            ->where('disponivel', true)
            ->orderBy('nome');

        if (!empty($excluirProdutoIds)) {
            $query->whereNotIn('id', $excluirProdutoIds);
        }

        return $query->get();
    }

    public function pegarItensContaMesa($mesaId, bool $pago): Collection
    {
        $status = $pago ? 'pago' : 'em_aberto';

        return ItemPedido::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', $status)
            ->with('produto')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function calcularTotalItens(Collection $itens): float
    {
        return (float) $itens->sum(function ($item) {
            $total = ((float) $item->preco_unitario) * ((int) $item->quantidade);
            $pago = (float) ($item->valor_pago ?? 0);
            $restante = $total - $pago;
            return $restante > 0 ? $restante : 0;
        });
    }

    public function atualizarPrecoMesa($mesaId): void
    {
        $mesa = Mesa::find($mesaId);
        if (!$mesa) {
            return;
        }

        $itensAbertos = ItemPedido::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->get();

        $mesa->preco = $this->calcularTotalItens($itensAbertos);
        $mesa->save();
    }

    public function abaterItensContaMesa($request, $id)
    {
        $itemIds = $request->input('item_ids', []);
        $pagamentoMetodo = (string) $request->input('pagamento_metodo');
        $valorPagamentoRaw = (string) $request->input('valor_pagamento_total');
        $quantidades = (array) $request->input('quantidades', []);

        $metodosPermitidos = ['cartao_credito', 'cartao_debito', 'pix', 'dinheiro'];

        if ($pagamentoMetodo === '' || !in_array($pagamentoMetodo, $metodosPermitidos, true)) {
            return ['status' => false, 'mensagem' => ErroMensagens::SELECIONE_FORMA_PAGAMENTO];
        }

        $valorPagamento = $this->parseValor($valorPagamentoRaw);
        if ($valorPagamento === null) {
            return ['status' => false, 'mensagem' => ErroMensagens::DIGITE_VALOR_PAGAMENTO_TOTAL];
        }

        $itens = ItemPedido::query()
            ->where('mesa_id', $id)
            ->where('status_da_comanda', 'em_aberto')
            ->whereIn('id', (array) $itemIds)
            ->get(['id', 'preco_unitario', 'quantidade', 'valor_pago']);

        if ($itens->isEmpty()) {
            return ['status' => false, 'mensagem' => ErroMensagens::NENHUM_ITEM_SELECIONADO];
        }

        $totalCalculado = 0.0;

        foreach ($itens as $item) {
            $qtdMax = (int) $item->quantidade;
            $qtd = (int) ($quantidades[$item->id] ?? 0);

            if ($qtd < 1 || $qtd > $qtdMax) {
                return ['status' => false, 'mensagem' => ErroMensagens::QUANTIDADE_MINIMA];
            }

            $unit = (float) $item->preco_unitario;
            $valorPago = (float) ($item->valor_pago ?? 0);

            if ($valorPago > 0) {
                $restante = $unit - $valorPago;
                if ($restante > 0) {
                    $totalCalculado += $restante;
                }
            } else {
                $totalCalculado += $unit * $qtd;
            }
        }

        if (round($valorPagamento, 2) > round($totalCalculado, 2)) {
            return ['status' => false, 'mensagem' => ErroMensagens::VALOR_PAGAMENTO_EXCEDE_TOTAL];
        }

        $erro = $this->abaterPorValor($id, $itemIds, $quantidades, $valorPagamento, $pagamentoMetodo);

        if ($erro) {
            return ['status' => false, 'mensagem' => $erro];
        }

        return ['status' => true, 'mensagem' => PassMensagens::PAGAMENTO_REALIZADO_SUCESSO];
    }

    private function parseValor(string $raw): ?float
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        $raw = preg_replace('/[^0-9\.,]/', '', $raw);

        if (str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        return is_numeric($raw) && (float)$raw > 0 ? (float)$raw : null;
    }


    public function abaterPorValor($mesaId, array $itemIds, array $quantidadesPorItem, float $valorPagamento, ?string $pagamentoMetodo = null): ?string
    {
        if (empty($itemIds)) {
            return 'Selecione pelo menos um item para abater.';
        }

        $valorCents = (int) round($valorPagamento * 100);
        if ($valorCents <= 0) {
            return 'Digite um valor de pagamento válido.';
        }

        return DB::transaction(function () use ($mesaId, $itemIds, $quantidadesPorItem, $valorCents, $pagamentoMetodo) {
            $itens = ItemPedido::query()
                ->where('mesa_id', $mesaId)
                ->where('status_da_comanda', 'em_aberto')
                ->whereIn('id', $itemIds)
                ->orderBy('id')
                ->get();

            if ($itens->isEmpty()) {
                return 'Nenhum item válido selecionado para abater.';
            }

            $restante = $valorCents;
            $agora = Carbon::now();

            foreach ($itens as $item) {
                if ($restante <= 0) {
                    break;
                }

                $cap = (int) ($quantidadesPorItem[$item->id] ?? 0);
                if ($cap < 1) {
                    continue;
                }

                $unitCents = (int) round(((float) $item->preco_unitario) * 100);
                if ($unitCents <= 0) {
                    return 'Preço inválido em um dos itens selecionados.';
                }

                $openQty = (int) $item->quantidade;
                $maxUnits = min($openQty, $cap);
                if ($maxUnits < 1) {
                    continue;
                }

                // Paga unidade a unidade (permite pagar menos que 1 unidade criando uma unidade separada).
                while ($restante > 0 && $maxUnits > 0) {
                    $target = $item;

                    // Se o item tem mais de 1 unidade, separa 1 unidade para permitir pagamento parcial dessa unidade.
                    if ((int) $target->quantidade > 1) {
                        $target->quantidade = (int) $target->quantidade - 1;
                        $target->save();

                        $target = ItemPedido::create([
                            'preco_unitario' => (float) $item->preco_unitario,
                            'valor_pago' => 0.00,
                            'quantidade' => 1,
                            'status_da_comanda' => 'em_aberto',
                            'pago_em' => null,
                            'pagamento_metodo' => null,
                            'produto_id' => $item->produto_id,
                            'usuario_id' => $item->usuario_id,
                            'pedido_id' => $item->pedido_id,
                            'mesa_id' => $item->mesa_id,
                        ]);
                    }

                    $pagoCents = (int) round(((float) ($target->valor_pago ?? 0)) * 100);
                    $restaUnit = $unitCents - $pagoCents;
                    if ($restaUnit <= 0) {
                        // já está quitado, garante status
                        $target->status_da_comanda = 'pago';
                        $target->pago_em = $agora;
                        $target->pagamento_metodo = $pagamentoMetodo;
                        $target->save();
                        break;
                    }

                    $pagarAgora = min($restante, $restaUnit);
                    $novoPagoCents = $pagoCents + $pagarAgora;

                    $target->valor_pago = round($novoPagoCents / 100, 2);
                    $target->pagamento_metodo = $this->combinarMetodosPagamento($target->pagamento_metodo, $pagamentoMetodo);

                    if ($novoPagoCents >= $unitCents) {
                        $target->status_da_comanda = 'pago';
                        $target->pago_em = $agora;
                    }

                    $target->save();

                    $restante -= $pagarAgora;
                    $maxUnits -= 1;
                }
            }

            $abatidoCents = $valorCents - $restante;
            if ($abatidoCents <= 0) {
                return 'O valor informado não cobre nenhum item nas quantidades selecionadas. Aumente o valor ou ajuste as quantidades.';
            }

            $this->atualizarPrecoMesa($mesaId);

            $restamAbertos = ItemPedido::query()
                ->where('mesa_id', $mesaId)
                ->where('status_da_comanda', 'em_aberto')
                ->exists();

            if (!$restamAbertos) {
                $this->finalizarPedidosDaMesa((int) $mesaId);

                ItemPedido::query()
                    ->where('mesa_id', $mesaId)
                    ->where('status_da_comanda', 'pago')
                    ->update(['mesa_id' => null]);

                $mesa = Mesa::find($mesaId);
                if ($mesa) {
                    $mesa->preco = 0.00;
                    $mesa->status = 'Disponivel';
                    $mesa->save();
                }
            }

            if ($restante > 0) {
                $abatido = number_format($abatidoCents / 100, 2, ',', '.');
                $faltou = number_format($restante / 100, 2, ',', '.');
                return "warn:Abatido R$ {$abatido}. Faltaram R$ {$faltou} para completar o valor informado. Se quiser, faça outro abatimento.";
            }

            return null;
        });
    }


    public function cadastrarMesa($request): ?RedirectResponse
    {
        if ($request->input('numero_da_mesa') < 1) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
        }

        if (Mesa::where('numero_da_mesa', $request->input('numero_da_mesa'))->exists()) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_JA_EXISTENTE);
        }

        $mesa = new Mesa();
        $mesa->numero_da_mesa = $request->input('numero_da_mesa');
        $mesa->status = $request->input('status') ?? 'disponivel';
        $mesa->preco = 0.00;
        $mesa->save();

        return null;
    }

    public function atualizarMesa($request): ?RedirectResponse
    {
        $id = $request->input('mesa_id');
        $mesa = Mesa::find($id);

        if (!$mesa) {
            return redirect()->back()->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $novoNumero = $request->input('numero_da_mesa');
        $novoStatus = $request->input('status');

        if ($novoNumero !== null && $novoNumero !== '') {
            if ($novoNumero < 1) {
                return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
            }

            $numeroJaExiste = Mesa::where('numero_da_mesa', $novoNumero)
                ->where('id', '!=', $mesa->id)
                ->exists();
            if ($numeroJaExiste) {
                return redirect()->back()->with('error', ErroMensagens::NUMERO_JA_EXISTENTE);
            }

            $mesa->numero_da_mesa = $novoNumero;
        }

        if ($novoStatus !== null && $novoStatus !== '') {
            $mesa->status = $novoStatus;
        }
        $mesa->save();

        return null;
    }


    public function removerMesa($id)
    {
        $mesa = Mesa::find($id);
        if ($mesa) {
            $mesa->delete();
        }
    }


    public function pegarProdutosMesa($id)
    {
        return Produto::where('mesa_id', $id)->get();
    }
}
