package fr.blr.mediatheque.view;

import fr.blr.mediatheque.controller.SalleController;
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
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

/** Module de gestion des salles de coworking (liste + formulaire CRUD). */
public class SallePanel extends JPanel implements Refreshable {

    private final SalleController controller = new SalleController();
    private final DefaultTableModel model;
    private final JTable table;
    private final TableRowSorter<DefaultTableModel> sorter;
    private List<Salle> data = new ArrayList<>();

    public SallePanel() {
        setLayout(new BorderLayout(8, 8));
        setBorder(new EmptyBorder(10, 10, 10, 10));

        JButton btnAjouter = new JButton("Ajouter");
        JButton btnModifier = new JButton("Modifier");
        JButton btnSupprimer = new JButton("Supprimer");
        JButton btnRafraichir = new JButton("Rafraîchir");
        JTextField recherche = new JTextField(16);

        JPanel barre = new JPanel(new FlowLayout(FlowLayout.LEFT, 6, 4));
        barre.add(btnAjouter);
        barre.add(btnModifier);
        barre.add(btnSupprimer);
        barre.add(btnRafraichir);
        barre.add(new JLabel("    Rechercher :"));
        barre.add(recherche);
        add(barre, BorderLayout.NORTH);

        model = new DefaultTableModel(new Object[]{
                "ID", "Nom", "Capacité", "Équipements", "Disponible"}, 0) {
            @Override public boolean isCellEditable(int r, int c) { return false; }
        };
        table = new JTable(model);
        table.setRowHeight(24);
        table.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        table.getColumnModel().getColumn(0).setMaxWidth(50);
        sorter = new TableRowSorter<>(model);
        table.setRowSorter(sorter);
        add(new JScrollPane(table), BorderLayout.CENTER);

        btnAjouter.addActionListener(e -> ouvrirFormulaire(null));
        btnModifier.addActionListener(e -> { Salle s = selection(); if (s != null) ouvrirFormulaire(s); });
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
        table.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent e) {
                if (e.getClickCount() == 2) { Salle s = selection(); if (s != null) ouvrirFormulaire(s); }
            }
        });

        rafraichir();
    }

    @Override
    public void rafraichir() {
        try {
            data = controller.lister();
            model.setRowCount(0);
            for (Salle s : data) {
                model.addRow(new Object[]{
                        s.getId(), s.getNom(), s.getCapacite(),
                        s.getEquipements(), s.isDisponible() ? "Oui" : "Non"});
            }
        } catch (SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private Salle selection() {
        int row = table.getSelectedRow();
        if (row < 0) {
            UiUtils.info(this, "Veuillez sélectionner une salle dans la liste.");
            return null;
        }
        int id = (int) model.getValueAt(table.convertRowIndexToModel(row), 0);
        for (Salle s : data) if (s.getId() == id) return s;
        return null;
    }

    private void supprimer() {
        Salle s = selection();
        if (s == null) return;
        if (UiUtils.confirmer(this, "Supprimer la salle « " + s.getNom() + " » ?")) {
            try { controller.supprimer(s.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void ouvrirFormulaire(Salle existant) {
        boolean creation = (existant == null);

        JTextField nom = new JTextField(creation ? "" : existant.getNom());
        JTextField capacite = new JTextField(creation ? "1" : String.valueOf(existant.getCapacite()));
        JTextField equipements = new JTextField(creation ? "" : nz(existant.getEquipements()));
        JCheckBox disponible = new JCheckBox("Disponible à la réservation", creation || existant.isDisponible());

        JPanel form = new JPanel(new GridLayout(0, 2, 6, 6));
        form.add(new JLabel("Nom *"));                  form.add(nom);
        form.add(new JLabel("Capacité (personnes)"));   form.add(capacite);
        form.add(new JLabel("Équipements"));            form.add(equipements);
        form.add(new JLabel(""));                       form.add(disponible);

        int res = JOptionPane.showConfirmDialog(this, form,
                creation ? "Nouvelle salle" : "Modifier la salle",
                JOptionPane.OK_CANCEL_OPTION, JOptionPane.PLAIN_MESSAGE);
        if (res != JOptionPane.OK_OPTION) return;

        Salle cible = creation ? new Salle() : existant;
        cible.setNom(nom.getText().trim());
        cible.setCapacite(parseIntOr(capacite.getText(), 1));
        cible.setEquipements(equipements.getText().trim());
        cible.setDisponible(disponible.isSelected());

        try {
            controller.enregistrer(cible);
            rafraichir();
        } catch (IllegalArgumentException | SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private static String nz(String s) { return s == null ? "" : s; }

    private static int parseIntOr(String s, int defaut) {
        try { return Integer.parseInt(s.trim()); }
        catch (NumberFormatException e) { return defaut; }
    }
}
