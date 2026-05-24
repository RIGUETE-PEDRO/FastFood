const QUANTITY_FORM_SELECTOR = 'form[data-qty-form]';
const QUANTITY_INPUT_SELECTOR = 'input[name="quantidade"]';
const QUANTITY_DEBOUNCE_MS = 400;

function debounce(callback, delay = QUANTITY_DEBOUNCE_MS) {
  let timerId;

  return (...args) => {
    window.clearTimeout(timerId);
    timerId = window.setTimeout(() => callback(...args), delay);
  };
}

function submitForm(form) {
  if (typeof form.requestSubmit === 'function') {
    form.requestSubmit();
    return;
  }

  form.submit();
}

function bindQuantityForm(form) {
  const input = form.querySelector(QUANTITY_INPUT_SELECTOR);
  if (!input) return;

  const submitAfterTyping = debounce(() => {
    if (input.value === '') return;
    submitForm(form);
  });

  input.addEventListener('input', submitAfterTyping);
}

function initCartQuantityAutoSubmit() {
  document.querySelectorAll(QUANTITY_FORM_SELECTOR).forEach(bindQuantityForm);
}

document.addEventListener('DOMContentLoaded', initCartQuantityAutoSubmit);
