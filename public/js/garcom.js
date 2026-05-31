document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const mesaSelect = document.getElementById('mesa_id');
    const categoriaSelect = document.getElementById('categoria_id');
    const filtroForm = document.querySelector('.garcom-filter-form');
    const limparFiltroBtn = document.querySelector('.garcom-filter-clear');
    const addButtons = document.querySelectorAll('[data-garcom-add]');
    const orderForm = document.getElementById('garcomOrderForm');
    const orderList = document.getElementById('garcomOrderList');
    const orderEmpty = document.getElementById('garcomOrderEmpty');
    const orderInputs = document.getElementById('garcomOrderInputs');
    const orderMesaId = document.getElementById('garcomOrderMesaId');
    const orderTotal = document.getElementById('garcomOrderTotal');
    const orderCount = document.getElementById('garcomOrderCount');
    const enviarPedidoBtn = document.getElementById('garcomEnviarPedido');
    const limparPedidoBtn = document.getElementById('garcomLimparPedido');
    const pedido = new Map();

    function garantirEstiloPopup() {
        if (document.getElementById('garcom-popup-style')) return;

        const style = document.createElement('style');
        style.id = 'garcom-popup-style';
        style.textContent = `
            .garcom-popup-stack {
                position: fixed;
                bottom: 16px;
                right: 16px;
                z-index: 4000;
                display: grid;
                gap: 10px;
                max-width: min(92vw, 420px);
            }

            .garcom-popup {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: start;
                gap: 10px;
                padding: 12px;
                border-radius: 12px;
                color: #fff;
                box-shadow: 0 12px 28px rgba(0, 0, 0, .22);
                border: 1px solid rgba(255, 255, 255, .22);
                background: linear-gradient(135deg, #f59e0b, #d97706);
                animation: garcomPopupIn .2s ease-out;
            }

            .garcom-popup__close {
                border: none;
                background: rgba(255,255,255,.2);
                color: #fff;
                width: 26px;
                height: 26px;
                border-radius: 999px;
                cursor: pointer;
                font-size: 16px;
                line-height: 1;
                padding: 0;
            }

            .garcom-popup--hide {
                opacity: 0;
                transform: translateY(6px);
                transition: all .18s ease;
            }

            @keyframes garcomPopupIn {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;

        document.head.appendChild(style);
    }

    function mostrarPopup(mensagem) {
        garantirEstiloPopup();

        let stack = document.getElementById('garcom-popup-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'garcom-popup-stack';
            stack.className = 'garcom-popup-stack';
            document.body.appendChild(stack);
        }

        const popup = document.createElement('div');
        popup.className = 'garcom-popup';

        const texto = document.createElement('div');
        texto.textContent = mensagem;

        const fecharBtn = document.createElement('button');
        fecharBtn.type = 'button';
        fecharBtn.className = 'garcom-popup__close';
        fecharBtn.setAttribute('aria-label', 'Fechar');
        fecharBtn.textContent = 'x';

        popup.append(texto, fecharBtn);

        const fechar = () => {
            popup.classList.add('garcom-popup--hide');
            setTimeout(() => popup.remove(), 180);
        };

        fecharBtn.addEventListener('click', fechar);
        stack.appendChild(popup);
        setTimeout(fechar, 2800);
    }

    function normalizarQuantidade(valor) {
        const quantidade = parseInt(valor, 10);
        return Number.isFinite(quantidade) && quantidade > 0 ? quantidade : 1;
    }

    function parsePreco(valor) {
        const preco = parseFloat(String(valor || '0').replace(',', '.'));
        return Number.isFinite(preco) ? preco : 0;
    }

    function formatarMoeda(valor) {
        try {
            return valor.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });
        } catch {
            return 'R$ ' + valor.toFixed(2).replace('.', ',');
        }
    }

    function criarBotao(classe, texto, acao, produtoId) {
        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = classe;
        botao.textContent = texto;
        botao.dataset.orderAction = acao;
        botao.dataset.produtoId = produtoId;
        return botao;
    }

    function adicionarInputOculto(nome, valor) {
        if (!orderInputs) return;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nome;
        input.value = valor;
        orderInputs.appendChild(input);
    }

    function renderizarPedido() {
        if (!orderList || !orderInputs) return;

        orderList.innerHTML = '';
        orderInputs.innerHTML = '';

        let total = 0;
        let quantidadeTotal = 0;
        let index = 0;

        pedido.forEach(function (item) {
            total += item.preco * item.quantidade;
            quantidadeTotal += item.quantidade;

            const row = document.createElement('div');
            row.className = 'garcom-order-item';
            row.dataset.produtoId = item.id;

            const info = document.createElement('div');
            info.className = 'garcom-order-item__info';

            const nome = document.createElement('strong');
            nome.textContent = item.nome;

            const detalhe = document.createElement('small');
            detalhe.textContent = `${formatarMoeda(item.preco)} cada`;

            info.append(nome, detalhe);

            const controls = document.createElement('div');
            controls.className = 'garcom-order-item__controls';

            const decBtn = criarBotao('garcom-order-icon-btn', '-', 'decrement', item.id);
            const qtdInput = document.createElement('input');
            qtdInput.type = 'number';
            qtdInput.min = '1';
            qtdInput.value = String(item.quantidade);
            qtdInput.className = 'garcom-order-qtd';
            qtdInput.dataset.orderQtd = item.id;

            const incBtn = criarBotao('garcom-order-icon-btn', '+', 'increment', item.id);
            const removeBtn = criarBotao('garcom-order-remove', 'Remover', 'remove', item.id);

            controls.append(decBtn, qtdInput, incBtn, removeBtn);
            row.append(info, controls);
            orderList.appendChild(row);

            adicionarInputOculto(`itens[${index}][produto_id]`, item.id);
            adicionarInputOculto(`itens[${index}][quantidade]`, item.quantidade);
            index += 1;
        });

        const temItens = pedido.size > 0;

        if (orderEmpty) {
            orderEmpty.hidden = temItens;
        }

        if (orderTotal) {
            orderTotal.textContent = formatarMoeda(total);
        }

        if (orderCount) {
            orderCount.textContent = `${quantidadeTotal} ${quantidadeTotal === 1 ? 'item' : 'itens'}`;
        }

        if (enviarPedidoBtn) {
            enviarPedidoBtn.disabled = !temItens;
        }

        if (limparPedidoBtn) {
            limparPedidoBtn.disabled = !temItens;
        }
    }

    addButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const row = button.closest('tr');
            if (!row) return;

            const produtoId = row.dataset.produtoId;
            const nome = row.dataset.produtoNome || row.querySelector('.nome-cell')?.textContent?.trim() || 'Produto';
            const preco = parsePreco(row.dataset.produtoPreco);
            const qtdInput = row.querySelector('[data-garcom-qtd]');
            const quantidade = normalizarQuantidade(qtdInput?.value || '1');

            if (qtdInput) {
                qtdInput.value = String(quantidade);
            }

            const itemAtual = pedido.get(produtoId);
            if (itemAtual) {
                itemAtual.quantidade += quantidade;
            } else {
                pedido.set(produtoId, {
                    id: produtoId,
                    nome,
                    preco,
                    quantidade,
                });
            }

            renderizarPedido();
            mostrarPopup('Produto adicionado a lista.');
        });
    });

    orderList?.addEventListener('click', function (event) {
        const target = event.target.closest('[data-order-action]');
        if (!target) return;

        const item = pedido.get(target.dataset.produtoId);
        if (!item) return;

        if (target.dataset.orderAction === 'increment') {
            item.quantidade += 1;
        }

        if (target.dataset.orderAction === 'decrement') {
            item.quantidade -= 1;
            if (item.quantidade < 1) {
                pedido.delete(item.id);
            }
        }

        if (target.dataset.orderAction === 'remove') {
            pedido.delete(item.id);
        }

        renderizarPedido();
    });

    orderList?.addEventListener('change', function (event) {
        const input = event.target.closest('[data-order-qtd]');
        if (!input) return;

        const item = pedido.get(input.dataset.orderQtd);
        if (!item) return;

        item.quantidade = normalizarQuantidade(input.value);
        input.value = String(item.quantidade);
        renderizarPedido();
    });

    limparPedidoBtn?.addEventListener('click', function () {
        pedido.clear();
        renderizarPedido();
    });

    orderForm?.addEventListener('submit', function (event) {
        if (!mesaSelect?.value) {
            event.preventDefault();
            mostrarPopup('Selecione uma mesa antes de enviar o pedido.');
            mesaSelect?.focus();
            return;
        }

        if (pedido.size === 0) {
            event.preventDefault();
            mostrarPopup('Adicione pelo menos um produto a lista.');
            return;
        }

        if (orderMesaId) {
            orderMesaId.value = mesaSelect.value;
        }
    });

    const rows = tableBody ? Array.from(tableBody.querySelectorAll('tr')) : [];

    function aplicarFiltros() {
        const termoBusca = (searchInput?.value || '').trim().toLowerCase();
        const categoriaSelecionadaId = (categoriaSelect?.value || '').trim();
        const categoriaSelecionadaNome = (categoriaSelect?.selectedOptions?.[0]?.textContent || '').trim().toLowerCase();

        rows.forEach(function (row) {
            const nome = row.querySelector('.nome-cell')?.textContent?.toLowerCase() || '';
            const categoria = row.children[3]?.textContent?.toLowerCase() || '';

            const passouBusca = !termoBusca || `${nome} ${categoria}`.includes(termoBusca);
            const passouCategoria = !categoriaSelecionadaId || categoria === categoriaSelecionadaNome;

            row.style.display = passouBusca && passouCategoria ? '' : 'none';
        });
    }

    filtroForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        aplicarFiltros();
    });

    categoriaSelect?.addEventListener('change', aplicarFiltros);

    limparFiltroBtn?.addEventListener('click', function () {
        if (categoriaSelect) {
            categoriaSelect.value = '';
        }
        if (searchInput) {
            searchInput.value = '';
        }
        aplicarFiltros();
    });

    searchInput?.addEventListener('input', aplicarFiltros);
    renderizarPedido();
});
