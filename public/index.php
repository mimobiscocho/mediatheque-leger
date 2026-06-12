<?php
/**
 * Contrôleur frontal (front controller).
 * Point d'entrée unique : toutes les requêtes passent par ce fichier.
 * Routage : index.php?ctrl=adherent&action=index&id=3
 */

define('ROOT', dirname(__DIR__));

// Chargement des fichiers de base (config + "framework" maison)
require ROOT . '/config/config.php';      // constantes (nom appli, BDD...)
require ROOT . '/config/Database.php';    // connexion PDO
require ROOT . '/app/core/helpers.php';   // petites fonctions (url, e, csrf...)
require ROOT . '/app/core/Model.php';     // classe mère des modèles
require ROOT . '/app/core/Controller.php';// classe mère des contrôleurs
require ROOT . '/app/core/Security.php';  // rôles + validations
require ROOT . '/app/core/Logger.php';    // journal des événements (logs/)

// --- Paramètres du cookie de session (sécurité) ---
// HttpOnly  : le cookie n'est pas lisible en JavaScript (anti-vol XSS).
// SameSite  : empêche l'envoi sur les requêtes cross-site (anti-CSRF de base).
// Secure    : transmis uniquement en HTTPS si la connexion l'est.
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

// --- Lecture et nettoyage des paramètres de routage ---
$ctrl   = strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['ctrl']   ?? 'home'));
$action = preg_replace('/[^a-zA-Z]/', '', $_GET['action'] ?? 'index');
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

// --- Vérification du jeton CSRF sur toute requête POST ---
// (les formulaires de l'application incluent le champ csrf_field())
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

// --- Contrôle d'accès : toute page exige un agent connecté,
//     sauf le contrôleur d'authentification (écran de connexion).
if ($ctrl !== 'auth' && empty($_SESSION['agent'])) {
    header('Location: ' . url('auth', 'login'));
    exit;
}

// --- Mise à jour automatique des prêts en retard ---
// (seuls les statuts évoluent ; ne touche pas à la disponibilité)
if (!empty($_SESSION['agent'])) {
    try {
        Database::getConnection()->exec(
            "UPDATE pret
                SET statut = 'en_retard'
              WHERE date_retour_effective IS NULL
                AND date_retour_prevue < CURDATE()
                AND statut = 'en_cours'"
        );
    } catch (PDOException $e) {
        error_log('[Mediatheque] MAJ statut en_retard : ' . $e->getMessage());
    }
}

$controllerClass = ucfirst($ctrl) . 'Controller';
$controllerFile  = ROOT . '/app/controllers/' . $controllerClass . '.php';

if (!is_file($controllerFile)) {
    http_response_code(404);
    exit('Page introuvable (contrôleur "' . e($ctrl) . '").');
}

require $controllerFile;
$controller = new $controllerClass();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    exit('Action introuvable ("' . e($action) . '").');
}

// Appel de l'action demandée
$controller->$action($id);
