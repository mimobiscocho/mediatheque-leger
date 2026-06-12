<?php
/**
 * Gestion des matériels empruntables (ordinateurs, liseuses, casques...) :
 * liste avec filtre multicritères, création, modification, suppression.
 */
class MaterielController extends Controller
{
    /** Liste des matériels, éventuellement filtrée. */
    public function index($id = null): void
    {
        $model = $this->model('Materiel');

        // Critères de recherche lus dans l'URL (formulaire envoyé en GET)
        $filtres = [
            'q'         => trim($_GET['q'] ?? ''),
            'categorie' => trim($_GET['categorie'] ?? ''),
            'etat'      => trim($_GET['etat'] ?? ''),
            'dispo'     => $_GET['dispo'] ?? '',
        ];

        $this->view('materiel/index', [
            'titre'      => 'Gestion des matériels',
            'materiels'  => $model->filter($filtres),
            'categories' => $model->distinctCategories(),
            'filtres'    => $filtres,
        ]);
    }

    /** Formulaire : création (sans id) ou modification (avec id). */
    public function form($id = null): void
    {
        $model = $this->model('Materiel');
        $this->view('materiel/form', [
            'titre'    => $id ? 'Modifier un matériel' : 'Nouveau matériel',
            'materiel' => $id ? $model->find($id) : null,
        ]);
    }

    /** Enregistre le formulaire (création ou mise à jour selon l'id). */
    public function save($id = null): void
    {
        // L'état doit faire partie de la liste autorisée (celle du ENUM
        // en base). Toute valeur inattendue retombe sur 'bon'.
        $etats = ['neuf', 'bon', 'use', 'hors_service'];
        $etat  = in_array($_POST['etat'] ?? '', $etats, true) ? $_POST['etat'] : 'bon';

        $data = [
            'nom'         => trim($_POST['nom']         ?? ''),
            'categorie'   => trim($_POST['categorie']   ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'etat'        => $etat,
            'disponible'  => isset($_POST['disponible']) ? 1 : 0,
        ];

        if ($data['nom'] === '') {
            $this->flash('Le nom du matériel est obligatoire.', 'danger');
            $this->redirect('materiel', 'form');
        }

        $model = $this->model('Materiel');
        try {
            $id ? $model->update($id, $data) : $model->create($data);
            $this->flash($id ? 'Matériel mis à jour.' : 'Matériel ajouté.');
        } catch (PDOException $e) {
            error_log('[Mediatheque] Materiel save: ' . $e->getMessage());
            $this->flash('Enregistrement impossible : la donnée saisie est invalide.', 'danger');
        }
        $this->redirect('materiel');
    }

    /** Supprime un matériel. */
    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Materiel')->delete($id);
            $this->flash('Matériel supprimé.');
        }
        $this->redirect('materiel');
    }
}
