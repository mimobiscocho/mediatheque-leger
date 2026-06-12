<?php
/**
 * Espace client (adhérent) : inscription en ligne, connexion à un compte
 * personnel, tableau de bord et réservation de salle.
 *
 * Différent du contrôleur "auth" (qui gère les agents de la médiathèque).
 * La session client est stockée dans $_SESSION['client'].
 */
class ClientController extends Controller
{
    /**
     * Page d'accueil de l'espace client : si déjà connecté, on file
     * directement au tableau de bord ; sinon on affiche la connexion.
     */
    public function index($id = null): void
    {
        if (!empty($_SESSION['client'])) {
            $this->redirect('client', 'dashboard');
        }
        $this->redirect('client', 'login');
    }

    /* =================================================================
     *  CONNEXION
     * ================================================================= */

    /** Formulaire de connexion client. */
    public function login($id = null): void
    {
        if (!empty($_SESSION['client'])) {
            $this->redirect('client', 'dashboard');
        }
        $this->view('client/login', ['titre' => 'Espace adhérent'], false);
    }

    /** Vérifie les identifiants saisis et ouvre la session client. */
    public function authenticate($id = null): void
    {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['mot_de_passe'] ?? '';

        $adh = $this->model('Adherent')->findByEmail($email);

        // Vérifications : compte actif, mot de passe défini, et empreinte
        // bcrypt correspondante. password_verify évite les comparaisons
        // sensibles au timing.
        if ($adh
            && (int) $adh['actif'] === 1
            && !empty($adh['mot_de_passe'])
            && password_verify($pass, $adh['mot_de_passe'])
        ) {
            // Régénère l'ID de session pour prévenir la fixation de session.
            session_regenerate_id(true);
            $_SESSION['client'] = [
                'id'     => (int) $adh['id'],
                'nom'    => $adh['nom'],
                'prenom' => $adh['prenom'],
                'email'  => $adh['email'],
            ];
            $this->flash('Bienvenue, ' . $adh['prenom'] . ' !');
            $this->redirect('client', 'dashboard');
        }

        $this->flash('Identifiants incorrects ou compte inactif.', 'danger');
        $this->redirect('client', 'login');
    }

    /** Ferme la session client. */
    public function logout($id = null): void
    {
        unset($_SESSION['client']);
        session_regenerate_id(true);
        $this->flash('Vous avez été déconnecté.');
        $this->redirect('client', 'login');
    }

    /* =================================================================
     *  INSCRIPTION
     * ================================================================= */

    /** Formulaire d'inscription. */
    public function register($id = null): void
    {
        if (!empty($_SESSION['client'])) {
            $this->redirect('client', 'dashboard');
        }
        $this->view('client/register', ['titre' => 'Créer un compte'], false);
    }

    /** Crée le compte client (ou active un compte existant sans mdp). */
    public function create($id = null): void
    {
        $nom       = trim($_POST['nom']       ?? '');
        $prenom    = trim($_POST['prenom']    ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse   = trim($_POST['adresse']   ?? '');
        $mdp       = $_POST['mot_de_passe']         ?? '';
        $mdp2      = $_POST['mot_de_passe_confirm'] ?? '';

        // Contrôles : champs obligatoires, format de l'email, longueur du mdp
        if ($nom === '' || $prenom === '' || $email === '' || $mdp === '') {
            $this->flash('Nom, prénom, email et mot de passe sont obligatoires.', 'danger');
            $this->redirect('client', 'register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('Adresse email invalide.', 'danger');
            $this->redirect('client', 'register');
        }
        if (strlen($mdp) < 8) {
            $this->flash('Le mot de passe doit comporter au moins 8 caractères.', 'danger');
            $this->redirect('client', 'register');
        }
        if ($mdp !== $mdp2) {
            $this->flash('Les deux mots de passe ne correspondent pas.', 'danger');
            $this->redirect('client', 'register');
        }

        $model = $this->model('Adherent');
        $existant = $model->findByEmail($email);

        try {
            if ($existant) {
                // Email déjà connu : si l'adhérent existe mais n'a pas de
                // compte web, on l'active. Sinon, on refuse (compte déjà ouvert).
                if (!empty($existant['mot_de_passe'])) {
                    $this->flash('Un compte existe déjà avec cet email. Connectez-vous.', 'danger');
                    $this->redirect('client', 'login');
                }
                $model->setPassword((int) $existant['id'], $mdp);
                $newId = (int) $existant['id'];
            } else {
                $newId = $model->createAccount([
                    'nom'          => $nom,
                    'prenom'       => $prenom,
                    'email'        => $email,
                    'telephone'    => $telephone,
                    'adresse'      => $adresse,
                    'mot_de_passe' => $mdp,
                ]);
            }
        } catch (PDOException $e) {
            error_log('[Mediatheque] Client register: ' . $e->getMessage());
            $msg = $e->getCode() === '23000'
                ? 'Cet email est déjà utilisé.'
                : 'Inscription impossible : données invalides.';
            $this->flash($msg, 'danger');
            $this->redirect('client', 'register');
        }

        // Connexion automatique après inscription
        session_regenerate_id(true);
        $_SESSION['client'] = [
            'id'     => $newId,
            'nom'    => $nom,
            'prenom' => $prenom,
            'email'  => $email,
        ];
        $this->flash('Compte créé. Bienvenue ' . $prenom . ' !');
        $this->redirect('client', 'dashboard');
    }

    /* =================================================================
     *  ESPACE PERSONNEL (zone protégée)
     * ================================================================= */

    /** Tableau de bord du client : ses prochaines réservations. */
    public function dashboard($id = null): void
    {
        $this->requireClient();
        $cid = (int) $_SESSION['client']['id'];

        $this->view('client/dashboard', [
            'titre'        => 'Mon espace',
            'reservations' => $this->model('Reservation')->byAdherent($cid),
        ], false);
    }

    /** Formulaire de réservation pour le client connecté. */
    public function reserver($id = null): void
    {
        $this->requireClient();
        $this->view('client/reserver', [
            'titre'  => 'Réserver une salle',
            'salles' => $this->model('Salle')->allSorted(),
        ], false);
    }

    /** Enregistre une réservation au nom du client connecté. */
    public function saveReservation($id = null): void
    {
        $this->requireClient();
        $cid = (int) $_SESSION['client']['id'];

        $salleId = (int) ($_POST['salle_id'] ?? 0);
        if (!$salleId) {
            $this->flash('Veuillez sélectionner une salle.', 'danger');
            $this->redirect('client', 'reserver');
        }

        $data = [
            // L'adhérent n'est PAS pris du formulaire : on force la session,
            // pour empêcher un client de réserver à la place d'un autre.
            'adherent_id'      => $cid,
            'salle_id'         => $salleId,
            'date_reservation' => ($_POST['date_reservation'] ?? '') ?: date('Y-m-d'),
            'heure_debut'      => ($_POST['heure_debut']      ?? '') ?: '09:00',
            'heure_fin'        => ($_POST['heure_fin']        ?? '') ?: '10:00',
        ];

        if ($data['heure_fin'] <= $data['heure_debut']) {
            $this->flash('L\'heure de fin doit être postérieure à l\'heure de début.', 'danger');
            $this->redirect('client', 'reserver');
        }
        if ($data['date_reservation'] < date('Y-m-d')) {
            $this->flash('La date de réservation ne peut pas être dans le passé.', 'danger');
            $this->redirect('client', 'reserver');
        }

        try {
            $this->model('Reservation')->create($data);
            $this->flash('Réservation confirmée.');
            $this->redirect('client', 'dashboard');
        } catch (PDOException $e) {
            error_log('[Mediatheque] Client resa: ' . $e->getMessage());
            // Les triggers métier renvoient le SQLSTATE 45000 avec un
            // message lisible (salle indisponible, créneau pris...).
            $msg = $e->getCode() === '45000'
                ? $e->getMessage()
                : 'Réservation impossible.';
            $this->flash('Réservation refusée : ' . $msg, 'danger');
            $this->redirect('client', 'reserver');
        }
    }

    /** Le client annule l'une de SES réservations confirmées. */
    public function annuler($id = null): void
    {
        $this->requireClient();
        $cid = (int) $_SESSION['client']['id'];

        if ($id && $this->model('Reservation')->annulerForAdherent((int) $id, $cid)) {
            $this->flash('Réservation annulée.');
        } else {
            $this->flash('Annulation impossible.', 'danger');
        }
        $this->redirect('client', 'dashboard');
    }

    /* =================================================================
     *  Garde d'accès : redirige vers le login si le client n'est pas connecté.
     * ================================================================= */
    private function requireClient(): void
    {
        if (empty($_SESSION['client'])) {
            $this->flash('Veuillez vous connecter pour accéder à votre espace.', 'danger');
            $this->redirect('client', 'login');
        }
    }
}
