<?php
/**
 * Redirection vers le contrôleur frontal (public/index.php).
 * Permet d'accéder à l'application même si la racine du projet est exposée
 * (ex. XAMPP : http://localhost/mediatheque-leger/).
 * En production, configurez plutôt la racine web (DocumentRoot) sur /public.
 */
header('Location: public/');
exit;
