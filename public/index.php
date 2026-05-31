<?php
/**
 * Contrôleur frontal (front controller).
 * Point d'entrée unique : toutes les requêtes passent par ce fichier.
 * Routage : index.php?ctrl=adherent&action=index&id=3
 */

session_start();

define('ROOT', dirname(__DIR__));

require ROOT . '/config/config.php';
require ROOT . '/config/Database.php';
require ROOT . '/app/core/helpers.php';
require ROOT . '/app/core/Model.php';
require ROOT . '/app/core/Controller.php';

// --- Lecture et nettoyage des paramètres de routage ---
$ctrl   = preg_replace('/[^a-zA-Z]/', '', $_GET['ctrl']   ?? 'home');
$action = preg_replace('/[^a-zA-Z]/', '', $_GET['action'] ?? 'index');
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

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
