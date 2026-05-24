const CHECKOUT_MODAL_IDS = {
  tipo: 'finalizarModal',
  mesa: 'mesaModal',
  endereco: 'enderecoModal',
  novoEndereco: 'enderecoNovoModal',
  pagamento: 'pagamentoModal',
};

const DELIVERY_TYPES = {
  retirar: 'retirar',
  entrega: 'entrega',
};

const REQUIRED_ADDRESS_FIELDS = [
  { key: 'bairro', label: 'Bairro' },
  { key: 'rua', label: 'Rua' },
];

function byId(id) {
  return document.getElementById(id);
}

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function setText(element, text = '') {
  if (element) element.textContent = text;
}

function syncModalBodyLock(isLocked) {
  document.body.classList.toggle('ff-modal-open', isLocked);

  if (isLocked) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.removeProperty('overflow');
  }

  if (window.ff?.syncSidebarLock) {
    window.ff.syncSidebarLock();
  }
}

function createCheckoutContext() {
  const modals = {
    tipo: byId(CHECKOUT_MODAL_IDS.tipo),
    mesa: byId(CHECKOUT_MODAL_IDS.mesa),
    endereco: byId(CHECKOUT_MODAL_IDS.endereco),
    novoEndereco: byId(CHECKOUT_MODAL_IDS.novoEndereco),
    pagamento: byId(CHECKOUT_MODAL_IDS.pagamento),
  };

  if (Object.values(modals).some((modal) => !modal)) return null;

  const forms = {
    tipo: byId('tipoEntregaForm'),
    mesa: byId('mesaForm'),
    endereco: byId('enderecoForm'),
    novoEndereco: byId('enderecoNovoForm'),
    pagamento: byId('pagamentoForm'),
  };

  return {
    modals,
    modalList: Object.values(modals),
    openButton: byId('btnFinalizarCompra'),
    csrfToken: getCsrfToken(),
    forms,
    fields: {
      mesa: byId('mesa_id'),
      novoEndereco: {
        bairro: byId('novo_bairro'),
        rua: byId('novo_rua'),
        numero: byId('novo_numero'),
        complemento: byId('novo_complemento'),
      },
    },
    errors: {
      tipo: byId('tipoEntregaErro'),
      mesa: byId('mesaErro'),
      endereco: byId('enderecoSelecionadoErro'),
      novoEndereco: byId('enderecoNovoErro'),
    },
  };
}

function isModalOpen(modal) {
  return modal.classList.contains('is-open');
}

function anyModalOpen(ctx) {
  return ctx.modalList.some(isModalOpen);
}

function openModal(ctx, modal) {
  if (window.ff?.closeSidebar) {
    window.ff.closeSidebar();
  }

  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
  syncModalBodyLock(true);
}

function closeModal(ctx, modal) {
  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden', 'true');

  if (!ctx.modalList.some((item) => item !== modal && isModalOpen(item))) {
    syncModalBodyLock(false);
  }
}

function clearErrors(ctx) {
  Object.values(ctx.errors).forEach((errorBox) => setText(errorBox));
}

function closeAllModals(ctx) {
  ctx.modalList.forEach((modal) => closeModal(ctx, modal));
  clearErrors(ctx);
}

function getAddressRadios(ctx) {
  return ctx.forms.endereco
    ? [...ctx.forms.endereco.querySelectorAll('input[name="endereco_opcao"]')]
    : [];
}

function hasSavedAddresses(ctx) {
  return getAddressRadios(ctx).length > 0;
}

function focusFirstAddress(ctx) {
  getAddressRadios(ctx)[0]?.focus();
}

function focusNewAddress(ctx) {
  ctx.fields.novoEndereco.bairro?.focus();
}

function focusPayment(ctx) {
  ctx.modals.pagamento.querySelector('input[name="pagamento_metodo"]')?.focus();
}

function focusDeliveryType(ctx) {
  ctx.modals.tipo.querySelector('input[name="tipo_entrega"]')?.focus();
}

function focusModalEntry(ctx, modal) {
  if (modal === ctx.modals.tipo) focusDeliveryType(ctx);
  if (modal === ctx.modals.mesa) ctx.fields.mesa?.focus();
  if (modal === ctx.modals.endereco) focusFirstAddress(ctx);
  if (modal === ctx.modals.novoEndereco) focusNewAddress(ctx);
  if (modal === ctx.modals.pagamento) focusPayment(ctx);
}

function openCheckoutFlow(ctx) {
  closeAllModals(ctx);
  openModal(ctx, ctx.modals.tipo);
  focusDeliveryType(ctx);
}

function getSelectedDeliveryType(ctx) {
  return ctx.modals.tipo.querySelector('input[name="tipo_entrega"]:checked')?.value || null;
}

function showDeliveryNextStep(ctx, deliveryType) {
  closeModal(ctx, ctx.modals.tipo);

  if (deliveryType === DELIVERY_TYPES.retirar) {
    openModal(ctx, ctx.modals.mesa);
    ctx.fields.mesa?.focus();
    return;
  }

  if (hasSavedAddresses(ctx)) {
    openModal(ctx, ctx.modals.endereco);
    focusFirstAddress(ctx);
    return;
  }

  openModal(ctx, ctx.modals.novoEndereco);
  focusNewAddress(ctx);
}

function getMissingAddressFields(ctx) {
  return REQUIRED_ADDRESS_FIELDS.filter(({ key }) => {
    const field = ctx.fields.novoEndereco[key];
    return !field || !field.value.trim();
  });
}

function ensureHiddenInput(form, name, value) {
  if (!form) return null;

  let input = form.querySelector(`input[name="${name}"]`);

  if (!input) {
    input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    form.appendChild(input);
  }

  input.value = value;
  return input;
}

function setPaymentSubmitState(ctx, isEnabled, label = 'Confirmar pagamento') {
  const submitButton = ctx.forms.pagamento?.querySelector('button[type="submit"]');
  if (!submitButton) return;

  submitButton.disabled = !isEnabled;
  submitButton.textContent = label;
}

function preparePaymentForm(ctx, enderecoId = '') {
  ensureHiddenInput(ctx.forms.pagamento, 'tipo_entrega', DELIVERY_TYPES.entrega);

  if (enderecoId) {
    ensureHiddenInput(ctx.forms.pagamento, 'endereco_id', enderecoId);
  }
}

function openPaymentStep(ctx, { enderecoId = '', waitForAddress = false } = {}) {
  closeAllModals(ctx);
  preparePaymentForm(ctx, enderecoId);
  setPaymentSubmitState(ctx, !waitForAddress, waitForAddress ? 'Preparando pagamento...' : 'Confirmar pagamento');
  openModal(ctx, ctx.modals.pagamento);
  focusPayment(ctx);
}

function validateNewAddressForm(ctx, event) {
  const missing = getMissingAddressFields(ctx);

  if (!missing.length) {
    setText(ctx.errors.novoEndereco);
    return true;
  }

  event.preventDefault();

  const labels = missing.map(({ label }) => label).join(' e ');
  setText(ctx.errors.novoEndereco, `Preencha os campos obrigatorios: ${labels}.`);

  const firstMissingField = ctx.fields.novoEndereco[missing[0]?.key];
  firstMissingField?.focus();
  return false;
}

async function submitAddressWithAjax(ctx, form, submitButton) {
  const originalText = submitButton?.textContent;

  if (submitButton) {
    submitButton.disabled = true;
    submitButton.textContent = 'Confirmando...';
  }

  try {
    const response = await fetch(form.action, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': ctx.csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: new FormData(form),
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || !payload?.status) {
      throw new Error(payload?.mensagem || 'Nao foi possivel confirmar o endereco.');
    }

    return payload;
  } catch (error) {
    throw error;
  } finally {
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.textContent = originalText || 'Usar endereco selecionado';
    }
  }
}

async function syncSavedAddress(ctx, form, selectedAddress) {
  try {
    await submitAddressWithAjax(ctx, form, form.querySelector('button[type="submit"]'));
  } catch (error) {
    setText(ctx.errors.endereco, error.message || 'Nao foi possivel confirmar o endereco.');
    closeModal(ctx, ctx.modals.pagamento);
    openModal(ctx, ctx.modals.endereco);
    focusFirstAddress(ctx);
  }
}

async function syncNewAddress(ctx, form) {
  try {
    const payload = await submitAddressWithAjax(ctx, form, form.querySelector('button[type="submit"]'));
    const enderecoId = payload?.endereco?.id;

    if (enderecoId) {
      preparePaymentForm(ctx, enderecoId);
      setPaymentSubmitState(ctx, true);
      return;
    }

    throw new Error('Nao foi possivel identificar o endereco cadastrado.');
  } catch (error) {
    setText(ctx.errors.novoEndereco, error.message || 'Nao foi possivel cadastrar o endereco.');
    closeModal(ctx, ctx.modals.pagamento);
    openModal(ctx, ctx.modals.novoEndereco);
    focusNewAddress(ctx);
  }
}

function handleSavedAddressSubmit(ctx, event) {
  const selectedAddress = ctx.forms.endereco.querySelector('input[name="endereco_opcao"]:checked');

  if (!selectedAddress) {
    event.preventDefault();

    if (hasSavedAddresses(ctx)) {
      setText(ctx.errors.endereco, 'Selecione um endereco para continuar.');
      focusFirstAddress(ctx);
    } else {
      openModal(ctx, ctx.modals.novoEndereco);
      focusNewAddress(ctx);
    }

    return;
  }

  setText(ctx.errors.endereco);
  event.preventDefault();
  openPaymentStep(ctx, { enderecoId: selectedAddress.value });

  if (!ctx.csrfToken || typeof window.fetch !== 'function') return;
  syncSavedAddress(ctx, ctx.forms.endereco, selectedAddress);
}

function handleNewAddressSubmit(ctx, event) {
  event.preventDefault();

  if (!validateNewAddressForm(ctx, event)) return;

  if (!ctx.csrfToken || typeof window.fetch !== 'function') {
    ctx.forms.novoEndereco.submit();
    return;
  }

  openPaymentStep(ctx, { waitForAddress: true });
  syncNewAddress(ctx, ctx.forms.novoEndereco);
}

function bindModalCloseEvents(ctx) {
  ctx.modalList.forEach((modal) => {
    modal.querySelectorAll('[data-modal-close]').forEach((button) => {
      button.addEventListener('click', () => closeAllModals(ctx));
    });

    modal.querySelector('.ff-modal__overlay')?.addEventListener('click', () => closeAllModals(ctx));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && anyModalOpen(ctx)) {
      closeAllModals(ctx);
    }
  });
}

function bindNavigationEvents(ctx) {
  document.querySelectorAll('[data-modal-back]').forEach((button) => {
    button.addEventListener('click', () => {
      const currentModal = button.closest('.ff-modal');
      const targetModal = byId(button.dataset.modalBack || CHECKOUT_MODAL_IDS.tipo);

      if (currentModal) closeModal(ctx, currentModal);
      if (!targetModal) return;

      openModal(ctx, targetModal);
      focusModalEntry(ctx, targetModal);
    });
  });

  document.querySelectorAll('[data-modal-open]').forEach((button) => {
    button.addEventListener('click', () => {
      const targetModal = byId(button.dataset.modalOpen);
      const currentModal = button.closest('.ff-modal');

      if (!targetModal) return;

      if (currentModal) closeModal(ctx, currentModal);
      openModal(ctx, targetModal);
      focusModalEntry(ctx, targetModal);
    });
  });
}

function bindFormEvents(ctx) {
  ctx.forms.tipo?.addEventListener('submit', (event) => {
    event.preventDefault();

    const deliveryType = getSelectedDeliveryType(ctx);

    if (!deliveryType) {
      setText(ctx.errors.tipo, 'Selecione uma opcao para continuar.');
      return;
    }

    setText(ctx.errors.tipo);
    showDeliveryNextStep(ctx, deliveryType);
  });

  ctx.forms.endereco?.addEventListener('submit', (event) => handleSavedAddressSubmit(ctx, event));
  ctx.forms.novoEndereco?.addEventListener('submit', (event) => handleNewAddressSubmit(ctx, event));
}

function openInitialModalFromSession(ctx) {
  const modalId = document.body?.dataset?.openModal;
  const targetModal = modalId ? byId(modalId) : null;

  if (!targetModal) return;

  closeAllModals(ctx);

  if ([CHECKOUT_MODAL_IDS.endereco, CHECKOUT_MODAL_IDS.novoEndereco, CHECKOUT_MODAL_IDS.pagamento].includes(modalId)) {
    const deliveryRadio = ctx.modals.tipo.querySelector(`input[name="tipo_entrega"][value="${DELIVERY_TYPES.entrega}"]`);
    if (deliveryRadio) deliveryRadio.checked = true;
  }

  openModal(ctx, targetModal);
  focusModalEntry(ctx, targetModal);
}

function initCheckoutModal() {
  const ctx = createCheckoutContext();
  if (!ctx) return;

  ctx.openButton?.addEventListener('click', (event) => {
    event.preventDefault();
    openCheckoutFlow(ctx);
  });

  bindModalCloseEvents(ctx);
  bindNavigationEvents(ctx);
  bindFormEvents(ctx);
  openInitialModalFromSession(ctx);
}

document.addEventListener('DOMContentLoaded', initCheckoutModal);
