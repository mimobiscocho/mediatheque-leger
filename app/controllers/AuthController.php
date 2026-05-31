<?php
/**
 * Authentification des agents : connexion, vérification, déconnexion.
 * Les pages métier ne sont accessibles qu'à un agent connecté (voir le
 * contrôle d'accès dans public/index.php).
 */
class AuthController extends Controller
{
    /** Affiche le formulaire de connexion (sans le gabarit principal). */
    public function login($id = null): void
    {
        if (!empty($_SESSION['agent'])) {
            $this->redirect('home');
        }
        $this->view('auth/login', ['titre' => 'Connexion'], false);
    }

    /** Vérifie les identifiants soumis et ouvre la session. */
    public function authenticate($id = null): void
    {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['mot_de_passe'] ?? '';

        $agent = $this->model('Agent')->findByEmail($email);

        // password_verify compare le mot de passe au haché stocké (anti-fuite).
        if ($agent && (int) $agent['actif'] === 1 && password_verify($pass, $agent['mot_de_passe'])) {
            // Régénère l'ID de session pour prévenir la fixation de session.
            session_regenerate_id(true);
            $_SESSION['agent'] = [
                'id'     => (int) $agent['id'],
                'nom'    => $agent['nom'],
                'prenom' => $agent['prenom'],
                'email'  => $agent['email'],
                'role'   => $agent['role'],
            ];
            $this->flash('Bienvenue, ' . $agent['prenom'] . ' !');
            $this->redirect('home');
        }

        $this->flash('Identifiants incorrects ou compte désactivé.', 'danger');
        $this->redirect('auth', 'login');
    }

    /** Ferme la session de l'agent. */
    public function logout($id = null): void
    {
        unset($_SESSION['agent']);
        session_regenerate_id(true);
        $this->flash('Vous avez été déconnecté.');
        $this->redirect('auth', 'login');
    }
}
