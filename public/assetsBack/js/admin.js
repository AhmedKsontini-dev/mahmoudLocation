/**
 * Admin Global JavaScript - Location Mahmoud
 * Comportements communs pour le Back-office
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebarToggle();
    initActiveMenuHighlight();
});

/**
 * Toggle sidebar sur mobile
 */
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
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
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
    });
    
    // Fermer la sidebar en cliquant sur l'overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Fermer la sidebar lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('active');
            }
            document.body.style.overflow = '';
        }
    });
}

/**
 * Surligner le menu actif basé sur l'URL courante
 */
function initActiveMenuHighlight() {
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.menu-link');
    
    menuLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        
        // Correspondance exacte ou partielle
        if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
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
