// Helper functions for searchable select
function displayResults(results, query, createUrl, container, hiddenSelect, input, fieldName) {
    container.innerHTML = '';
    
    if (results.length === 0) {
        const createOption = document.createElement('div');
        createOption.className = 'searchable-result searchable-create';
        createOption.textContent = `+ Ajouter "${query}"`;
        createOption.addEventListener('click', function() {
            createNewEntity(query, createUrl, hiddenSelect, input, container, fieldName);
        });
        container.appendChild(createOption);
    } else {
        results.forEach(result => {
            const option = document.createElement('div');
            option.className = 'searchable-result';
            option.textContent = result.name;
            option.addEventListener('click', function() {
                selectEntity(result.id, result.name, hiddenSelect, input, container);
            });
            container.appendChild(option);
        });
    }
    
    container.style.display = 'block';
}

function selectEntity(id, name, hiddenSelect, input, container) {
    hiddenSelect.value = id;
    input.value = name;
    container.style.display = 'none';
}

function createNewEntity(name, createUrl, hiddenSelect, input, container, fieldName) {
    fetch(createUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Erreur: ' + data.error);
            return;
        }
        selectEntity(data.id, data.name, hiddenSelect, input, container);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la création: ' + error.message);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Searchable select with create option
    const searchableSelects = document.querySelectorAll('.searchable-select');
    
    searchableSelects.forEach(select => {
        const input = select.querySelector('.searchable-input');
        const resultsContainer = select.querySelector('.searchable-results');
        const hiddenSelect = select.nextElementSibling;
        const searchUrl = select.getAttribute('data-url');
        const createUrl = select.getAttribute('data-create-url');
        const fieldName = select.getAttribute('data-field');
        
        let debounceTimer;
        
        // Load initial value if already selected
        if (hiddenSelect.value) {
            const selectedOption = hiddenSelect.querySelector(`option[value="${hiddenSelect.value}"]`);
            if (selectedOption) {
                input.value = selectedOption.text;
            }
        }
        
        input.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(debounceTimer);
            
            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            debounceTimer = setTimeout(() => {
                fetch(`${searchUrl}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        displayResults(data.results, query, createUrl, resultsContainer, hiddenSelect, input, fieldName);
                    })
                    .catch(error => console.error('Error:', error));
            }, 300);
        });
        
        input.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                this.dispatchEvent(new Event('input'));
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!select.contains(e.target)) {
                resultsContainer.style.display = 'none';
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
            const index = collectionContainer.children.length;
            const newFormHtml = prototype.replace(/__name__/g, index);
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newFormHtml;
            const newForm = tempDiv.firstElementChild;
            
            newForm.classList.add('collection-item');
            collectionContainer.appendChild(newForm);
            
            // Initialize nested translation collections if they exist
            const subCollections = newForm.querySelectorAll('.sub-collection');
            subCollections.forEach(subCollection => {
                const subPrototype = subCollection.getAttribute('data-prototype');
                if (subPrototype) {
                    const locales = ['fr', 'en', 'es', 'de', 'it'];
                    locales.forEach((locale, localeIndex) => {
                        const subIndex = localeIndex;
                        const subFormHtml = subPrototype.replace(/__name__/g, subIndex);
                        const subTempDiv = document.createElement('div');
                        subTempDiv.innerHTML = subFormHtml;
                        const subForm = subTempDiv.firstElementChild;
                        subForm.classList.add('sub-collection-item');
                        subCollection.appendChild(subForm);
                    });
                }
            });
            
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

    // Auto-generate slug from French title
    const titleFr = document.querySelector('input[name*="[translations]"][name*="[title]"]');
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
            
            let isValid = true;
            let errorMessage = '';
            
            // Find if there are native HTML5 validation errors (like required fields)
            let hasErrors = false;
            let firstInvalidTab = null;
            let firstInvalidInput = null;

            // Clear previous error indicators
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('has-error'));

            // Check required select fields specifically
            if (!categorySelect || !categorySelect.value) {
                isValid = false;
                errorMessage = 'Veuillez sélectionner une catégorie.';
                hasErrors = true;
                firstInvalidTab = 'general';
            }
            
            if (!destinationSelect || !destinationSelect.value) {
                isValid = false;
                errorMessage = errorMessage ? errorMessage + '\nVeuillez sélectionner une destination.' : 'Veuillez sélectionner une destination.';
                hasErrors = true;
                firstInvalidTab = firstInvalidTab || 'general';
            }

            // Check all other form fields for HTML5 validation errors
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    hasErrors = true;
                    // Find the tab containing this input
                    const tabPane = input.closest('.tab-pane');
                    if (tabPane) {
                        const tabId = tabPane.id.replace('tab-', '');
                        // Mark the tab button as having an error
                        const tabBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
                        if (tabBtn) {
                            tabBtn.classList.add('has-error');
                        }
                        
                        if (!firstInvalidTab) {
                            firstInvalidTab = tabId;
                            firstInvalidInput = input;
                        }
                    }
                }
            });

            if (hasErrors || !isValid) {
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
                        // Focus the input if possible
                        if (firstInvalidInput) {
                            setTimeout(() => firstInvalidInput.focus(), 100);
                        }
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
