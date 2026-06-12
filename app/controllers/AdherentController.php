<?php
/**
 * Gestion des adhérents (les membres inscrits à la médiathèque) :
 * liste, création, modification, suppression.
 */
class AdherentController extends Controller
{
    /** Liste de tous les adhérents. */
    public function index($id = null): void
    {
        $this->view('adherent/index', [
            'titre'     => 'Gestion des adhérents',
            'adherents' => $this->model('Adherent')->allWithAbonnement(),
        ]);
    }

    /** Formulaire : création (sans id) ou modification (avec id). */
    public function form($id = null): void
    {
        $model = $this->model('Adherent');
        $this->view('adherent/form', [
            'titre'       => $id ? 'Modifier un adhérent' : 'Nouvel adhérent',
            'adherent'    => $id ? $model->find($id) : null,
            'abonnements' => $this->model('Abonnement')->allSorted(),
        ]);
    }

    /** Enregistre le formulaire (création ou mise à jour selon l'id). */
    public function save($id = null): void
    {
        // Récupération des champs du formulaire.
        // - trim() enlève les espaces parasites en début/fin
        // - abonnement_id et date_fin peuvent rester vides -> NULL en base
        // - la case à cocher "actif" n'est envoyée que si elle est cochée
        $data = [
            'nom'                 => trim($_POST['nom']                 ?? ''),
            'prenom'              => trim($_POST['prenom']              ?? ''),
            'email'               => trim($_POST['email']               ?? ''),
            'telephone'           => trim($_POST['telephone']           ?? ''),
            'adresse'             => trim($_POST['adresse']             ?? ''),
            'abonnement_id'       => ($_POST['abonnement_id']           ?? '') !== '' ? (int) $_POST['abonnement_id'] : null,
            'date_inscription'    => ($_POST['date_inscription']        ?? '') ?: date('Y-m-d'),
            'date_fin_abonnement' => ($_POST['date_fin_abonnement']     ?? '') ?: null,
            'actif'               => isset($_POST['actif']) ? 1 : 0,
        ];

        // Contrôles de saisie côté serveur (en plus du "required" HTML,
        // qui peut être contourné) : champs obligatoires puis format email.
        if ($data['nom'] === '' || $data['prenom'] === '' || $data['email'] === '') {
            $this->flash('Nom, prénom et email sont obligatoires.', 'danger');
            $this->redirect('adherent', 'form');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('Adresse email invalide.', 'danger');
            $this->redirect('adherent', 'form');
        }

        $model = $this->model('Adherent');
        try {
            // Avec un id on modifie l'adhérent existant, sinon on le crée
            $id ? $model->update($id, $data) : $model->create($data);
            $this->flash($id ? 'Adhérent mis à jour.' : 'Adhérent ajouté.');
        } catch (PDOException $e) {
            // Le détail technique part dans le journal du serveur ;
            // l'utilisateur ne voit qu'un message compréhensible.
            error_log('[Mediatheque] Adherent save: ' . $e->getMessage());
            // Code SQL 23000 = violation de contrainte (ici l'email UNIQUE)
            $msg = $e->getCode() === '23000'
                ? 'Cet email est déjà utilisé par un autre adhérent.'
                : 'Enregistrement impossible : la donnée saisie est invalide.';
            $this->flash($msg, 'danger');
        }
        $this->redirect('adherent');
    }

    /** Supprime un adhérent. */
    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Adherent')->delete($id);
            $this->flash('Adhérent supprimé.');
        }
        $this->redirect('adherent');
    }
}
