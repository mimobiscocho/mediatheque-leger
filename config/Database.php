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
                die('Erreur de connexion à la base de données : ' . $e->getMessage()
                    . '<br>Vérifiez la configuration dans config/config.php et que la base "'
                    . DB_NAME . '" a bien été importée (sql/schema.sql).');
            }
        }
        return self::$instance;
    }
}
