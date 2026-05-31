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
                <section class="mesas-page">
                    <header class="mesas-header">
                        <div>
                            <h1 class="mesas-title">Mesa {{ $mesa->numero_da_mesa }}</h1>
                            <p class="mesas-subtitle">Comanda da mesa: itens entram automaticamente pelo pedido e você pode abater (pagar) por partes.</p>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('mesas.index') }}" class="btn btn-secondary">Voltar</a>
                        </div>
                    </header>


                    <div class="card p-3 mb-3 mesa-summary-card">
                        <div class="mesa-summary">
                            <div class="mesa-summary__item">
                                <span>Status da mesa</span>
                                <strong>{{ $mesa->status }}</strong>
                            </div>
                            <div class="mesa-summary__item mesa-summary__item--total">
                                <span>Total em aberto</span>
                                <strong>R$ {{ number_format((float) $totalAberto, 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="card p-3 mb-3 mesa-order-editor">
                        <div class="mesa-order-editor__header">
                            <div>
                                <span class="mesa-order-editor__eyebrow">Comanda ativa</span>
                                <h2>Pedidos em aberto</h2>
                            </div>
                            <span class="mesa-order-editor__count">{{ $itensAbertos->count() }} {{ $itensAbertos->count() === 1 ? 'item' : 'itens' }}</span>
                        </div>

                        @if ($itensAbertos->isEmpty())
                            <div class="alert alert-info mb-0">Nenhum item em aberto nesta mesa.</div>
                        @else
                            <form id="abaterForm" action="{{ route('mesas.conta.abater', $mesa->id) }}" method="POST" class="d-none">
                                @csrf
                            </form>

                            <div class="table-responsive mesa-order-table-wrap">
                                <table class="table align-middle mesa-order-table">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>Item</th>
                                            <th style="width:220px;">Qtd em aberto</th>
                                            <th style="width:140px;">Unit.</th>
                                            <th style="width:140px;">Subtotal</th>
                                            <th style="width:260px;">Editar pedido</th>
                                        </tr>
                                    </thead>
                                    <tbody>
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
                                            <tr>
                                                <td data-label="Baixa">
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
                                                </td>
                                                <td data-label="Item">
                                                    <div class="mesa-item-name">
                                                        <strong>{{ $nome }}</strong>
                                                        <small>Item #{{ $item->id }}</small>
                                                    </div>
                                                </td>
                                                <td data-label="Quantidade">
                                                    <div class="mesa-qty-control">
                                                        <span class="mesa-qty-open">{{ (int) $item->quantidade }}x</span>

                                                        <button type="button" class="btn btn-secondary btn-sm" data-qtd-dec data-item-id="{{ $item->id }}">−</button>
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
                                                    <small class="text-muted">Quantidade para dar baixa agora</small>
                                                </td>
                                                <td data-label="Unit.">R$ {{ number_format($unit, 2, ',', '.') }}</td>
                                                <td data-label="Subtotal">
                                                    <div>R$ {{ number_format($sub, 2, ',', '.') }}</div>
                                                    @if($valorPago > 0)
                                                        <small class="text-muted">Pago: R$ {{ number_format($valorPago, 2, ',', '.') }} | Falta: R$ {{ number_format($restanteLinha, 2, ',', '.') }}</small>
                                                    @endif
                                                </td>
                                                <td data-label="Editar pedido">
                                                    <div class="mesa-edit-actions">
                                                        <form action="{{ route('mesas.conta.item.atualizar', ['id' => $mesa->id, 'itemId' => $item->id]) }}" method="POST" class="mesa-edit-form">
                                                            @csrf
                                                            <label for="editar-qtd-{{ $item->id }}">Nova qtd.</label>
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
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mesa-checkout-bar">
                                <div>
                                    <strong>Dar baixa parcial ou total</strong>
                                    <span id="mesaCheckoutHint">Marque os itens que a pessoa vai pagar agora.</span>
                                </div>
                                <div class="mesa-checkout-bar__actions">
                                    <strong class="mesa-checkout-total" id="mesaCheckoutTotalTexto">R$ 0,00</strong>
                                    <button class="btn btn-primary" type="button" id="btnAbrirAbaterModal" disabled>Dar baixa selecionados</button>
                                </div>
                            </div>

                            <!-- Modal: confirmar abatimento e forma de pagamento -->
                            <div id="abaterModal" class="ff-modal" aria-hidden="true">
                                <div class="ff-modal__overlay" aria-hidden="true"></div>
                                <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="abaterModalTitle">
                                    <div class="ff-modal__header">
                                        <h2 id="abaterModalTitle">Dar baixa nos itens</h2>
                                        <button type="button" class="ff-modal__close" data-abater-modal-close aria-label="Fechar">×</button>
                                    </div>

                                    <p class="ff-modal__hint">Selecione a forma de pagamento e informe o valor a pagar agora. Você pode pagar em partes (ex: Pix e depois Cartão).</p>

                                    <div class="card p-3 mb-3 abater-total-card">
                                        <div class="abater-total">
                                            <span>Total selecionado</span>
                                            <strong id="abaterTotalTexto">R$ 0,00</strong>
                                        </div>
                                        <div class="mt-2">
                                            <label for="abaterValorInput" class="form-label mb-1"><strong>Valor do pagamento (R$)</strong></label>
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
                                            <small class="text-muted">Digite o valor total do pagamento deste abatimento.</small>
                                        </div>
                                    </div>

                                    <div class="ff-choice" style="margin-bottom: 12px;">
                                        @php
                                            $metodoSelecionado = old('pagamento_metodo', 'pix');
                                            $metodos = [
                                                'cartao_credito' => 'Cartão de crédito',
                                                'cartao_debito' => 'Cartão de débito',
                                                'pix' => 'Pix',
                                                'dinheiro' => 'Dinheiro',
                                            ];
                                        @endphp

                                        @foreach ($metodos as $valor => $label)
                                            <label class="ff-choice__item">
                                                <input type="radio" name="pagamento_metodo" value="{{ $valor }}" form="abaterForm" {{ $metodoSelecionado === $valor ? 'checked' : '' }}>
                                                <span>
                                                    <strong>{{ $label }}</strong>
                                                    <small>Para este abatimento.</small>
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
                    </div>

                    <details class="card p-3 mb-3">
                        <summary class="h6 mb-0" style="cursor:pointer;">Finalizados (abatidos/pagos) — {{ $itensPagos->count() }}</summary>
                        <div class="mt-3">
                            @if ($itensPagos->isEmpty())
                                <div class="text-muted">Nenhum item abatido ainda.</div>
                            @else
                                <ul class="mb-0">
                                    @foreach ($itensPagos as $item)
                                        @php
                                            $nome = optional($item->produto)->nome ?? 'Produto';
                                            $unit = (float) $item->preco_unitario;
                                            $sub = $unit * (int) $item->quantidade;
                                        @endphp
                                        <li>
                                            {{ $nome }} — {{ $item->quantidade }}x (R$ {{ number_format($sub, 2, ',', '.') }})
                                            <span class="text-muted" style="font-size:.9rem;">
                                                {{ $item->pagamento_metodo ? ' | ' . strtoupper(str_replace('_', ' ', $item->pagamento_metodo)) : '' }}
                                                {{ $item->pago_em ? ' | ' . $item->pago_em->format('d/m/Y H:i') : '' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
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
