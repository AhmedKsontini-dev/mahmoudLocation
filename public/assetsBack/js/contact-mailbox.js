/**
 * Mailbox Contacts - Location Mahmoud
 * Messagerie admin : recherche, sélection instantanée, impression, pièce jointe.
 */

document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('mailboxSearch');

    /**
     * Filtre la liste des messages selon la recherche saisie.
     */
    function applySearch() {
        if (!searchInput) {
            return;
        }

        var query = searchInput.value.trim().toLowerCase();
        var items = document.querySelectorAll('#mailboxItems .mailbox-item');
        var visibleCount = 0;

        items.forEach(function (item) {
            var haystack = (item.dataset.search || '').toLowerCase();
            var matches = query === '' || haystack.indexOf(query) !== -1;
            item.hidden = !matches;

            if (matches) {
                visibleCount++;
            }
        });

        var emptyState = document.getElementById('mailboxNoResults');

        if (emptyState) {
            emptyState.hidden = !(query !== '' && visibleCount === 0);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applySearch);
    }

    /**
     * Change le message affiché sans recharger la page (progressive enhancement).
     * Sans JavaScript, les liens de la liste fonctionnent normalement.
     */
    async function loadSelection(url, push) {
        try {
            var response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

            if (!response.ok) {
                window.location.href = url;
                return;
            }

            var html = await response.text();
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var newItems = doc.getElementById('mailboxItems');
            var newReader = doc.getElementById('mailboxReader');
            var items = document.getElementById('mailboxItems');
            var reader = document.getElementById('mailboxReader');

            if (!newItems || !newReader || !items || !reader) {
                window.location.href = url;
                return;
            }

            items.replaceWith(newItems);
            reader.replaceWith(newReader);

            if (push) {
                history.pushState({ mailbox: true }, '', url);
            }

            applySearch();
        } catch (error) {
            window.location.href = url;
        }
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.mailbox-item');

        if (!link || event.defaultPrevented || event.button !== 0) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        loadSelection(link.href, true);
    });

    window.addEventListener('popstate', function () {
        loadSelection(window.location.href, false);
    });

    /**
     * Focus du champ de réponse après le clic sur "Répondre".
     */
    document.addEventListener('click', function (event) {
        if (!event.target.closest('#mailboxReplyBtn')) {
            return;
        }

        var textarea = document.getElementById('mailboxReplyText');

        if (textarea) {
            window.setTimeout(function () {
                textarea.focus();
            }, 50);
        }
    });

    /**
     * Impression du message affiché.
     */
    document.addEventListener('click', function (event) {
        if (event.target.closest('#mailboxPrintBtn')) {
            window.print();
        }
    });

    /**
     * Affiche le nom du fichier joint sélectionné.
     */
    document.addEventListener('change', function (event) {
        var input = event.target;

        if (!input || input.id !== 'mailboxAttachment') {
            return;
        }

        var form = input.closest('form');
        var nameElement = form ? form.querySelector('.mailbox-attach-name') : null;

        if (nameElement) {
            nameElement.textContent = input.files && input.files.length ? '📎 ' + input.files[0].name : '';
        }
    });
});
