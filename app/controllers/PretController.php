<?php
/**
 * Système de prêts : un prêt relie un adhérent à UN produit
 * (un livre OU un matériel), avec une date de retour prévue.
 * Les disponibilités sont mises à jour par des triggers MySQL.
 */
class PretController extends Controller
{
    /** Liste de tous les prêts (en cours, en retard, rendus). */
    public function index($id = null): void
    {
        $this->view('pret/index', [
            'titre' => 'Système de prêts',
            'prets' => $this->model('Pret')->allDetailed(),
        ]);
    }

    /** Formulaire de nouveau prêt : seuls les produits disponibles sont proposés. */
    public function form($id = null): void
    {
        $this->view('pret/form', [
            'titre'     => 'Nouveau prêt',
            'adherents' => $this->model('Adherent')->allWithAbonnement(),
            'livres'    => $this->model('Livre')->disponibles(),
            'materiels' => $this->model('Materiel')->disponibles(),
        ]);
    }

    /** Enregistre un nouveau prêt. */
    public function save($id = null): void
    {
        // Le formulaire envoie le produit sous la forme "livre:3" ou
        // "materiel:5" : on regarde le préfixe pour savoir si c'est un
        // livre ou un matériel, puis on récupère l'id après les ":".
        $produit    = $_POST['produit'] ?? '';
        $livreId    = null;
        $materielId = null;
        if (str_starts_with($produit, 'livre:')) {
            $livreId = (int) substr($produit, strlen('livre:'));
        } elseif (str_starts_with($produit, 'materiel:')) {
            $materielId = (int) substr($produit, strlen('materiel:'));
        }

        // Il faut au minimum un adhérent et un produit valides
        $adherentId = (int) ($_POST['adherent_id'] ?? 0);
        if (!$adherentId || (!$livreId && !$materielId)) {
            $this->flash('Veuillez sélectionner un adhérent et un produit à emprunter.', 'danger');
            $this->redirect('pret', 'form');
        }

        // Dates par défaut si non renseignées : prêt aujourd'hui,
        // retour attendu dans 14 jours.
        $data = [
            'adherent_id'        => $adherentId,
            'livre_id'           => $livreId,
            'materiel_id'        => $materielId,
            'date_pret'          => ($_POST['date_pret']          ?? '') ?: date('Y-m-d'),
            'date_retour_prevue' => ($_POST['date_retour_prevue'] ?? '') ?: date('Y-m-d', strtotime('+14 days')),
        ];

        try {
            $this->model('Pret')->create($data);
            $this->flash('Prêt enregistré : la disponibilité du produit a été mise à jour.');
        } catch (PDOException $e) {
            error_log('[Mediatheque] Pret save: ' . $e->getMessage());
            // Si le refus vient d'un trigger métier (code SQL 45000,
            // ex : produit indisponible), son message est affichable tel quel.
            $msg = $e->getCode() === '45000' ? $e->getMessage() : 'Le prêt n\'a pas pu être enregistré.';
            $this->flash('Prêt refusé : ' . $msg, 'danger');
        }
        $this->redirect('pret');
    }

    /** Enregistre le retour d'un prêt (le stock est restauré par trigger). */
    public function retour($id = null): void
    {
        if ($id) {
            $this->model('Pret')->retour($id);
            $this->flash('Retour enregistré : le produit est de nouveau disponible.');
        }
        $this->redirect('pret');
    }

    /** Supprime un prêt de l'historique. */
    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Pret')->delete($id);
            $this->flash('Prêt supprimé.');
        }
        $this->redirect('pret');
    }
}
