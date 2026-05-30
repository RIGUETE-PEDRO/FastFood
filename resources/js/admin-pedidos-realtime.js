(function () {
  const notificationStorageKey = 'flashfood.notificacoesPedidos';

  const supportsNotification = () => 'Notification' in window;

  const isNotificationConfigured = () => {
    try {
      return window.localStorage.getItem(notificationStorageKey) === '1';
    } catch (_) {
      return false;
    }
  };

  const saveNotificationPreference = (enabled) => {
    try {
      window.localStorage.setItem(notificationStorageKey, enabled ? '1' : '0');
    } catch (_) {
      // localStorage can be blocked by the browser.
    }
  };

  const canNotify = () => (
    supportsNotification()
    && Notification.permission === 'granted'
    && isNotificationConfigured()
  );

  const updateNotificationButton = (button) => {
    if (!button) return false;

    const status = document.getElementById('notification-settings-status');
    if (!supportsNotification()) {
      button.hidden = true;
      if (status) status.textContent = 'Seu navegador nao suporta notificacoes.';
      return false;
    }

    const enabled = canNotify();
    button.classList.toggle('is-active', enabled);
    button.textContent = enabled ? 'Notificacoes ativas' : 'Ativar notificacoes';
    if (status) {
      status.textContent = enabled
        ? 'Voce recebera avisos quando chegarem pedidos pendentes.'
        : 'Clique para permitir avisos de novos pedidos neste navegador.';
    }

    return enabled;
  };

  const initNotificationSettings = () => {
    const button = document.getElementById('pedidos-enable-notifications');
    if (!button) return;

    updateNotificationButton(button);
    button.addEventListener('click', async () => {
      if (!supportsNotification()) return;

      if (canNotify()) {
        saveNotificationPreference(false);
        updateNotificationButton(button);
        return;
      }

      if (Notification.permission === 'default') {
        await Notification.requestPermission();
      }

      saveNotificationPreference(Notification.permission === 'granted');
      updateNotificationButton(button);
    });
  };

  function init() {
    initNotificationSettings();

    const root = document.getElementById('pedidos-admin-root');
    if (!root) return;

    const pollingUrl = root.dataset.pollingUrl;
    if (!pollingUrl) return;

    let currentChecksum = root.dataset.checksum || '';
    let currentPending = normalizeNumber(root.dataset.pendingCount || '0');
    let lastPendingId = root.dataset.lastPendingId || '';
    let isUpdating = false;
    let notificationEnabled = canNotify();
    let audioContext = null;

    const totalBadge = document.getElementById('pedidos-total-badge');
    const resumoWrapper = document.getElementById('pedidos-resumo-wrapper');
    const listaWrapper = document.getElementById('pedidos-lista-wrapper');
    const newOrderAlert = document.getElementById('pedido-alerta-novo');

    function normalizeNumber(value, fallback = 0) {
      const number = parseInt(value, 10);
      return Number.isFinite(number) ? number : fallback;
    }

    function playAlertSound() {
      try {
        audioContext = audioContext || new (window.AudioContext || window.webkitAudioContext)();
        if (audioContext.state === 'suspended') {
          audioContext.resume();
        }

        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(880, audioContext.currentTime);
        gain.gain.setValueAtTime(0.001, audioContext.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.18, audioContext.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.28);

        oscillator.connect(gain);
        gain.connect(audioContext.destination);
        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.3);
      } catch (_) {
        // Audio API can be blocked by the browser.
      }
    }

    function refreshNotificationState() {
      notificationEnabled = canNotify();
    }

    function showNewOrderAlert(quantity = 1) {
      if (!newOrderAlert) return;

      const title = newOrderAlert.querySelector('strong');
      const text = newOrderAlert.querySelector('span');

      if (title) {
        title.textContent = quantity > 1 ? `${quantity} novos pedidos para aceitar` : 'Novo pedido para aceitar';
      }

      if (text) {
        text.textContent = 'Confira a lista de pedidos pendentes.';
      }

      newOrderAlert.hidden = false;
      newOrderAlert.classList.add('is-visible');

      window.setTimeout(() => {
        newOrderAlert.classList.remove('is-visible');
        newOrderAlert.hidden = true;
      }, 9000);
    }

    function notifyNewOrder(quantity = 1) {
      showNewOrderAlert(quantity);
      playAlertSound();
      refreshNotificationState();

      if (!notificationEnabled) return;

      const title = quantity > 1 ? `${quantity} novos pedidos` : 'Novo pedido';
      const notification = new Notification(title, {
        body: 'Tem pedido pendente para aceitar no painel.',
        icon: '/img/logo.png',
        tag: 'flashfood-pedido-pendente',
      });

      notification.onclick = () => {
        window.focus();
        notification.close();
      };
    }

    function syncPending(data, shouldNotify = false) {
      if (!data) return;

      const newTotal = normalizeNumber(data.pendentes, currentPending);
      const newLastId = data.ultimoPendenteId ? String(data.ultimoPendenteId) : '';
      const newQuantity = Math.max(0, newTotal - currentPending);
      const hasNewId = newLastId && newLastId !== lastPendingId;

      if (shouldNotify && newTotal > 0 && (newQuantity > 0 || hasNewId)) {
        notifyNewOrder(newQuantity || 1);
      }

      currentPending = newTotal;
      lastPendingId = newLastId;
      root.dataset.pendingCount = String(currentPending);
      root.dataset.lastPendingId = lastPendingId;
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
        input.closest('.acordeao-pedidos__conteudo')?.querySelector('[data-filtro-dia-entregues]')?.addEventListener('input', filterDeliveredOrders);
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

          if (group) {
            group.querySelectorAll('.pedido-collapse[open]').forEach((other) => {
              if (other !== details) {
                other.open = false;
              }
            });
          }

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

        if (button.getAttribute('aria-expanded') === 'true') {
          open();
        } else {
          content.hidden = true;
        }
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
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        credentials: 'same-origin',
        cache: 'no-store',
      });

      if (!response.ok) return;

      const data = await response.json();
      if (!data || !data.checksum) return;

      if (typeof data.resumoHtml === 'string' && resumoWrapper) {
        resumoWrapper.innerHTML = data.resumoHtml;
      }

      if (typeof data.listaHtml === 'string' && listaWrapper) {
        listaWrapper.innerHTML = data.listaHtml;
      }

      if (totalBadge && data.totalLabel) {
        totalBadge.textContent = data.totalLabel;
      }

      syncPending(data, true);
      currentChecksum = data.checksum;
      initInteractions();
    }

    async function checkChanges() {
      if (isUpdating) return;

      try {
        const response = await fetch(`${pollingUrl}?t=${Date.now()}`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
          cache: 'no-store',
        });

        if (!response.ok) return;

        const data = await response.json();
        if (!data || !data.checksum) return;

        if (currentChecksum && data.checksum !== currentChecksum) {
          isUpdating = true;
          await updateContent();
          isUpdating = false;
        } else {
          syncPending(data, true);
          currentChecksum = data.checksum;
        }
      } catch (_) {
        isUpdating = false;
      }
    }

    newOrderAlert?.querySelector('[data-close-order-alert]')?.addEventListener('click', () => {
      newOrderAlert.classList.remove('is-visible');
      newOrderAlert.hidden = true;
    });

    refreshNotificationState();
    initInteractions();
    window.setInterval(checkChanges, 8000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
