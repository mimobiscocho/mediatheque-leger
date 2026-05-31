package fr.blr.mediatheque.view;

import fr.blr.mediatheque.controller.PretController;
import fr.blr.mediatheque.model.Adherent;
import fr.blr.mediatheque.model.Livre;
import fr.blr.mediatheque.model.Materiel;
import fr.blr.mediatheque.model.Pret;

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
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

/** Module du système de prêts (emprunt et retour de livres/matériels). */
public class PretPanel extends JPanel implements Refreshable {

    private final PretController controller = new PretController();
    private final DefaultTableModel model;
    private final JTable table;
    private final TableRowSorter<DefaultTableModel> sorter;
    private List<Pret> data = new ArrayList<>();

    public PretPanel() {
        setLayout(new BorderLayout(8, 8));
        setBorder(new EmptyBorder(10, 10, 10, 10));

        JButton btnNouveau = new JButton("Nouveau prêt");
        JButton btnRetour = new JButton("Enregistrer le retour");
        JButton btnSupprimer = new JButton("Supprimer");
        JButton btnRafraichir = new JButton("Rafraîchir");
        JTextField recherche = new JTextField(16);

        JPanel barre = new JPanel(new FlowLayout(FlowLayout.LEFT, 6, 4));
        barre.add(btnNouveau);
        barre.add(btnRetour);
        barre.add(btnSupprimer);
        barre.add(btnRafraichir);
        barre.add(new JLabel("    Rechercher :"));
        barre.add(recherche);
        add(barre, BorderLayout.NORTH);

        model = new DefaultTableModel(new Object[]{
                "ID", "Adhérent", "Produit", "Type", "Emprunt", "Retour prévu", "Statut"}, 0) {
            @Override public boolean isCellEditable(int r, int c) { return false; }
        };
        table = new JTable(model);
        table.setRowHeight(24);
        table.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        table.getColumnModel().getColumn(0).setMaxWidth(50);
        sorter = new TableRowSorter<>(model);
        table.setRowSorter(sorter);
        add(new JScrollPane(table), BorderLayout.CENTER);

        btnNouveau.addActionListener(e -> ouvrirFormulaire());
        btnRetour.addActionListener(e -> enregistrerRetour());
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
            String today = LocalDate.now().toString();
            model.setRowCount(0);
            for (Pret p : data) {
                String statut;
                if (p.isRendu()) {
                    statut = "Rendu le " + p.getDateRetourEffective();
                } else if (p.getDateRetourPrevue() != null && p.getDateRetourPrevue().compareTo(today) < 0) {
                    statut = "En retard";
                } else {
                    statut = "En cours";
                }
                model.addRow(new Object[]{
                        p.getId(), p.getAdherentNom(), p.getProduit(), p.getType(),
                        p.getDatePret(), p.getDateRetourPrevue(), statut});
            }
        } catch (SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private Pret selection() {
        int row = table.getSelectedRow();
        if (row < 0) {
            UiUtils.info(this, "Veuillez sélectionner un prêt dans la liste.");
            return null;
        }
        int id = (int) model.getValueAt(table.convertRowIndexToModel(row), 0);
        for (Pret p : data) if (p.getId() == id) return p;
        return null;
    }

    private void enregistrerRetour() {
        Pret p = selection();
        if (p == null) return;
        if (p.isRendu()) {
            UiUtils.info(this, "Ce prêt a déjà été rendu.");
            return;
        }
        if (UiUtils.confirmer(this, "Confirmer le retour de « " + p.getProduit() + " » ?")) {
            try { controller.enregistrerRetour(p.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void supprimer() {
        Pret p = selection();
        if (p == null) return;
        if (UiUtils.confirmer(this, "Supprimer ce prêt ?")) {
            try { controller.supprimer(p.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void ouvrirFormulaire() {
        JComboBox<Adherent> cbAdherent = new JComboBox<>();
        JComboBox<Object> cbProduit = new JComboBox<>();
        cbProduit.addItem(null);
        try {
            for (Adherent a : controller.listerAdherents()) cbAdherent.addItem(a);
            for (Livre l : controller.listerLivresDisponibles()) cbProduit.addItem(l);
            for (Materiel m : controller.listerMaterielsDisponibles()) cbProduit.addItem(m);
        } catch (SQLException ex) { UiUtils.erreur(this, ex); return; }

        cbProduit.setRenderer(new DefaultListCellRenderer() {
            public Component getListCellRendererComponent(JList<?> l, Object v, int i, boolean s, boolean f) {
                super.getListCellRendererComponent(l, v, i, s, f);
                if (v instanceof Livre livre) setText("[Livre] " + livre);
                else if (v instanceof Materiel mat) setText("[Matériel] " + mat);
                else setText("— Choisir un produit —");
                return this;
            }
        });

        JTextField datePret = new JTextField(LocalDate.now().toString());
        JTextField dateRetour = new JTextField(LocalDate.now().plusDays(14).toString());

        JPanel form = new JPanel(new GridLayout(0, 2, 6, 6));
        form.add(new JLabel("Adhérent *"));                 form.add(cbAdherent);
        form.add(new JLabel("Produit à emprunter *"));      form.add(cbProduit);
        form.add(new JLabel("Date du prêt (AAAA-MM-JJ)"));  form.add(datePret);
        form.add(new JLabel("Retour prévu (AAAA-MM-JJ)"));  form.add(dateRetour);

        int res = JOptionPane.showConfirmDialog(this, form, "Nouveau prêt",
                JOptionPane.OK_CANCEL_OPTION, JOptionPane.PLAIN_MESSAGE);
        if (res != JOptionPane.OK_OPTION) return;

        Adherent a = (Adherent) cbAdherent.getSelectedItem();
        Object produit = cbProduit.getSelectedItem();
        if (a == null || produit == null) {
            UiUtils.info(this, "Veuillez sélectionner un adhérent et un produit.");
            return;
        }

        Pret p = new Pret();
        p.setAdherentId(a.getId());
        if (produit instanceof Livre livre) p.setLivreId(livre.getId());
        else if (produit instanceof Materiel mat) p.setMaterielId(mat.getId());
        p.setDatePret(datePret.getText().trim());
        p.setDateRetourPrevue(dateRetour.getText().trim());

        try {
            controller.enregistrer(p);
            rafraichir();
        } catch (IllegalArgumentException | SQLException ex) {
            // Inclut le refus remonté par le trigger si le produit est indisponible
            UiUtils.erreur(this, ex);
        }
    }
}
