<?php
/**
 * Contrôleur de base : chargement des modèles, rendu des vues,
 * redirections et messages flash.
 */
abstract class Controller
{
    /** Instancie un modèle métier (app/models/Nom.php). */
    protected function model(string $name)
    {
        require_once dirname(__DIR__) . '/models/' . $name . '.php';
        return new $name();
    }

    /** Affiche une vue encadrée par le gabarit (header + footer). */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';
        require dirname(__DIR__) . '/views/layouts/header.php';
        require $viewFile;
        require dirname(__DIR__) . '/views/layouts/footer.php';
    }

    /** Redirige vers une action puis stoppe le script. */
    protected function redirect(string $ctrl, string $action = 'index'): void
    {
        header('Location: ' . url($ctrl, $action));
        exit;
    }

    /** Mémorise un message flash affiché au prochain chargement de page. */
    protected function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }
}
