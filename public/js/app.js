/*
 * app.js — petites interactions côté navigateur.
 * Tout le code attend que la page soit chargée (DOMContentLoaded)
 * avant de chercher les éléments dans le HTML.
 */
document.addEventListener('DOMContentLoaded', function () {

    /*
     * 1) Confirmation avant les actions sensibles.
     * Tout bouton ou lien portant un attribut data-confirm="..." ouvre
     * d'abord une boîte de dialogue. Si l'agent clique sur "Annuler",
     * on bloque le clic (donc la suppression / le retour n'a pas lieu).
     */
    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            if (!window.confirm(element.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    /*
     * 2) Fermeture automatique des messages flash ("Adhérent ajouté." etc.)
     * au bout de 4 secondes, pour ne pas encombrer l'écran.
     * On passe par l'API Alert de Bootstrap pour garder l'animation.
     */
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 4000);
    });

    /*
     * 3) Recherche instantanée dans les tableaux.
     * Un champ <input data-filter="#idDuTableau"> filtre les lignes au fur
     * et à mesure de la frappe : on cache simplement les lignes dont le
     * texte ne contient pas ce qui est tapé (aucun appel au serveur).
     */
    document.querySelectorAll('[data-filter]').forEach(function (input) {
        var table = document.querySelector(input.dataset.filter);
        if (!table) {
            return; // le tableau visé n'existe pas sur cette page
        }
        input.addEventListener('input', function () {
            var recherche = input.value.toLowerCase().trim();
            table.querySelectorAll('tbody tr').forEach(function (ligne) {
                var visible = ligne.textContent.toLowerCase().includes(recherche);
                ligne.style.display = visible ? '' : 'none';
            });
        });
    });
});
