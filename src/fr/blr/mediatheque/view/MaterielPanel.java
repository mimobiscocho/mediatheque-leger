package fr.blr.mediatheque.view;

import fr.blr.mediatheque.controller.MaterielController;
import fr.blr.mediatheque.model.Materiel;

import javax.swing.*;
import javax.swing.border.EmptyBorder;
import javax.swing.event.DocumentEvent;
import javax.swing.event.DocumentListener;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableRowSorter;
import java.awt.BorderLayout;
import java.awt.Component;
import java.awt.FlowLayout;
import java.awt.GridLayout;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

/** Module de gestion des matériels (liste + formulaire CRUD). */
public class MaterielPanel extends JPanel implements Refreshable {

    private static final String[] ETATS_LBL = {"Neuf", "Bon", "Usé", "Hors service"};

    private final MaterielController controller = new MaterielController();
    private final DefaultTableModel model;
    private final JTable table;
    private final TableRowSorter<DefaultTableModel> sorter;
    private List<Materiel> data = new ArrayList<>();

    public MaterielPanel() {
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
                "ID", "Nom", "Catégorie", "État", "Disponible"}, 0) {
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
        btnModifier.addActionListener(e -> { Materiel s = selection(); if (s != null) ouvrirFormulaire(s); });
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
                if (e.getClickCount() == 2) { Materiel s = selection(); if (s != null) ouvrirFormulaire(s); }
            }
        });

        rafraichir();
    }

    @Override
    public void rafraichir() {
        try {
            data = controller.lister();
            model.setRowCount(0);
            for (Materiel m : data) {
                model.addRow(new Object[]{
                        m.getId(), m.getNom(), m.getCategorie(),
                        labelEtat(m.getEtat()), m.isDisponible() ? "Oui" : "Non"});
            }
        } catch (SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private Materiel selection() {
        int row = table.getSelectedRow();
        if (row < 0) {
            UiUtils.info(this, "Veuillez sélectionner un matériel dans la liste.");
            return null;
        }
        int id = (int) model.getValueAt(table.convertRowIndexToModel(row), 0);
        for (Materiel m : data) if (m.getId() == id) return m;
        return null;
    }

    private void supprimer() {
        Materiel m = selection();
        if (m == null) return;
        if (UiUtils.confirmer(this, "Supprimer le matériel « " + m.getNom() + " » ?")) {
            try { controller.supprimer(m.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void ouvrirFormulaire(Materiel existant) {
        boolean creation = (existant == null);

        JTextField nom = new JTextField(creation ? "" : existant.getNom());
        JTextField categorie = new JTextField(creation ? "" : nz(existant.getCategorie()));
        JTextArea description = new JTextArea(creation ? "" : nz(existant.getDescription()), 3, 20);
        description.setLineWrap(true);
        description.setWrapStyleWord(true);
        JComboBox<String> cbEtat = new JComboBox<>(MaterielController.ETATS);
        cbEtat.setSelectedItem(creation ? "bon" : existant.getEtat());
        cbEtat.setRenderer(new DefaultListCellRenderer() {
            public Component getListCellRendererComponent(JList<?> l, Object v, int i, boolean s, boolean f) {
                super.getListCellRendererComponent(l, v, i, s, f);
                setText(labelEtat((String) v));
                return this;
            }
        });
        JCheckBox disponible = new JCheckBox("Disponible à l'emprunt", creation || existant.isDisponible());

        JPanel form = new JPanel(new GridLayout(0, 2, 6, 6));
        form.add(new JLabel("Nom *"));        form.add(nom);
        form.add(new JLabel("Catégorie"));    form.add(categorie);
        form.add(new JLabel("Description"));  form.add(new JScrollPane(description));
        form.add(new JLabel("État"));         form.add(cbEtat);
        form.add(new JLabel(""));             form.add(disponible);

        int res = JOptionPane.showConfirmDialog(this, form,
                creation ? "Nouveau matériel" : "Modifier le matériel",
                JOptionPane.OK_CANCEL_OPTION, JOptionPane.PLAIN_MESSAGE);
        if (res != JOptionPane.OK_OPTION) return;

        Materiel cible = creation ? new Materiel() : existant;
        cible.setNom(nom.getText().trim());
        cible.setCategorie(categorie.getText().trim());
        cible.setDescription(description.getText().trim());
        cible.setEtat((String) cbEtat.getSelectedItem());
        cible.setDisponible(disponible.isSelected());

        try {
            controller.enregistrer(cible);
            rafraichir();
        } catch (IllegalArgumentException | SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    /** Convertit la valeur d'état stockée en libellé lisible. */
    private static String labelEtat(String valeur) {
        if (valeur == null) return "";
        for (int i = 0; i < MaterielController.ETATS.length; i++) {
            if (MaterielController.ETATS[i].equals(valeur)) return ETATS_LBL[i];
        }
        return valeur;
    }

    private static String nz(String s) { return s == null ? "" : s; }
}
