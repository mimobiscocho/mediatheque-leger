<?php
/** Système de prêts : enregistrement et retour des emprunts. */
class PretController extends Controller
{
    public function index($id = null): void
    {
        $this->view('pret/index', [
            'titre' => 'Système de prêts',
            'prets' => $this->model('Pret')->allDetailed(),
        ]);
    }

    public function form($id = null): void
    {
        $this->view('pret/form', [
            'titre'     => 'Nouveau prêt',
            'adherents' => $this->model('Adherent')->allWithAbonnement(),
            'livres'    => $this->model('Livre')->disponibles(),
            'materiels' => $this->model('Materiel')->disponibles(),
        ]);
    }

    public function save($id = null): void
    {
        // Le produit est transmis sous la forme "livre:3" ou "materiel:5"
        $produit    = $_POST['produit'] ?? '';
        $livreId    = null;
        $materielId = null;
        if (strncmp($produit, 'livre:', 6) === 0) {
            $livreId = (int) substr($produit, 6);
        } elseif (strncmp($produit, 'materiel:', 9) === 0) {
            $materielId = (int) substr($produit, 9);
        }

        $adherentId = (int) ($_POST['adherent_id'] ?? 0);
        if (!$adherentId || (!$livreId && !$materielId)) {
            $this->flash('Veuillez sélectionner un adhérent et un produit à emprunter.', 'danger');
            $this->redirect('pret', 'form');
        }

        $data = [
            'adherent_id'        => $adherentId,
            'livre_id'           => $livreId,
            'materiel_id'        => $materielId,
            'date_pret'          => $_POST['date_pret'] ?: date('Y-m-d'),
            'date_retour_prevue' => $_POST['date_retour_prevue'] ?: date('Y-m-d', strtotime('+14 days')),
        ];

        try {
            $this->model('Pret')->create($data);
            $this->flash('Prêt enregistré : la disponibilité du produit a été mise à jour.');
        } catch (PDOException $e) {
            // Message remonté par le trigger trg_pret_before_insert
            $this->flash('Prêt refusé : ' . $e->getMessage(), 'danger');
        }
        $this->redirect('pret');
    }

    /** Enregistre le retour d'un prêt. */
    public function retour($id = null): void
    {
        if ($id) {
            $this->model('Pret')->retour($id);
            $this->flash('Retour enregistré : le produit est de nouveau disponible.');
        }
        $this->redirect('pret');
    }

    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Pret')->delete($id);
            $this->flash('Prêt supprimé.');
        }
        $this->redirect('pret');
    }
}
