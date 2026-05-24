const ADD_TO_CART_MODAL_ID = 'addToCartModal';
const PRODUCT_CARD_SELECTOR = '[data-produto-id]';
const ADD_BUTTON_SELECTOR = '.button-adicionar';
const CAROUSEL_CARD_SELECTOR = '.produto-card-mini';
const INGREDIENTS_SELECTOR = '.ingredientes';
const INGREDIENTS_WRAP_SELECTOR = '.ingredientes-wrap';
const OPEN_THROTTLE_MS = 500;

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function getEventTarget(event) {
  if (event.target instanceof Element) return event.target;
  if (event.target?.parentElement instanceof Element) return event.target.parentElement;
  return null;
}

function findProductCard(element) {
  return element?.closest(PRODUCT_CARD_SELECTOR) || null;
}

function syncBodyModalState(isOpen) {
  document.body.classList.toggle('modal-open', isOpen);
  document.body.classList.toggle('ff-modal-open', isOpen);

  if (isOpen) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
  }

  if (window.ff?.syncSidebarLock) {
    window.ff.syncSidebarLock();
  }
}

function cleanupBackdrops() {
  document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
}

function cleanupModalState() {
  cleanupBackdrops();
  syncBodyModalState(false);

  if (window.matchMedia('(max-width: 768px)').matches) {
    document.body.classList.remove('ff-sidebar-open');
    document.body.classList.add('ff-sidebar-collapsed');
  }
}

function createBackdrop(onClose) {
  const backdrop = document.createElement('div');
  backdrop.className = 'modal-backdrop fade show';
  backdrop.style.zIndex = '1999';
  backdrop.addEventListener('click', onClose);
  document.body.appendChild(backdrop);

  return backdrop;
}

function ensureCsrfField(form) {
  const csrf = getCsrfToken();
  if (!form || !csrf) return;

  let field = form.querySelector('input[name="_token"]');

  if (!field) {
    field = document.createElement('input');
    field.type = 'hidden';
    field.name = '_token';
    form.appendChild(field);
  }

  field.value = csrf;
}

function readProductData(card) {
  return {
    id: card.getAttribute('data-produto-id') || '',
    nome: card.getAttribute('data-produto-nome') || '',
    preco: card.getAttribute('data-produto-preco') || '',
  };
}

function fillCartForm(fields, product) {
  if (fields.produtoId) fields.produtoId.value = product.id;
  if (fields.produtoNome) fields.produtoNome.value = product.nome;
  if (fields.preco) fields.preco.value = product.preco;
  if (fields.quantidade) fields.quantidade.value = '1';
  if (fields.observacao) fields.observacao.value = '';
}

function createAddToCartController(modal) {
  const fields = {
    produtoId: modal.querySelector('#cart_produto_id'),
    produtoNome: modal.querySelector('#cart_produto_nome'),
    preco: modal.querySelector('#cart_preco'),
    quantidade: modal.querySelector('#cart_quantidade'),
    observacao: modal.querySelector('#cart_observacao'),
  };

  const state = {
    backdrop: null,
    lastOpenAt: 0,
  };

  function close() {
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');

    state.backdrop?.remove();
    state.backdrop = null;

    cleanupModalState();
    window.setTimeout(cleanupModalState, 80);
    window.dispatchEvent(new CustomEvent('ff:cart-modal-closed'));
  }

  function open() {
    if (state.backdrop && !state.backdrop.isConnected) {
      state.backdrop = null;
    }

    if (state.backdrop) return;

    if (window.ff?.closeSidebar) {
      window.ff.closeSidebar();
    }

    modal.classList.add('show');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    modal.setAttribute('aria-modal', 'true');
    syncBodyModalState(true);

    state.backdrop = createBackdrop(close);

    const focusTarget = fields.quantidade || modal.querySelector('button, input, textarea');
    focusTarget?.focus();

    window.setTimeout(() => window.scrollTo(0, 0), 100);
  }

  function openForProduct(card) {
    const now = Date.now();
    if (now - state.lastOpenAt < OPEN_THROTTLE_MS) return;

    state.lastOpenAt = now;
    fillCartForm(fields, readProductData(card));
    open();
  }

  function reset() {
    close();
    document.body.classList.remove('modal-open', 'ff-modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    cleanupBackdrops();
  }

  return { close, openForProduct, reset };
}

function handleProductTrigger(event, controller) {
  const target = getEventTarget(event);
  if (!target) return;

  const trigger = target.closest(ADD_BUTTON_SELECTOR);
  const card = trigger ? findProductCard(trigger) : findProductCard(target);
  if (!card) return;

  event.preventDefault?.();
  event.stopPropagation?.();

  controller.openForProduct(card);
}

function bindDirectAddButtons(controller) {
  document.querySelectorAll(ADD_BUTTON_SELECTOR).forEach((button) => {
    button.addEventListener('click', (event) => handleProductTrigger(event, controller));
  });
}

function bindCarouselCards(controller) {
  const openFromCarousel = (event) => {
    const target = getEventTarget(event);
    if (!target) return;
    if (target.closest(`#${ADD_TO_CART_MODAL_ID}`)) return;
    if (target.closest(ADD_BUTTON_SELECTOR)) return;

    const card = target.closest(CAROUSEL_CARD_SELECTOR);
    if (!card) return;

    handleProductTrigger(event, controller);
  };

  document.addEventListener('click', openFromCarousel);
  document.addEventListener('touchend', openFromCarousel, { passive: false });
}

function bindModalCloseEvents(modal, controller) {
  window.addEventListener('ff:cart-modal-force-cleanup', () => {
    cleanupModalState();
  });

  modal.querySelectorAll('[data-bs-dismiss="modal"]').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (!modal.classList.contains('show')) return;

      event.preventDefault();
      event.stopPropagation();
      controller.close();
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('show')) {
      controller.close();
    }
  });
}

function initAddToCartModal() {
  const modal = document.getElementById(ADD_TO_CART_MODAL_ID);
  if (!modal) return;

  const controller = createAddToCartController(modal);

  controller.reset();
  ensureCsrfField(modal.querySelector('form'));
  bindDirectAddButtons(controller);
  bindCarouselCards(controller);
  bindModalCloseEvents(modal, controller);
}

function closeOtherIngredientLists(currentWrap) {
  document.querySelectorAll(`${INGREDIENTS_WRAP_SELECTOR}.is-open`).forEach((wrap) => {
    if (wrap !== currentWrap) wrap.classList.remove('is-open');
  });
}

function bindIngredientsToggle() {
  document.addEventListener('click', (event) => {
    const target = getEventTarget(event);
    const trigger = target?.closest(INGREDIENTS_SELECTOR);

    if (!trigger) {
      if (!target?.closest(INGREDIENTS_WRAP_SELECTOR)) {
        closeOtherIngredientLists(null);
      }
      return;
    }

    const wrap = trigger.closest(INGREDIENTS_WRAP_SELECTOR);
    if (!wrap) return;

    closeOtherIngredientLists(wrap);
    wrap.classList.toggle('is-open');
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeOtherIngredientLists(null);
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initAddToCartModal();
  bindIngredientsToggle();
});
