package fr.blr.mediatheque.view;

import fr.blr.mediatheque.controller.AdherentController;
import fr.blr.mediatheque.controller.LivreController;
import fr.blr.mediatheque.controller.MaterielController;
import fr.blr.mediatheque.controller.PretController;
import fr.blr.mediatheque.controller.ReservationController;
import fr.blr.mediatheque.controller.SalleController;
import fr.blr.mediatheque.model.Pret;

import javax.swing.BorderFactory;
import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.JTable;
import javax.swing.border.EmptyBorder;
import javax.swing.table.DefaultTableModel;
import java.awt.BorderLayout;
import java.awt.Color;
import java.awt.Font;
import java.awt.GridLayout;
import java.sql.SQLException;

/** Tableau de bord : synthèse chiffrée et prêts en retard. */
public class DashboardPanel extends JPanel implements Refreshable {

    private final AdherentController adherentCtrl = new AdherentController();
    private final LivreController livreCtrl = new LivreController();
    private final MaterielController materielCtrl = new MaterielController();
    private final SalleController salleCtrl = new SalleController();
    private final PretController pretCtrl = new PretController();
    private final ReservationController resaCtrl = new ReservationController();

    private final JLabel valAdherents = bigValue();
    private final JLabel valLivres = bigValue();
    private final JLabel valMateriels = bigValue();
    private final JLabel valSalles = bigValue();
    private final JLabel valPrets = bigValue();
    private final JLabel valResa = bigValue();
    private final DefaultTableModel retardModel;

    public DashboardPanel() {
        setLayout(new BorderLayout(10, 10));
        setBorder(new EmptyBorder(12, 12, 12, 12));

        JLabel titre = new JLabel("Tableau de bord");
        titre.setFont(titre.getFont().deriveFont(Font.BOLD, 20f));
        add(titre, BorderLayout.NORTH);

        JPanel cartes = new JPanel(new GridLayout(2, 3, 10, 10));
        cartes.add(carte("Adhérents", valAdherents, UiUtils.BLEU));
        cartes.add(carte("Livres", valLivres, UiUtils.BLEU));
        cartes.add(carte("Matériels", valMateriels, UiUtils.BLEU));
        cartes.add(carte("Salles", valSalles, UiUtils.BLEU));
        cartes.add(carte("Prêts en cours", valPrets, new Color(0x2C7BE5)));
        cartes.add(carte("Réservations à venir", valResa, new Color(0x17A2B8)));

        retardModel = new DefaultTableModel(new Object[]{"Adhérent", "Produit", "Retour prévu"}, 0) {
            @Override public boolean isCellEditable(int r, int c) { return false; }
        };
        JTable retardTable = new JTable(retardModel);
        retardTable.setRowHeight(22);
        JPanel retardPanel = new JPanel(new BorderLayout());
        retardPanel.setBorder(BorderFactory.createTitledBorder("⚠ Prêts en retard"));
        retardPanel.add(new JScrollPane(retardTable), BorderLayout.CENTER);

        JPanel centre = new JPanel(new BorderLayout(10, 10));
        centre.add(cartes, BorderLayout.NORTH);
        centre.add(retardPanel, BorderLayout.CENTER);
        add(centre, BorderLayout.CENTER);

        rafraichir();
    }

    @Override
    public void rafraichir() {
        try {
            valAdherents.setText(String.valueOf(adherentCtrl.compter()));
            valLivres.setText(String.valueOf(livreCtrl.compter()));
            valMateriels.setText(String.valueOf(materielCtrl.compter()));
            valSalles.setText(String.valueOf(salleCtrl.compter()));
            valPrets.setText(String.valueOf(pretCtrl.compterEnCours()));
            valResa.setText(String.valueOf(resaCtrl.compterActives()));

            retardModel.setRowCount(0);
            for (Pret p : pretCtrl.listerEnRetard()) {
                retardModel.addRow(new Object[]{p.getAdherentNom(), p.getProduit(), p.getDateRetourPrevue()});
            }
        } catch (SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private JLabel bigValue() {
        JLabel l = new JLabel("0", JLabel.CENTER);
        l.setFont(l.getFont().deriveFont(Font.BOLD, 30f));
        l.setForeground(Color.WHITE);
        return l;
    }

    private JPanel carte(String titre, JLabel valeur, Color couleur) {
        JPanel p = new JPanel(new BorderLayout());
        p.setBackground(couleur);
        p.setBorder(new EmptyBorder(14, 14, 14, 14));
        JLabel t = new JLabel(titre, JLabel.CENTER);
        t.setForeground(Color.WHITE);
        t.setFont(t.getFont().deriveFont(Font.PLAIN, 13f));
        p.add(valeur, BorderLayout.CENTER);
        p.add(t, BorderLayout.SOUTH);
        return p;
    }
}
