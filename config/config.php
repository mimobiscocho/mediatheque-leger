<?php
/**
 * Configuration générale de l'application.
 * Les identifiants de base de données peuvent être surchargés par variables
 * d'environnement (pratique pour passer de XAMPP au serveur Linux de prod).
 */

define('APP_NAME', 'Médiathèque de Bourg-la-Reine');

// --- Base de données (valeurs par défaut compatibles XAMPP / WAMP) ---
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'mediatheque');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// --- Routage : tout passe par le contrôleur frontal public/index.php ---
define('BASE_URL', 'index.php');

date_default_timezone_set('Europe/Paris');
