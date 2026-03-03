<?php

namespace App\Services;

use App\Models\Mesa;
use App\Mensagens\ErroMensagens;
use App\Models\ItemPedido;
use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MesasService extends GenericBase
{
    public function pegarMesas()
    {
        return Mesa::all();
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

    public function abaterItensContaMesa($mesaId, array $itemIds, array $quantidadesPorItem = [], ?string $pagamentoMetodo = null): ?string
    {
        if (empty($itemIds)) {
            return 'Selecione pelo menos um item para abater.';
        }

        $itens = ItemPedido::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->whereIn('id', $itemIds)
            ->get();

        if ($itens->isEmpty()) {
            return 'Nenhum item válido selecionado para abater.';
        }

        $agora = Carbon::now();
        foreach ($itens as $item) {
            $max = (int) $item->quantidade;
            $qtd = (int) ($quantidadesPorItem[$item->id] ?? 0);

            if ($qtd < 1) {
                return 'Informe a quantidade a abater para os itens selecionados.';
            }
            if ($qtd > $max) {
                return 'A quantidade a abater não pode ser maior que a quantidade em aberto.';
            }

            if ($qtd === $max) {
                $item->status_da_comanda = 'pago';
                $item->pago_em = $agora;
                $item->pagamento_metodo = $pagamentoMetodo;
                $item->save();
                continue;
            }

            // Abatimento parcial: cria um novo item pago e reduz o item em aberto.
            ItemPedido::create([
                'preco_unitario' => (float) $item->preco_unitario,
                'quantidade' => $qtd,
                'status_da_comanda' => 'pago',
                'pago_em' => $agora,
                'pagamento_metodo' => $pagamentoMetodo,
                'produto_id' => $item->produto_id,
                'usuario_id' => $item->usuario_id,
                'pedido_id' => $item->pedido_id,
                'mesa_id' => $item->mesa_id,
            ]);

            $item->quantidade = $max - $qtd;
            $item->save();
        }

        $this->atualizarPrecoMesa($mesaId);

        $restamAbertos = ItemPedido::query()
            ->where('mesa_id', $mesaId)
            ->where('status_da_comanda', 'em_aberto')
            ->exists();

        if (!$restamAbertos) {
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

        return null;
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
                    $target->pagamento_metodo = $pagamentoMetodo;

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


    public function removerMesa($id){
        $mesa = Mesa::find($id);
        if ($mesa) {
            $mesa->delete();
        }
    }


    public function pegarProdutosMesa($id){
        return Produto::where('mesa_id', $id)->get();
    }
}
