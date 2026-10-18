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

function formatCurrencyAmount(amount, currency) {
  const locale = currency.code === 'USD' ? 'en-US' : 'fr-FR';
  const fractionDigits = currency.code === 'TND' ? 3 : 2;
  const formatted = new Intl.NumberFormat(locale, {
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits
  }).format(amount);

  return currency.position === 'prefix'
    ? `${currency.symbol}${formatted}`
    : `${formatted} ${currency.symbol}`;
}

function refreshPriceValues() {
  document.querySelectorAll('[data-price-base], .price-val').forEach(el => {
    const base = Number(el.dataset.priceBase ?? el.dataset.base);
    if (!Number.isFinite(base)) return;
    el.textContent = formatCurrencyAmount(base * currentCurrency.rate, currentCurrency);
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
    fetch(button.dataset.currencyUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(response => {
        if (!response.ok) throw new Error(`Currency preference could not be saved (${response.status}).`);
      })
      .catch(error => console.error(error));
    closeNavbarDropdown(button.closest('[data-navbar-dropdown]'));
  });
});

const storedCurrency = localStorage.getItem('location-currency');
const selectedCurrency = (storedCurrency && document.querySelector(`[data-currency-code="${storedCurrency}"]`))
  || document.querySelector('[data-currency-selected="true"]');
if (selectedCurrency) {
  setCurrency(selectedCurrency.dataset.currencyCode, Number(selectedCurrency.dataset.currencyRate), selectedCurrency.dataset.currencySymbol, selectedCurrency.dataset.currencyPosition);
}