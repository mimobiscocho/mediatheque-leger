<?php
/**
 * Gestion des livres : liste avec filtre multicritères,
 * création, modification, suppression.
 */
class LivreController extends Controller
{
    /** Liste des livres, éventuellement filtrée. */
    public function index($id = null): void
    {
        $model = $this->model('Livre');

        // Critères de recherche lus dans l'URL (formulaire envoyé en GET).
        // S'ils sont vides, le modèle renverra tous les livres.
        $filtres = [
            'q'     => trim($_GET['q'] ?? ''),
            'genre' => trim($_GET['genre'] ?? ''),
            'dispo' => $_GET['dispo'] ?? '',
        ];

        $this->view('livre/index', [
            'titre'   => 'Gestion des livres',
            'livres'  => $model->filter($filtres),
            'genres'  => $model->distinctGenres(),
            'filtres' => $filtres,
        ]);
    }

    /** Formulaire : création (sans id) ou modification (avec id). */
    public function form($id = null): void
    {
        $model = $this->model('Livre');
        $this->view('livre/form', [
            'titre' => $id ? 'Modifier un livre' : 'Nouveau livre',
            'livre' => $id ? $model->find($id) : null,
        ]);
    }

    /** Enregistre le formulaire (création ou mise à jour selon l'id). */
    public function save($id = null): void
    {
        // Gestion des quantités : un livre peut exister en plusieurs
        // exemplaires. Si la quantité disponible n'est pas renseignée,
        // on considère que tous les exemplaires sont disponibles.
        $total = max(0, (int) ($_POST['quantite_totale'] ?? 1));
        $dispo = ($_POST['quantite_disponible'] ?? '') !== ''
            ? (int) $_POST['quantite_disponible'] : $total;
        // La disponibilité reste entre 0 et le total (pas de valeur absurde)
        $dispo = max(0, min($dispo, $total));

        $data = [
            'titre'               => trim($_POST['titre']             ?? ''),
            'auteur'              => trim($_POST['auteur']            ?? ''),
            'isbn'                => trim($_POST['isbn']              ?? ''),
            'editeur'             => trim($_POST['editeur']           ?? ''),
            'annee_publication'   => ($_POST['annee_publication']     ?? '') !== '' ? (int) $_POST['annee_publication'] : null,
            'genre'               => trim($_POST['genre']             ?? ''),
            'quantite_totale'     => $total,
            'quantite_disponible' => $dispo,
        ];

        // Contrôle de saisie côté serveur : champs obligatoires
        if ($data['titre'] === '' || $data['auteur'] === '') {
            $this->flash('Le titre et l\'auteur sont obligatoires.', 'danger');
            $this->redirect('livre', 'form');
        }

        $model = $this->model('Livre');
        try {
            $id ? $model->update($id, $data) : $model->create($data);
            $this->flash($id ? 'Livre mis à jour.' : 'Livre ajouté.');
        } catch (PDOException $e) {
            error_log('[Mediatheque] Livre save: ' . $e->getMessage());
            $this->flash('Enregistrement impossible : la donnée saisie est invalide.', 'danger');
        }
        $this->redirect('livre');
    }

    /** Supprime un livre. */
    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Livre')->delete($id);
            $this->flash('Livre supprimé.');
        }
        $this->redirect('livre');
    }
}
