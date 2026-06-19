(function () {
  function init() {
    const root = document.getElementById('pedidos-admin-root');
    const notifier = document.getElementById('pedidos-global-notifier');
    if (!root && !notifier) return;

    const pollingUrl = notifier?.dataset.pollingUrl || root?.dataset.pollingUrl;
    if (!pollingUrl) return;

    let currentChecksum = root?.dataset.checksum || '';
    let currentPending = normalizeNumber(root?.dataset.pendingCount || '0');
    let lastPendingId = root?.dataset.lastPendingId || '';
    let isUpdating = false;
    let isChecking = false;
    let isAlertPlaying = false;

    const totalBadge = document.getElementById('pedidos-total-badge');
    const resumoWrapper = document.getElementById('pedidos-resumo-wrapper');
    const listaWrapper = document.getElementById('pedidos-lista-wrapper');
    const notifierUserId = notifier?.dataset.userId || '';
    const lastSeenStorageKey = notifierUserId
      ? `flashfood.pedidos.ultimoVisto.${notifierUserId}`
      : '';
    const alertAudio = notifier?.dataset.soundUrl
      ? new Audio(notifier.dataset.soundUrl)
      : null;

    if (alertAudio) {
      alertAudio.preload = 'auto';
      alertAudio.load();

      const unlockAudio = async () => {
        try {
          alertAudio.muted = true;
          await alertAudio.play();
          alertAudio.pause();
          alertAudio.currentTime = 0;
          alertAudio.muted = false;
        } catch (_) {
          alertAudio.muted = false;
        }
      };

      document.addEventListener('pointerdown', unlockAudio, { once: true, capture: true });
      document.addEventListener('keydown', unlockAudio, { once: true, capture: true });
    }

    function normalizeNumber(value, fallback = 0) {
      const number = parseInt(value, 10);
      return Number.isFinite(number) ? number : fallback;
    }

    function readLastSeenId() {
      if (!lastSeenStorageKey) return null;

      try {
        const value = window.localStorage.getItem(lastSeenStorageKey);
        if (value === null) return null;

        const parsed = parseInt(value, 10);
        return Number.isFinite(parsed) ? parsed : null;
      } catch (_) {
        return null;
      }
    }

    function saveLastSeenId(id) {
      if (!lastSeenStorageKey || !Number.isFinite(id)) return;

      try {
        window.localStorage.setItem(lastSeenStorageKey, String(id));
      } catch (_) {
        // Continua monitorando na aba atual se o armazenamento estiver bloqueado.
      }
    }

    function waitForAudioEnd(audio) {
      return new Promise((resolve) => {
        const finish = () => {
          audio.removeEventListener('ended', finish);
          audio.removeEventListener('error', finish);
          resolve();
        };

        audio.addEventListener('ended', finish, { once: true });
        audio.addEventListener('error', finish, { once: true });
      });
    }

    async function playNewOrderSound() {
      if (!alertAudio || isAlertPlaying) return false;

      isAlertPlaying = true;

      try {
        alertAudio.pause();
        alertAudio.currentTime = 0;
        alertAudio.muted = false;

        const ended = waitForAudioEnd(alertAudio);
        await alertAudio.play();
        await ended;

        return true;
      } catch (_) {
        return false;
      } finally {
        isAlertPlaying = false;
      }
    }

    async function monitorNewOrder(data) {
      if (!notifier || !data) return;

      const newestPendingId = normalizeNumber(data.ultimoPendenteId, 0);
      if (newestPendingId <= 0) return;

      const storedLastSeenId = readLastSeenId();
      if (storedLastSeenId === null) {
        saveLastSeenId(newestPendingId);
        return;
      }

      if (newestPendingId > storedLastSeenId) {
        saveLastSeenId(newestPendingId);
        const played = await playNewOrderSound();
        if (!played) {
          saveLastSeenId(storedLastSeenId);
        }
      }
    }

    function syncPending(data) {
      if (!data) return;

      currentPending = normalizeNumber(data.pendentes, currentPending);
      lastPendingId = data.ultimoPendenteId ? String(data.ultimoPendenteId) : '';
      void monitorNewOrder(data);

      if (root) {
        root.dataset.pendingCount = String(currentPending);
        root.dataset.lastPendingId = lastPendingId;
      }
    }

    function initInteractions() {
      document.querySelectorAll('[data-filtro-clientes-entregues]').forEach((input) => {
        if (input.dataset.enhanced === '1') return;
        input.dataset.enhanced = '1';

        const filterDeliveredOrders = () => {
          const container = input.closest('.acordeao-pedidos__conteudo');
          if (!container) return;

          const term = input.value.trim().toLocaleLowerCase('pt-BR');
          const dateInput = container.querySelector('[data-filtro-dia-entregues]');
          const day = dateInput?.value || '';

          container.querySelectorAll('.pedido-card').forEach((card) => {
            const client = card.dataset.cliente?.toLocaleLowerCase('pt-BR')
              || card.querySelector('.pedido-card__cliente')?.textContent.toLocaleLowerCase('pt-BR')
              || '';
            const cardDay = card.dataset.pedidoData || '';
            const matchesClient = term === '' || client.includes(term);
            const matchesDay = day === '' || cardDay === day;

            card.classList.toggle('is-filter-hidden', !matchesClient || !matchesDay);
          });
        };

        input.addEventListener('input', filterDeliveredOrders);
        input.closest('.acordeao-pedidos__conteudo')
          ?.querySelector('[data-filtro-dia-entregues]')
          ?.addEventListener('input', filterDeliveredOrders);
        filterDeliveredOrders();
      });

      document.querySelectorAll('.pedido-collapse').forEach((details) => {
        if (details.dataset.enhanced === '1') return;
        details.dataset.enhanced = '1';

        const summary = details.querySelector('.pedido-collapse__summary');
        if (!summary) return;

        summary.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();

          const shouldOpen = !details.open;
          const group = details.closest('.lista-pedidos-admin, .acordeao-pedidos__conteudo');

          group?.querySelectorAll('.pedido-collapse[open]').forEach((other) => {
            if (other !== details) other.open = false;
          });

          details.open = shouldOpen;
        });
      });

      document.querySelectorAll('.acordeao-pedidos__gatilho').forEach((button) => {
        const selector = button.dataset.target;
        const content = selector ? document.querySelector(selector) : null;
        if (!content) return;

        const open = () => {
          button.setAttribute('aria-expanded', 'true');
          content.hidden = false;
          content.classList.add('is-open');
        };

        const close = () => {
          button.setAttribute('aria-expanded', 'false');
          content.classList.remove('is-open');
          content.hidden = true;
        };

        if (button.dataset.enhanced !== '1') {
          button.dataset.enhanced = '1';
          button.addEventListener('click', () => {
            button.getAttribute('aria-expanded') === 'true' ? close() : open();
          });
        }

        button.getAttribute('aria-expanded') === 'true' ? open() : close();
      });

      document.querySelectorAll('form[data-disable-on-submit]').forEach((form) => {
        if (form.dataset.enhanced === '1') return;
        form.dataset.enhanced = '1';

        form.addEventListener('submit', () => {
          const button = form.querySelector('[data-avancar-button]');
          if (!button) return;

          button.classList.add('is-loading');
          button.setAttribute('disabled', 'disabled');
        });
      });
    }

    async function updateContent() {
      const response = await fetch(`${pollingUrl}?full=1&t=${Date.now()}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        credentials: 'same-origin',
        cache: 'no-store',
      });

      if (!response.ok) return;

      const data = await response.json();
      if (!data?.checksum) return;

      if (typeof data.resumoHtml === 'string' && resumoWrapper) {
        resumoWrapper.innerHTML = data.resumoHtml;
      }

      if (typeof data.listaHtml === 'string' && listaWrapper) {
        listaWrapper.innerHTML = data.listaHtml;
      }

      if (totalBadge && data.totalLabel) {
        totalBadge.textContent = data.totalLabel;
      }

      syncPending(data);
      currentChecksum = data.checksum;
      initInteractions();
    }

    async function checkChanges() {
      if (isUpdating || isChecking) return;
      isChecking = true;

      try {
        const response = await fetch(`${pollingUrl}?t=${Date.now()}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
          cache: 'no-store',
        });

        if (!response.ok) return;

        const data = await response.json();
        if (!data?.checksum) return;

        if (root && currentChecksum && data.checksum !== currentChecksum) {
          isUpdating = true;
          await updateContent();
          isUpdating = false;
        } else {
          syncPending(data);
          currentChecksum = data.checksum;
        }
      } catch (_) {
        isUpdating = false;
      } finally {
        isChecking = false;
      }
    }

    initInteractions();

    const initialPendingId = normalizeNumber(notifier?.dataset.lastPendingId, 0);
    if (notifier && readLastSeenId() === null && initialPendingId > 0) {
      saveLastSeenId(initialPendingId);
    }

    void checkChanges();
    window.setInterval(checkChanges, 1000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
