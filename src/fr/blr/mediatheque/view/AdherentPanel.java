package fr.blr.mediatheque.view;

import fr.blr.mediatheque.controller.AdherentController;
import fr.blr.mediatheque.model.Abonnement;
import fr.blr.mediatheque.model.Adherent;

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

/** Module de gestion des adhérents (liste + formulaire CRUD). */
public class AdherentPanel extends JPanel implements Refreshable {

    private final AdherentController controller = new AdherentController();
    private final DefaultTableModel model;
    private final JTable table;
    private final TableRowSorter<DefaultTableModel> sorter;
    private List<Adherent> data = new ArrayList<>();

    public AdherentPanel() {
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
                "ID", "Nom", "Prénom", "Email", "Téléphone", "Abonnement", "Inscription", "Actif"}, 0) {
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
        btnModifier.addActionListener(e -> { Adherent s = selection(); if (s != null) ouvrirFormulaire(s); });
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
                if (e.getClickCount() == 2) { Adherent s = selection(); if (s != null) ouvrirFormulaire(s); }
            }
        });

        rafraichir();
    }

    @Override
    public void rafraichir() {
        try {
            data = controller.lister();
            model.setRowCount(0);
            for (Adherent a : data) {
                model.addRow(new Object[]{
                        a.getId(), a.getNom(), a.getPrenom(), a.getEmail(),
                        a.getTelephone(), a.getAbonnementLibelle(),
                        a.getDateInscription(), a.isActif() ? "Oui" : "Non"});
            }
        } catch (SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private Adherent selection() {
        int row = table.getSelectedRow();
        if (row < 0) {
            UiUtils.info(this, "Veuillez sélectionner un adhérent dans la liste.");
            return null;
        }
        int id = (int) model.getValueAt(table.convertRowIndexToModel(row), 0);
        for (Adherent a : data) if (a.getId() == id) return a;
        return null;
    }

    private void supprimer() {
        Adherent a = selection();
        if (a == null) return;
        if (UiUtils.confirmer(this, "Supprimer l'adhérent « " + a.getNomComplet() + " » ?")) {
            try { controller.supprimer(a.getId()); rafraichir(); }
            catch (SQLException ex) { UiUtils.erreur(this, ex); }
        }
    }

    private void ouvrirFormulaire(Adherent existant) {
        boolean creation = (existant == null);

        JTextField nom = new JTextField(creation ? "" : existant.getNom());
        JTextField prenom = new JTextField(creation ? "" : existant.getPrenom());
        JTextField email = new JTextField(creation ? "" : existant.getEmail());
        JTextField tel = new JTextField(creation ? "" : nz(existant.getTelephone()));
        JTextField adresse = new JTextField(creation ? "" : nz(existant.getAdresse()));
        JTextField dateInscription = new JTextField(creation
                ? java.time.LocalDate.now().toString() : nz(existant.getDateInscription()));
        JTextField dateFin = new JTextField(creation ? "" : nz(existant.getDateFinAbonnement()));
        JCheckBox actif = new JCheckBox("Adhérent actif", creation || existant.isActif());

        JComboBox<Abonnement> cbAbo = new JComboBox<>();
        cbAbo.addItem(null);
        try {
            for (Abonnement ab : controller.listerAbonnements()) {
                cbAbo.addItem(ab);
                if (!creation && existant.getAbonnementId() != null
                        && existant.getAbonnementId() == ab.getId()) {
                    cbAbo.setSelectedItem(ab);
                }
            }
        } catch (SQLException ex) { UiUtils.erreur(this, ex); return; }
        cbAbo.setRenderer(new DefaultListCellRenderer() {
            public Component getListCellRendererComponent(JList<?> l, Object v, int i, boolean s, boolean f) {
                super.getListCellRendererComponent(l, v, i, s, f);
                setText(v == null ? "— Aucun —" : v.toString());
                return this;
            }
        });

        JPanel form = new JPanel(new GridLayout(0, 2, 6, 6));
        form.add(new JLabel("Nom *"));                       form.add(nom);
        form.add(new JLabel("Prénom *"));                    form.add(prenom);
        form.add(new JLabel("Email *"));                     form.add(email);
        form.add(new JLabel("Téléphone"));                   form.add(tel);
        form.add(new JLabel("Adresse"));                     form.add(adresse);
        form.add(new JLabel("Abonnement"));                  form.add(cbAbo);
        form.add(new JLabel("Inscription (AAAA-MM-JJ)"));    form.add(dateInscription);
        form.add(new JLabel("Fin abonnement (AAAA-MM-JJ)")); form.add(dateFin);
        form.add(new JLabel(""));                            form.add(actif);

        int res = JOptionPane.showConfirmDialog(this, form,
                creation ? "Nouvel adhérent" : "Modifier l'adhérent",
                JOptionPane.OK_CANCEL_OPTION, JOptionPane.PLAIN_MESSAGE);
        if (res != JOptionPane.OK_OPTION) return;

        Adherent cible = creation ? new Adherent() : existant;
        cible.setNom(nom.getText().trim());
        cible.setPrenom(prenom.getText().trim());
        cible.setEmail(email.getText().trim());
        cible.setTelephone(tel.getText().trim());
        cible.setAdresse(adresse.getText().trim());
        Abonnement abo = (Abonnement) cbAbo.getSelectedItem();
        cible.setAbonnementId(abo == null ? null : abo.getId());
        cible.setDateInscription(vide(dateInscription.getText())
                ? java.time.LocalDate.now().toString() : dateInscription.getText().trim());
        cible.setDateFinAbonnement(vide(dateFin.getText()) ? null : dateFin.getText().trim());
        cible.setActif(actif.isSelected());

        try {
            controller.enregistrer(cible);
            rafraichir();
        } catch (IllegalArgumentException | SQLException ex) {
            UiUtils.erreur(this, ex);
        }
    }

    private static String nz(String s) { return s == null ? "" : s; }
    private static boolean vide(String s) { return s == null || s.trim().isEmpty(); }
}
