<?php
/**
 * Gestion des salles de coworking :
 * liste, création, modification, suppression.
 */
class SalleController extends Controller
{
    /** Liste des salles. */
    public function index($id = null): void
    {
        $this->view('salle/index', [
            'titre'  => 'Gestion des salles',
            'salles' => $this->model('Salle')->allSorted(),
        ]);
    }

    /** Formulaire : création (sans id) ou modification (avec id). */
    public function form($id = null): void
    {
        $model = $this->model('Salle');
        $this->view('salle/form', [
            'titre' => $id ? 'Modifier une salle' : 'Nouvelle salle',
            'salle' => $id ? $model->find($id) : null,
        ]);
    }

    /** Enregistre le formulaire (création ou mise à jour selon l'id). */
    public function save($id = null): void
    {
        // Récupération des champs : la capacité est au minimum de 1
        // personne, la case "disponible" vaut 1 si cochée, 0 sinon.
        $data = [
            'nom'        => trim($_POST['nom']        ?? ''),
            'capacite'   => max(1, (int) ($_POST['capacite'] ?? 1)),
            'equipement' => trim($_POST['equipement'] ?? ''),
            'disponible' => isset($_POST['disponible']) ? 1 : 0,
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
            error_log('[Mediatheque] Salle save: ' . $e->getMessage());
            $this->flash('Enregistrement impossible : la donnée saisie est invalide.', 'danger');
        }
        $this->redirect('salle');
    }

    /** Supprime une salle. */
    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Salle')->delete($id);
            $this->flash('Salle supprimée.');
        }
        $this->redirect('salle');
    }
}
