const dialog = document.getElementById('pizzaPricesModal');

if (dialog instanceof HTMLDialogElement) {
  document.querySelectorAll('[data-pizza-prices-open]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!dialog.open) dialog.showModal();
    });
  });

  dialog.querySelectorAll('[data-pizza-prices-close]').forEach((button) => {
    button.addEventListener('click', () => dialog.close());
  });

  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
}
