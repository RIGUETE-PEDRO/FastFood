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
    const observacaoModal = document.getElementById('garcomObservacaoModal');
    const observacaoProduto = document.getElementById('garcomObservacaoProduto');
    const observacaoInput = document.getElementById('garcomObservacaoInput');
    const observacaoSemBtn = document.getElementById('garcomObservacaoSem');
    const personalizacaoPizza = document.getElementById('garcomPizzaOptions');
    const modoPizza = document.getElementById('garcomPizzaModo');
    const campoSegundoSabor = document.getElementById('garcomPizzaSecondField');
    const segundoSabor = document.getElementById('garcomPizzaSabor2');
    const campoTamanhoPizza = document.getElementById('garcomPizzaSizeField');
    const tamanhoPizza = document.getElementById('garcomPizzaTamanho');
    const bordaPizza = document.getElementById('garcomPizzaBorda');
    const tamanhoIndisponivel = document.getElementById('garcomPizzaSizeUnavailable');
    const personalizacaoPorcao = document.getElementById('garcomPortionOptions');
    const tipoPorcao = document.getElementById('garcomTipoPorcao');
    const previewPersonalizacao = document.getElementById('garcomPersonalizationPrice');
    const confirmarObservacaoBtn = document.getElementById('garcomObservacaoConfirmar');
    const pedido = new Map();
    let itemPendente = null;

    function lerPrecos(valor) {
        try {
            return JSON.parse(valor || '{}') || {};
        } catch {
            return {};
        }
    }

    function lerBordas(valor) {
        try {
            const bordas = JSON.parse(valor || '[]');
            return Array.isArray(bordas) ? bordas : [];
        } catch {
            return [];
        }
    }

    function opcoesSaborSegundo() {
        return segundoSabor ? Array.from(segundoSabor.options).filter((option) => option.value) : [];
    }

    function sincronizarPersonalizacao() {
        if (!itemPendente) return;

        const isPizza = itemPendente.tipo === 'pizza';
        const isPorcao = itemPendente.tipo === 'porcao';
        if (personalizacaoPizza) personalizacaoPizza.hidden = !isPizza;
        if (personalizacaoPorcao) personalizacaoPorcao.hidden = !isPorcao;
        if (modoPizza) modoPizza.disabled = !isPizza;
        if (tipoPorcao) tipoPorcao.disabled = !isPorcao;
        if (segundoSabor && !isPizza) {
            segundoSabor.disabled = true;
            segundoSabor.required = false;
        }
        if (tamanhoPizza && !isPizza) {
            tamanhoPizza.disabled = true;
            tamanhoPizza.required = false;
        }
        if (bordaPizza) {
            bordaPizza.disabled = !isPizza;
            bordaPizza.required = isPizza;
            if (!isPizza) bordaPizza.value = '';
        }
        if (confirmarObservacaoBtn) confirmarObservacaoBtn.disabled = false;

        let preco = itemPendente.precoBase;
        itemPendente.personalizacao = null;
        itemPendente.detalhePersonalizacao = '';
        itemPendente.nomeExibicao = itemPendente.nome;

        if (isPizza) {
            const opcaoMeiaAMeia = modoPizza?.querySelector('option[value="meia_a_meia"]');
            if (opcaoMeiaAMeia) {
                opcaoMeiaAMeia.disabled = opcoesSaborSegundo().length < 2;
                if (opcaoMeiaAMeia.disabled && modoPizza.value === 'meia_a_meia') modoPizza.value = 'inteira';
            }
            const modo = modoPizza?.value || 'inteira';
            const isMeiaAMeia = modo === 'meia_a_meia';
            if (campoSegundoSabor) campoSegundoSabor.hidden = !isMeiaAMeia;
            if (segundoSabor) {
                segundoSabor.required = isMeiaAMeia;
                segundoSabor.disabled = !isMeiaAMeia;
                opcoesSaborSegundo().forEach((option) => {
                    option.disabled = option.value === itemPendente.id;
                });
                if (!isMeiaAMeia || segundoSabor.value === itemPendente.id) segundoSabor.value = '';
            }

            const sabor2 = segundoSabor?.selectedOptions?.[0];
            const precos1 = itemPendente.precos || {};
            const precos2 = lerPrecos(sabor2?.dataset.precos);
            const bordas1 = itemPendente.bordas || [];
            const bordas2 = lerBordas(sabor2?.dataset.bordas);
            const bordasDisponiveis = isMeiaAMeia
                ? bordas1.filter((borda) => bordas2.some((outra) => String(outra.id) === String(borda.id)))
                : bordas1;
            const bordaAnterior = bordaPizza?.value || '';
            if (bordaPizza) {
                bordaPizza.replaceChildren(new Option('Escolha uma opção', ''), new Option('Sem borda', '0'));
                bordasDisponiveis.forEach((borda) => bordaPizza.add(new Option(borda.nome, String(borda.id))));
                if ([...bordaPizza.options].some((option) => option.value === bordaAnterior)) bordaPizza.value = bordaAnterior;
            }
            const tamanhos1 = Object.keys(precos1);
            const tamanhos2 = Object.keys(precos2);
            const usaTamanho = tamanhos1.length > 0 || (isMeiaAMeia && tamanhos2.length > 0);
            const tamanhosComuns = ['P', 'M', 'G', 'GG', 'XG'].filter((tamanho) =>
                tamanhos1.includes(tamanho) && (!isMeiaAMeia || tamanhos2.includes(tamanho))
            );

            if (tamanhoPizza) {
                const anterior = tamanhoPizza.value;
                const bordaSelecionada = bordasDisponiveis.find((borda) => String(borda.id) === String(bordaPizza?.value));
                tamanhoPizza.replaceChildren(new Option('Selecione', ''));
                const fatias = { P: 4, M: 6, G: 8, GG: 10, XG: 12 };
                tamanhosComuns.forEach((tamanho) => {
                    const primeiroPreco = Number(precos1[tamanho] || 0);
                    const segundoPreco = isMeiaAMeia ? Number(precos2[tamanho] || 0) : 0;
                    const precoBase = isMeiaAMeia ? Math.max(primeiroPreco, segundoPreco) : primeiroPreco;
                    const adicional = Number(bordaSelecionada?.adicionais_por_tamanho?.[tamanho] || 0);
                    const rotuloBorda = bordaSelecionada ? `com ${bordaSelecionada.nome}` : 'sem borda';
                    const rotulo = `${tamanho} (${fatias[tamanho]} fatias) — ${formatarMoeda(precoBase + adicional)} ${rotuloBorda}`;
                    tamanhoPizza.add(new Option(rotulo, tamanho));
                });
                tamanhoPizza.disabled = tamanhosComuns.length === 0 || !bordaPizza?.value;
                tamanhoPizza.required = usaTamanho && tamanhosComuns.length > 0 && !!bordaPizza?.value;
                if (tamanhosComuns.includes(anterior)) tamanhoPizza.value = anterior;
            }
            if (campoTamanhoPizza) campoTamanhoPizza.hidden = tamanhosComuns.length === 0 || !bordaPizza?.value;

            const semTamanhoComum = isMeiaAMeia && !!sabor2?.value && usaTamanho && tamanhosComuns.length === 0;
            if (tamanhoIndisponivel) tamanhoIndisponivel.hidden = !semTamanhoComum;
            if (semTamanhoComum && confirmarObservacaoBtn) confirmarObservacaoBtn.disabled = true;

            const tamanho = tamanhoPizza?.value || '';
            const preco1 = tamanho ? Number(precos1[tamanho] || 0) : itemPendente.precoBase;
            const preco2 = tamanho ? Number(precos2[tamanho] || 0) : Number(sabor2?.dataset.preco || 0);
            const borda = bordaPizza?.value || '';
            if (isMeiaAMeia && !sabor2?.value) {
                if (previewPersonalizacao) previewPersonalizacao.textContent = 'Escolha o segundo sabor para calcular o preço.';
                if (confirmarObservacaoBtn) confirmarObservacaoBtn.disabled = true;
            } else if (!borda) {
                if (previewPersonalizacao) previewPersonalizacao.textContent = 'Escolha a borda antes do tamanho.';
                if (confirmarObservacaoBtn) confirmarObservacaoBtn.disabled = true;
            } else if (isMeiaAMeia && usaTamanho && !tamanho) {
                if (previewPersonalizacao) previewPersonalizacao.textContent = 'Selecione o tamanho da pizza.';
                if (confirmarObservacaoBtn) confirmarObservacaoBtn.disabled = true;
            } else if (!isMeiaAMeia && usaTamanho && !tamanho) {
                if (previewPersonalizacao) previewPersonalizacao.textContent = 'Selecione o tamanho da pizza.';
                if (confirmarObservacaoBtn) confirmarObservacaoBtn.disabled = true;
            } else {
                const base = isMeiaAMeia ? Math.max(preco1, preco2) : preco1;
                const bordaSelecionada = bordasDisponiveis.find((opcao) => String(opcao.id) === String(borda));
                const extraBorda = tamanho ? Number(bordaSelecionada?.adicionais_por_tamanho?.[tamanho] || 0) : 0;
                preco = base + extraBorda;
                const descricaoBorda = bordaSelecionada ? `${bordaSelecionada.nome} (+${formatarMoeda(extraBorda)})` : 'Sem borda';
                const precoTexto = `Preço unitário estimado: ${formatarMoeda(preco)} · ${descricaoBorda}${isMeiaAMeia ? ' (maior preço entre os sabores)' : ''}.`;
                if (previewPersonalizacao) previewPersonalizacao.textContent = precoTexto;
            }

            itemPendente.personalizacao = {
                tipo: 'pizza',
                modo,
                ...(tamanho ? { tamanho } : {}),
                ...(borda ? { borda_id: borda } : {}),
                ...(isMeiaAMeia && sabor2?.value ? { sabor_2_id: sabor2.value } : {}),
            };
            itemPendente.preco = preco;
            itemPendente.nomeExibicao = tamanho ? `Pizza ${tamanho}` : 'Pizza';
            itemPendente.detalhePersonalizacao = isMeiaAMeia && sabor2?.value
                ? `1/2 ${itemPendente.nome} · 1/2 ${sabor2.textContent.trim()}`
                : `Sabor: ${itemPendente.nome}`;
            if (borda) itemPendente.detalhePersonalizacao += ` · ${bordasDisponiveis.find((opcao) => String(opcao.id) === String(borda))?.nome || 'Sem borda'}`;
            return;
        }

        if (isPorcao) {
            const meia = tipoPorcao?.value === 'meia';
            const opcaoMeiaPorcao = tipoPorcao?.querySelector('option[value="meia"]');
            if (opcaoMeiaPorcao) opcaoMeiaPorcao.disabled = !itemPendente.aceitaMeia;
            if (meia && !itemPendente.aceitaMeia) {
                if (confirmarObservacaoBtn) confirmarObservacaoBtn.disabled = true;
            } else if (meia) {
                preco = itemPendente.precoMeia;
            }
            itemPendente.personalizacao = { tipo: 'porcao', tipo_porcao: meia ? 'meia' : 'inteira' };
            itemPendente.preco = preco;
            itemPendente.detalhePersonalizacao = meia ? 'Meia porção' : 'Porção inteira';
            if (previewPersonalizacao) previewPersonalizacao.textContent = `Preço unitário: ${formatarMoeda(preco)}.`;
            return;
        }

        if (previewPersonalizacao) previewPersonalizacao.textContent = '';
        itemPendente.preco = preco;
    }

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
        return Number.isFinite(quantidade) && quantidade > 0 ? Math.min(99, quantidade) : 1;
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
            row.dataset.produtoId = item.chave;

            const info = document.createElement('div');
            info.className = 'garcom-order-item__info';

            const nome = document.createElement('strong');
            nome.textContent = item.nomeExibicao || item.nome;

            const detalhe = document.createElement('small');
            detalhe.textContent = `${formatarMoeda(item.preco)} cada`;

            info.append(nome, detalhe);
            if (item.detalhePersonalizacao) {
                const personalizacao = document.createElement('small');
                personalizacao.textContent = item.detalhePersonalizacao;
                info.appendChild(personalizacao);
            }
            if (item.observacao) {
                const observacao = document.createElement('small');
                observacao.className = 'garcom-order-item__observacao';
                observacao.textContent = `Obs.: ${item.observacao}`;
                info.appendChild(observacao);
            }

            const controls = document.createElement('div');
            controls.className = 'garcom-order-item__controls';

            const decBtn = criarBotao('garcom-order-icon-btn', '-', 'decrement', item.chave);
            const qtdInput = document.createElement('input');
            qtdInput.type = 'number';
            qtdInput.min = '1';
            qtdInput.max = '99';
            qtdInput.value = String(item.quantidade);
            qtdInput.className = 'garcom-order-qtd';
            qtdInput.dataset.orderQtd = item.chave;

            const incBtn = criarBotao('garcom-order-icon-btn', '+', 'increment', item.chave);
            const removeBtn = criarBotao('garcom-order-remove', 'Remover', 'remove', item.chave);

            controls.append(decBtn, qtdInput, incBtn, removeBtn);
            row.append(info, controls);
            orderList.appendChild(row);

            adicionarInputOculto(`itens[${index}][produto_id]`, item.id);
            adicionarInputOculto(`itens[${index}][quantidade]`, item.quantidade);
            adicionarInputOculto(`itens[${index}][observacao]`, item.observacao || '');
            if (item.personalizacao) {
                Object.entries(item.personalizacao).forEach(([campo, valor]) => {
                    if (valor !== null && valor !== '') {
                        adicionarInputOculto(`itens[${index}][personalizacao][${campo}]`, valor);
                    }
                });
            }
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

    function adicionarItemPendente(observacao) {
        if (!itemPendente) return;

        const observacaoNormalizada = String(observacao || '').trim();
        const personalizacaoNormalizada = itemPendente.personalizacao || {};
        const chave = `${itemPendente.id}|${JSON.stringify(personalizacaoNormalizada)}|${observacaoNormalizada}`;
        const itemAtual = pedido.get(chave);
        if (itemAtual) {
            itemAtual.quantidade = Math.min(99, itemAtual.quantidade + itemPendente.quantidade);
        } else {
            pedido.set(chave, { ...itemPendente, chave, observacao: observacaoNormalizada });
        }

        itemPendente = null;
        if (observacaoInput) observacaoInput.value = '';
        observacaoModal?.close();
        renderizarPedido();
        mostrarPopup('Produto adicionado a lista.');
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
            const aceitaMeia = row.dataset.aceitaMeia === '1';

            if (qtdInput) {
                qtdInput.value = String(quantidade);
            }

            itemPendente = {
                id: produtoId,
                nome,
                tipo: row.dataset.produtoTipo || '',
                preco,
                precoBase: preco,
                precoMeia: parsePreco(row.dataset.precoMeia),
                aceitaMeia,
                precos: lerPrecos(row.dataset.precos),
                bordas: lerBordas(row.dataset.bordas),
                quantidade,
            };
            if (modoPizza) modoPizza.value = 'inteira';
            if (segundoSabor) segundoSabor.value = '';
            if (tamanhoPizza) tamanhoPizza.value = '';
            if (bordaPizza) bordaPizza.value = '';
            if (tipoPorcao) {
                tipoPorcao.value = 'inteira';
                const meiaOption = tipoPorcao.querySelector('option[value="meia"]');
                if (meiaOption) {
                    meiaOption.disabled = !aceitaMeia;
                    meiaOption.textContent = aceitaMeia
                        ? `Meia porção — ${formatarMoeda(itemPendente.precoMeia)}`
                        : 'Meia porção — indisponível';
                }
            }
            sincronizarPersonalizacao();
            if (observacaoProduto) observacaoProduto.textContent = `${quantidade}x ${nome}`;
            if (observacaoInput) observacaoInput.value = '';

            if (observacaoModal?.showModal) {
                observacaoModal.showModal();
                setTimeout(() => observacaoInput?.focus(), 0);
            } else {
                adicionarItemPendente(window.prompt('Alguma observação? (opcional)') || '');
            }
        });
    });

    observacaoModal?.querySelector('form')?.addEventListener('submit', function (event) {
        event.preventDefault();
        if (confirmarObservacaoBtn?.disabled) return;
        adicionarItemPendente(observacaoInput?.value || '');
    });

    [modoPizza, segundoSabor, tamanhoPizza, bordaPizza, tipoPorcao].forEach((campo) => {
        campo?.addEventListener('change', sincronizarPersonalizacao);
    });

    observacaoSemBtn?.addEventListener('click', function () {
        itemPendente = null;

        if (observacaoInput) {
            observacaoInput.value = '';
        }

        observacaoModal?.close();
    });

    observacaoModal?.addEventListener('cancel', function () {
        itemPendente = null;
    });

    orderList?.addEventListener('click', function (event) {
        const target = event.target.closest('[data-order-action]');
        if (!target) return;

        const item = pedido.get(target.dataset.produtoId);
        if (!item) return;

        if (target.dataset.orderAction === 'increment') {
            item.quantidade = Math.min(99, item.quantidade + 1);
        }

        if (target.dataset.orderAction === 'decrement') {
            item.quantidade -= 1;
            if (item.quantidade < 1) {
                pedido.delete(item.chave);
            }
        }

        if (target.dataset.orderAction === 'remove') {
            pedido.delete(item.chave);
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
