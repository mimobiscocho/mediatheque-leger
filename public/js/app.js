/* =====================================================================
   Interactions côté client — Médiathèque de Bourg-la-Reine
   ===================================================================== */
document.addEventListener('DOMContentLoaded', function () {

    // Confirmation avant toute suppression / annulation
    document.querySelectorAll('[data-confirm]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (!window.confirm(link.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Masquage automatique des messages flash après 4 secondes
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 4000);
    });

    // Filtre de recherche instantané sur les tableaux (champ [data-filter])
    document.querySelectorAll('[data-filter]').forEach(function (input) {
        const table = document.querySelector(input.dataset.filter);
        if (!table) return;
        input.addEventListener('input', function () {
            const q = input.value.toLowerCase().trim();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });
});
