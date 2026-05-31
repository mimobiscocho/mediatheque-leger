<?php
/** CRUD des matériels. */
class MaterielController extends Controller
{
    public function index($id = null): void
    {
        $model   = $this->model('Materiel');
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

    public function form($id = null): void
    {
        $model = $this->model('Materiel');
        $this->view('materiel/form', [
            'titre'    => $id ? 'Modifier un matériel' : 'Nouveau matériel',
            'materiel' => $id ? $model->find($id) : null,
        ]);
    }

    public function save($id = null): void
    {
        $etats = ['neuf', 'bon', 'use', 'hors_service'];
        $etat  = in_array($_POST['etat'] ?? '', $etats, true) ? $_POST['etat'] : 'bon';

        $data = [
            'nom'         => trim($_POST['nom'] ?? ''),
            'categorie'   => trim($_POST['categorie'] ?? ''),
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
            $this->flash('Erreur : ' . $e->getMessage(), 'danger');
        }
        $this->redirect('materiel');
    }

    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Materiel')->delete($id);
            $this->flash('Matériel supprimé.');
        }
        $this->redirect('materiel');
    }
}
