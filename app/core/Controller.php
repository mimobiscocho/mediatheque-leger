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

    /**
     * Affiche une vue. Par défaut elle est encadrée par le gabarit
     * (header + footer) ; passer $withLayout = false pour une page autonome
     * (ex. l'écran de connexion).
     */
    protected function view(string $view, array $data = [], bool $withLayout = true): void
    {
        extract($data);
        $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';
        if ($withLayout) {
            require dirname(__DIR__) . '/views/layouts/header.php';
            require $viewFile;
            require dirname(__DIR__) . '/views/layouts/footer.php';
        } else {
            require $viewFile;
        }
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
