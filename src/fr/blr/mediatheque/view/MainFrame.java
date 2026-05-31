package fr.blr.mediatheque.view;

import javax.swing.BorderFactory;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JTabbedPane;
import java.awt.BorderLayout;
import java.awt.Color;
import java.awt.Component;
import java.awt.Dimension;
import java.awt.Font;

/** Fenêtre principale : navigation par onglets entre les modules de gestion. */
public class MainFrame extends JFrame {

    private final JTabbedPane onglets = new JTabbedPane();

    public MainFrame() {
        setTitle("Médiathèque de Bourg-la-Reine — Gestion (client lourd)");
        setDefaultCloseOperation(EXIT_ON_CLOSE);
        setSize(1040, 680);
        setMinimumSize(new Dimension(840, 560));
        setLocationRelativeTo(null);
        setLayout(new BorderLayout());

        // Bandeau institutionnel
        JLabel entete = new JLabel("  📚  Médiathèque de Bourg-la-Reine");
        entete.setOpaque(true);
        entete.setBackground(UiUtils.BLEU);
        entete.setForeground(Color.WHITE);
        entete.setFont(entete.getFont().deriveFont(Font.BOLD, 18f));
        entete.setBorder(BorderFactory.createEmptyBorder(10, 12, 10, 12));
        add(entete, BorderLayout.NORTH);

        onglets.addTab("Tableau de bord", new DashboardPanel());
        onglets.addTab("Adhérents", new AdherentPanel());
        onglets.addTab("Livres", new LivrePanel());
        onglets.addTab("Matériels", new MaterielPanel());
        onglets.addTab("Salles", new SallePanel());
        onglets.addTab("Prêts", new PretPanel());
        onglets.addTab("Réservations", new ReservationPanel());

        // Recharge l'onglet sélectionné (les données peuvent avoir changé ailleurs)
        onglets.addChangeListener(e -> {
            Component c = onglets.getSelectedComponent();
            if (c instanceof Refreshable r) {
                r.rafraichir();
            }
        });

        add(onglets, BorderLayout.CENTER);
    }
}
