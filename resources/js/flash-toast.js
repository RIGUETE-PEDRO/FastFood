document.addEventListener('DOMContentLoaded', () => {
  const stack = document.getElementById('ff-toast-stack');
  if (!stack) return;

  function hideToast(toast) {
    if (!toast || toast.classList.contains('is-hiding')) return;
    toast.classList.add('is-hiding');
    window.setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 220);
  }

  stack.querySelectorAll('.ff-toast').forEach((toast) => {
    let timeout = parseInt(toast.getAttribute('data-timeout') || '5000', 10);
    if (!Number.isFinite(timeout) || timeout < 500) timeout = 5000;

    const close = toast.querySelector('.ff-toast__close');
    if (close) {
      close.addEventListener('click', () => hideToast(toast));
    }

    window.setTimeout(() => hideToast(toast), timeout);
  });
});
