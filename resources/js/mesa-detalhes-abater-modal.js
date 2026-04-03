document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-confirm-remove-item]').forEach((removeForm) => {
    removeForm.addEventListener('submit', (event) => {
      const ok = window.confirm('Remover este item da comanda?');
      if (!ok) {
        event.preventDefault();
      }
    });
  });

  const modal = document.getElementById('abaterModal');
  const btnOpen = document.getElementById('btnAbrirAbaterModal');
  const btnConfirm = document.getElementById('btnConfirmarAbater');
  const totalText = document.getElementById('abaterTotalTexto');
  const valorInput = document.getElementById('abaterValorInput');
  const errorBox = document.getElementById('abaterModalErro');
  const form = document.getElementById('abaterForm');

  if (!modal || !btnOpen || !btnConfirm || !totalText || !errorBox || !form) return;

  const overlay = modal.querySelector('.ff-modal__overlay');
  let valorFoiEditado = false;

  function formatBRL(value) {
    try {
      return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    } catch {
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
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('is-open');
  }

  function closeModal() {
    modal.setAttribute('aria-hidden', 'true');
    modal.classList.remove('is-open');
  }

  function updateModalTotal() {
    const total = calcSelectedTotal();
    totalText.textContent = formatBRL(total);

    if (valorInput && !valorFoiEditado) {
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

  btnOpen.addEventListener('click', () => {
    errorBox.textContent = '';

    valorFoiEditado = false;
    if (valorInput) valorInput.value = '';

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

  document.querySelectorAll('[data-abater-modal-close]').forEach((el) => {
    el.addEventListener('click', closeModal);
  });

  if (overlay) overlay.addEventListener('click', closeModal);

  document.querySelectorAll('input[name="item_ids[]"]').forEach((cb) => {
    cb.addEventListener('change', () => {
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

      if (modal.classList.contains('is-open')) {
        updateModalTotal();
      }
    });
  });

  document.querySelectorAll('input[data-qtd-input]').forEach((input) => {
    input.addEventListener('input', () => {
      const itemId = input.getAttribute('data-item-id');
      const cb = getCheckboxByItemId(itemId);
      const max = cb ? parseInt(cb.getAttribute('data-max') || '0', 10) : 0;
      const cur = parseInt(input.value || '0', 10);
      const next = clamp(Number.isFinite(cur) ? cur : 0, 0, Number.isFinite(max) ? max : 999);
      input.value = String(next);
      syncCheckboxWithQtd(itemId);

      if (modal.classList.contains('is-open')) {
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

    if (modal.classList.contains('is-open')) {
      updateModalTotal();
    }
  }

  document.querySelectorAll('[data-qtd-inc]').forEach((btn) => {
    btn.addEventListener('click', () => {
      bumpQtd(btn.getAttribute('data-item-id'), +1);
    });
  });

  document.querySelectorAll('[data-qtd-dec]').forEach((btn) => {
    btn.addEventListener('click', () => {
      bumpQtd(btn.getAttribute('data-item-id'), -1);
    });
  });

  if (valorInput) {
    valorInput.addEventListener('input', () => {
      valorFoiEditado = true;
    });
  }

  btnConfirm.addEventListener('click', () => {
    errorBox.textContent = '';

    const selected = getSelectedCheckboxes();
    if (selected.length === 0) {
      errorBox.textContent = 'Selecione pelo menos um item para abater.';
      return;
    }

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

    if (valorDigitado - total > 0.009) {
      errorBox.textContent = `O valor digitado (${formatBRL(valorDigitado)}) não pode ser maior que o total selecionado (${formatBRL(total)}).`;
      return;
    }

    form.submit();
  });
});
