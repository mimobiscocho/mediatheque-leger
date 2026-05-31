package fr.blr.mediatheque.view;

import javax.swing.JOptionPane;
import java.awt.Color;
import java.awt.Component;

/** Petites fonctions utilitaires partagées par les vues Swing. */
public final class UiUtils {

    public static final Color BLEU = new Color(0x1B5E7A);
    public static final Color BLEU_FONCE = new Color(0x134B62);
    public static final Color ORANGE = new Color(0xE8A33D);

    private UiUtils() { }

    public static void erreur(Component parent, Exception e) {
        JOptionPane.showMessageDialog(parent, message(e), "Erreur", JOptionPane.ERROR_MESSAGE);
    }

    public static void info(Component parent, String msg) {
        JOptionPane.showMessageDialog(parent, msg, "Information", JOptionPane.INFORMATION_MESSAGE);
    }

    public static boolean confirmer(Component parent, String msg) {
        return JOptionPane.showConfirmDialog(parent, msg, "Confirmation",
                JOptionPane.YES_NO_OPTION) == JOptionPane.YES_OPTION;
    }

    /** Extrait un message lisible (pour les SQLException remontées par les triggers). */
    public static String message(Exception e) {
        String m = e.getMessage();
        return (m == null || m.trim().isEmpty()) ? e.toString() : m;
    }
}
