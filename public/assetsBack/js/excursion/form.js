// ── Champs Catégorie / Destination : recherche et création en AJAX ──
// Un objet « field » regroupe tout le contexte d'un champ .searchable-select.

const FIELD_LABELS = { category: 'catégorie', destination: 'destination' };

function fieldLabel(fieldName) {
    return FIELD_LABELS[fieldName] || 'valeur';
}

function normalizeText(value) {
    return String(value || '').trim().toLowerCase();
}

// Message clair quand le serveur ne fournit pas son propre libellé.
function fallbackHttpMessage(status) {
    if (status === 400) return 'Requête refusée par le serveur : la saisie est invalide.';
    if (status === 403 || status === 419) return 'Session expirée. Rechargez la page puis réessayez.';
    if (status === 404) return 'Adresse du serveur introuvable. Prévenez un administrateur.';
    if (status === 409) return 'Cette valeur existe déjà mais n\'a pas pu être récupérée.';
    if (status >= 500) return 'Erreur serveur : l\'enregistrement a échoué.';
    return 'Erreur inattendue (code ' + status + ').';
}

// Vérifie le statut HTTP et le Content-Type AVANT de parser : le HTML d'une page
// d'erreur Symfony ne doit jamais être donné à JSON.parse() ni affiché brut.
function parseJsonResponse(response, context) {
    return response.text().then(function(text) {
        const contentType = (response.headers.get('content-type') || '').toLowerCase();
        let payload = null;

        if (contentType.indexOf('json') !== -1) {
            try {
                payload = JSON.parse(text);
            } catch (parseError) {
                payload = null;
            }
        }

        if (payload === null) {
            // La réponse brute est journalisée pour le diagnostic, pas affichée à l'utilisateur.
            console.error(context + ' : réponse serveur illisible', {
                status: response.status,
                contentType: contentType,
                body: text.slice(0, 500)
            });
            throw new Error(response.ok
                ? 'Réponse du serveur illisible. Réessayez.'
                : fallbackHttpMessage(response.status));
        }

        if (!response.ok) {
            console.error(context + ' : erreur HTTP ' + response.status, payload);
            throw new Error(payload.error || fallbackHttpMessage(response.status));
        }

        return payload;
    });
}

function dedupeResults(results) {
    const seen = {};

    return results.filter(function(result) {
        if (!result || !result.id || seen[result.id]) return false;
        seen[result.id] = true;
        return true;
    });
}

function findExactMatch(results, query) {
    const needle = normalizeText(query);

    return results.find(function(result) {
        return normalizeText(result.name) === needle;
    });
}

function showFieldMessage(field, message, className, onRetry) {
    field.container.innerHTML = '';

    const messageEl = document.createElement('div');
    messageEl.className = 'searchable-result ' + className;
    messageEl.textContent = message;

    if (onRetry) {
        messageEl.addEventListener('click', onRetry);
        messageEl.title = 'Cliquer pour réessayer';
    }

    field.container.appendChild(messageEl);
    field.container.style.display = 'block';
}

function showFieldNotice(field, message) {
    showFieldMessage(field, message, 'searchable-note');

    window.setTimeout(function() {
        field.container.style.display = 'none';
    }, 2600);
}

function showFieldError(field, message, onRetry) {
    showFieldMessage(field, '⚠ ' + message, 'searchable-error', onRetry);
}

// L'option est ajoutée au <select> si elle n'y figure pas encore : sans option
// correspondante, la valeur ne serait pas transmise au formulaire de l'excursion.
function selectEntity(id, name, hiddenSelect, input, container, field, icon) {
    let option = hiddenSelect.querySelector('option[value="' + id + '"]');

    if (!option) {
        option = document.createElement('option');
        option.value = id;
        hiddenSelect.appendChild(option);
    }

    option.textContent = name;
    hiddenSelect.value = id;
    input.value = name;
    container.style.display = 'none';

    // L'icône de la catégorie sélectionnée s'affiche dans le champ, à gauche de la saisie.
    if (field) {
        setFieldIcon(field, icon || '');
    }

    hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
}

// Affiche (ou retire) l'icône de la valeur sélectionnée dans le champ.
function setFieldIcon(field, icon) {
    if (!field.iconEl) return;

    if (icon) {
        field.iconEl.textContent = icon;
        field.iconEl.style.display = 'inline-block';
        field.select.classList.add('has-selection-icon');
    } else {
        field.iconEl.textContent = '';
        field.iconEl.style.display = 'none';
        field.select.classList.remove('has-selection-icon');
    }
}

// Interroge le serveur et renvoie les résultats dédupliqués, sans les afficher.
function requestEntities(field, query) {
    return fetch(field.searchUrl + '?q=' + encodeURIComponent(query), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        return parseJsonResponse(response, 'Recherche ' + fieldLabel(field.fieldName));
    })
    .then(function(data) {
        return dedupeResults(Array.isArray(data && data.results) ? data.results : []);
    });
}

function searchEntities(field) {
    const query = field.input.value.trim();
    const requestId = ++field.requestId;

    // Les champs « ouverture sur clic » affichent la liste complète même sans texte.
    if (query.length < 2 && !field.showAll) {
        field.container.style.display = 'none';
        return Promise.resolve([]);
    }

    return requestEntities(field, query).then(function(results) {
        // Une saisie plus récente rend obsolètes les résultats qui arrivent après.
        if (requestId === field.requestId) {
            displayResults(field, results, query);
        }

        return results;
    });
}

// Ouvre la liste complète des catégories enregistrées, sans exiger de saisie.
function openFullList(field) {
    const requestId = ++field.requestId;

    return requestEntities(field, '').then(function(results) {
        if (requestId === field.requestId) {
            displayResults(field, results, '');
        }

        return results;
    }).catch(function(error) {
        showFieldError(field, error.message, function() {
            openFullList(field);
        });
    });
}

function displayResults(field, results, query) {
    const container = field.container;
    container.innerHTML = '';

    if (results.length === 0 && '' === query) {
        // Aucune catégorie en base : message clair, sans erreur ni proposition de création.
        showFieldMessage(field, 'Aucune ' + fieldLabel(field.fieldName) + ' disponible', 'searchable-note');
        return;
    }

    results.forEach(function(result) {
        const option = document.createElement('div');
        option.className = 'searchable-result';
        option.setAttribute('data-id', result.id);

        if (result.icon) {
            const iconEl = document.createElement('span');
            iconEl.className = 'searchable-result-icon';
            iconEl.textContent = result.icon;
            option.appendChild(iconEl);
        }

        const labelEl = document.createElement('span');
        labelEl.className = 'searchable-result-label';
        labelEl.textContent = result.name;
        option.appendChild(labelEl);

        option.addEventListener('click', function() {
            selectEntity(result.id, result.name, field.hiddenSelect, field.input, container, field, result.icon);
        });
        container.appendChild(option);
    });

    // La création n'est proposée que si aucun choix ne porte déjà ce nom exact.
    if (query && !findExactMatch(results, query)) {
        const createOption = document.createElement('div');
        createOption.className = 'searchable-result searchable-create';
        createOption.textContent = '+ Ajouter "' + query + '"';
        createOption.addEventListener('click', function() {
            createNewEntity(field, query);
        });
        container.appendChild(createOption);
    }

    container.style.display = 'block';
}

function createNewEntity(field, name) {
    if (field.busy) return Promise.resolve();

    field.busy = true;
    showFieldMessage(field, 'Création de la ' + fieldLabel(field.fieldName) + ' « ' + name + ' »…', 'searchable-loading');

    return fetch(field.createUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': field.csrfToken
        },
        body: JSON.stringify({ name: name, _token: field.csrfToken })
    })
    .then(function(response) {
        return parseJsonResponse(response, 'Création ' + fieldLabel(field.fieldName));
    })
    .then(function(data) {
        if (!data || !data.id) {
            // Aucune option n'est posée tant que l'enregistrement n'est pas confirmé.
            showFieldError(field, (data && data.error) || ('La ' + fieldLabel(field.fieldName) + ' n\'a pas été enregistrée.'), function() {
                createNewEntity(field, name);
            });
            return;
        }

        // Une catégorie créée en AJAX n'a pas encore d'icône : on retire celle de l'ancienne sélection.
        selectEntity(data.id, data.name || name, field.hiddenSelect, field.input, field.container, field, '');
        showFieldNotice(field, data.existed
            ? '« ' + (data.name || name) + ' » existait déjà : elle a été réutilisée.'
            : '« ' + (data.name || name) + ' » a été créée et sélectionnée.');
    })
    .catch(function(error) {
        showFieldError(field, error.message, function() {
            createNewEntity(field, name);
        });
    })
    .finally(function() {
        field.busy = false;
    });
}

// Touche Entrée : réutilise la valeur existante à l'identique, sinon la crée,
// sans jamais soumettre le formulaire principal.
function handleFieldEnter(field) {
    const query = field.input.value.trim();

    window.clearTimeout(field.timer);

    if (query === '') {
        showFieldError(field, 'Saisissez un nom de ' + fieldLabel(field.fieldName) + ' avant de valider.');
        return;
    }

    if (field.busy) return;

    field.busy = true;
    showFieldMessage(field, 'Vérification de « ' + query + ' »…', 'searchable-loading');

    searchEntities(field)
        .then(function(results) {
            field.busy = false;

            const exact = findExactMatch(results, query);
            if (exact) {
                selectEntity(exact.id, exact.name, field.hiddenSelect, field.input, field.container, field, exact.icon);
                showFieldNotice(field, '« ' + exact.name + ' » a été sélectionnée.');
                return;
            }

            if (results.length > 0) {
                displayResults(field, results, query);
            }

            return createNewEntity(field, query);
        })
        .catch(function(error) {
            field.busy = false;
            showFieldError(field, error.message);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // Searchable select with create option
    const fields = [];

    document.querySelectorAll('.searchable-select').forEach(function(select) {
        const input = select.querySelector('.searchable-input');
        const container = select.querySelector('.searchable-results');
        const hiddenSelect = select.nextElementSibling;

        if (!input || !container || !hiddenSelect || hiddenSelect.tagName !== 'SELECT') {
            console.error('Champ searchable-select mal configuré :', select);
            return;
        }

        // L'icône de la valeur sélectionnée est affichée dans le champ, à gauche de la saisie.
        const iconEl = document.createElement('span');
        iconEl.className = 'searchable-selected-icon';
        iconEl.setAttribute('aria-hidden', 'true');
        iconEl.style.display = 'none';
        select.insertBefore(iconEl, input);

        const field = {
            select: select,
            input: input,
            container: container,
            iconEl: iconEl,
            hiddenSelect: hiddenSelect,
            searchUrl: select.getAttribute('data-url'),
            createUrl: select.getAttribute('data-create-url'),
            csrfToken: select.getAttribute('data-create-token') || '',
            fieldName: select.getAttribute('data-field'),
            // data-show-all : la liste complète s'ouvre au clic, sans saisie préalable.
            showAll: select.getAttribute('data-show-all') === 'true',
            timer: null,
            busy: false,
            requestId: 0
        };

        fields.push(field);

        // Load initial value if already selected
        if (hiddenSelect.value) {
            const selectedOption = hiddenSelect.querySelector('option[value="' + hiddenSelect.value + '"]');
            if (selectedOption) {
                input.value = selectedOption.text;
            }

            // En modification (ou après erreurs de validation), l'icône de la catégorie
            // déjà enregistrée est récupérée depuis la liste complète fournie par le serveur.
            if (field.showAll) {
                requestEntities(field, '').then(function(results) {
                    const match = results.find(function(result) {
                        return String(result.id) === String(hiddenSelect.value);
                    });
                    if (match) {
                        setFieldIcon(field, match.icon || '');
                        if (match.name && (!selectedOption || selectedOption.text !== match.name)) {
                            input.value = match.name;
                        }
                    }
                }).catch(function() {
                    // L'affichage du nom sans icône reste acceptable si le serveur est indisponible.
                });
            }
        }

        function runSearch() {
            searchEntities(field).catch(function(error) {
                showFieldError(field, error.message);
            });
        }

        input.addEventListener('input', function() {
            window.clearTimeout(field.timer);

            if (this.value.trim().length < 2 && !field.showAll) {
                container.style.display = 'none';
                return;
            }

            field.timer = window.setTimeout(runSearch, 300);
        });

        function openOnInteraction() {
            if (field.showAll) {
                // Clic/focus : toutes les catégories s'affichent immédiatement,
                // même si le champ porte déjà une sélection.
                window.clearTimeout(field.timer);
                openFullList(field);
            } else if (input.value.trim().length >= 2) {
                runSearch();
            }
        }

        input.addEventListener('focus', openOnInteraction);

        // Le clic sur un champ déjà focalisé ne déclenche pas « focus » : la liste
        // doit malgré tout se rouvrir (elle a pu être fermée par un clic extérieur).
        input.addEventListener('click', function() {
            if (field.showAll && container.style.display === 'none') {
                openFullList(field);
            }
        });

        input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                // La touche Entrée ajoute/selectionne la valeur, elle ne soumet pas le formulaire.
                event.preventDefault();
                handleFieldEnter(field);
            }
        });
    });

    // Ferme les listes déroulantes au clic en dehors du champ
    document.addEventListener('click', function(event) {
        fields.forEach(function(field) {
            if (!field.select.contains(event.target)) {
                field.container.style.display = 'none';
            }
        });
    });
    
    // Main tabs functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding pane
            this.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        });
    });

    // Translation tabs functionality
    const translationTabs = document.querySelectorAll('.translation-tab');
    const translationPanes = document.querySelectorAll('.translation-pane');

    translationTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const locale = this.getAttribute('data-locale');
            
            translationTabs.forEach(t => t.classList.remove('active'));
            translationPanes.forEach(p => p.classList.remove('active'));
            
            this.classList.add('active');
            document.querySelector(`.translation-pane[data-locale="${locale}"]`).classList.add('active');
        });
    });

    // Collection add/remove functionality
    const addButtons = document.querySelectorAll('.add-collection');
    
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            const collectionName = this.getAttribute('data-collection');
            const collectionContainer = document.querySelector(`.collection-container[data-collection="${collectionName}"]`);
            
            if (!collectionContainer) return;
            
            const prototype = collectionContainer.getAttribute('data-prototype');
            // L'index du nouveau élément doit dépasser tous les indices encore
            // présents dans le DOM : children.length réutiliserait un index après
            // une suppression (doublon [n]) et désynchroniserait la collection
            // soumise de la collection Doctrine côté serveur.
            const indexRegex = new RegExp('^excursion\\[' + collectionName + '\\]\\[(\\d+)\\]');
            let maxIndex = -1;
            collectionContainer.querySelectorAll('input, select, textarea').forEach(function(el) {
                const match = el.name ? el.name.match(indexRegex) : null;
                if (match) {
                    maxIndex = Math.max(maxIndex, parseInt(match[1], 10));
                }
            });
            const index = maxIndex + 1;
            const newFormHtml = prototype.replace(/__name__/g, index);
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newFormHtml;
            const newForm = tempDiv.firstElementChild;
            
            newForm.classList.add('collection-item');
            collectionContainer.appendChild(newForm);
            
            // Add remove button functionality
            const removeBtn = newForm.querySelector('.remove-collection');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    newForm.remove();
                });
            }
        });
    });

    // Remove collection items
    document.querySelectorAll('.remove-collection').forEach(button => {
        button.addEventListener('click', function() {
            const item = this.closest('.collection-item');
            if (item) {
                item.remove();
            }
        });
    });

    // Image preview on file selection
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.closest('.collection-item')?.querySelector('.preview-image');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'preview-image';
                        input.parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Le panneau français est identifié par data-locale, jamais par un index
    // de collection : les clés Doctrine peuvent être non contiguës ou réordonnées.
    const frenchPane = document.querySelector('.translation-pane[data-locale="fr"]');
    const titleFr = frenchPane ? frenchPane.querySelector('input[name*="[title]"]') : null;
    const slugField = document.querySelector('input[name="excursion[slug]"]');
    
    if (titleFr && slugField) {
        titleFr.addEventListener('input', function() {
            if (!slugField.value) {
                const slug = this.value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                slugField.value = slug;
            }
        });
    }
    
    // Form submission validation
    const form = document.querySelector('.excursion-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const categorySelect = document.querySelector('select[name="excursion[category]"]');
            const destinationSelect = document.querySelector('select[name="excursion[destination]"]');
            const frPane = document.querySelector('.translation-pane[data-locale="fr"]');
            const titleFr = frPane ? frPane.querySelector('input[name*="[title]"]') : null;
            const descFr = frPane ? frPane.querySelector('textarea[name*="[description]"]') : null;
            
            let isValid = true;
            let errorMessage = '';
            let firstInvalidTab = null;
            let firstInvalidInput = null;
            let firstInvalidLocale = null;

            // Clear previous error indicators
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('has-error'));
            document.querySelectorAll('.translation-tab').forEach(btn => btn.classList.remove('has-error'));

            // 1. Check French Title (mandatory)
            if (!titleFr || !titleFr.value.trim()) {
                isValid = false;
                errorMessage = 'Le titre en français est obligatoire dans l\'onglet Traductions.';
                firstInvalidTab = 'translations';
                firstInvalidLocale = 'fr';
                firstInvalidInput = titleFr;
                const transBtn = document.querySelector('.tab-btn[data-tab="translations"]');
                if (transBtn) transBtn.classList.add('has-error');
            } 
            // 2. Check French Description (mandatory)
            else if (!descFr || !descFr.value.trim()) {
                isValid = false;
                errorMessage = 'La description en français est obligatoire dans l\'onglet Traductions.';
                firstInvalidTab = 'translations';
                firstInvalidLocale = 'fr';
                firstInvalidInput = descFr;
                const transBtn = document.querySelector('.tab-btn[data-tab="translations"]');
                if (transBtn) transBtn.classList.add('has-error');
            }

            // 3. Check Category
            if (!categorySelect || !categorySelect.value) {
                isValid = false;
                errorMessage = errorMessage ? errorMessage + '\nVeuillez sélectionner une catégorie.' : 'Veuillez sélectionner une catégorie.';
                if (!firstInvalidTab) {
                    firstInvalidTab = 'general';
                    firstInvalidInput = document.querySelector('.searchable-select[data-field="category"] .searchable-input');
                }
                const genBtn = document.querySelector('.tab-btn[data-tab="general"]');
                if (genBtn) genBtn.classList.add('has-error');
            }
            
            // 4. Check Destination
            if (!destinationSelect || !destinationSelect.value) {
                isValid = false;
                errorMessage = errorMessage ? errorMessage + '\nVeuillez sélectionner une destination.' : 'Veuillez sélectionner une destination.';
                if (!firstInvalidTab) {
                    firstInvalidTab = 'general';
                    firstInvalidInput = document.querySelector('.searchable-select[data-field="destination"] .searchable-input');
                }
                const genBtn = document.querySelector('.tab-btn[data-tab="general"]');
                if (genBtn) genBtn.classList.add('has-error');
            }

            // 5. Check any other fields with native validation if visible
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    isValid = false;
                    const tabPane = input.closest('.tab-pane');
                    if (tabPane) {
                        const tabId = tabPane.id.replace('tab-', '');
                        const tabBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
                        if (tabBtn) tabBtn.classList.add('has-error');
                        
                        if (!firstInvalidTab) {
                            firstInvalidTab = tabId;
                            firstInvalidInput = input;
                            const transPane = input.closest('.translation-pane');
                            if (transPane) {
                                firstInvalidLocale = transPane.getAttribute('data-locale');
                            }
                        }
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                
                if (errorMessage) {
                    alert(errorMessage);
                } else {
                    alert('Veuillez corriger les erreurs dans le formulaire.');
                }

                // Switch to the first tab containing an error
                if (firstInvalidTab) {
                    const tabBtn = document.querySelector(`.tab-btn[data-tab="${firstInvalidTab}"]`);
                    if (tabBtn) {
                        tabBtn.click();
                    }
                    if (firstInvalidLocale) {
                        const localeBtn = document.querySelector(`.translation-tab[data-locale="${firstInvalidLocale}"]`);
                        if (localeBtn) {
                            localeBtn.click();
                        }
                    }
                    // Focus the input if possible
                    if (firstInvalidInput) {
                        setTimeout(() => {
                            firstInvalidInput.focus();
                            firstInvalidInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 150);
                    }
                }
                
                return false;
            }
        });
    }

    // Function to scroll to first error field
    window.scrollToFirstError = function() {
        const firstErrorField = document.querySelector('.has-error input, .has-error select, .has-error textarea, .invalid-feedback');
        if (firstErrorField) {
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (firstErrorField.tagName === 'INPUT' || firstErrorField.tagName === 'SELECT' || firstErrorField.tagName === 'TEXTAREA') {
                firstErrorField.focus();
            }
        }
    };

    // Mark tabs with errors after page load (for server-side validation errors)
    document.querySelectorAll('.invalid-feedback').forEach(errorElement => {
        const tabPane = errorElement.closest('.tab-pane');
        if (tabPane) {
            const tabId = tabPane.id.replace('tab-', '');
            const tabBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
            if (tabBtn) {
                tabBtn.classList.add('has-error');
            }
        }
    });
});