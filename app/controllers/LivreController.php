<?php
/** CRUD des livres. */
class LivreController extends Controller
{
    public function index($id = null): void
    {
        $this->view('livre/index', [
            'titre'  => 'Gestion des livres',
            'livres' => $this->model('Livre')->allSorted(),
        ]);
    }

    public function form($id = null): void
    {
        $model = $this->model('Livre');
        $this->view('livre/form', [
            'titre' => $id ? 'Modifier un livre' : 'Nouveau livre',
            'livre' => $id ? $model->find($id) : null,
        ]);
    }

    public function save($id = null): void
    {
        $total = max(0, (int) ($_POST['quantite_totale'] ?? 1));
        $dispo = ($_POST['quantite_disponible'] ?? '') !== ''
            ? (int) $_POST['quantite_disponible'] : $total;
        $dispo = min($dispo, $total); // la dispo ne peut dépasser le stock total

        $data = [
            'titre'               => trim($_POST['titre'] ?? ''),
            'auteur'              => trim($_POST['auteur'] ?? ''),
            'isbn'                => trim($_POST['isbn'] ?? ''),
            'editeur'             => trim($_POST['editeur'] ?? ''),
            'annee_publication'   => ($_POST['annee_publication'] ?? '') !== '' ? (int) $_POST['annee_publication'] : null,
            'genre'               => trim($_POST['genre'] ?? ''),
            'quantite_totale'     => $total,
            'quantite_disponible' => $dispo,
        ];

        if ($data['titre'] === '' || $data['auteur'] === '') {
            $this->flash('Le titre et l\'auteur sont obligatoires.', 'danger');
            $this->redirect('livre', 'form');
        }

        $model = $this->model('Livre');
        try {
            $id ? $model->update($id, $data) : $model->create($data);
            $this->flash($id ? 'Livre mis à jour.' : 'Livre ajouté.');
        } catch (PDOException $e) {
            $this->flash('Erreur : ' . $e->getMessage(), 'danger');
        }
        $this->redirect('livre');
    }

    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Livre')->delete($id);
            $this->flash('Livre supprimé.');
        }
        $this->redirect('livre');
    }
}
