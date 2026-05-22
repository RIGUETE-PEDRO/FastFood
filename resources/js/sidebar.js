(function () {
   const persistedCollapsedKey = 'ff-sidebar-collapsed-state';
   const forceCollapsedKey = 'ff-force-sidebar-collapsed';
   const collapsedClass = 'ff-sidebar-collapsed';
   const openClass = 'ff-sidebar-open';
   const mq = window.matchMedia('(max-width: 768px)');
   let scrollTopBeforeLock = 0;

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
     const isMobile = mq.matches;
     const isCollapsed = document.body.classList.contains(collapsedClass);
     const isOpenOnMobile = isMobile && !isCollapsed;
     const hasModalOpen = document.body.classList.contains('modal-open') ||
       document.body.classList.contains('ff-modal-open');

     document.body.classList.toggle(openClass, isOpenOnMobile);

     if (isOpenOnMobile && !hasModalOpen) {
       scrollTopBeforeLock = window.scrollY || document.documentElement.scrollTop || 0;
       document.body.style.overflow = 'hidden';
       document.body.style.position = 'fixed';
       document.body.style.top = `-${scrollTopBeforeLock}px`;
       document.body.style.width = '100%';
     } else {
       const hadFixedLock = document.body.style.position === 'fixed';
       const topValue = parseInt(document.body.style.top || '0', 10);
       document.body.style.removeProperty('position');
       document.body.style.removeProperty('top');
       document.body.style.removeProperty('width');
       if (!hasModalOpen) {
         document.body.style.removeProperty('overflow');
       }
       if (hadFixedLock && topValue < 0) {
         window.scrollTo(0, Math.abs(topValue));
       }
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
     const isMobile = mq.matches;

     if (forceCollapsed) {
       setCollapsedState(true, true);
       document.body.classList.remove(openClass);
     } else if (isMobile) {
       // Em mobile, siempre comeza com sidebar fechada
       setCollapsedState(true, false);
     } else if (persistedState === '1') {
       setCollapsedState(true, false);
     } else if (persistedState === '0') {
       setCollapsedState(false, false);
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
       event.stopPropagation();
       const isCollapsed = document.body.classList.contains(collapsedClass);
       setCollapsedState(!isCollapsed, true);
       return;
     }

     // Sidebar: permet clicar em toda a linha do item
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
       event.stopPropagation();
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
       if (event.type === 'touchend') {
         return;
       }

       // Fecha a sidebar após clicar em um link no mobile
       setCollapsedState(true, true);
     }
   }

   // Fechar sidebar ao clicar no overlay
   document.addEventListener('click', (event) => {
     const overlay = event.target.closest('.ff-sidebar-overlay');
     if (overlay && mq.matches) {
       event.preventDefault();
       event.stopPropagation();
       setCollapsedState(true, true);
     }
   });

   let lastTouchAt = 0;

   const touchOptions = { passive: false };
   document.addEventListener('touchend', (event) => {
     const target = getEventElementTarget(event);
     if (!target) return;

     const isSidebarTouch = target.closest('.ff-sidebar');
     const isToggleTouch = target.closest('[data-sidebar-toggle]');
     const isOverlayTouch = target.closest('.ff-sidebar-overlay');

     if (!isSidebarTouch && !isToggleTouch && !isOverlayTouch) return;

     if (target.closest('.ff-sidebar a.nav-link')) return;

     lastTouchAt = Date.now();
     handleSidebarInteractions(event);
   }, touchOptions);

   document.addEventListener('click', (event) => {
     // Em mobile, o mesmo toque dispara touchend e depois click.
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

   // Expor função para fechar sidebar externamente (ex: quando modal abre)
   window.ff = window.ff || {};
   window.ff.closeSidebar = function() {
     setCollapsedState(true, true);
   };
   window.ff.syncSidebarLock = syncMobileInteractionLock;

})();
