let currentCurrency = { code: 'TND', symbol: 'DT', rate: 1.0, position: 'suffix' };

function setCurrency(code, rate, symbol, position) {
  currentCurrency = { code, rate, symbol, position };
  window.currentCurrency = currentCurrency;
  localStorage.setItem('location-currency', code);

  document.querySelectorAll('[data-navbar-currency-label]').forEach(label => {
    label.textContent = symbol;
  });
  refreshPriceValues();
  window.dispatchEvent(new CustomEvent('currency:changed', { detail: currentCurrency }));
}

function refreshPriceValues() {
  document.querySelectorAll('.price-val').forEach(el => {
    const base = parseFloat(el.getAttribute('data-base'));
    if (isNaN(base)) return;
    const converted = (base * currentCurrency.rate).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    el.textContent = converted + ' ' + currentCurrency.code;
  });
}

function closeNavbarDropdown(dropdown) {
  const menu = dropdown.querySelector('[data-navbar-dropdown-menu]');
  const toggle = dropdown.querySelector('[data-navbar-dropdown-toggle]');
  menu.classList.add('opacity-0', 'pointer-events-none');
  menu.classList.remove('opacity-100', 'pointer-events-auto');
  toggle.setAttribute('aria-expanded', 'false');
}

document.querySelectorAll('[data-navbar-dropdown]').forEach(dropdown => {
  const toggle = dropdown.querySelector('[data-navbar-dropdown-toggle]');
  const menu = dropdown.querySelector('[data-navbar-dropdown-menu]');

  toggle.addEventListener('click', event => {
    event.stopPropagation();
    const isOpen = toggle.getAttribute('aria-expanded') === 'true';
    document.querySelectorAll('[data-navbar-dropdown]').forEach(closeNavbarDropdown);
    if (!isOpen) {
      menu.classList.remove('opacity-0', 'pointer-events-none');
      menu.classList.add('opacity-100', 'pointer-events-auto');
      toggle.setAttribute('aria-expanded', 'true');
    }
  });
});

document.addEventListener('click', event => {
  if (!event.target.closest('[data-navbar-dropdown]')) {
    document.querySelectorAll('[data-navbar-dropdown]').forEach(closeNavbarDropdown);
  }
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape') {
    document.querySelectorAll('[data-navbar-dropdown]').forEach(closeNavbarDropdown);
  }
});

document.querySelectorAll('[data-currency-code]').forEach(button => {
  button.addEventListener('click', () => {
    setCurrency(button.dataset.currencyCode, Number(button.dataset.currencyRate), button.dataset.currencySymbol, button.dataset.currencyPosition);
    fetch(button.dataset.currencyUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => {});
    closeNavbarDropdown(button.closest('[data-navbar-dropdown]'));
  });
});

const storedCurrency = localStorage.getItem('location-currency');
if (storedCurrency) {
  const button = document.querySelector(`[data-currency-code="${storedCurrency}"]`);
  if (button) setCurrency(button.dataset.currencyCode, Number(button.dataset.currencyRate), button.dataset.currencySymbol, button.dataset.currencyPosition);
}