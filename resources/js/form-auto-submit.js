document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-auto-submit-on-change]').forEach((field) => {
    field.addEventListener('change', () => {
      const form = field.closest('form');
      if (form) {
        form.submit();
      }
    });
  });
});
