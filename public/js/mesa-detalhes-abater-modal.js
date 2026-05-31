(function () {
    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    }

    ready(function () {
        if (window.__ffMesaDetalhesAbaterInitialized) return;
        window.__ffMesaDetalhesAbaterInitialized = true;

        document.querySelectorAll('form[data-confirm-remove-item]').forEach(function (removeForm) {
            removeForm.addEventListener('submit', function (event) {
                if (!window.confirm('Remover este item da comanda?')) {
                    event.preventDefault();
                }
            });
        });

        const modal = document.getElementById('abaterModal');
        const btnOpen = document.getElementById('btnAbrirAbaterModal');
        const btnConfirm = document.getElementById('btnConfirmarAbater');
        const totalText = document.getElementById('abaterTotalTexto');
        const checkoutTotalText = document.getElementById('mesaCheckoutTotalTexto');
        const checkoutHint = document.getElementById('mesaCheckoutHint');
        const valorInput = document.getElementById('abaterValorInput');
        const errorBox = document.getElementById('abaterModalErro');
        const form = document.getElementById('abaterForm');

        if (!modal || !btnOpen || !btnConfirm || !totalText || !errorBox || !form) return;

        const overlay = modal.querySelector('.ff-modal__overlay');
        let valorFoiEditado = false;

        function formatBRL(value) {
            return Number(value || 0).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });
        }

        function parseCurrency(raw) {
            if (!raw) return NaN;

            return Number.parseFloat(String(raw)
                .trim()
                .replace(/\s/g, '')
                .replace(/^R\$/i, '')
                .replace(/\./g, '')
                .replace(',', '.'));
        }

        function selectedCheckboxes() {
            return Array.from(document.querySelectorAll('input[name="item_ids[]"]:checked'));
        }

        function getCheckboxByItemId(itemId) {
            return document.querySelector('input[name="item_ids[]"][value="' + itemId + '"]');
        }

        function getQtyInputByItemId(itemId) {
            return document.querySelector('input[data-qtd-input][data-item-id="' + itemId + '"]');
        }

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function calcSelectedTotal() {
            return selectedCheckboxes().reduce(function (sum, checkbox) {
                const unit = Number.parseFloat((checkbox.getAttribute('data-unit') || '0').replace(',', '.'));
                const paid = Number.parseFloat((checkbox.getAttribute('data-pago') || '0').replace(',', '.'));
                const itemId = checkbox.value;
                const qtyInput = getQtyInputByItemId(itemId);
                const qty = qtyInput ? Number.parseInt(qtyInput.value || '0', 10) : 0;

                if (!Number.isFinite(unit) || !Number.isFinite(qty) || qty <= 0) return sum;

                if (Number.isFinite(paid) && paid > 0) {
                    return sum + Math.max(0, unit - paid);
                }

                return sum + (unit * qty);
            }, 0);
        }

        function syncCheckboxWithQty(itemId) {
            const checkbox = getCheckboxByItemId(itemId);
            const input = getQtyInputByItemId(itemId);
            if (!checkbox || !input) return;

            const qty = Number.parseInt(input.value || '0', 10);
            checkbox.checked = Number.isFinite(qty) && qty > 0;
        }

        function syncCheckoutBar() {
            const selected = selectedCheckboxes();
            const total = calcSelectedTotal();
            const hasSelection = selected.length > 0 && total > 0;

            if (checkoutTotalText) checkoutTotalText.textContent = formatBRL(total);
            if (checkoutHint) {
                checkoutHint.textContent = hasSelection
                    ? selected.length + ' item(ns) selecionado(s) para baixa.'
                    : 'Marque os itens que a pessoa vai pagar agora.';
            }

            btnOpen.disabled = !hasSelection;
        }

        function updateModalTotal() {
            const total = calcSelectedTotal();
            totalText.textContent = formatBRL(total);
            if (checkoutTotalText) checkoutTotalText.textContent = formatBRL(total);

            if (valorInput && !valorFoiEditado) {
                valorInput.value = total.toFixed(2).replace('.', ',');
            }
        }

        function openModal() {
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('is-open');
            document.body.classList.add('ff-modal-open');
            document.body.style.overflow = 'hidden';
            window.setTimeout(function () {
                if (valorInput) {
                    valorInput.focus();
                }
            }, 0);
        }

        function closeModal() {
            modal.setAttribute('aria-hidden', 'true');
            modal.classList.remove('is-open');
            document.body.classList.remove('ff-modal-open');
            document.body.style.removeProperty('overflow');
        }

        function ensureSelectedQuantities() {
            selectedCheckboxes().forEach(function (checkbox) {
                const itemId = checkbox.value;
                const max = Number.parseInt(checkbox.getAttribute('data-max') || '0', 10);
                const qtyInput = getQtyInputByItemId(itemId);
                if (!qtyInput) return;

                const current = Number.parseInt(qtyInput.value || '0', 10);
                if (!Number.isFinite(current) || current <= 0) {
                    qtyInput.value = Number.isFinite(max) && max > 0 ? String(max) : '1';
                }
            });
        }

        btnOpen.addEventListener('click', function () {
            errorBox.textContent = '';
            valorFoiEditado = false;
            if (valorInput) valorInput.value = '';

            ensureSelectedQuantities();
            syncCheckoutBar();
            updateModalTotal();
            openModal();
        });

        document.querySelectorAll('[data-abater-modal-close]').forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        if (overlay) overlay.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        document.querySelectorAll('input[name="item_ids[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const itemId = checkbox.value;
                const max = Number.parseInt(checkbox.getAttribute('data-max') || '0', 10);
                const qtyInput = getQtyInputByItemId(itemId);

                if (qtyInput) {
                    if (checkbox.checked) {
                        const current = Number.parseInt(qtyInput.value || '0', 10);
                        if (!Number.isFinite(current) || current <= 0) {
                            qtyInput.value = Number.isFinite(max) && max > 0 ? String(max) : '1';
                        }
                    } else {
                        qtyInput.value = '0';
                    }
                }

                valorFoiEditado = false;
                syncCheckoutBar();
                if (modal.classList.contains('is-open')) updateModalTotal();
            });
        });

        document.querySelectorAll('input[data-qtd-input]').forEach(function (input) {
            input.addEventListener('input', function () {
                const itemId = input.getAttribute('data-item-id');
                const checkbox = getCheckboxByItemId(itemId);
                const max = checkbox ? Number.parseInt(checkbox.getAttribute('data-max') || '0', 10) : 0;
                const current = Number.parseInt(input.value || '0', 10);
                const next = clamp(Number.isFinite(current) ? current : 0, 0, Number.isFinite(max) ? max : 999);

                input.value = String(next);
                syncCheckboxWithQty(itemId);
                valorFoiEditado = false;
                syncCheckoutBar();
                if (modal.classList.contains('is-open')) updateModalTotal();
            });
        });

        function bumpQty(itemId, delta) {
            const checkbox = getCheckboxByItemId(itemId);
            const input = getQtyInputByItemId(itemId);
            if (!checkbox || !input) return;

            const max = Number.parseInt(checkbox.getAttribute('data-max') || '0', 10);
            const current = Number.parseInt(input.value || '0', 10);
            const next = clamp((Number.isFinite(current) ? current : 0) + delta, 0, Number.isFinite(max) ? max : 999);

            input.value = String(next);
            syncCheckboxWithQty(itemId);
            valorFoiEditado = false;
            syncCheckoutBar();
            if (modal.classList.contains('is-open')) updateModalTotal();
        }

        document.querySelectorAll('[data-qtd-inc]').forEach(function (button) {
            button.addEventListener('click', function () {
                bumpQty(button.getAttribute('data-item-id'), 1);
            });
        });

        document.querySelectorAll('[data-qtd-dec]').forEach(function (button) {
            button.addEventListener('click', function () {
                bumpQty(button.getAttribute('data-item-id'), -1);
            });
        });

        if (valorInput) {
            valorInput.addEventListener('input', function () {
                valorFoiEditado = true;
            });
        }

        btnConfirm.addEventListener('click', function () {
            errorBox.textContent = '';

            const selected = selectedCheckboxes();
            if (selected.length === 0) {
                errorBox.textContent = 'Selecione pelo menos um item para dar baixa.';
                return;
            }

            for (const checkbox of selected) {
                const itemId = checkbox.value;
                const max = Number.parseInt(checkbox.getAttribute('data-max') || '0', 10);
                const qtyInput = getQtyInputByItemId(itemId);
                const qty = qtyInput ? Number.parseInt(qtyInput.value || '0', 10) : 0;

                if (!Number.isFinite(qty) || qty < 1 || (Number.isFinite(max) && max > 0 && qty > max)) {
                    errorBox.textContent = 'Informe uma quantidade valida para cada item selecionado.';
                    return;
                }
            }

            if (!document.querySelector('input[name="pagamento_metodo"]:checked')) {
                errorBox.textContent = 'Selecione uma forma de pagamento.';
                return;
            }

            const total = calcSelectedTotal();
            const typedValue = valorInput ? parseCurrency(valorInput.value) : NaN;

            if (!Number.isFinite(typedValue) || typedValue <= 0) {
                errorBox.textContent = 'Digite um valor de pagamento valido.';
                return;
            }

            if (typedValue - total > 0.009) {
                errorBox.textContent = 'O valor digitado nao pode ser maior que o total selecionado.';
                return;
            }

            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Confirmando...';
            form.submit();
        });

        syncCheckoutBar();
    });
})();
