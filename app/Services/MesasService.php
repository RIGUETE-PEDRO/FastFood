<?php

namespace App\Services;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Models\MesaModel;
use App\Repository\GenericBaseRepository;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MesasRepository;

class MesasService extends GenericBase
{
    public function __construct(
        GenericBaseRepository $repository,
        private MesasRepository $mesasRepository
    ) {
        parent::__construct($repository);
    }

    private function normalizarMetodoPagamento(?string $metodo): ?string
    {
        $metodo = trim((string) $metodo);
        if ($metodo === '') {
            return null;
        }

        return $metodo;
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
        $pedidoIds = $this->mesasRepository->pegarPedidoIdsDaMesa($mesaId);

        if ($pedidoIds->isEmpty()) {
            return;
        }

        $this->mesasRepository->finalizarPedidosPorIds($pedidoIds);
    }

    public function pegarMesas()
    {
        return $this->mesasRepository->listarMesas();
    }

    public function listarHistoricoMesas(int $porPagina = 12, array $filtros = [])
    {
        return $this->mesasRepository->listarHistoricoFechamentos($porPagina, $filtros);
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

        $formasPagamento = $this->mesasRepository->listarFormasPagamento();

        return [
            'mesa' => $mesa,
            'itensAbertos' => $itensAbertos,
            'itensPagos' => $itensPagos,
            'totalAberto' => $totalAberto,
            'formasPagamento' => $formasPagamento,
        ];
    }

    public function pegarMesaPorId($id): ?MesaModel
    {
        return $this->mesasRepository->pegarMesaPorId((int) $id);
    }

    public function pegarProdutosDisponiveis(array $excluirProdutoIds = []): Collection
    {
        return $this->mesasRepository->pegarProdutosDisponiveis($excluirProdutoIds);
    }

    public function pegarItensContaMesa($mesaId, bool $pago): Collection
    {
        return $this->mesasRepository->pegarItensContaMesa((int) $mesaId, $pago);
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

    private function registrarFechamentoMesa(int $mesaId, Carbon $fechadoEm): void
    {
        $mesa = $this->mesasRepository->pegarMesaPorId($mesaId);
        if (!$mesa) {
            return;
        }

        $itensPagos = $this->mesasRepository->pegarItensPagosParaFechamento($mesaId);
        if ($itensPagos->isEmpty()) {
            return;
        }

        $formasPagamento = [];
        $pagamentosResumo = [];
        $totalPagoItens = 0.0;
        $totalItens = 0;

        foreach ($itensPagos as $item) {
            $quantidade = max(1, (int) $item->quantidade);
            $valorPago = (float) ($item->valor_pago ?? 0);
            $valorItem = $valorPago > 0
                ? $valorPago
                : ((float) $item->preco_unitario) * $quantidade;

            $totalPagoItens += $valorItem;
            $totalItens += $quantidade;
        }

        $produtosResumo = $itensPagos
            ->groupBy(fn ($item) => (int) ($item->produto_id ?? 0) . '|' . (float) $item->preco_unitario)
            ->map(function ($itensProduto) {
                $primeiro = $itensProduto->first();
                $quantidade = (int) $itensProduto->sum(fn ($item) => max(1, (int) $item->quantidade));
                $unitario = (float) $primeiro->preco_unitario;

                return [
                    'produto_id' => (int) ($primeiro->produto_id ?? 0),
                    'nome' => optional($primeiro->produto)->nome ?? 'Produto removido',
                    'quantidade' => $quantidade,
                    'preco_unitario' => round($unitario, 2),
                    'subtotal' => round($unitario * $quantidade, 2),
                ];
            })
            ->values()
            ->all();

        $pagamentosRegistrados = $this->mesasRepository->listarPagamentosAbertosMesa($mesaId);

        if ($pagamentosRegistrados->isNotEmpty()) {
            foreach ($pagamentosRegistrados as $pagamento) {
                $metodo = $this->normalizarMetodoPagamento($pagamento->pagamento_metodo) ?? 'nao_informado';
                $valor = (float) $pagamento->valor;

                $formasPagamento[$metodo] = true;
                $pagamentosResumo[$metodo] = ($pagamentosResumo[$metodo] ?? 0) + $valor;
            }

            $totalRegistrado = (float) array_sum($pagamentosResumo);
            $diferenca = round($totalPagoItens - $totalRegistrado, 2);

            if ($diferenca > 0.00) {
                $formasPagamento['nao_informado'] = true;
                $pagamentosResumo['nao_informado'] = ($pagamentosResumo['nao_informado'] ?? 0) + $diferenca;
            }
        } else {
            foreach ($itensPagos as $item) {
                $quantidade = max(1, (int) $item->quantidade);
                $valorPago = (float) ($item->valor_pago ?? 0);
                $valorItem = $valorPago > 0
                    ? $valorPago
                    : ((float) $item->preco_unitario) * $quantidade;

                $metodos = collect(preg_split('/\s*\+\s*/', (string) $item->pagamento_metodo) ?: [])
                    ->map(fn ($metodo) => $this->normalizarMetodoPagamento($metodo))
                    ->filter(fn ($metodo) => !empty($metodo))
                    ->values()
                    ->all();

                if (empty($metodos)) {
                    $metodos = ['nao_informado'];
                }

                foreach ($metodos as $metodo) {
                    $formasPagamento[$metodo] = true;
                }

                $metodoResumo = implode(' + ', $metodos);
                $pagamentosResumo[$metodoResumo] = ($pagamentosResumo[$metodoResumo] ?? 0) + $valorItem;
            }
        }

        $fechamento = $this->mesasRepository->registrarFechamentoMesa([
            'mesa_id' => $mesa->id,
            'numero_da_mesa' => $mesa->numero_da_mesa,
            'total_pago' => round($totalPagoItens, 2),
            'total_itens' => $totalItens,
            'formas_pagamento' => array_keys($formasPagamento),
            'pagamentos_resumo' => collect($pagamentosResumo)
                ->map(fn ($valor, $metodo) => [
                    'metodo' => $metodo,
                    'valor' => round((float) $valor, 2),
                ])
                ->values()
                ->all(),
            'produtos_resumo' => $produtosResumo,
            'fechado_em' => $fechadoEm,
        ]);

        $this->mesasRepository->vincularPagamentosAoFechamento($mesaId, (int) $fechamento->id);
    }

    public function atualizarPrecoMesa($mesaId): void
    {
        $mesa = $this->mesasRepository->pegarMesaPorId((int) $mesaId);
        if (!$mesa) {
            return;
        }

        $itensAbertos = $this->mesasRepository->listarItensAbertosMesa((int) $mesaId);

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

        $itens = $this->mesasRepository->pegarItensAbertosSelecionados((int) $id, (array) $itemIds);

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

    public function atualizarItemContaMesa($request, int $mesaId, int $itemId): array
    {
        $quantidade = (int) $request->input('quantidade');
        if ($quantidade < 1) {
            return ['status' => false, 'mensagem' => ErroMensagens::QUANTIDADE_MINIMA];
        }

        $item = $this->mesasRepository->pegarItemAbertoDaMesa($mesaId, $itemId);
        if (!$item) {
            return ['status' => false, 'mensagem' => 'Item não encontrado para esta mesa.'];
        }

        if ((float) ($item->valor_pago ?? 0) > 0) {
            return ['status' => false, 'mensagem' => 'Não é possível editar quantidade de item com pagamento parcial.'];
        }

        $item->quantidade = $quantidade;
        $this->mesasRepository->salvarItem($item);

        $this->atualizarPrecoMesa($mesaId);

        if (!empty($item->pedido_id)) {
            $this->mesasRepository->atualizarTotaisPedido((int) $item->pedido_id);
        }

        return ['status' => true, 'mensagem' => 'Item da comanda atualizado com sucesso!'];
    }

    public function removerItemContaMesa(int $mesaId, int $itemId): array
    {
        $item = $this->mesasRepository->pegarItemAbertoDaMesa($mesaId, $itemId);
        if (!$item) {
            return ['status' => false, 'mensagem' => 'Item não encontrado para esta mesa.'];
        }

        if ((float) ($item->valor_pago ?? 0) > 0) {
            return ['status' => false, 'mensagem' => 'Não é possível remover item com pagamento parcial.'];
        }

        $pedidoId = (int) ($item->pedido_id ?? 0);
        $this->mesasRepository->removerItem($item);

        $this->atualizarPrecoMesa($mesaId);

        if ($pedidoId > 0) {
            $this->mesasRepository->atualizarTotaisPedido($pedidoId);
        }

        if (!$this->mesasRepository->existemItensAbertosMesa($mesaId)) {
            $mesa = $this->mesasRepository->pegarMesaPorId($mesaId);
            if ($mesa) {
                $mesa->preco = 0.00;
                $mesa->status = 'Disponivel';
                $mesa->save();
            }
        }

        return ['status' => true, 'mensagem' => 'Item removido da comanda com sucesso!'];
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
            $itens = $this->mesasRepository->pegarItensParaAbatimento((int) $mesaId, $itemIds);

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

                        $target = $this->mesasRepository->criarItemPedido([
                            'preco_unitario' => (float) $item->preco_unitario,
                            'valor_pago' => 0.00,
                            'quantidade' => 1,
                            'status_da_comanda' => 'em_aberto',
                            'pago_em' => null,
                            'pagamento_metodo' => null,
                            'produto_id' => $item->produto_id,
                            'pedido_id' => $item->pedido_id,
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

            $this->mesasRepository->registrarPagamentoMesa([
                'mesa_id' => (int) $mesaId,
                'pagamento_metodo' => $this->normalizarMetodoPagamento($pagamentoMetodo) ?? 'nao_informado',
                'valor' => round($abatidoCents / 100, 2),
                'pago_em' => $agora,
            ]);

            $this->atualizarPrecoMesa($mesaId);

            $restamAbertos = $this->mesasRepository->existemItensAbertosMesa((int) $mesaId);

            if (!$restamAbertos) {
                $this->registrarFechamentoMesa((int) $mesaId, $agora);
                $this->finalizarPedidosDaMesa((int) $mesaId);

                $this->mesasRepository->desvincularMesaItensPagos((int) $mesaId);

                $mesa = $this->mesasRepository->pegarMesaPorId((int) $mesaId);
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

        if ($this->mesasRepository->existeNumeroMesa((int) $request->input('numero_da_mesa'))) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_JA_EXISTENTE);
        }

        $this->mesasRepository->criarMesa([
            'numero_da_mesa' => $request->input('numero_da_mesa'),
            'status' => $request->input('status') ?? 'disponivel',
            'preco' => 0.00,
        ]);

        return null;
    }

    public function atualizarMesa($request): ?RedirectResponse
    {
        $id = $request->input('mesa_id');
        $mesa = $this->mesasRepository->pegarMesaPorId((int) $id);

        if (!$mesa) {
            return redirect()->back()->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $novoNumero = $request->input('numero_da_mesa');
        $novoStatus = $request->input('status');

        if ($novoNumero !== null && $novoNumero !== '') {
            if ($novoNumero < 1) {
                return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
            }

            $numeroJaExiste = $this->mesasRepository->existeNumeroMesa((int) $novoNumero, (int) $mesa->id);
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


    public function removerMesa($id): ?RedirectResponse
    {
        $mesaId = (int) $id;
        $mesa = $this->mesasRepository->pegarMesaPorId($mesaId);

        if (!$mesa) {
            return redirect()->back()->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $this->atualizarPrecoMesa($mesaId);
        $mesa->refresh();

        if ((float) $mesa->preco > 0 || $this->mesasRepository->existemItensAbertosMesa($mesaId)) {
            return redirect()->back()->with('error', ErroMensagens::MESA_COM_SALDO_ABERTO);
        }

        $this->mesasRepository->removerMesa($mesaId);

        return null;
    }


    public function pegarProdutosMesa($id)
    {
        return $this->mesasRepository->pegarProdutosMesa((int) $id);
    }
}
