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
  const departure = document.getElementById('transfer-departure') ? document.getElementById('transfer-departure').value : '';
  const destination = document.getElementById('transfer-destination') ? document.getElementById('transfer-destination').value : '';

  if (!departure && !destination) {
    return null;
  }

  const match = transferBookingData.find((item) => item.departure === departure && item.destination === destination);
  if (!match) {
    window.alert('Aucun transfert disponible pour ce trajet.');
    return null;
  }

  const slug = match.slug || match.id;
  if (!slug) {
    window.alert('Aucun transfert disponible pour ce trajet.');
    return null;
  }

  return getBookingLocalePrefix() + '/transfers/' + slug;
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

function isDateInSeason(dateValue, startValue, endValue) {
  if (!dateValue || !startValue || !endValue) return false;
  const date = dateValue.slice(5, 10);
  return startValue <= endValue
    ? date >= startValue && date <= endValue
    : date >= startValue || date <= endValue;
}

function updateTransferPrices(dateValue) {
  document.querySelectorAll('[data-transfer-card]').forEach(card => {
    const inSeason = isDateInSeason(dateValue, card.dataset.seasonStart, card.dataset.seasonEnd);
    const price = inSeason ? card.dataset.seasonPrice : card.dataset.normalPrice;
    const priceElement = card.querySelector('.price-val');
    if (priceElement) {
      priceElement.dataset.base = price;
    }
  });
  if (typeof refreshPriceValues === 'function') refreshPriceValues();
}

const transferDateInput = document.getElementById('transfer-date');
if (transferDateInput) {
  transferDateInput.addEventListener('change', () => updateTransferPrices(transferDateInput.value));
  updateTransferPrices('');
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

const transferBookingData = window.transferBookingData || [];
const excursionBookingData = window.excursionBookingData || [];

function populateTransferOptions() {
  const departureSelect = document.getElementById('transfer-departure');
  const destinationSelect = document.getElementById('transfer-destination');
  const transferHidden = document.getElementById('transfer-slug');

  if (!departureSelect || !destinationSelect) {
    return;
  }

  departureSelect.innerHTML = '';
  const uniqueDepartures = [...new Set(transferBookingData.map(item => item.departure))];

  if (!uniqueDepartures.length) {
    const emptyOption = document.createElement('option');
    emptyOption.value = '';
    emptyOption.textContent = departureSelect.dataset.emptyLabel || 'Aucun transfert disponible';
    departureSelect.appendChild(emptyOption);
    destinationSelect.innerHTML = '';
    if (transferHidden) transferHidden.value = '';
    return;
  }

  uniqueDepartures.forEach((departure) => {
    const option = document.createElement('option');
    option.value = departure;
    option.textContent = departure;
    departureSelect.appendChild(option);
  });

  departureSelect.addEventListener('change', () => populateTransferDestinations());
  populateTransferDestinations();
}

function populateTransferDestinations() {
  const departureSelect = document.getElementById('transfer-departure');
  const destinationSelect = document.getElementById('transfer-destination');
  const transferHidden = document.getElementById('transfer-slug');

  if (!departureSelect || !destinationSelect) {
    return;
  }

  const selectedDeparture = departureSelect.value;
  destinationSelect.innerHTML = '';
  const destinations = transferBookingData.filter(item => item.departure === selectedDeparture);

  if (!destinations.length) {
    const emptyOption = document.createElement('option');
    emptyOption.value = '';
    emptyOption.textContent = destinationSelect.dataset.emptyLabel || 'Aucune destination disponible';
    destinationSelect.appendChild(emptyOption);
    if (transferHidden) transferHidden.value = '';
    return;
  }

  const uniqueDestinations = [...new Map(destinations.map(item => [item.destination, item])).values()];
  uniqueDestinations.forEach((item) => {
    const option = document.createElement('option');
    option.value = item.destination;
    option.textContent = item.destination;
    destinationSelect.appendChild(option);
  });

  destinationSelect.value = uniqueDestinations[0].destination;
  if (transferHidden) {
    const selected = uniqueDestinations[0];
    transferHidden.value = selected.slug || selected.id ? String(selected.slug || selected.id) : '';
  }

  destinationSelect.onchange = () => {
    const matched = transferBookingData.find((item) => item.departure === selectedDeparture && item.destination === destinationSelect.value);
    if (transferHidden && matched) {
      transferHidden.value = matched.slug || String(matched.id || '');
    }
  };
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
