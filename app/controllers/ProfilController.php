<?php
/** Gestion du profil de l'agent connecté (consultation, changement de mot de passe). */
class ProfilController extends Controller
{
    public function index($id = null): void
    {
        $agent = $this->model('Agent')->find($_SESSION['agent']['id']);
        $this->view('profil/index', [
            'titre' => 'Mon profil',
            'agent' => $agent,
        ]);
    }

    /**
     * Changement de mot de passe, en 3 contrôles :
     *   1. les deux saisies du nouveau mot de passe correspondent
     *   2. il fait au moins 8 caractères
     *   3. l'ancien mot de passe est le bon (preuve d'identité)
     */
    public function password($id = null): void
    {
        $ancien  = $_POST['ancien_mdp'] ?? '';
        $nouveau = $_POST['nouveau_mdp'] ?? '';
        $confirm = $_POST['confirm_mdp'] ?? '';

        if ($nouveau !== $confirm) {
            $this->flash('Les deux mots de passe ne correspondent pas.', 'danger');
            $this->redirect('profil');
        }

        if (strlen($nouveau) < 8) {
            $this->flash('Le mot de passe doit contenir au moins 8 caractères.', 'danger');
            $this->redirect('profil');
        }

        $agentModel = $this->model('Agent');
        $agent = $agentModel->find($_SESSION['agent']['id']);

        if (!password_verify($ancien, $agent['mot_de_passe'])) {
            Logger::security('Échec de changement de mot de passe (ancien MDP incorrect)');
            $this->flash('L\'ancien mot de passe est incorrect.', 'danger');
            $this->redirect('profil');
        }

        $agentModel->updatePassword($_SESSION['agent']['id'], $nouveau);
        Logger::info('Mot de passe modifié avec succès');
        $this->flash('Mot de passe modifié avec succès.');
        $this->redirect('profil');
    }
}
