package fr.blr.mediatheque.view;

import fr.blr.mediatheque.controller.ReservationController;
import fr.blr.mediatheque.model.Adherent;
import fr.blr.mediatheque.model.Reservation;
import fr.blr.mediatheque.model.Salle;

import javax.swing.*;
import javax.swing.border.EmptyBorder;
import javax.swing.event.DocumentEvent;
import javax.swing.event.DocumentListener;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableRowSorter;
import java.awt.BorderLayout;
import java.awt.FlowLayout;
import java.awt.GridLayout;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

/** Module de réservation des espaces (salles de coworking). */
public class ReservationPanel extends JPanel implements Refreshable {

    private final ReservationController controller = new ReservationController();
    private final DefaultTableModel model;
    private final JTable table;
    private final TableRowSorter<DefaultTableModel> sorter;
    private List<Reservation> data = new ArrayList<>();

    public ReservationPanel() {
        setLayout(new BorderLayout(8, 8));
        setBorder(new EmptyBorder(10, 10, 10, 10));

        JButton btnNouvelle = new JButton("Nouvelle réservation");
        JButton btnAnnuler = new JButton("Annuler");
        JButton btnSupprimer = new JButton("Supprimer");
        JButton btnRafraichir = new JButton("Rafraîchir");
        JTextField recherche = new JTextField(16);

        JPanel barre = new JPanel(new FlowLayout(FlowLayout.LEFT, 6, 4));
        barre.add(btnNouvelle);
        barre.add(btnAnnuler);
        barre.add(btnSupprimer);
        barre.add(btnRafraichir);
        barre.add(new JLabel("    Rechercher :"));
        barre.add(recherche);
        add(barre, BorderLayout.NORTH);

        model = new DefaultTableModel(new Object[]{
                "ID", "Adhérent", "Salle", "Date", "Créneau", "Statut"}, 0) {
            @Override public boolean isCellEditable(int r, int c) { return false; }
        };
        table = new JTable(model);
        table.setRowHeight(24);
        table.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        table.getColumnModel().getColumn(0).setMaxWidth(50);
        sorter = new TableRowSorter<>(model);
        table.setRowSorter(sorter);
        add(new JScrollPane(table), BorderLayout.CENTER);

        btnNouvelle.addActionListener(e -> ouvrirFormulaire());
        btnAnnuler.addActionListener(e -> annuler());
        btnSupprimer.addActionListener(e -> supprimer());
        btnRafraichir.addActionListener(e -> rafraichir());
        recherche.getDocument().addDocumentListener(new DocumentListener() {
            private void filtrer() {
                String q = recherche.getText().trim();
                sorter.setRowFilter(q.isEmpty() ? null : RowFilter.regexFilter("(?i)" + Pattern.quote(q)));
            }
            public void insertUpdate(DocumentEvent e) { filtrer(); }
            public void removeUpdate(DocumentEvent e) { filtrer(); }
            public void changedUpdate(DocumentEvent e) { filtrer(); }
        });

        rafraichir();
    }

    @Override
    public void rafraichir() {
        try {
            data = controller.lister();
            model.setRowCount(0);
            for (Reservation r : data) {
                model.addRow(new Object[]{
                        r.getId(), r.getAdherentNom(), r.getSalleNom(), r.getDateReservation(),
                        r.getHeureDebut() + " - " + r.getHeureFin(), libelleStatut(r.getStatut())});
            }
        } catch (SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private Reservation selection() {
        int row = table.getSelectedRow();
        if (row < 0) {
            UiUtils.info(this, "Veuillez sélectionner une réservation dans la liste.");
            return null;
        }
        int id = (int) model.getValueAt(table.convertRowIndexToModel(row), 0);
        for (Reservation r : data) if (r.getId() == id) return r;
        return null;
    }

    private void annuler() {
        Reservation r = selection();
        if (r == null) return;
        if ("annulee".equals(r.getStatut())) {
            UiUtils.info(this, "Cette réservation est déjà annulée.");
            return;
        }
        if (UiUtils.confirmer(this, "Annuler cette réservation ?")) {
            try { controller.annuler(r.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void supprimer() {
        Reservation r = selection();
        if (r == null) return;
        if (UiUtils.confirmer(this, "Supprimer cette réservation ?")) {
            try { controller.supprimer(r.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void ouvrirFormulaire() {
        JComboBox<Adherent> cbAdherent = new JComboBox<>();
        JComboBox<Salle> cbSalle = new JComboBox<>();
        try {
            for (Adherent a : controller.listerAdherents()) cbAdherent.addItem(a);
            for (Salle s : controller.listerSalles()) cbSalle.addItem(s);
        } catch (SQLException ex) { UiUtils.erreur(this, ex); return; }

        JTextField date = new JTextField(LocalDate.now().toString());
        JTextField heureDebut = new JTextField("09:00");
        JTextField heureFin = new JTextField("10:00");

        JPanel form = new JPanel(new GridLayout(0, 2, 6, 6));
        form.add(new JLabel("Adhérent *"));            form.add(cbAdherent);
        form.add(new JLabel("Salle *"));               form.add(cbSalle);
        form.add(new JLabel("Date (AAAA-MM-JJ)"));     form.add(date);
        form.add(new JLabel("Heure début (HH:MM)"));   form.add(heureDebut);
        form.add(new JLabel("Heure fin (HH:MM)"));     form.add(heureFin);

        int res = JOptionPane.showConfirmDialog(this, form, "Nouvelle réservation",
                JOptionPane.OK_CANCEL_OPTION, JOptionPane.PLAIN_MESSAGE);
        if (res != JOptionPane.OK_OPTION) return;

        Adherent a = (Adherent) cbAdherent.getSelectedItem();
        Salle s = (Salle) cbSalle.getSelectedItem();
        if (a == null || s == null) {
            UiUtils.info(this, "Veuillez sélectionner un adhérent et une salle.");
            return;
        }

        Reservation r = new Reservation();
        r.setAdherentId(a.getId());
        r.setSalleId(s.getId());
        r.setDateReservation(date.getText().trim());
        r.setHeureDebut(heureDebut.getText().trim());
        r.setHeureFin(heureFin.getText().trim());

        try {
            controller.enregistrer(r);
            rafraichir();
        } catch (IllegalArgumentException | SQLException ex) {
            // Inclut le refus remonté par le trigger en cas de conflit de créneau
            UiUtils.erreur(this, ex);
        }
    }

    private static String libelleStatut(String statut) {
        switch (statut) {
            case "confirmee": return "Confirmée";
            case "annulee":   return "Annulée";
            case "terminee":  return "Terminée";
            default:          return statut;
        }
    }
}
