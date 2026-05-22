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

        document.addEventListener('click', function (event) {
            const target = getTarget(event);
            if (!target) return;

            const toggle = target.closest('[data-sidebar-toggle]');
            if (toggle) {
                if (!mobileMq.matches) return;

                event.preventDefault();
                event.stopPropagation();

                if (document.body.classList.contains(openClass)) {
                    closeSidebar();
                } else {
                    openSidebar();
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
