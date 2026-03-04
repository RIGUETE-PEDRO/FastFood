(function () {
  const persistedCollapsedKey = 'ff-sidebar-collapsed-state';
  const forceCollapsedKey = 'ff-force-sidebar-collapsed';
  const collapsedClass = 'ff-sidebar-collapsed';
  const openClass = 'ff-sidebar-open';
  const mq = window.matchMedia('(max-width: 768px)');

  function getPersistedCollapsedState() {
    try {
      return localStorage.getItem(persistedCollapsedKey);
    } catch (_) {
      return null;
    }
  }

  function setPersistedCollapsedState(isCollapsed) {
    try {
      localStorage.setItem(persistedCollapsedKey, isCollapsed ? '1' : '0');
    } catch (_) {
      // sem persistência disponível
    }
  }

  function setCollapsedState(isCollapsed, persist = true) {
    document.body.classList.toggle(collapsedClass, isCollapsed);
    if (persist) {
      setPersistedCollapsedState(isCollapsed);
    }
    syncMobileInteractionLock();
  }

  function syncMobileInteractionLock() {
    const isOpenOnMobile = mq.matches && !document.body.classList.contains(collapsedClass);
    document.body.classList.toggle(openClass, isOpenOnMobile);

    if (isOpenOnMobile) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.removeProperty('overflow');
    }
  }

  function closeUserDropdowns() {
    document
      .querySelectorAll('.ff-sidebar__footer .dropdown-menu.show')
      .forEach((openMenu) => openMenu.classList.remove('show'));

    document
      .querySelectorAll('.ff-sidebar__footer .ff-sidebar__user-btn[aria-expanded="true"]')
      .forEach((btn) => btn.setAttribute('aria-expanded', 'false'));
  }

  function ensureMobileDefault() {
    const forceCollapsed = sessionStorage.getItem(forceCollapsedKey) === '1';
    const persistedState = getPersistedCollapsedState();

    if (forceCollapsed) {
      setCollapsedState(true, true);
      document.body.classList.remove(openClass);
    } else if (persistedState === '1') {
      setCollapsedState(true, false);
    } else if (persistedState === '0') {
      setCollapsedState(false, false);
    } else if (mq.matches) {
      setCollapsedState(true, false);
    } else {
      setCollapsedState(false, false);
    }

    if (forceCollapsed) {
      sessionStorage.removeItem(forceCollapsedKey);
    }
  }

  function initSidebarState() {
    if (!document.body) return;
    ensureMobileDefault();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarState, { once: true });
  } else {
    initSidebarState();
  }

  function getEventElementTarget(event) {
    if (event.target instanceof Element) return event.target;
    if (event.target && event.target.parentElement instanceof Element) return event.target.parentElement;
    return null;
  }

  function handleSidebarInteractions(event) {
    const target = getEventElementTarget(event);
    if (!target) return;

    const toggle = target.closest('[data-sidebar-toggle]');
    if (toggle) {
      event.preventDefault();
      const willCollapse = !document.body.classList.contains(collapsedClass);
      setCollapsedState(willCollapse, true);
      return;
    }

    // Sidebar: permite clicar em toda a linha do item, não só no texto do link
    const sidebarRow = target.closest('.ff-sidebar__nav li');
    if (sidebarRow && !target.closest('a.nav-link')) {
      const rowLink = sidebarRow.querySelector('a.nav-link:not(.disabled)');
      if (rowLink && rowLink.href) {
        window.location.href = rowLink.href;
        return;
      }
    }

    const userToggle = target.closest('.ff-sidebar__user-btn');
    if (userToggle) {
      event.preventDefault();
      const dropdown = userToggle.closest('.dropdown');
      if (!dropdown) return;

      const menu = dropdown.querySelector('.dropdown-menu');
      if (!menu) return;

      const isOpen = menu.classList.contains('show');
      closeUserDropdowns();

      if (!isOpen) {
        menu.classList.add('show');
        userToggle.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    if (!target.closest('.ff-sidebar__footer .dropdown')) {
      closeUserDropdowns();
    }

    const sidebarLink = target.closest('.ff-sidebar a.nav-link');
    if (sidebarLink && mq.matches) {
      setCollapsedState(true, true);
    }
  }

  let lastTouchAt = 0;

  document.addEventListener('touchend', (event) => {
    lastTouchAt = Date.now();
    handleSidebarInteractions(event);
  }, { passive: false });

  document.addEventListener('click', (event) => {
    // Em mobile, o mesmo toque dispara touchend e depois click.
    // Sem este filtro, o toggle executa duas vezes e reabre a sidebar.
    if (Date.now() - lastTouchAt < 700) {
      return;
    }
    handleSidebarInteractions(event);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    closeUserDropdowns();

    if (!mq.matches) return;
    setCollapsedState(true, true);
  });

  if (mq.addEventListener) {
    mq.addEventListener('change', ensureMobileDefault);
  } else if (mq.addListener) {
    mq.addListener(ensureMobileDefault);
  }

})();
