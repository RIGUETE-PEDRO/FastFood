const ADD_TO_CART_MODAL_ID = 'addToCartModal';
const PRODUCT_CARD_SELECTOR = '[data-produto-id]';
const ADD_BUTTON_SELECTOR = '.button-adicionar';
const CAROUSEL_CARD_SELECTOR = '.produto-card-mini';
const INGREDIENTS_SELECTOR = '.ingredientes';
const INGREDIENTS_WRAP_SELECTOR = '.ingredientes-wrap';
const OPEN_THROTTLE_MS = 500;
const PIZZA_SIZES = { P: { slices: 4 }, M: { slices: 6 }, G: { slices: 8 }, GG: { slices: 10 }, XG: { slices: 12 } };
const CURRENCY_BRL = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

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

function readPrices(value) {
  try {
    return JSON.parse(value || '{}') || {};
  } catch {
    return {};
  }
}

function readBorders(value) {
  try {
    const borders = JSON.parse(value || '[]');
    return Array.isArray(borders) ? borders : [];
  } catch {
    return [];
  }
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
    tipo: card.getAttribute('data-produto-tipo') || '',
    aceitaMeia: card.getAttribute('data-aceita-meia') === '1',
    precoMeia: card.getAttribute('data-preco-meia') || '',
    precos: readPrices(card.getAttribute('data-precos')),
    bordas: readBorders(card.getAttribute('data-bordas')),
  };
}

function getSelectedPizza(select) {
  const option = select?.selectedOptions?.[0];
  if (!option || !option.value) return null;

  let prices = {};
  try {
    prices = JSON.parse(option.getAttribute('data-precos') || '{}') || {};
  } catch (error) {
    prices = {};
  }

  const borders = readBorders(option.getAttribute('data-bordas'));
  return {
    id: option.value,
    nome: option.getAttribute('data-nome') || option.textContent.trim(),
    price: Number(option.getAttribute('data-preco') || 0),
    prices,
    borders,
  };
}

function fillCartForm(fields, product, modal) {
  if (fields.produtoId) fields.produtoId.value = product.id;
  if (fields.produtoNome) fields.produtoNome.value = product.nome;
  if (fields.quantidade) fields.quantidade.value = '1';
  if (fields.observacao) fields.observacao.value = '';

  const pizzaOptions = modal.querySelector('#cartPizzaOptions');
  const pizzaType = modal.querySelector('#cart_personalizacao_tipo');
  const firstFlavor = modal.querySelector('#cart_pizza_sabor_1');
  const secondFlavor = modal.querySelector('#cart_pizza_sabor_2');
  const pizzaMode = modal.querySelector('#cart_pizza_modo');
  const pizzaBorder = modal.querySelector('#cart_pizza_borda');
  const portionOptions = modal.querySelector('#cartPortionOptions');
  const portionType = modal.querySelector('#cart_tipo_porcao');
  const pizzaSize = modal.querySelector('#cart_pizza_tamanho');

  if (pizzaOptions && firstFlavor && product.tipo === 'pizza') {
    [firstFlavor, secondFlavor].forEach((select) => {
      if (!select || Array.from(select.options).some((option) => option.value === product.id)) return;
      const option = new Option(product.nome, product.id);
      option.setAttribute('data-nome', product.nome);
      option.setAttribute('data-preco', product.preco);
      option.setAttribute('data-precos', JSON.stringify(product.precos || {}));
      option.setAttribute('data-bordas', JSON.stringify(product.bordas || []));
      select.add(option);
    });
    pizzaOptions.hidden = false;
    if (pizzaType) pizzaType.value = 'pizza';
    if (pizzaMode) {
      pizzaMode.disabled = false;
      pizzaMode.value = 'inteira';
      const halfMode = pizzaMode.querySelector('option[value="meia_a_meia"]');
      if (halfMode) halfMode.hidden = firstFlavor.options.length < 2;
    }
    if (pizzaBorder) {
      pizzaBorder.disabled = false;
      pizzaBorder.value = '';
    }
    if (pizzaSize) pizzaSize.value = '';
    if (firstFlavor) {
      firstFlavor.disabled = false;
      firstFlavor.value = product.id;
    }
    if (secondFlavor) {
      secondFlavor.disabled = false;
      secondFlavor.value = '';
    }
    if (portionOptions) portionOptions.hidden = true;
    if (portionType) portionType.disabled = true;
    syncPizzaOptions(modal, fields);
  } else {
    if (pizzaType) pizzaType.value = product.tipo === 'porcao' ? 'porcao' : '';
    if (pizzaOptions) pizzaOptions.hidden = true;
    if (pizzaMode) pizzaMode.disabled = true;
    if (pizzaBorder) {
      pizzaBorder.disabled = true;
      pizzaBorder.value = '';
    }
    if (firstFlavor) firstFlavor.disabled = true;
    if (secondFlavor) secondFlavor.disabled = true;

    const showPortionChoice = product.tipo === 'porcao';
    if (portionOptions) portionOptions.hidden = !showPortionChoice;
    if (portionType) {
      portionType.disabled = !showPortionChoice;
      portionType.value = 'inteira';
      const fullOption = portionType.options.namedItem('inteira') || portionType.options[0];
      const halfOption = portionType.options.namedItem('meia') || portionType.options[1];
      const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
      if (fullOption) fullOption.textContent = `Porção inteira — ${currency.format(Number(product.preco || 0))}`;
      if (halfOption) {
        halfOption.hidden = false;
        halfOption.disabled = !product.aceitaMeia;
        halfOption.textContent = product.aceitaMeia
          ? `Meia porção — ${currency.format(Number(product.precoMeia || 0))}`
          : 'Meia porção — indisponível';
      }
    }
  }
}

function syncPizzaOptions(modal, fields = {}) {
  fields.produtoId ||= modal.querySelector('#cart_produto_id');
  fields.produtoNome ||= modal.querySelector('#cart_produto_nome');
  const first = getSelectedPizza(modal.querySelector('#cart_pizza_sabor_1'));
  const secondSelect = modal.querySelector('#cart_pizza_sabor_2');
  const isHalf = modal.querySelector('#cart_pizza_modo')?.value === 'meia_a_meia';
  const secondField = modal.querySelector('#cartPizzaSecondFlavor');
  const sizeField = modal.querySelector('#cartPizzaSizeField');
  const sizeSelect = modal.querySelector('#cart_pizza_tamanho');
  const borderSelect = modal.querySelector('#cart_pizza_borda');
  const sizeUnavailable = modal.querySelector('#cartPizzaSizeUnavailable');
  const pricePreview = modal.querySelector('#cartPizzaPricePreview');

  if (secondField) secondField.hidden = !isHalf;
  if (secondSelect) {
    secondSelect.disabled = false;
    secondSelect.required = isHalf;
    if (first) {
      Array.from(secondSelect.options).forEach((option) => {
        option.disabled = option.value === first.id;
      });
    }
  }
  if (borderSelect) borderSelect.disabled = !first;

  if (first && fields.produtoId) fields.produtoId.value = first.id;
  if (first && fields.produtoNome) fields.produtoNome.value = first.nome;

  let second = getSelectedPizza(secondSelect);
  if (isHalf && first && second?.id === first.id) {
    secondSelect.value = '';
    second = null;
  }

  const firstSizes = Object.keys(first?.prices || {});
  const secondSizes = Object.keys(second?.prices || {});
  const firstBorders = first?.borders || [];
  const secondBorders = second?.borders || [];
  const availableBorders = isHalf
    ? firstBorders.filter((border) => secondBorders.some((candidate) => String(candidate.id) === String(border.id)))
    : firstBorders;
  const previousBorder = borderSelect?.value || '';
  if (borderSelect) {
    borderSelect.replaceChildren(new Option('Escolha uma opção', ''));
    borderSelect.add(new Option('Sem borda', '0'));
    availableBorders.forEach((border) => {
      borderSelect.add(new Option(border.nome, String(border.id)));
    });
    borderSelect.disabled = !first;
    if ([...borderSelect.options].some((option) => option.value === previousBorder)) {
      borderSelect.value = previousBorder;
    }
  }
  const hasSizePricing = firstSizes.length > 0 || (isHalf && secondSizes.length > 0);
  const commonSizes = ['P', 'M', 'G', 'GG', 'XG'].filter((size) =>
    firstSizes.includes(size) && (!isHalf || secondSizes.includes(size))
  );

  if (sizeSelect) {
    const previousSize = sizeSelect.value;
    const selectedBorder = availableBorders.find((border) => String(border.id) === String(borderSelect?.value));
    sizeSelect.replaceChildren(new Option('Selecione', ''));
    commonSizes.forEach((size) => {
      const firstPrice = Number(first?.prices?.[size] || 0);
      const secondPrice = isHalf ? Number(second?.prices?.[size] || 0) : 0;
      const basePrice = isHalf ? Math.max(firstPrice, secondPrice) : firstPrice;
      const extra = Number(selectedBorder?.adicionais_por_tamanho?.[size] || 0);
      const finalPrice = basePrice + extra;
      const borderText = selectedBorder ? `com ${selectedBorder.nome}` : 'sem borda';
      const label = `${size} (${PIZZA_SIZES[size].slices} fatias) — ${CURRENCY_BRL.format(finalPrice)} ${borderText}`;
      sizeSelect.add(new Option(label, size));
    });
    sizeSelect.disabled = commonSizes.length === 0 || !borderSelect?.value;
    sizeSelect.required = commonSizes.length > 0 && !!borderSelect?.value;
    if (commonSizes.includes(previousSize)) sizeSelect.value = previousSize;
  }

  if (sizeField) sizeField.hidden = commonSizes.length === 0 || !borderSelect?.value;
  if (sizeUnavailable) sizeUnavailable.hidden = !isHalf || !second || !hasSizePricing || commonSizes.length > 0;

  if (pricePreview && first) {
    if (isHalf && !second) {
      pricePreview.textContent = 'Escolha o segundo sabor para calcular o preço da pizza.';
      return;
    }
    if (!borderSelect?.value) {
      pricePreview.textContent = 'Escolha a borda antes do tamanho.';
      return;
    }
    const selectedSize = sizeSelect?.value || '';
    if (!selectedSize) {
      pricePreview.textContent = 'Selecione o tamanho da pizza.';
      return;
    }
    const firstPrice = Number(first.prices?.[selectedSize] || 0);
    const secondPrice = isHalf ? Number(second?.prices?.[selectedSize] || 0) : 0;
    const basePrice = isHalf ? Math.max(firstPrice, secondPrice) : firstPrice;
    const selectedBorder = availableBorders.find((border) => String(border.id) === String(borderSelect.value));
    const extra = Number(selectedBorder?.adicionais_por_tamanho?.[selectedSize] || 0);
    const price = basePrice + extra;
    const borderLabel = selectedBorder ? `${selectedBorder.nome} (+${CURRENCY_BRL.format(extra)})` : 'Sem borda';
    pricePreview.textContent = `Preço unitário: ${CURRENCY_BRL.format(price)} · ${borderLabel}${isHalf ? ' (maior preço entre os sabores)' : ''}.`;
  }
}

function createAddToCartController(modal) {
  const fields = {
    produtoId: modal.querySelector('#cart_produto_id'),
    produtoNome: modal.querySelector('#cart_produto_nome'),
    preco: modal.querySelector('#cart_preco'),
    quantidade: modal.querySelector('#cart_quantidade'),
    observacao: modal.querySelector('#cart_observacao'),
  };

  window.ff = window.ff || {};
  window.ff.prepareCartProduct = (card) => fillCartForm(fields, readProductData(card), modal);

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
    fillCartForm(fields, readProductData(card), modal);
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
  modal.querySelector('#cart_pizza_sabor_1')?.addEventListener('change', () => syncPizzaOptions(modal));
  modal.querySelector('#cart_pizza_sabor_2')?.addEventListener('change', (event) => {
    if (event.currentTarget.value) {
      modal.querySelector('#cart_pizza_modo').value = 'meia_a_meia';
    }
    syncPizzaOptions(modal);
  });
  modal.querySelector('#cart_pizza_modo')?.addEventListener('change', (event) => {
    if (event.currentTarget.value === 'inteira') {
      modal.querySelector('#cart_pizza_sabor_2').value = '';
    }
    syncPizzaOptions(modal);
  });
  modal.querySelector('#cart_pizza_tamanho')?.addEventListener('change', () => syncPizzaOptions(modal));
  modal.querySelector('#cart_pizza_borda')?.addEventListener('change', () => syncPizzaOptions(modal));
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
