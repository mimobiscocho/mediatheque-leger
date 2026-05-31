package fr.blr.mediatheque.config;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Gestion centralisée de la connexion JDBC à MySQL (singleton).
 * Le pilote (connecteur/J) est chargé automatiquement depuis lib/
 * dès lors qu'il est présent sur le classpath.
 */
public final class Database {

    // --- Paramètres de connexion (à adapter selon l'environnement) ---
    private static final String HOST = "localhost";
    private static final int    PORT = 3306;
    private static final String NAME = "mediatheque";
    private static final String USER = "root";
    private static final String PASS = "";

    private static final String URL =
        "jdbc:mysql://" + HOST + ":" + PORT + "/" + NAME
        + "?useSSL=false&allowPublicKeyRetrieval=true"
        + "&serverTimezone=Europe/Paris&characterEncoding=UTF-8";

    private static Connection connection;

    private Database() {
        // classe utilitaire : pas d'instanciation
    }

    /** Retourne la connexion partagée, en la (ré)ouvrant si nécessaire. */
    public static Connection getConnection() throws SQLException {
        if (connection == null || connection.isClosed()) {
            connection = DriverManager.getConnection(URL, USER, PASS);
        }
        return connection;
    }

    /** Teste la connexion au démarrage de l'application. */
    public static boolean testConnection() {
        try {
            return getConnection() != null;
        } catch (SQLException e) {
            System.err.println("Connexion à la base échouée : " + e.getMessage());
            return false;
        }
    }
}
