/**
 * Admin Global JavaScript - Location Mahmoud
 * Comportements communs pour le Back-office
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebarToggle();
    initSidebarAccordion();
    initSubmitGuard();
});

/**
 * Toggle sidebar sur mobile
 */
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebarToggle || !sidebar) return;
    
    // Créer l'overlay s'il n'existe pas
    if (!overlay) {
        const newOverlay = document.createElement('div');
        newOverlay.className = 'sidebar-overlay';
        document.body.appendChild(newOverlay);
    }
    
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    let bodyOverflowBeforeOpen = '';

    function setSidebarOpen(isOpen) {
        const wasOpen = sidebar.classList.contains('open');
        if (wasOpen === isOpen) return;

        if (isOpen) {
            bodyOverflowBeforeOpen = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = bodyOverflowBeforeOpen;
            bodyOverflowBeforeOpen = '';
        }

        sidebar.classList.toggle('open', isOpen);
        sidebarOverlay.classList.toggle('active', isOpen);
        sidebarToggle.setAttribute('aria-expanded', String(isOpen));
        sidebarToggle.setAttribute('aria-label', isOpen ? 'Fermer le menu de navigation' : 'Ouvrir le menu de navigation');
    }

    sidebarToggle.addEventListener('click', function() {
        setSidebarOpen(!sidebar.classList.contains('open'));
    });

    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            setSidebarOpen(false);
            sidebarToggle.focus();
        });
    }
    
    // Fermer la sidebar en cliquant sur l'overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            setSidebarOpen(false);
        });
    }

    sidebar.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            setSidebarOpen(false);
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && sidebar.classList.contains('open')) {
            setSidebarOpen(false);
            sidebarToggle.focus();
        }
    });
    
    // Fermer la sidebar lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            setSidebarOpen(false);
        }
    });
}

/**
 * Toggle the active sidebar section and keep the accordion mutually exclusive.
 */
function initSidebarAccordion() {
    const toggles = document.querySelectorAll('.menu-group-toggle');

    toggles.forEach(toggle => {
        const submenu = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!submenu) return;

        submenu.inert = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.addEventListener('click', function() {
            const wasExpanded = toggle.getAttribute('aria-expanded') === 'true';

            toggles.forEach(otherToggle => {
                const otherSubmenu = document.getElementById(otherToggle.getAttribute('aria-controls'));
                if (!otherSubmenu) return;

                const isExpanded = otherToggle === toggle ? !wasExpanded : false;

                otherToggle.setAttribute('aria-expanded', String(isExpanded));
                otherSubmenu.classList.toggle('is-open', isExpanded);
                otherSubmenu.inert = !isExpanded;
            });
        });
    });
}

/**
 * Prevent accidental repeated submissions while a form is being sent.
 */
function initSubmitGuard() {
    const forms = document.querySelectorAll('form[data-disable-submit-once]');

    forms.forEach(form => {
        form.addEventListener('submit', function() {
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(button => {
                button.dataset.originalLabel = button.textContent;
                button.disabled = true;
                if (button instanceof HTMLButtonElement) {
                    button.textContent = 'Envoi en cours…';
                }
            });
        });
    });

    window.addEventListener('pageshow', function() {
        forms.forEach(form => {
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(button => {
                button.disabled = false;
                if (button instanceof HTMLButtonElement && button.dataset.originalLabel) {
                    button.textContent = button.dataset.originalLabel;
                }
            });
        });
    });
}

/**
 * Fonction utilitaire pour confirmer une action
 */
function confirmAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}

/**
 * Fonction utilitaire pour afficher une notification (à étendre)
 */
function showNotification(message, type = 'success') {
    // Placeholder pour un futur système de notifications
    console.log(`[${type.toUpperCase()}] ${message}`);
}
