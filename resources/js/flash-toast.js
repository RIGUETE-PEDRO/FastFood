document.addEventListener('DOMContentLoaded', () => {
  function hideToast(toast) {
    if (!toast || toast.classList.contains('is-hiding')) return;
    toast.classList.add('is-hiding');
    window.setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 220);
  }

  document.querySelectorAll('.ff-toast-stack').forEach((stack) => {
    if (stack.dataset.flashToastReady === '1') return;
    stack.dataset.flashToastReady = '1';

    stack.addEventListener('click', (event) => {
      const close = event.target.closest('.ff-toast__close');
      if (!close || !stack.contains(close)) return;
      hideToast(close.closest('.ff-toast'));
    });

    stack.querySelectorAll('.ff-toast').forEach((toast) => {
      let timeout = parseInt(toast.getAttribute('data-timeout') || '5000', 10);
      if (!Number.isFinite(timeout) || timeout < 500) timeout = 5000;

      window.setTimeout(() => hideToast(toast), timeout);
    });
  });
});
