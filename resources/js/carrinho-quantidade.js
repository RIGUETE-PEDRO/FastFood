// Atualização automática da quantidade no carrinho (sem precisar clicar em botão)
// - Espera o usuário parar de digitar (debounce)
// - Evita enviar "1" antes de "10" / "101" etc.

function debounce(fn, delay = 400) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-qty-form]').forEach((form) => {
    const input = form.querySelector('input[name="quantidade"]');
    if (!input) return;

    const submitDebounced = debounce(() => {
      // não envia vazio
      if (input.value === '') return;
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    }, 400);

    input.addEventListener('input', submitDebounced);
  });
});
