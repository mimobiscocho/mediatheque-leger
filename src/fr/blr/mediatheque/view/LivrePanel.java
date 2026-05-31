package fr.blr.mediatheque.view;

import fr.blr.mediatheque.controller.LivreController;
import fr.blr.mediatheque.model.Livre;

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

/** Module de gestion des livres (liste + formulaire CRUD). */
public class LivrePanel extends JPanel implements Refreshable {

    private final LivreController controller = new LivreController();
    private final DefaultTableModel model;
    private final JTable table;
    private final TableRowSorter<DefaultTableModel> sorter;
    private List<Livre> data = new ArrayList<>();

    public LivrePanel() {
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
                "ID", "Titre", "Auteur", "Genre", "Année", "Disponibles"}, 0) {
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
        btnModifier.addActionListener(e -> { Livre s = selection(); if (s != null) ouvrirFormulaire(s); });
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
                if (e.getClickCount() == 2) { Livre s = selection(); if (s != null) ouvrirFormulaire(s); }
            }
        });

        rafraichir();
    }

    @Override
    public void rafraichir() {
        try {
            data = controller.lister();
            model.setRowCount(0);
            for (Livre l : data) {
                model.addRow(new Object[]{
                        l.getId(), l.getTitre(), l.getAuteur(), l.getGenre(),
                        l.getAnneePublication(),
                        l.getQuantiteDisponible() + " / " + l.getQuantiteTotale()});
            }
        } catch (SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private Livre selection() {
        int row = table.getSelectedRow();
        if (row < 0) {
            UiUtils.info(this, "Veuillez sélectionner un livre dans la liste.");
            return null;
        }
        int id = (int) model.getValueAt(table.convertRowIndexToModel(row), 0);
        for (Livre l : data) if (l.getId() == id) return l;
        return null;
    }

    private void supprimer() {
        Livre l = selection();
        if (l == null) return;
        if (UiUtils.confirmer(this, "Supprimer le livre « " + l.getTitre() + " » ?")) {
            try { controller.supprimer(l.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void ouvrirFormulaire(Livre existant) {
        boolean creation = (existant == null);

        JTextField titre = new JTextField(creation ? "" : existant.getTitre());
        JTextField auteur = new JTextField(creation ? "" : existant.getAuteur());
        JTextField isbn = new JTextField(creation ? "" : nz(existant.getIsbn()));
        JTextField editeur = new JTextField(creation ? "" : nz(existant.getEditeur()));
        JTextField annee = new JTextField(creation || existant.getAnneePublication() == null
                ? "" : String.valueOf(existant.getAnneePublication()));
        JTextField genre = new JTextField(creation ? "" : nz(existant.getGenre()));
        JTextField total = new JTextField(creation ? "1" : String.valueOf(existant.getQuantiteTotale()));
        JTextField dispo = new JTextField(creation ? "1" : String.valueOf(existant.getQuantiteDisponible()));

        JPanel form = new JPanel(new GridLayout(0, 2, 6, 6));
        form.add(new JLabel("Titre *"));               form.add(titre);
        form.add(new JLabel("Auteur *"));              form.add(auteur);
        form.add(new JLabel("ISBN"));                  form.add(isbn);
        form.add(new JLabel("Éditeur"));               form.add(editeur);
        form.add(new JLabel("Année"));                 form.add(annee);
        form.add(new JLabel("Genre"));                 form.add(genre);
        form.add(new JLabel("Quantité totale"));       form.add(total);
        form.add(new JLabel("Exemplaires disponibles")); form.add(dispo);

        int res = JOptionPane.showConfirmDialog(this, form,
                creation ? "Nouveau livre" : "Modifier le livre",
                JOptionPane.OK_CANCEL_OPTION, JOptionPane.PLAIN_MESSAGE);
        if (res != JOptionPane.OK_OPTION) return;

        Livre cible = creation ? new Livre() : existant;
        cible.setTitre(titre.getText().trim());
        cible.setAuteur(auteur.getText().trim());
        cible.setIsbn(isbn.getText().trim());
        cible.setEditeur(editeur.getText().trim());
        cible.setAnneePublication(parseIntOrNull(annee.getText()));
        cible.setGenre(genre.getText().trim());
        cible.setQuantiteTotale(parseIntOr(total.getText(), 1));
        cible.setQuantiteDisponible(parseIntOr(dispo.getText(), 0));

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

    private static Integer parseIntOrNull(String s) {
        try { return Integer.valueOf(s.trim()); }
        catch (NumberFormatException e) { return null; }
    }
}
