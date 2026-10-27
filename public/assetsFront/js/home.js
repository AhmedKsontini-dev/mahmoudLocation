/* Booking tab switcher */
function switchBookingTab(tabKey) {
  document.querySelectorAll('.booking-tab').forEach(b => {
    b.className = 'booking-tab flex items-center gap-2.5 px-5 py-3 rounded-xl font-label-lg text-label-lg text-on-surface-variant hover:bg-surface-container-low transition-all';
  });
  const btn = document.getElementById('tab-btn-' + tabKey);
  if (btn) btn.className = 'booking-tab active flex items-center gap-2.5 px-5 py-3 rounded-xl font-label-lg text-label-lg bg-primary text-on-primary font-bold transition-all shadow-sm';
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
  const panel = document.getElementById('panel-' + tabKey);
  if (panel) panel.classList.remove('hidden');
}

function getBookingLocalePrefix() {
  const path = window.location.pathname || '/';
  const firstSegment = path.split('/').filter(Boolean)[0] || '';
  const currentLocale = ['fr', 'en', 'it', 'es', 'de'].includes(firstSegment)
    ? firstSegment
    : (document.documentElement && document.documentElement.lang && ['fr', 'en', 'it', 'es', 'de'].includes(document.documentElement.lang)
      ? document.documentElement.lang
      : 'fr');

  return '/' + currentLocale;
}

function buildExcursionRedirectUrl() {
  const params = new URLSearchParams();
  const destinationSelect = document.getElementById('excursion-destination');
  const excursionSelect = document.getElementById('excursion-choice');

  const destinationId = destinationSelect ? destinationSelect.value : '';
  const excursionValue = excursionSelect ? excursionSelect.value : '';

  if (destinationId) {
    params.set('destination', String(destinationId));
  }

  if (excursionValue) {
    params.set('excursion', String(excursionValue));
  }

  return getBookingLocalePrefix() + '/excursions' + (params.toString() ? ('?' + params.toString()) : '');
}

function buildTransferRedirectUrl() {
  const form = document.getElementById('panel-transfer');
  const departureSelect = document.getElementById('transfer-departure');
  const destinationSelect = document.getElementById('transfer-destination');
  const departure = departureSelect ? departureSelect.value : '';
  const destination = destinationSelect ? destinationSelect.value : '';

  if (!form || !departure || !destination || destinationSelect.disabled) {
    window.alert(departureSelect?.dataset.invalidLabel || 'Aucun transfert disponible');
    return null;
  }

  const params = new URLSearchParams();
  params.set('departure', departure);
  params.set('destination', destination);
  const selectedDate = document.getElementById('transfer-date')?.value;
  if (selectedDate) params.set('pickupDate', selectedDate.slice(0, 10));
  return form.dataset.searchUrl + '?' + params.toString();
}

/* Modal */
function handleSearchSubmit(e, serviceName) {
  e.preventDefault();

  if (serviceName === 'Excursion') {
    const url = buildExcursionRedirectUrl();
    if (url) {
      window.location.href = url;
    }
    return false;
  }

  if (serviceName === 'Transfert') {
    const url = buildTransferRedirectUrl();
    if (url) {
      window.location.href = url;
    }
    return false;
  }

  return false;
}

function quickReserveModal(title, priceInDT) {
  const formatted = (priceInDT * currentCurrency.rate).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currentCurrency.code;
  openModal("Réservation express", "Votre demande pour " + title + " a été pré-enregistrée.", title, formatted);
}

function isInRecurringSeason(date, period) {
  if (!date || !period) return false;
  const value = Number(date.slice(5, 7)) * 100 + Number(date.slice(8, 10));
  const start = Number(period.startMonth) * 100 + Number(period.startDay);
  const end = Number(period.endMonth) * 100 + Number(period.endDay);
  if (start <= end) {
    return value >= start && value <= end;
  }
  return value >= start || value <= end;
}

function updateTransferPrices(dateValue) {
  document.querySelectorAll('[data-transfer-card]').forEach(card => {
    const date = dateValue ? dateValue.slice(0, 10) : '';
    const seasons = JSON.parse(card.dataset.seasonPeriods || '[]');
    const matchingSeasons = seasons.filter(period => isInRecurringSeason(date, period));
    const price = matchingSeasons.length > 1
      ? ''
      : matchingSeasons.length === 1
        ? card.dataset.seasonPrice
        : card.dataset.normalPrice;
    const priceElement = card.querySelector('.price-val');
    if (priceElement) {
      if (price === undefined || price === '') {
        delete priceElement.dataset.base;
        priceElement.textContent = '—';
        priceElement.title = matchingSeasons.length > 1 ? card.dataset.seasonConflict : card.dataset.priceUnavailable;
        priceElement.setAttribute('aria-label', priceElement.title);
      } else {
        priceElement.dataset.base = price;
        priceElement.removeAttribute('title');
        priceElement.removeAttribute('aria-label');
      }
    }
  });
  if (typeof refreshPriceValues === 'function') refreshPriceValues();
}

const transferDateInput = document.getElementById('transfer-date');
if (transferDateInput) {
  transferDateInput.addEventListener('change', () => updateTransferPrices(transferDateInput.value));
  updateTransferPrices(transferDateInput.value);
}

function openDetails(carName) {
  openModal("Fiche technique : " + carName, "Kilométrage illimité, climatisation, franchise rachetable et assistance 24/7 incluse.", carName, "Détails contractuels");
}

function openModal(title, desc, service, price) {
  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-desc').textContent = desc;
  document.getElementById('modal-service').textContent = service;
  document.getElementById('modal-price').textContent = price;
  document.getElementById('booking-modal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('booking-modal').classList.add('hidden');
}

const transferDepartureLocations = window.transferDepartureLocations || [];
const excursionBookingData = window.excursionBookingData || [];

function createTransferOption(value, label) {
  const option = document.createElement('option');
  option.value = value;
  option.textContent = label;
  return option;
}

function normalizeTransferDeparture(location) {
  return String(location || '')
    .trim()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/gu, '')
    .replace(/['`]/gu, '')
    .toLowerCase()
    .replace(/\b(?:aeroport|airport|aireport|aereport)\b/gu, ' airport ')
    .replace(/[^a-z0-9]+/gu, ' ')
    .replace(/\s+/gu, ' ')
    .trim();
}

function populateTransferOptions() {
  const departureSelect = document.getElementById('transfer-departure');
  const destinationSelect = document.getElementById('transfer-destination');

  if (!departureSelect || !destinationSelect) {
    return;
  }

  departureSelect.innerHTML = '';
  const placeholder = createTransferOption('', departureSelect.dataset.placeholderLabel || 'Choose a departure');
  departureSelect.appendChild(placeholder);
  const locations = transferDepartureLocations.length
    ? transferDepartureLocations
    : [];
  const uniqueDepartures = new Map();
  locations.forEach((departure) => {
    const label = String(departure || '').trim().replace(/\s+/gu, ' ');
    const key = normalizeTransferDeparture(label);
    if (key && !uniqueDepartures.has(key)) {
      uniqueDepartures.set(key, label);
    }
  });

  if (!uniqueDepartures.size) {
    placeholder.textContent = departureSelect.dataset.emptyLabel || 'No transfers available';
    departureSelect.disabled = true;
    destinationSelect.innerHTML = '';
    destinationSelect.appendChild(createTransferOption('', destinationSelect.dataset.emptyLabel || 'No destinations available'));
    destinationSelect.disabled = true;
    return;
  }

  departureSelect.disabled = false;
  uniqueDepartures.forEach((label) => {
    departureSelect.appendChild(createTransferOption(label, label));
  });

  departureSelect.addEventListener('change', () => populateTransferDestinations());
  destinationSelect.innerHTML = '';
  destinationSelect.appendChild(createTransferOption('', destinationSelect.dataset.placeholderLabel || 'Choose a departure first'));
  destinationSelect.disabled = true;
}

async function populateTransferDestinations() {
  const departureSelect = document.getElementById('transfer-departure');
  const destinationSelect = document.getElementById('transfer-destination');

  if (!departureSelect || !destinationSelect) {
    return;
  }

  const selectedDeparture = departureSelect.value;
  destinationSelect.innerHTML = '';
  destinationSelect.disabled = true;
  if (!selectedDeparture) {
    destinationSelect.appendChild(createTransferOption('', destinationSelect.dataset.placeholderLabel || 'Choose a departure first'));
    return;
  }

  const requestId = (Number(destinationSelect.dataset.requestId) || 0) + 1;
  destinationSelect.dataset.requestId = String(requestId);
  destinationSelect.appendChild(createTransferOption('', destinationSelect.dataset.loadingLabel || 'Loading destinations'));

  try {
    const endpoint = new URL(destinationSelect.dataset.destinationsUrl, window.location.origin);
    endpoint.searchParams.set('departure', selectedDeparture);
    const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
    if (!response.ok) {
      throw new Error(`Destination request failed with status ${response.status}`);
    }
    const data = await response.json();
    if (Number(destinationSelect.dataset.requestId) !== requestId || departureSelect.value !== selectedDeparture) {
      return;
    }

    destinationSelect.innerHTML = '';
    const uniqueDestinations = new Map();
    (Array.isArray(data.destinations) ? data.destinations : []).forEach((destination) => {
      const label = String(destination || '').trim().replace(/\s+/gu, ' ');
      const key = label.toLocaleLowerCase();
      if (key && !uniqueDestinations.has(key)) {
        uniqueDestinations.set(key, label);
      }
    });
    destinationSelect.appendChild(createTransferOption(
      '',
      uniqueDestinations.size
        ? (destinationSelect.dataset.selectLabel || 'Choose a destination')
        : (destinationSelect.dataset.emptyLabel || 'No destinations available')
    ));
    uniqueDestinations.forEach((label) => {
      destinationSelect.appendChild(createTransferOption(label, label));
    });
    destinationSelect.disabled = uniqueDestinations.size === 0;
  } catch (error) {
    if (Number(destinationSelect.dataset.requestId) !== requestId) {
      return;
    }
    console.error('Unable to load transfer destinations.', error);
    destinationSelect.innerHTML = '';
    destinationSelect.appendChild(createTransferOption('', destinationSelect.dataset.errorLabel || 'Unable to load destinations'));
    destinationSelect.disabled = true;
  }
}

function populateExcursionOptions() {
  const destinationSelect = document.getElementById('excursion-destination');
  const choiceSelect = document.getElementById('excursion-choice');
  const excursionHidden = document.getElementById('excursion-slug');

  if (!destinationSelect || !choiceSelect) {
    return;
  }

  destinationSelect.innerHTML = '';
  const uniqueDestinations = [...new Map(excursionBookingData.map(item => [item.destination, item])).values()];

  if (!uniqueDestinations.length) {
    const emptyOption = document.createElement('option');
    emptyOption.value = '';
    emptyOption.textContent = destinationSelect.dataset.emptyLabel || 'Aucune excursion disponible';
    destinationSelect.appendChild(emptyOption);
    choiceSelect.innerHTML = '';
    if (excursionHidden) excursionHidden.value = '';
    return;
  }

  uniqueDestinations.forEach((item) => {
    const option = document.createElement('option');
    option.value = String(item.destinationId);
    option.textContent = item.destination;
    destinationSelect.appendChild(option);
  });

  destinationSelect.addEventListener('change', () => populateExcursionChoices());
  populateExcursionChoices();
}

function populateExcursionChoices() {
  const destinationSelect = document.getElementById('excursion-destination');
  const choiceSelect = document.getElementById('excursion-choice');
  const excursionHidden = document.getElementById('excursion-slug');

  if (!destinationSelect || !choiceSelect) {
    return;
  }

  const selectedDestinationId = destinationSelect.value;
  choiceSelect.innerHTML = '';

  const matches = excursionBookingData.filter((item) => String(item.destinationId) === String(selectedDestinationId));
  const excursions = matches.length ? matches[0].excursions : [];

  if (!excursions.length) {
    const emptyOption = document.createElement('option');
    emptyOption.value = '';
    emptyOption.textContent = choiceSelect.dataset.emptyLabel || 'Aucune excursion disponible';
    choiceSelect.appendChild(emptyOption);
    if (excursionHidden) excursionHidden.value = '';
    return;
  }

  excursions.forEach((excursion) => {
    const option = document.createElement('option');
    option.value = excursion.slug || String(excursion.id || '');
    option.textContent = excursion.title;
    choiceSelect.appendChild(option);
  });

  choiceSelect.value = excursions[0].slug || String(excursions[0].id || '');
  if (excursionHidden) excursionHidden.value = choiceSelect.value;

  choiceSelect.onchange = () => {
    if (excursionHidden) excursionHidden.value = choiceSelect.value;
  };
}

if (document.getElementById('transfer-departure')) {
  populateTransferOptions();
}

if (document.getElementById('excursion-destination')) {
  populateExcursionOptions();
}
