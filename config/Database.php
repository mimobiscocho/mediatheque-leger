<?php
/**
 * Connexion PDO à MySQL, gérée en singleton.
 * On utilise PDO + requêtes préparées pour se prémunir des injections SQL.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // On journalise l'erreur technique côté serveur,
                // et on n'affiche qu'un message neutre côté navigateur
                // (pas de fuite d'identifiants, d'hôte ou de schéma).
                error_log('[Mediatheque] Connexion BD impossible : ' . $e->getMessage());
                http_response_code(503);
                exit(
                    '<h1>Service indisponible</h1>'
                  . '<p>La base de données est momentanément inaccessible. '
                  . 'Veuillez réessayer dans quelques instants.</p>'
                );
            }
        }
        return self::$instance;
    }
}
