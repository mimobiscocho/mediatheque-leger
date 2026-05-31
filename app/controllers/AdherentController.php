<?php
/** CRUD des adhérents. */
class AdherentController extends Controller
{
    public function index($id = null): void
    {
        $this->view('adherent/index', [
            'titre'     => 'Gestion des adhérents',
            'adherents' => $this->model('Adherent')->allWithAbonnement(),
        ]);
    }

    public function form($id = null): void
    {
        $model = $this->model('Adherent');
        $this->view('adherent/form', [
            'titre'       => $id ? 'Modifier un adhérent' : 'Nouvel adhérent',
            'adherent'    => $id ? $model->find($id) : null,
            'abonnements' => $this->model('Abonnement')->allSorted(),
        ]);
    }

    public function save($id = null): void
    {
        $data = [
            'nom'                 => trim($_POST['nom'] ?? ''),
            'prenom'              => trim($_POST['prenom'] ?? ''),
            'email'               => trim($_POST['email'] ?? ''),
            'telephone'           => trim($_POST['telephone'] ?? ''),
            'adresse'             => trim($_POST['adresse'] ?? ''),
            'abonnement_id'       => ($_POST['abonnement_id'] ?? '') !== '' ? (int) $_POST['abonnement_id'] : null,
            'date_inscription'    => $_POST['date_inscription'] ?: date('Y-m-d'),
            'date_fin_abonnement' => $_POST['date_fin_abonnement'] ?: null,
            'actif'               => isset($_POST['actif']) ? 1 : 0,
        ];

        if ($data['nom'] === '' || $data['prenom'] === '' || $data['email'] === '') {
            $this->flash('Nom, prénom et email sont obligatoires.', 'danger');
            $this->redirect('adherent', 'form');
        }

        $model = $this->model('Adherent');
        try {
            $id ? $model->update($id, $data) : $model->create($data);
            $this->flash($id ? 'Adhérent mis à jour.' : 'Adhérent ajouté.');
        } catch (PDOException $e) {
            $this->flash('Erreur : ' . $e->getMessage(), 'danger');
        }
        $this->redirect('adherent');
    }

    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Adherent')->delete($id);
            $this->flash('Adhérent supprimé.');
        }
        $this->redirect('adherent');
    }
}
