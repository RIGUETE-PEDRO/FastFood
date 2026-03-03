<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card p-3 mb-3">
                        <div class="d-flex flex-wrap gap-3 justify-content-between">
                            <div><strong>Status:</strong> {{ $mesa->status }}</div>
                            <div><strong>Total em aberto:</strong> R$ {{ number_format((float) $totalAberto, 2, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="card p-3 mb-3">
                        <h2 class="h5 mb-3">Pedidos em aberto</h2>

                        @if ($itensAbertos->isEmpty())
                            <div class="alert alert-info mb-0">Nenhum item em aberto nesta mesa.</div>
                        @else
                            <form id="abaterForm" action="{{ route('mesas.conta.abater', $mesa->id) }}" method="POST" class="d-none">
                                @csrf
                            </form>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>Item</th>
                                            <th style="width:220px;">Qtd em aberto</th>
                                            <th style="width:140px;">Unit.</th>
                                            <th style="width:140px;">Subtotal</th>
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
                                                <td>
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
                                                </td>
                                                <td>{{ $nome }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2" style="max-width: 220px;">
                                                        <span class="text-muted" style="min-width: 36px;">{{ (int) $item->quantidade }}x</span>

                                                        <button type="button" class="btn btn-secondary btn-sm" data-qtd-dec data-item-id="{{ $item->id }}">−</button>
                                                        <input
                                                            type="number"
                                                            class="form-control form-control-sm"
                                                            style="max-width: 90px;"
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
                                                    <small class="text-muted">Quantidade para abater agora</small>
                                                </td>
                                                <td>R$ {{ number_format($unit, 2, ',', '.') }}</td>
                                                <td>
                                                    <div>R$ {{ number_format($sub, 2, ',', '.') }}</div>
                                                    @if($valorPago > 0)
                                                        <small class="text-muted">Pago: R$ {{ number_format($valorPago, 2, ',', '.') }} | Falta: R$ {{ number_format($restanteLinha, 2, ',', '.') }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <div class="text-muted">Marque os itens que a pessoa vai pagar agora.</div>
                                <button class="btn btn-primary" type="button" id="btnAbrirAbaterModal">Abater selecionados (Pago)</button>
                            </div>

                            <!-- Modal: confirmar abatimento e forma de pagamento -->
                            <div id="abaterModal" class="ff-modal" aria-hidden="true">
                                <div class="ff-modal__overlay" aria-hidden="true"></div>
                                <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="abaterModalTitle">
                                    <div class="ff-modal__header">
                                        <h2 id="abaterModalTitle">Abater itens (pagar)</h2>
                                        <button type="button" class="ff-modal__close" data-abater-modal-close aria-label="Fechar">×</button>
                                    </div>

                                    <p class="ff-modal__hint">Selecione a forma de pagamento e informe o valor a pagar agora. Você pode pagar em partes (ex: Pix e depois Cartão).</p>

                                    <div class="card p-3 mb-3">
                                        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                                            <div><strong>Total selecionado:</strong> <span id="abaterTotalTexto">R$ 0,00</span></div>
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

                            <script>
                                (function () {
                                    const modal = document.getElementById('abaterModal');
                                    const btnOpen = document.getElementById('btnAbrirAbaterModal');
                                    const btnConfirm = document.getElementById('btnConfirmarAbater');
                                    const totalText = document.getElementById('abaterTotalTexto');
                                    const valorInput = document.getElementById('abaterValorInput');
                                    const errorBox = document.getElementById('abaterModalErro');
                                    const form = document.getElementById('abaterForm');
                                    const overlay = modal ? modal.querySelector('.ff-modal__overlay') : null;

                                    let valorFoiEditado = false;

                                    function formatBRL(value) {
                                        try {
                                            return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                                        } catch (e) {
                                            return 'R$ ' + value.toFixed(2).replace('.', ',');
                                        }
                                    }

                                    function getSelectedCheckboxes() {
                                        return Array.from(document.querySelectorAll('input[name="item_ids[]"]:checked'));
                                    }

                                    function calcSelectedTotal() {
                                        return getSelectedCheckboxes().reduce((sum, cb) => {
                                            const unitRaw = (cb.getAttribute('data-unit') || '0').replace(',', '.');
                                            const unit = parseFloat(unitRaw);
                                            const pagoRaw = (cb.getAttribute('data-pago') || '0').replace(',', '.');
                                            const pago = parseFloat(pagoRaw);
                                            const itemId = cb.value;
                                            const qtdInput = document.querySelector(`input[data-qtd-input][data-item-id="${itemId}"]`);
                                            const qtd = qtdInput ? parseInt(qtdInput.value || '0', 10) : 0;
                                            if (!Number.isFinite(unit) || !Number.isFinite(qtd) || qtd <= 0) return sum;

                                            // Se tiver pagamento parcial, este item é unidade (qtd=1) e o total é apenas o restante.
                                            if (Number.isFinite(pago) && pago > 0) {
                                                return sum + Math.max(0, unit - pago);
                                            }

                                            return sum + (unit * qtd);
                                        }, 0);
                                    }

                                    function parseValorBRL(raw) {
                                        if (!raw) return NaN;
                                        const s = String(raw)
                                            .trim()
                                            .replace(/\s/g, '')
                                            .replace(/^R\$/i, '')
                                            .replace(/\./g, '')
                                            .replace(',', '.');
                                        return parseFloat(s);
                                    }

                                    function openModal() {
                                        if (!modal) return;
                                        modal.setAttribute('aria-hidden', 'false');
                                        modal.classList.add('is-open');
                                    }

                                    function closeModal() {
                                        if (!modal) return;
                                        modal.setAttribute('aria-hidden', 'true');
                                        modal.classList.remove('is-open');
                                    }

                                    function updateModalTotal() {
                                        const total = calcSelectedTotal();
                                        totalText.textContent = formatBRL(total);

                                        if (valorInput && !valorFoiEditado) {
                                            // Preenche com o total selecionado (formato pt-BR)
                                            valorInput.value = total.toFixed(2).replace('.', ',');
                                        }
                                    }

                                    function clamp(val, min, max) {
                                        return Math.max(min, Math.min(max, val));
                                    }

                                    function getCheckboxByItemId(itemId) {
                                        return document.querySelector(`input[name="item_ids[]"][value="${itemId}"]`);
                                    }

                                    function getQtdInputByItemId(itemId) {
                                        return document.querySelector(`input[data-qtd-input][data-item-id="${itemId}"]`);
                                    }

                                    function syncCheckboxWithQtd(itemId) {
                                        const cb = getCheckboxByItemId(itemId);
                                        const input = getQtdInputByItemId(itemId);
                                        if (!cb || !input) return;
                                        const qtd = parseInt(input.value || '0', 10);
                                        cb.checked = Number.isFinite(qtd) && qtd > 0;
                                    }

                                    function getPagamentoSelecionado() {
                                        const el = document.querySelector('input[name="pagamento_metodo"]:checked');
                                        return el ? el.value : '';
                                    }

                                    if (btnOpen) {
                                        btnOpen.addEventListener('click', function () {
                                            errorBox.textContent = '';

                                            valorFoiEditado = false;
                                            if (valorInput) valorInput.value = '';

                                            // Se marcaram o checkbox mas deixaram quantidade 0, assume quantidade máxima.
                                            document.querySelectorAll('input[name="item_ids[]"]:checked').forEach((cb) => {
                                                const itemId = cb.value;
                                                const max = parseInt(cb.getAttribute('data-max') || '0', 10);
                                                const qtdInput = getQtdInputByItemId(itemId);
                                                if (qtdInput) {
                                                    const cur = parseInt(qtdInput.value || '0', 10);
                                                    if (!Number.isFinite(cur) || cur <= 0) {
                                                        qtdInput.value = Number.isFinite(max) && max > 0 ? String(max) : '1';
                                                    }
                                                }
                                            });

                                            const selected = getSelectedCheckboxes();
                                            if (selected.length === 0) {
                                                errorBox.textContent = 'Selecione pelo menos um item para abater.';
                                                openModal();
                                                updateModalTotal();
                                                return;
                                            }

                                            updateModalTotal();
                                            openModal();
                                        });
                                    }

                                    document.querySelectorAll('[data-abater-modal-close]').forEach((el) => {
                                        el.addEventListener('click', closeModal);
                                    });
                                    if (overlay) overlay.addEventListener('click', closeModal);

                                    document.querySelectorAll('input[name="item_ids[]"]').forEach((cb) => {
                                        cb.addEventListener('change', function () {
                                            const itemId = cb.value;
                                            const max = parseInt(cb.getAttribute('data-max') || '0', 10);
                                            const qtdInput = getQtdInputByItemId(itemId);
                                            if (qtdInput) {
                                                if (cb.checked) {
                                                    const cur = parseInt(qtdInput.value || '0', 10);
                                                    if (!Number.isFinite(cur) || cur <= 0) {
                                                        qtdInput.value = Number.isFinite(max) && max > 0 ? String(max) : '1';
                                                    }
                                                } else {
                                                    qtdInput.value = '0';
                                                }
                                            }

                                            if (modal && modal.classList.contains('is-open')) {
                                                updateModalTotal();
                                            }
                                        });
                                    });

                                    document.querySelectorAll('input[data-qtd-input]').forEach((input) => {
                                        input.addEventListener('input', function () {
                                            const itemId = input.getAttribute('data-item-id');
                                            const cb = getCheckboxByItemId(itemId);
                                            const max = cb ? parseInt(cb.getAttribute('data-max') || '0', 10) : 0;
                                            const cur = parseInt(input.value || '0', 10);
                                            const next = clamp(Number.isFinite(cur) ? cur : 0, 0, Number.isFinite(max) ? max : 999);
                                            input.value = String(next);
                                            syncCheckboxWithQtd(itemId);

                                            if (modal && modal.classList.contains('is-open')) {
                                                updateModalTotal();
                                            }
                                        });
                                    });

                                    function bumpQtd(itemId, delta) {
                                        const cb = getCheckboxByItemId(itemId);
                                        const input = getQtdInputByItemId(itemId);
                                        if (!cb || !input) return;
                                        const max = parseInt(cb.getAttribute('data-max') || '0', 10);
                                        const cur = parseInt(input.value || '0', 10);
                                        const next = clamp((Number.isFinite(cur) ? cur : 0) + delta, 0, Number.isFinite(max) ? max : 999);
                                        input.value = String(next);
                                        syncCheckboxWithQtd(itemId);
                                        if (modal && modal.classList.contains('is-open')) {
                                            updateModalTotal();
                                        }
                                    }

                                    document.querySelectorAll('[data-qtd-inc]').forEach((btn) => {
                                        btn.addEventListener('click', function () {
                                            bumpQtd(btn.getAttribute('data-item-id'), +1);
                                        });
                                    });
                                    document.querySelectorAll('[data-qtd-dec]').forEach((btn) => {
                                        btn.addEventListener('click', function () {
                                            bumpQtd(btn.getAttribute('data-item-id'), -1);
                                        });
                                    });

                                    if (valorInput) {
                                        valorInput.addEventListener('input', function () {
                                            valorFoiEditado = true;
                                        });
                                    }

                                    if (btnConfirm) {
                                        btnConfirm.addEventListener('click', function () {
                                            errorBox.textContent = '';

                                            const selected = getSelectedCheckboxes();
                                            if (selected.length === 0) {
                                                errorBox.textContent = 'Selecione pelo menos um item para abater.';
                                                return;
                                            }

                                            // valida quantidades
                                            for (const cb of selected) {
                                                const itemId = cb.value;
                                                const max = parseInt(cb.getAttribute('data-max') || '0', 10);
                                                const qtdInput = getQtdInputByItemId(itemId);
                                                const qtd = qtdInput ? parseInt(qtdInput.value || '0', 10) : 0;
                                                if (!Number.isFinite(qtd) || qtd < 1 || (Number.isFinite(max) && max > 0 && qtd > max)) {
                                                    errorBox.textContent = 'Informe uma quantidade válida para cada item selecionado.';
                                                    return;
                                                }
                                            }

                                            if (!getPagamentoSelecionado()) {
                                                errorBox.textContent = 'Selecione uma forma de pagamento.';
                                                return;
                                            }

                                            const total = calcSelectedTotal();
                                            const valorDigitado = valorInput ? parseValorBRL(valorInput.value) : NaN;
                                            if (!Number.isFinite(valorDigitado) || valorDigitado <= 0) {
                                                errorBox.textContent = 'Digite um valor de pagamento válido.';
                                                return;
                                            }

                                            // validação com tolerância de centavos
                                            if (valorDigitado - total > 0.009) {
                                                errorBox.textContent = `O valor digitado (${formatBRL(valorDigitado)}) não pode ser maior que o total selecionado (${formatBRL(total)}).`;
                                                return;
                                            }

                                            form.submit();
                                        });
                                    }
                                })();
                            </script>
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
</body>

</html>
