const QUANTITY_FORM_SELECTOR = 'form[data-qty-form]';
const QUANTITY_INPUT_SELECTOR = 'input[name="quantidade"]';
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

  const submitQuantity = () => {
    if (input.value === '') return;
    submitForm(form);
  };

  input.addEventListener('change', submitQuantity);
  input.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;

    event.preventDefault();
    submitQuantity();
  });
}

function initCartQuantityAutoSubmit() {
  document.querySelectorAll(QUANTITY_FORM_SELECTOR).forEach(bindQuantityForm);
}

document.addEventListener('DOMContentLoaded', initCartQuantityAutoSubmit);
