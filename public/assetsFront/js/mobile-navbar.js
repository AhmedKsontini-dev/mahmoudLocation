function initMobileNavbar() {
  const toggle = document.querySelector('[data-frontend-menu-toggle]');
  const nav = document.querySelector('[data-frontend-nav]');
  const closeButton = document.querySelector('[data-frontend-menu-close]');
  const overlay = document.querySelector('[data-frontend-menu-overlay]');
  const authModal = document.querySelector('[data-auth-modal]');

  if (!toggle || !nav || !closeButton || !overlay) return;

  const mobileViewport = window.matchMedia('(max-width: 1279px)');
  let bodyOverflowBeforeOpen = '';

  function getFocusableItems() {
    return Array.from(nav.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'))
      .filter(function(element) {
        return !element.hidden && element.getAttribute('aria-hidden') !== 'true' && element.offsetParent !== null;
      });
  }

  function setMenuOpen(isOpen, restoreFocus = false) {
    if (isOpen && !mobileViewport.matches) return;

    const wasOpen = toggle.getAttribute('aria-expanded') === 'true';
    if (wasOpen === isOpen) return;

    if (isOpen) {
      bodyOverflowBeforeOpen = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = bodyOverflowBeforeOpen;
      bodyOverflowBeforeOpen = '';
    }

    toggle.setAttribute('aria-expanded', String(isOpen));
    toggle.setAttribute('aria-label', isOpen ? toggle.dataset.closeLabel : toggle.dataset.openLabel);
    nav.classList.toggle('is-open', isOpen);
    nav.setAttribute('aria-hidden', String(!isOpen && mobileViewport.matches));
    nav.inert = !isOpen && mobileViewport.matches;
    overlay.classList.toggle('is-active', isOpen);

    if (isOpen || restoreFocus) {
      window.requestAnimationFrame(function() {
        if (isOpen && toggle.getAttribute('aria-expanded') === 'true') {
          closeButton.focus();
        } else if (!isOpen && toggle.getAttribute('aria-expanded') === 'false') {
          toggle.focus();
        }
      });
    }
  }

  function updateViewportMode() {
    if (mobileViewport.matches) {
      nav.setAttribute('aria-hidden', 'true');
      nav.inert = true;
      return;
    }

    setMenuOpen(false);
    nav.removeAttribute('aria-hidden');
    nav.inert = false;
  }

  toggle.addEventListener('click', function() {
    setMenuOpen(toggle.getAttribute('aria-expanded') !== 'true');
  });

  closeButton.addEventListener('click', function() {
    setMenuOpen(false, true);
  });

  overlay.addEventListener('click', function() {
    setMenuOpen(false, true);
  });

  nav.querySelectorAll('a').forEach(function(link) {
    link.addEventListener('click', function() {
      setMenuOpen(false);
    });
  });

  document.addEventListener('keydown', function(event) {
    if (toggle.getAttribute('aria-expanded') !== 'true') return;

    if (event.key === 'Escape') {
      event.preventDefault();
      setMenuOpen(false, true);
      return;
    }

    if (event.key === 'Tab') {
      if (authModal && !authModal.hidden) return;

      const focusableItems = getFocusableItems();
      const firstItem = focusableItems[0];
      const lastItem = focusableItems[focusableItems.length - 1];

      if (!firstItem) {
        event.preventDefault();
        closeButton.focus();
      } else if (document.activeElement === toggle) {
        event.preventDefault();
        (event.shiftKey ? lastItem : firstItem).focus();
      } else if (event.shiftKey && document.activeElement === firstItem) {
        event.preventDefault();
        lastItem.focus();
      } else if (!event.shiftKey && document.activeElement === lastItem) {
        event.preventDefault();
        firstItem.focus();
      }
    }
  });

  if (mobileViewport.addEventListener) {
    mobileViewport.addEventListener('change', updateViewportMode);
  } else {
    mobileViewport.addListener(updateViewportMode);
  }

  updateViewportMode();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMobileNavbar);
} else {
  initMobileNavbar();
}
