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
  let lastOpenAt = 0;

   function openModal() {
     if (manualBackdrop && !manualBackdrop.isConnected) {
       manualBackdrop = null;
     }

     if (manualBackdrop) return;

     // Fecha sidebar em mobile se estiver aberta
     if (window.ff && window.ff.closeSidebar) {
       window.ff.closeSidebar();
     }

     modalEl.classList.add('show');
     modalEl.style.display = 'block';
     modalEl.setAttribute('aria-hidden', 'false');
     modalEl.setAttribute('aria-modal', 'true');
     document.body.classList.add('modal-open');
     document.body.classList.add('ff-modal-open');
     document.body.style.overflow = 'hidden';
     if (window.ff?.syncSidebarLock) window.ff.syncSidebarLock();

     manualBackdrop = document.createElement('div');
     manualBackdrop.className = 'modal-backdrop fade show';
     manualBackdrop.style.zIndex = '1999';
     manualBackdrop.addEventListener('click', () => closeModal());
     document.body.appendChild(manualBackdrop);

     const focusEl = modalEl.querySelector('#cart_quantidade') || modalEl.querySelector('button, input, textarea');
     if (focusEl) focusEl.focus();

     // Scroll to top em mobile
     setTimeout(() => {
       window.scrollTo(0, 0);
     }, 100);
   }

  function cleanupModalState() {
    document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
    document.body.classList.remove('modal-open');
    document.body.classList.remove('ff-modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');

    if (window.matchMedia('(max-width: 768px)').matches) {
      document.body.classList.remove('ff-sidebar-open');
      document.body.classList.add('ff-sidebar-collapsed');
    }
  }

  function closeModal() {
    modalEl.classList.remove('show');
    modalEl.style.display = 'none';
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.removeAttribute('aria-modal');
    if (window.ff?.syncSidebarLock) window.ff.syncSidebarLock();
    if (manualBackdrop) {
      manualBackdrop.remove();
      manualBackdrop = null;
    }
    cleanupModalState();
    window.setTimeout(cleanupModalState, 80);
    window.dispatchEvent(new CustomEvent('ff:cart-modal-closed'));
  }

  // Segurança: garante que o modal sempre inicia fechado
  closeModal();

  // Limpa resíduos de um estado aberto anterior (comum após navegação/cache no mobile)
  document.body.classList.remove('modal-open');
  document.body.classList.remove('ff-modal-open');
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

    // Primeiro tenta abrir quando o elemento clicado (ou seu pai) é o botão "Adicionar ao carrinho"
    const trigger = eventTarget.closest('.button-adicionar');
    // Se não for o botão, também aceitamos clique direto no cartão do produto (ex: carrossel)
    const card = trigger ? findProdutoCard(trigger) : findProdutoCard(eventTarget);
    if (!card) return;

    if (e.preventDefault) e.preventDefault();
    if (e.stopPropagation) e.stopPropagation();

    const now = Date.now();
    if (now - lastOpenAt < 500) return;
    lastOpenAt = now;

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

  // Delegated handler: abrir modal ao clicar no cartão do produto (ex: carrossel)
  // Isso permite clicar em .produto-card-mini ou .produto--interactive para abrir
  document.addEventListener('click', (e) => {
    const evTarget = getEventElementTarget(e);
    if (!evTarget) return;

    // não abrir se o clique for dentro do próprio modal
    if (evTarget.closest('#addToCartModal')) return;

    // se o clique já foi em um botão que já tem handler, ignora (evita duplo)
    if (evTarget.closest('.button-adicionar')) return;

    // Apenas abrir o modal quando o clique for em um card do carrossel
    // (classe .produto-card-mini). Para a lista abaixo (.produto--interactive)
    // apenas o botão .button-adicionar deve abrir o modal — esse binding
    // já existe acima.
    const card = evTarget.closest('.produto-card-mini');
    if (!card) return;

    // Construí um objeto mínimo compatível com handleOpenByTrigger
    try {
      handleOpenByTrigger({ target: card });
    } catch (err) {
      // degrade silencioso
      console.error('Erro ao abrir modal por clique no cartão:', err);
    }
  });

  document.addEventListener('touchend', (e) => {
    const evTarget = getEventElementTarget(e);
    if (!evTarget) return;

    if (evTarget.closest('#addToCartModal')) return;
    if (evTarget.closest('.button-adicionar')) return;

    const card = evTarget.closest('.produto-card-mini');
    if (!card) return;

    handleOpenByTrigger(e);
  }, { passive: false });

  window.addEventListener('ff:cart-modal-force-cleanup', () => {
    manualBackdrop = null;
    cleanupModalState();
  });

  // Fallback para fechar no modo manual
  modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      if (!modalEl.classList.contains('show')) return;
      event.preventDefault();
      event.stopPropagation();
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
