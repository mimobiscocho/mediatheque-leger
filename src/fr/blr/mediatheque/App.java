package fr.blr.mediatheque;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.view.MainFrame;

import javax.swing.JOptionPane;
import javax.swing.SwingUtilities;
import javax.swing.UIManager;

/**
 * Point d'entrée de l'application de bureau (client lourd) de gestion
 * de la Médiathèque de Bourg-la-Reine.
 *
 * Architecture MVC :
 *   - model       : entités métier (POJO)
 *   - dao         : accès aux données (JDBC + requêtes préparées)
 *   - controller  : logique métier / orchestration
 *   - view        : interfaces graphiques Java Swing
 */
public class App {

    public static void main(String[] args) {
        // Apparence native du système d'exploitation
        try {
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (Exception ignored) {
            // on conserve le thème par défaut si indisponible
        }

        SwingUtilities.invokeLater(() -> {
            // Vérification de la connexion à la base avant d'ouvrir l'IHM
            if (!Database.testConnection()) {
                JOptionPane.showMessageDialog(null,
                    "Impossible de se connecter à la base MySQL « mediatheque ».\n\n"
                    + "Vérifiez :\n"
                    + "  • que le serveur MySQL/MariaDB est démarré ;\n"
                    + "  • que la base est importée (sql/schema.sql) ;\n"
                    + "  • les identifiants dans config/Database.java ;\n"
                    + "  • que le connecteur JDBC (.jar) est présent dans lib/.",
                    "Erreur de connexion", JOptionPane.ERROR_MESSAGE);
                return;
            }
            new MainFrame().setVisible(true);
        });
    }
}
