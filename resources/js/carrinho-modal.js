// Modal de "Adicionar ao carrinho"
// - Abre ao clicar em .button-adicionar
// - Preenche dados do produto
// - Funciona no mobile mesmo sem dependência do Bootstrap JS

function findProdutoCard(el) {
  return el.closest('[data-produto-id]');
}

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.getAttribute('content') || '';
}

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('addToCartModal');
  if (!modalEl) return;

  let manualBackdrop = null;

  function openModal() {
    if (manualBackdrop) return;

    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    modalEl.setAttribute('aria-hidden', 'false');
    modalEl.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';

    manualBackdrop = document.createElement('div');
    manualBackdrop.className = 'modal-backdrop fade show';
    manualBackdrop.addEventListener('click', () => closeModal());
    document.body.appendChild(manualBackdrop);

    const focusEl = modalEl.querySelector('#cart_quantidade') || modalEl.querySelector('button, input, textarea');
    if (focusEl) focusEl.focus();
  }

  function closeModal() {
    modalEl.classList.remove('show');
    modalEl.style.display = 'none';
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    if (manualBackdrop) {
      manualBackdrop.remove();
      manualBackdrop = null;
    }
  }

  // Segurança: garante que o modal sempre inicia fechado
  closeModal();

  // Limpa resíduos de um estado aberto anterior (comum após navegação/cache no mobile)
  document.body.classList.remove('modal-open');
  document.body.style.removeProperty('overflow');
  document.body.style.removeProperty('padding-right');
  document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());

  const inputProdutoId = modalEl.querySelector('#cart_produto_id');
  const inputProdutoNome = modalEl.querySelector('#cart_produto_nome');
  const inputPreco = modalEl.querySelector('#cart_preco');
  const inputQtd = modalEl.querySelector('#cart_quantidade');
  const inputObs = modalEl.querySelector('#cart_observacao');

  function getEventElementTarget(e) {
    if (e.target instanceof Element) return e.target;
    if (e.target && e.target.parentElement instanceof Element) return e.target.parentElement;
    return null;
  }

  function handleOpenByTrigger(e) {
    const eventTarget = getEventElementTarget(e);
    if (!eventTarget) return;

    const trigger = eventTarget.closest('.button-adicionar');
    if (!trigger) return;

    const card = findProdutoCard(trigger);
    if (!card) return;

    const produtoId = card.getAttribute('data-produto-id');
    const produtoNome = card.getAttribute('data-produto-nome') || '';
  const produtoPreco = card.getAttribute('data-produto-preco') || '';

    if (inputProdutoId) inputProdutoId.value = produtoId || '';
    if (inputProdutoNome) inputProdutoNome.value = produtoNome;
  if (inputPreco) inputPreco.value = produtoPreco;

    if (inputQtd) inputQtd.value = '1';
    if (inputObs) inputObs.value = '';

    openModal();
  }

  // Bind direto no botão para evitar abertura acidental em outros elementos
  document.querySelectorAll('.button-adicionar').forEach((btn) => {
    btn.addEventListener('click', handleOpenByTrigger);
  });

  // Fallback para fechar no modo manual
  modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (!modalEl.classList.contains('show')) return;
      closeModal();
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (!modalEl.classList.contains('show')) return;
    closeModal();
  });

  // Garantir CSRF no form
  const form = modalEl.querySelector('form');
  if (form) {
    const csrf = getCsrfToken();
    if (csrf) {
      let hidden = form.querySelector('input[name="_token"]');
      if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = '_token';
        form.appendChild(hidden);
      }
      hidden.value = csrf;
    }
  }
});

// Ingredientes: mostrar apenas ao clicar (não no hover)
document.addEventListener('DOMContentLoaded', () => {
  // Toggle ao clicar no label .ingredientes
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.ingredientes');
    if (!trigger) return;

    const wrap = trigger.closest('.ingredientes-wrap');
    if (!wrap) return;

    // fecha outros abertos
    document.querySelectorAll('.ingredientes-wrap.is-open').forEach((el) => {
      if (el !== wrap) el.classList.remove('is-open');
    });

    wrap.classList.toggle('is-open');
  });

  // Clicar fora fecha
  document.addEventListener('click', (e) => {
    const clickedInside = e.target.closest('.ingredientes-wrap');
    if (clickedInside) return;
    document.querySelectorAll('.ingredientes-wrap.is-open').forEach((el) => el.classList.remove('is-open'));
  });

  // ESC fecha
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.ingredientes-wrap.is-open').forEach((el) => el.classList.remove('is-open'));
  });
});
