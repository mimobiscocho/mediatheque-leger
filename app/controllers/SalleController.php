<?php
/** CRUD des salles de coworking. */
class SalleController extends Controller
{
    public function index($id = null): void
    {
        $this->view('salle/index', [
            'titre'  => 'Gestion des salles',
            'salles' => $this->model('Salle')->allSorted(),
        ]);
    }

    public function form($id = null): void
    {
        $model = $this->model('Salle');
        $this->view('salle/form', [
            'titre' => $id ? 'Modifier une salle' : 'Nouvelle salle',
            'salle' => $id ? $model->find($id) : null,
        ]);
    }

    public function save($id = null): void
    {
        $data = [
            'nom'         => trim($_POST['nom'] ?? ''),
            'capacite'    => max(1, (int) ($_POST['capacite'] ?? 1)),
            'equipements' => trim($_POST['equipements'] ?? ''),
            'disponible'  => isset($_POST['disponible']) ? 1 : 0,
        ];

        if ($data['nom'] === '') {
            $this->flash('Le nom de la salle est obligatoire.', 'danger');
            $this->redirect('salle', 'form');
        }

        $model = $this->model('Salle');
        try {
            $id ? $model->update($id, $data) : $model->create($data);
            $this->flash($id ? 'Salle mise à jour.' : 'Salle ajoutée.');
        } catch (PDOException $e) {
            $this->flash('Erreur : ' . $e->getMessage(), 'danger');
        }
        $this->redirect('salle');
    }

    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Salle')->delete($id);
            $this->flash('Salle supprimée.');
        }
        $this->redirect('salle');
    }
}
