(function () {
    const checkboxSelector = '[data-cart-select]';
    const selectionFormSelector = '[data-cart-selection-form]';
    const checkoutButtonSelector = '[data-checkout-button]';
    const cartTotalSelector = '[data-cart-total]';
    const cartWarningSelector = '[data-cart-warning]';

    let pendingSelectionRequests = 0;

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function formatBRL(value) {
        return Number(value || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function getSelectedCheckboxes() {
        return Array.from(document.querySelectorAll(`${checkboxSelector}:checked`));
    }

    function calculateSelectedTotal() {
        return getSelectedCheckboxes().reduce((total, checkbox) => {
            return total + Number.parseFloat(checkbox.dataset.lineTotal || '0');
        }, 0);
    }

    function syncCheckoutSummary() {
        const selectedCount = getSelectedCheckboxes().length;
        const totalElement = document.querySelector(cartTotalSelector);
        const warningElement = document.querySelector(cartWarningSelector);
        const checkoutButton = document.querySelector(checkoutButtonSelector);

        if (totalElement) {
            totalElement.textContent = formatBRL(calculateSelectedTotal());
        }

        if (warningElement) {
            warningElement.hidden = selectedCount > 0;
        }

        if (checkoutButton) {
            checkoutButton.disabled = selectedCount === 0 || pendingSelectionRequests > 0;
            checkoutButton.textContent = pendingSelectionRequests > 0 ? 'Atualizando...' : 'Finalizar pedido';
        }
    }

    function submitSelection(form) {
        pendingSelectionRequests += 1;
        syncCheckoutSummary();

        const formData = new FormData(form);

        return fetch(form.action, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        }).then((response) => {
            if (!response.ok) {
                throw new Error('Nao foi possivel atualizar a selecao.');
            }

            return response.json().catch(() => ({}));
        }).finally(() => {
            pendingSelectionRequests = Math.max(0, pendingSelectionRequests - 1);
            syncCheckoutSummary();
        });
    }

    function openCheckoutModalFallback() {
        const modal = document.getElementById('finalizarModal');
        if (!modal || modal.classList.contains('is-open')) return;

        openModal(modal);
        modal.querySelector('input[name="tipo_entrega"]')?.focus();
    }

    function closeAllModals() {
        document.querySelectorAll('.ff-modal.is-open').forEach((modal) => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        });

        document.body.classList.remove('ff-modal-open');
        document.body.style.removeProperty('overflow');
    }

    function openModal(modal) {
        if (!modal) return;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('ff-modal-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.ff-modal.is-open')) {
            document.body.classList.remove('ff-modal-open');
            document.body.style.removeProperty('overflow');
        }
    }

    function focusModal(modal) {
        const firstField = modal?.querySelector('input, select, textarea, button');
        firstField?.focus();
    }

    function openOnly(modal) {
        closeAllModals();
        openModal(modal);
        focusModal(modal);
    }

    function hasSavedAddresses() {
        return Boolean(document.querySelector('#enderecoForm input[name="endereco_opcao"]'));
    }

    function submitPickupOrder(form) {
        if (!form || window.__ffPickupOrderSubmitting) return;

        window.__ffPickupOrderSubmitting = true;

        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Finalizando...';
        }

        HTMLFormElement.prototype.submit.call(form);
    }

    function bindCheckoutFallbackFlow() {
        const tipoForm = document.getElementById('tipoEntregaForm');

        tipoForm?.addEventListener('submit', (event) => {
            event.preventDefault();

            const deliveryType = tipoForm.querySelector('input[name="tipo_entrega"]:checked')?.value;

            if (deliveryType === 'retirar') {
                submitPickupOrder(tipoForm);
                return;
            }

            openOnly(document.getElementById(hasSavedAddresses() ? 'enderecoModal' : 'enderecoNovoModal'));
        });

        document.querySelectorAll('[data-modal-back]').forEach((button) => {
            button.addEventListener('click', () => {
                const currentModal = button.closest('.ff-modal');
                const targetModal = document.getElementById(button.dataset.modalBack || 'finalizarModal');

                closeModal(currentModal);
                openModal(targetModal);
                focusModal(targetModal);
            });
        });

        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const currentModal = button.closest('.ff-modal');
                const targetModal = document.getElementById(button.dataset.modalOpen);

                closeModal(currentModal);
                openModal(targetModal);
                focusModal(targetModal);
            });
        });

        const initialModalId = document.body?.dataset?.openModal;
        if (initialModalId) {
            openOnly(document.getElementById(initialModalId));
        }
    }

    function closeCheckoutModalFallback(event) {
        const closeTrigger = event.target.closest('[data-modal-close], .ff-modal__overlay');
        if (!closeTrigger) return;

        closeAllModals();
    }

    ready(() => {
        document.querySelectorAll(selectionFormSelector).forEach((form) => {
            const checkbox = form.querySelector(checkboxSelector);
            if (!checkbox) return;

            checkbox.addEventListener('change', () => {
                syncCheckoutSummary();

                submitSelection(form).catch(() => {
                    form.submit();
                });
            });
        });

        const checkoutButton = document.querySelector(checkoutButtonSelector);
        if (checkoutButton) {
            checkoutButton.addEventListener('click', () => {
                window.setTimeout(openCheckoutModalFallback, 0);
            });
        }

        document.addEventListener('click', closeCheckoutModalFallback);
        bindCheckoutFallbackFlow();
        syncCheckoutSummary();
    });
})();
