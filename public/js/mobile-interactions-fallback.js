(function () {
    const mobileMq = window.matchMedia('(max-width: 768px)');
    const collapsedClass = 'ff-sidebar-collapsed';
    const openClass = 'ff-sidebar-open';

    function getTarget(event) {
        return event.target instanceof Element ? event.target : null;
    }

    function syncSidebar(isCollapsed) {
        document.body.classList.toggle(collapsedClass, isCollapsed);
        document.body.classList.toggle(openClass, mobileMq.matches && !isCollapsed);
        document.body.style.overflow = mobileMq.matches && !isCollapsed ? 'hidden' : '';
    }

    function closeSidebar() {
        syncSidebar(true);
    }

    function openSidebar() {
        syncSidebar(false);
    }

    function setupSidebar() {
        if (mobileMq.matches) {
            closeSidebar();
        }

        function closeUserDropdowns() {
            document.querySelectorAll('.ff-sidebar__footer .dropdown-menu.show').forEach(function (menu) {
                menu.classList.remove('show');
            });

            document.querySelectorAll('.ff-sidebar__footer .ff-sidebar__user-btn[aria-expanded="true"]').forEach(function (button) {
                button.setAttribute('aria-expanded', 'false');
            });
        }

        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const userButton = target.closest('.ff-sidebar__user-btn');
            if (userButton) {
                event.preventDefault();
                event.stopPropagation();

                const dropdown = userButton.closest('.dropdown');
                const menu = dropdown ? dropdown.querySelector('.dropdown-menu') : null;
                if (!menu) return;

                const shouldOpen = !menu.classList.contains('show');
                closeUserDropdowns();

                if (shouldOpen) {
                    menu.classList.add('show');
                    userButton.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            if (!target.closest('.ff-sidebar__footer .dropdown')) {
                closeUserDropdowns();
            }
        }, true);

        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const toggle = target.closest('[data-sidebar-toggle]');
            if (toggle) {
                event.preventDefault();
                event.stopPropagation();

                if (mobileMq.matches && document.body.classList.contains(openClass)) {
                    closeSidebar();
                } else {
                    syncSidebar(!document.body.classList.contains(collapsedClass));
                }
                return;
            }

            const link = target.closest('.ff-sidebar a.nav-link');
            if (link && mobileMq.matches) {
                if (link.href && !link.classList.contains('disabled')) {
                    event.preventDefault();
                    event.stopPropagation();
                    window.location.href = link.href;
                    return;
                }

                closeSidebar();
            }
        }, true);

        if (mobileMq.addEventListener) {
            mobileMq.addEventListener('change', function () {
                if (mobileMq.matches) {
                    closeSidebar();
                } else {
                    document.body.classList.remove(openClass, collapsedClass);
                    document.body.style.overflow = '';
                }
            });
        }
    }

    function setupCartModal() {
        const modal = document.getElementById('addToCartModal');
        if (!modal) return;

        let backdrop = null;
        let lastOpenAt = 0;

        const inputProdutoId = modal.querySelector('#cart_produto_id');
        const inputProdutoNome = modal.querySelector('#cart_produto_nome');
        const inputPreco = modal.querySelector('#cart_preco');
        const inputQtd = modal.querySelector('#cart_quantidade');
        const inputObs = modal.querySelector('#cart_observacao');

        function cleanupModalState() {
            document.querySelectorAll('.modal-backdrop').forEach(function (item) {
                item.remove();
            });
            document.body.classList.remove('modal-open', 'ff-modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            if (mobileMq.matches) {
                document.body.classList.remove(openClass);
                document.body.classList.add(collapsedClass);
            }
        }

        function closeModal() {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');

            if (backdrop) {
                backdrop.remove();
                backdrop = null;
            }

            cleanupModalState();
            window.setTimeout(cleanupModalState, 80);
            window.dispatchEvent(new CustomEvent('ff:cart-modal-force-cleanup'));
        }

        function openModal(card) {
            const now = Date.now();
            if (now - lastOpenAt < 350) return;
            lastOpenAt = now;

            closeSidebar();

            if (inputProdutoId) inputProdutoId.value = card.getAttribute('data-produto-id') || '';
            if (inputProdutoNome) inputProdutoNome.value = card.getAttribute('data-produto-nome') || '';
            if (inputPreco) inputPreco.value = card.getAttribute('data-produto-preco') || '';
            if (inputQtd) inputQtd.value = '1';
            if (inputObs) inputObs.value = '';

            modal.classList.add('show');
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            modal.setAttribute('aria-modal', 'true');
            document.body.classList.add('modal-open', 'ff-modal-open');
            document.body.style.overflow = 'hidden';

            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.addEventListener('click', closeModal);
            document.body.appendChild(backdrop);
        }

        closeModal();

        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const closeButton = target.closest('#addToCartModal [data-bs-dismiss="modal"]');
            if (closeButton) {
                event.preventDefault();
                event.stopPropagation();
                closeModal();
                return;
            }

            const button = target.closest('.button-adicionar');
            const carouselCard = target.closest('.produto-card-mini');
            const card = button ? button.closest('[data-produto-id]') : carouselCard;
            if (!card) return;

            event.preventDefault();
            event.stopPropagation();
            openModal(card);
        }, true);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
                closeSidebar();
            }
        });
    }

    function setupBootstrapModalFallback() {
        function getModalFromTrigger(trigger) {
            const selector = trigger.getAttribute('data-bs-target') || trigger.getAttribute('href');
            if (!selector || !selector.startsWith('#')) return null;
            return document.querySelector(selector);
        }

        function removeBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (item) {
                item.remove();
            });
        }

        function closeModal(modal) {
            if (!modal) return;

            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');

            removeBackdrops();
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        function openModal(modal) {
            if (!modal) return;

            document.querySelectorAll('.modal.show').forEach(closeModal);
            removeBackdrops();

            modal.style.display = 'block';
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            modal.classList.add('show');

            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.addEventListener('click', function () {
                closeModal(modal);
            });
            document.body.appendChild(backdrop);

            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        }

        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const trigger = target.closest('[data-bs-toggle="modal"]');
            if (trigger) {
                const modal = getModalFromTrigger(trigger);
                if (!modal) return;

                event.preventDefault();
                event.stopPropagation();
                openModal(modal);
                return;
            }

            const closeButton = target.closest('.modal [data-bs-dismiss="modal"], .modal .btn-close');
            if (closeButton) {
                const modal = closeButton.closest('.modal');
                event.preventDefault();
                event.stopPropagation();
                closeModal(modal);
            }
        }, true);

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('.modal.show').forEach(closeModal);
        });
    }

    function setupMesaDetalhesFallback() {
        if (window.__ffMesaDetalhesAbaterInitialized) return;

        const modal = document.getElementById('abaterModal');
        const btnOpen = document.getElementById('btnAbrirAbaterModal');
        const btnConfirm = document.getElementById('btnConfirmarAbater');
        const totalText = document.getElementById('abaterTotalTexto');
        const valorInput = document.getElementById('abaterValorInput');
        const errorBox = document.getElementById('abaterModalErro');
        const form = document.getElementById('abaterForm');

        if (!modal || !btnOpen || !btnConfirm || !totalText || !errorBox || !form) return;

        window.__ffMesaDetalhesAbaterInitialized = true;
        let valorFoiEditado = false;

        function formatBRL(value) {
            try {
                return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            } catch (_) {
                return 'R$ ' + value.toFixed(2).replace('.', ',');
            }
        }

        function parseValorBRL(raw) {
            if (!raw) return NaN;
            return parseFloat(String(raw)
                .trim()
                .replace(/\s/g, '')
                .replace(/^R\$/i, '')
                .replace(/\./g, '')
                .replace(',', '.'));
        }

        function getSelectedCheckboxes() {
            return Array.from(document.querySelectorAll('input[name="item_ids[]"]:checked'));
        }

        function getCheckboxByItemId(itemId) {
            return document.querySelector('input[name="item_ids[]"][value="' + itemId + '"]');
        }

        function getQtdInputByItemId(itemId) {
            return document.querySelector('input[data-qtd-input][data-item-id="' + itemId + '"]');
        }

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function syncCheckboxWithQtd(itemId) {
            const checkbox = getCheckboxByItemId(itemId);
            const input = getQtdInputByItemId(itemId);
            if (!checkbox || !input) return;
            const qtd = parseInt(input.value || '0', 10);
            checkbox.checked = Number.isFinite(qtd) && qtd > 0;
        }

        function calcSelectedTotal() {
            return getSelectedCheckboxes().reduce(function (sum, checkbox) {
                const unit = parseFloat((checkbox.getAttribute('data-unit') || '0').replace(',', '.'));
                const pago = parseFloat((checkbox.getAttribute('data-pago') || '0').replace(',', '.'));
                const input = getQtdInputByItemId(checkbox.value);
                const qtd = input ? parseInt(input.value || '0', 10) : 0;

                if (!Number.isFinite(unit) || !Number.isFinite(qtd) || qtd <= 0) return sum;
                if (Number.isFinite(pago) && pago > 0) return sum + Math.max(0, unit - pago);
                return sum + (unit * qtd);
            }, 0);
        }

        function updateModalTotal() {
            const total = calcSelectedTotal();
            totalText.textContent = formatBRL(total);
            if (valorInput && !valorFoiEditado) {
                valorInput.value = total.toFixed(2).replace('.', ',');
            }
        }

        function openAbaterModal() {
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('is-open');
        }

        function closeAbaterModal() {
            modal.setAttribute('aria-hidden', 'true');
            modal.classList.remove('is-open');
        }

        function getPagamentoSelecionado() {
            const selected = document.querySelector('input[name="pagamento_metodo"]:checked');
            return selected ? selected.value : '';
        }

        document.querySelectorAll('form[data-confirm-remove-item]').forEach(function (removeForm) {
            removeForm.addEventListener('submit', function (event) {
                if (!window.confirm('Remover este item da comanda?')) {
                    event.preventDefault();
                }
            });
        });

        btnOpen.addEventListener('click', function () {
            errorBox.textContent = '';
            valorFoiEditado = false;
            if (valorInput) valorInput.value = '';

            getSelectedCheckboxes().forEach(function (checkbox) {
                const input = getQtdInputByItemId(checkbox.value);
                const max = parseInt(checkbox.getAttribute('data-max') || '0', 10);
                const cur = input ? parseInt(input.value || '0', 10) : 0;
                if (input && (!Number.isFinite(cur) || cur <= 0)) {
                    input.value = Number.isFinite(max) && max > 0 ? String(max) : '1';
                }
            });

            if (getSelectedCheckboxes().length === 0) {
                errorBox.textContent = 'Selecione pelo menos um item para abater.';
            }

            updateModalTotal();
            openAbaterModal();
        });

        document.querySelectorAll('[data-abater-modal-close]').forEach(function (element) {
            element.addEventListener('click', closeAbaterModal);
        });

        const overlay = modal.querySelector('.ff-modal__overlay');
        if (overlay) overlay.addEventListener('click', closeAbaterModal);

        document.querySelectorAll('input[name="item_ids[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const input = getQtdInputByItemId(checkbox.value);
                const max = parseInt(checkbox.getAttribute('data-max') || '0', 10);
                if (input) {
                    if (checkbox.checked) {
                        const cur = parseInt(input.value || '0', 10);
                        if (!Number.isFinite(cur) || cur <= 0) {
                            input.value = Number.isFinite(max) && max > 0 ? String(max) : '1';
                        }
                    } else {
                        input.value = '0';
                    }
                }
                if (modal.classList.contains('is-open')) updateModalTotal();
            });
        });

        document.querySelectorAll('input[data-qtd-input]').forEach(function (input) {
            input.addEventListener('input', function () {
                const itemId = input.getAttribute('data-item-id');
                const checkbox = getCheckboxByItemId(itemId);
                const max = checkbox ? parseInt(checkbox.getAttribute('data-max') || '0', 10) : 0;
                const cur = parseInt(input.value || '0', 10);
                input.value = String(clamp(Number.isFinite(cur) ? cur : 0, 0, Number.isFinite(max) ? max : 999));
                syncCheckboxWithQtd(itemId);
                if (modal.classList.contains('is-open')) updateModalTotal();
            });
        });

        document.querySelectorAll('[data-qtd-inc], [data-qtd-dec]').forEach(function (button) {
            button.addEventListener('click', function () {
                const itemId = button.getAttribute('data-item-id');
                const input = getQtdInputByItemId(itemId);
                const checkbox = getCheckboxByItemId(itemId);
                if (!input || !checkbox) return;

                const max = parseInt(checkbox.getAttribute('data-max') || '0', 10);
                const cur = parseInt(input.value || '0', 10);
                const delta = button.hasAttribute('data-qtd-inc') ? 1 : -1;
                input.value = String(clamp((Number.isFinite(cur) ? cur : 0) + delta, 0, Number.isFinite(max) ? max : 999));
                syncCheckboxWithQtd(itemId);
                if (modal.classList.contains('is-open')) updateModalTotal();
            });
        });

        if (valorInput) {
            valorInput.addEventListener('input', function () {
                valorFoiEditado = true;
            });
        }

        btnConfirm.addEventListener('click', function () {
            errorBox.textContent = '';

            const selected = getSelectedCheckboxes();
            if (selected.length === 0) {
                errorBox.textContent = 'Selecione pelo menos um item para abater.';
                return;
            }

            for (const checkbox of selected) {
                const input = getQtdInputByItemId(checkbox.value);
                const max = parseInt(checkbox.getAttribute('data-max') || '0', 10);
                const qtd = input ? parseInt(input.value || '0', 10) : 0;
                if (!Number.isFinite(qtd) || qtd < 1 || (Number.isFinite(max) && max > 0 && qtd > max)) {
                    errorBox.textContent = 'Informe uma quantidade valida para cada item selecionado.';
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
                errorBox.textContent = 'Digite um valor de pagamento valido.';
                return;
            }

            if (valorDigitado - total > 0.009) {
                errorBox.textContent = 'O valor digitado (' + formatBRL(valorDigitado) + ') nao pode ser maior que o total selecionado (' + formatBRL(total) + ').';
                return;
            }

            form.submit();
        });
    }

    function setupPedidosAccordionFallback() {
        document.addEventListener('input', function (event) {
            const target = getTarget(event);
            if (!target || !target.matches('[data-filtro-clientes-entregues], [data-filtro-dia-entregues]')) return;

            const container = target.closest('.acordeao-pedidos__conteudo');
            if (!container) return;

            const clientInput = container.querySelector('[data-filtro-clientes-entregues]');
            const dateInput = container.querySelector('[data-filtro-dia-entregues]');
            const term = clientInput ? clientInput.value.trim().toLocaleLowerCase('pt-BR') : '';
            const day = dateInput ? dateInput.value : '';

            container.querySelectorAll('.pedido-card').forEach(function (card) {
                const clientElement = card.querySelector('.pedido-card__cliente');
                const client = card.dataset.cliente
                    ? card.dataset.cliente.toLocaleLowerCase('pt-BR')
                    : (clientElement ? clientElement.textContent.toLocaleLowerCase('pt-BR') : '');
                const cardDay = card.dataset.pedidoData || '';
                const matchesClient = term === '' || client.includes(term);
                const matchesDay = day === '' || cardDay === day;

                card.classList.toggle('is-filter-hidden', !matchesClient || !matchesDay);
            });
        });

        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const summary = target.closest('.pedido-collapse__summary');
            if (!summary) return;

            const details = summary.closest('.pedido-collapse');
            if (!details) return;

            event.preventDefault();
            event.stopPropagation();

            const shouldOpen = !details.open;
            const group = details.closest('.lista-pedidos-admin, .acordeao-pedidos__conteudo');

            if (group) {
                group.querySelectorAll('.pedido-collapse[open]').forEach(function (other) {
                    if (other !== details) {
                        other.open = false;
                    }
                });
            }

            details.open = shouldOpen;
        }, true);

        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const button = target.closest('.acordeao-pedidos__gatilho');
            if (!button) return;

            const selector = button.getAttribute('data-target');
            const content = selector ? document.querySelector(selector) : null;
            if (!content) return;

            event.preventDefault();
            event.stopPropagation();

            const shouldOpen = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            content.hidden = !shouldOpen;
            content.classList.toggle('is-open', shouldOpen);
        }, true);
    }

    function setupIngredients() {
        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const trigger = target.closest('.ingredientes');
            if (!trigger) {
                if (!target.closest('.ingredientes-wrap')) {
                    document.querySelectorAll('.ingredientes-wrap.is-open').forEach(function (item) {
                        item.classList.remove('is-open');
                    });
                }
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const wrap = trigger.closest('.ingredientes-wrap');
            if (!wrap) return;

            document.querySelectorAll('.ingredientes-wrap.is-open').forEach(function (item) {
                if (item !== wrap) item.classList.remove('is-open');
            });
            wrap.classList.toggle('is-open');
        }, true);
    }

    function init() {
        setupSidebar();
        setupBootstrapModalFallback();
        setupMesaDetalhesFallback();
        setupPedidosAccordionFallback();
        setupCartModal();
        setupIngredients();

        window.ff = window.ff || {};
        window.ff.closeSidebar = closeSidebar;
        window.ff.syncSidebarLock = function () {};
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
