(function () {
  function init() {
    const root = document.getElementById('pedidos-admin-root');
    const notifier = document.getElementById('pedidos-global-notifier');
    if (!root && !notifier) return;

    const pollingUrl = notifier?.dataset.pollingUrl || root?.dataset.pollingUrl;
    const realtimeChannel = notifier?.dataset.realtimeChannel || root?.dataset.realtimeChannel || 'pedidos.admin';

    let currentChecksum = root?.dataset.checksum || '';
    let currentPending = normalizeNumber(root?.dataset.pendingCount || '0');
    let lastPendingId = root?.dataset.lastPendingId || '';
    let isUpdating = false;
    let isChecking = false;
    let isAlertPlaying = false;
    let fallbackTimer = null;

    const totalBadge = document.getElementById('pedidos-total-badge');
    const resumoWrapper = document.getElementById('pedidos-resumo-wrapper');
    const listaWrapper = document.getElementById('pedidos-lista-wrapper');
    const notifierUserId = notifier?.dataset.userId || '';
    const lastSeenStorageKey = notifierUserId
      ? `flashfood.pedidos.ultimoVisto.${notifierUserId}`
      : '';
    const pendingHighlightStorageKey = notifierUserId
      ? `flashfood.pedidos.destacar.${notifierUserId}`
      : '';
    const alertAudio = notifier?.dataset.soundUrl
      ? new Audio(notifier.dataset.soundUrl)
      : null;
    let audioUnlocked = false;

    if (alertAudio) {
      alertAudio.preload = 'auto';
      alertAudio.load();

      const unlockAudio = async () => {
        try {
          alertAudio.volume = 0.01;
          alertAudio.muted = false;
          await alertAudio.play();
          alertAudio.pause();
          alertAudio.currentTime = 0;
          alertAudio.volume = 1;
          audioUnlocked = true;
          void monitorNewOrder({ ultimoPendenteId: lastPendingId });
        } catch (_) {
          alertAudio.volume = 1;
          audioUnlocked = false;
        }
      };

      document.addEventListener('pointerdown', async () => {
        if (!audioUnlocked) await unlockAudio();
      }, { once: true, capture: true });

      document.addEventListener('keydown', async () => {
        if (!audioUnlocked) await unlockAudio();
      }, { once: true, capture: true });

      void unlockAudio();
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

    function savePendingHighlight(id) {
      if (!pendingHighlightStorageKey || !Number.isFinite(id) || id <= 0) return;

      try {
        window.localStorage.setItem(pendingHighlightStorageKey, JSON.stringify({
          id,
          createdAt: Date.now(),
        }));
      } catch (_) {
        // O destaque imediato continua funcionando na tela de pedidos.
      }
    }

    function readPendingHighlight() {
      if (!pendingHighlightStorageKey) return null;

      try {
        const stored = window.localStorage.getItem(pendingHighlightStorageKey);
        if (!stored) return null;

        const highlight = JSON.parse(stored);
        const id = normalizeNumber(highlight?.id, 0);
        const createdAt = normalizeNumber(highlight?.createdAt, 0);

        if (id <= 0 || createdAt <= 0 || Date.now() - createdAt > 10 * 60 * 1000) {
          window.localStorage.removeItem(pendingHighlightStorageKey);
          return null;
        }

        return { id, createdAt };
      } catch (_) {
        return null;
      }
    }

    function clearPendingHighlight() {
      if (!pendingHighlightStorageKey) return;

      try {
        window.localStorage.removeItem(pendingHighlightStorageKey);
      } catch (_) {
        // Sem ação adicional.
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
        audioUnlocked = false;
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
        savePendingHighlight(newestPendingId);
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
      document.querySelectorAll('[data-filtro-clientes-andamento]').forEach((input) => {
        if (input.dataset.enhanced === '1') return;
        input.dataset.enhanced = '1';

        const filterOpenOrders = () => {
          const container = input.closest('.lista-pedidos-admin');
          if (!container) return;

          const term = input.value.trim().toLocaleLowerCase('pt-BR');
          let visibleOrders = 0;

          container.querySelectorAll('.pedido-card').forEach((card) => {
            const client = card.dataset.cliente?.toLocaleLowerCase('pt-BR')
              || card.querySelector('.pedido-card__cliente')?.textContent.toLocaleLowerCase('pt-BR')
              || '';
            const matchesClient = term === '' || client.includes(term);

            card.classList.toggle('is-filter-hidden', !matchesClient);
            if (matchesClient) visibleOrders += 1;
          });

          const emptyMessage = container.querySelector('[data-filtro-andamento-vazio]');
          if (emptyMessage) {
            emptyMessage.hidden = term === '' || visibleOrders > 0;
          }
        };

        input.addEventListener('input', filterOpenOrders);
        filterOpenOrders();
      });

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

    function highlightNewOrder(pedidoId) {
      if (!root || !pedidoId) return false;

      const card = root.querySelector(`.pedido-card[data-pedido-id="${pedidoId}"]`);
      if (!card) return false;

      const details = card.querySelector('.pedido-collapse');
      if (details) {
        details.open = true;
      }

      card.classList.remove('is-new-order');
      void card.offsetWidth;
      card.classList.add('is-new-order');
      card.scrollIntoView({ behavior: 'smooth', block: 'center' });

      window.setTimeout(() => {
        card.classList.remove('is-new-order');
      }, 7000);

      clearPendingHighlight();
      return true;
    }

    function readFilters() {
      return {
        openOrders: document.querySelector('[data-filtro-clientes-andamento]')?.value || '',
        deliveredOrders: document.querySelector('[data-filtro-clientes-entregues]')?.value || '',
        deliveredDate: document.querySelector('[data-filtro-dia-entregues]')?.value || '',
      };
    }

    function restoreFilters(filters) {
      const refreshedOpenFilter = document.querySelector('[data-filtro-clientes-andamento]');
      const refreshedDeliveredFilter = document.querySelector('[data-filtro-clientes-entregues]');
      const refreshedDeliveredDate = document.querySelector('[data-filtro-dia-entregues]');

      if (refreshedOpenFilter && filters.openOrders) {
        refreshedOpenFilter.value = filters.openOrders;
        refreshedOpenFilter.dispatchEvent(new Event('input'));
      }

      if (refreshedDeliveredFilter && filters.deliveredOrders) {
        refreshedDeliveredFilter.value = filters.deliveredOrders;
        refreshedDeliveredFilter.dispatchEvent(new Event('input'));
      }

      if (refreshedDeliveredDate && filters.deliveredDate) {
        refreshedDeliveredDate.value = filters.deliveredDate;
        refreshedDeliveredDate.dispatchEvent(new Event('input'));
      }
    }

    function applyRealtimeData(data, newPendingId = 0) {
      if (!data?.checksum) return;

      const filters = readFilters();

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
      restoreFilters(filters);
      highlightNewOrder(newPendingId);

      return true;
    }

    async function fetchContent(newPendingId = 0) {
      if (!pollingUrl) return false;

      const response = await fetch(`${pollingUrl}?full=1&t=${Date.now()}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        credentials: 'same-origin',
        cache: 'no-store',
      });

      if (!response.ok) return false;

      const data = await response.json();
      return applyRealtimeData(data, newPendingId);
    }

    function getNewPendingId(data) {
      const previousPendingId = normalizeNumber(lastPendingId, 0);
      const newestPendingId = normalizeNumber(data?.ultimoPendenteId, 0);

      return newestPendingId > previousPendingId ? newestPendingId : 0;
    }

    async function handleRealtimePayload(data) {
      if (!data?.checksum) return;

      const newPendingId = getNewPendingId(data);
      if (newPendingId > 0) {
        savePendingHighlight(newPendingId);
      }

      if (data.checksum === currentChecksum) {
        syncPending(data);
        currentChecksum = data.checksum;
        return;
      }

      const hasHtmlPayload = typeof data.resumoHtml === 'string' && typeof data.listaHtml === 'string';
      if (root && !hasHtmlPayload) {
        try {
          isUpdating = true;
          await fetchContent(newPendingId);
        } finally {
          isUpdating = false;
        }
        return;
      }

      applyRealtimeData(data, newPendingId);
    }

    function subscribeRealtime() {
      if (!window.Echo?.private) return false;

      try {
        window.Echo
          .private(realtimeChannel)
          .listen('.pedidos.atualizados', (data) => {
            void handleRealtimePayload(data);
          });

        window.addEventListener('beforeunload', () => {
          window.Echo?.leave?.(realtimeChannel);
        }, { once: true });

        return true;
      } catch (_) {
        return false;
      }
    }

    async function checkChanges() {
      if (!pollingUrl || isUpdating || isChecking) return;
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
          const previousPendingId = normalizeNumber(lastPendingId, 0);
          const newestPendingId = normalizeNumber(data.ultimoPendenteId, 0);
          const newPendingId = newestPendingId > previousPendingId ? newestPendingId : 0;

          if (newPendingId > 0) {
            savePendingHighlight(newPendingId);
          }

          isUpdating = true;
          await fetchContent(newPendingId);
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

    function startPollingFallback() {
      if (!pollingUrl || fallbackTimer) return;

      void checkChanges();
      fallbackTimer = window.setInterval(checkChanges, 5000);
    }

    function startRealtime(attempt = 0) {
      if (subscribeRealtime()) return;

      if (attempt < 20) {
        window.setTimeout(() => startRealtime(attempt + 1), 250);
        return;
      }

      startPollingFallback();
    }

    initInteractions();

    const initialPendingId = normalizeNumber(notifier?.dataset.lastPendingId, 0);
    if (notifier && readLastSeenId() === null && initialPendingId > 0) {
      saveLastSeenId(initialPendingId);
    }

    const pendingHighlight = readPendingHighlight();
    if (root && pendingHighlight) {
      window.setTimeout(() => {
        highlightNewOrder(pendingHighlight.id);
      }, 120);
    }

    startRealtime();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
