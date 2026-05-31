<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <title>Detalhes da Mesa</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Mesa.css') }}">
</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')

        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>

            <main>
                <section class="mesas-page mesa-details-page">
                    <header class="mesa-detail-header">
                        <div>
                            <span class="mesa-detail-header__eyebrow">Comanda da lanchonete</span>
                            <h1 class="mesas-title">Mesa {{ $mesa->numero_da_mesa }}</h1>
                            <p class="mesas-subtitle">Gerencie os itens em aberto, ajuste quantidades e registre pagamentos parciais.</p>
                        </div>
                        <a href="{{ route('mesas.index') }}" class="mesa-back-button">Voltar para mesas</a>
                    </header>

                    <section class="mesa-summary-grid" aria-label="Resumo da mesa">
                        <article class="mesa-summary-tile">
                            <span>Status</span>
                            <strong>{{ $mesa->status }}</strong>
                        </article>
                        <article class="mesa-summary-tile mesa-summary-tile--total">
                            <span>Total em aberto</span>
                            <strong>R$ {{ number_format((float) $totalAberto, 2, ',', '.') }}</strong>
                        </article>
                        <article class="mesa-summary-tile">
                            <span>Itens em aberto</span>
                            <strong>{{ $itensAbertos->count() }}</strong>
                        </article>
                    </section>

                    <section class="mesa-order-editor">
                        <div class="mesa-order-editor__header">
                            <div>
                                <span class="mesa-order-editor__eyebrow">Comanda ativa</span>
                                <h2>Pedidos em aberto</h2>
                            </div>
                            <span class="mesa-order-editor__count">{{ $itensAbertos->count() }} {{ $itensAbertos->count() === 1 ? 'item' : 'itens' }}</span>
                        </div>

                        @if ($itensAbertos->isEmpty())
                            <div class="mesa-empty-state">
                                <strong>Nenhum item em aberto</strong>
                                <span>Quando houver pedidos nesta mesa, eles aparecem aqui.</span>
                            </div>
                        @else
                            <form id="abaterForm" action="{{ route('mesas.conta.abater', $mesa->id) }}" method="POST" hidden>
                                @csrf
                            </form>

                            <div class="mesa-order-list">
                                @foreach ($itensAbertos as $item)
                                    @php
                                        $nome = optional($item->produto)->nome ?? 'Produto';
                                        $unit = (float) $item->preco_unitario;
                                        $sub = $unit * (int) $item->quantidade;
                                        $valorPago = (float) ($item->valor_pago ?? 0);
                                        $restanteLinha = max(0, $sub - $valorPago);
                                        $preselectId = (int) (session('mesa.conta.preselect_item_id') ?? 0);
                                        $maxParaAbater = $valorPago > 0 ? 1 : (int) $item->quantidade;
                                    @endphp

                                    <article class="mesa-order-row">
                                        <div class="mesa-order-row__select">
                                            <label class="mesa-check" aria-label="Selecionar {{ $nome }} para baixa">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="item_ids[]"
                                                    value="{{ $item->id }}"
                                                    form="abaterForm"
                                                    data-unit="{{ number_format($unit, 2, '.', '') }}"
                                                    data-pago="{{ number_format($valorPago, 2, '.', '') }}"
                                                    data-max="{{ (int) $item->quantidade }}"
                                                    {{ $preselectId === (int) $item->id ? 'checked' : '' }}
                                                >
                                                <span></span>
                                            </label>
                                        </div>

                                        <div class="mesa-order-row__main">
                                            <div class="mesa-item-name">
                                                <strong>{{ $nome }}</strong>
                                                <small>Item #{{ $item->id }}</small>
                                            </div>
                                            @if($valorPago > 0)
                                                <span class="mesa-paid-chip">Parcialmente pago</span>
                                            @endif
                                        </div>

                                        <div class="mesa-order-row__numbers">
                                            <div>
                                                <span>Unitario</span>
                                                <strong>R$ {{ number_format($unit, 2, ',', '.') }}</strong>
                                            </div>
                                            <div>
                                                <span>Subtotal</span>
                                                <strong>R$ {{ number_format($sub, 2, ',', '.') }}</strong>
                                            </div>
                                            @if($valorPago > 0)
                                                <div>
                                                    <span>Restante</span>
                                                    <strong>R$ {{ number_format($restanteLinha, 2, ',', '.') }}</strong>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mesa-order-row__controls">
                                            <div class="mesa-order-block">
                                                <span>Dar baixa agora</span>
                                                <div class="mesa-qty-control">
                                                    <span class="mesa-qty-open">{{ (int) $item->quantidade }}x</span>
                                                    <button type="button" class="btn btn-secondary btn-sm" data-qtd-dec data-item-id="{{ $item->id }}">-</button>
                                                    <input
                                                        type="number"
                                                        class="form-control form-control-sm mesa-qty-input"
                                                        min="0"
                                                        max="{{ $maxParaAbater }}"
                                                        step="1"
                                                        name="quantidades[{{ $item->id }}]"
                                                        form="abaterForm"
                                                        value="{{ $preselectId === (int) $item->id ? $maxParaAbater : 0 }}"
                                                        data-qtd-input
                                                        data-item-id="{{ $item->id }}"
                                                    >
                                                    <button type="button" class="btn btn-secondary btn-sm" data-qtd-inc data-item-id="{{ $item->id }}">+</button>
                                                </div>
                                            </div>

                                            <div class="mesa-order-block mesa-order-block--edit">
                                                <span>Editar pedido</span>
                                                <div class="mesa-edit-actions">
                                                    <form action="{{ route('mesas.conta.item.atualizar', ['id' => $mesa->id, 'itemId' => $item->id]) }}" method="POST" class="mesa-edit-form">
                                                        @csrf
                                                        <label for="editar-qtd-{{ $item->id }}">Qtd.</label>
                                                        <input
                                                            type="number"
                                                            name="quantidade"
                                                            id="editar-qtd-{{ $item->id }}"
                                                            class="form-control form-control-sm mesa-edit-input"
                                                            min="1"
                                                            max="1000"
                                                            value="{{ (int) $item->quantidade }}"
                                                        >
                                                        <button type="submit" class="btn btn-sm btn-warning mesa-btn-save">Salvar</button>
                                                    </form>

                                                    <form action="{{ route('mesas.conta.item.remover', ['id' => $mesa->id, 'itemId' => $item->id]) }}" method="POST" data-confirm-remove-item>
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger mesa-btn-remove">Remover</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="mesa-checkout-bar">
                                <div>
                                    <strong>Dar baixa parcial ou total</strong>
                                    <span id="mesaCheckoutHint">Selecione os itens pagos agora.</span>
                                </div>
                                <div class="mesa-checkout-bar__actions">
                                    <strong class="mesa-checkout-total" id="mesaCheckoutTotalTexto">R$ 0,00</strong>
                                    <button class="btn btn-primary" type="button" id="btnAbrirAbaterModal" disabled>Dar baixa selecionados</button>
                                </div>
                            </div>

                            <div id="abaterModal" class="ff-modal" aria-hidden="true">
                                <div class="ff-modal__overlay" aria-hidden="true"></div>
                                <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="abaterModalTitle">
                                    <div class="ff-modal__header">
                                        <h2 id="abaterModalTitle">Dar baixa nos itens</h2>
                                        <button type="button" class="ff-modal__close" data-abater-modal-close aria-label="Fechar">&times;</button>
                                    </div>

                                    <p class="ff-modal__hint">Selecione a forma de pagamento e informe o valor recebido agora.</p>

                                    <div class="abater-total-card">
                                        <div class="abater-total">
                                            <span>Total selecionado</span>
                                            <strong id="abaterTotalTexto">R$ 0,00</strong>
                                        </div>
                                        <div class="abater-value-field">
                                            <label for="abaterValorInput">Valor do pagamento (R$)</label>
                                            <input
                                                id="abaterValorInput"
                                                name="valor_pagamento_total"
                                                form="abaterForm"
                                                type="text"
                                                inputmode="decimal"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder="Ex: 19,99"
                                            >
                                        </div>
                                    </div>

                                    <div class="ff-choice abater-payment-grid">
                                        @php
                                            $metodoSelecionado = old('pagamento_metodo', 'pix');
                                            $metodos = [
                                                'cartao_credito' => 'Cartao de credito',
                                                'cartao_debito' => 'Cartao de debito',
                                                'pix' => 'Pix',
                                                'dinheiro' => 'Dinheiro',
                                            ];
                                        @endphp

                                        @foreach ($metodos as $valor => $label)
                                            <label class="ff-choice__item">
                                                <input type="radio" name="pagamento_metodo" value="{{ $valor }}" form="abaterForm" {{ $metodoSelecionado === $valor ? 'checked' : '' }}>
                                                <span>
                                                    <strong>{{ $label }}</strong>
                                                    <small>Para este pagamento.</small>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div id="abaterModalErro" class="ff-modal__error" aria-live="polite"></div>

                                    <div class="ff-modal__footer ff-modal__footer--stack">
                                        <button type="button" class="btn btn-secondary" data-abater-modal-close>Cancelar</button>
                                        <button type="button" class="btn btn-primary" id="btnConfirmarAbater">Confirmar abatimento</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </section>

                    <details class="mesa-paid-panel">
                        <summary>
                            <span>Finalizados</span>
                            <strong>{{ $itensPagos->count() }}</strong>
                        </summary>
                        <div class="mesa-paid-panel__body">
                            @if ($itensPagos->isEmpty())
                                <p class="mesa-paid-empty">Nenhum item abatido ainda.</p>
                            @else
                                <div class="mesa-paid-list">
                                    @foreach ($itensPagos as $item)
                                        @php
                                            $nome = optional($item->produto)->nome ?? 'Produto';
                                            $unit = (float) $item->preco_unitario;
                                            $sub = $unit * (int) $item->quantidade;
                                        @endphp
                                        <article class="mesa-paid-item">
                                            <div>
                                                <strong>{{ $nome }}</strong>
                                                <span>{{ $item->quantidade }}x - R$ {{ number_format($sub, 2, ',', '.') }}</span>
                                            </div>
                                            <small>
                                                {{ $item->pagamento_metodo ? strtoupper(str_replace('_', ' ', $item->pagamento_metodo)) : 'PAGO' }}
                                                {{ $item->pago_em ? ' | ' . $item->pago_em->format('d/m/Y H:i') : '' }}
                                            </small>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </details>
                </section>
            </main>
        </div>
    </div>
    @include('components.flash-toast')
    <script src="{{ asset('js/mesa-detalhes-abater-modal.js') }}?v={{ filemtime(public_path('js/mesa-detalhes-abater-modal.js')) }}"></script>
</body>

</html>
