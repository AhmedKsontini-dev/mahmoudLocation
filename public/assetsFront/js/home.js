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

/* Modal */
function handleSearchSubmit(e, serviceName) {
  e.preventDefault();
  openModal("Recherche : " + serviceName, "Disponibilité immédiate pour votre sélection en Tunisie.", serviceName, "Calcul en cours");
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
