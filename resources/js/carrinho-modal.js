// Modal de "Adicionar ao carrinho" (MVP)
// - Abre ao clicar no botão .button-adicionar
// - Preenche nome do produto
// - Permite quantidade e observação

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

  const modal = new window.bootstrap.Modal(modalEl);

  const inputProdutoId = modalEl.querySelector('#cart_produto_id');
  const inputProdutoNome = modalEl.querySelector('#cart_produto_nome');
  const inputPreco = modalEl.querySelector('#cart_preco');
  const inputQtd = modalEl.querySelector('#cart_quantidade');
  const inputObs = modalEl.querySelector('#cart_observacao');

  // Delega clique para funcionar em todas as páginas sem repetir código
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.button-adicionar');
    if (!btn) return;

    const card = findProdutoCard(btn);
    if (!card) return;

    const produtoId = card.getAttribute('data-produto-id');
    const produtoNome = card.getAttribute('data-produto-nome') || '';
  const produtoPreco = card.getAttribute('data-produto-preco') || '';

    if (inputProdutoId) inputProdutoId.value = produtoId || '';
    if (inputProdutoNome) inputProdutoNome.value = produtoNome;
  if (inputPreco) inputPreco.value = produtoPreco;

    if (inputQtd) inputQtd.value = '1';
    if (inputObs) inputObs.value = '';

    modal.show();
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
