package fr.blr.mediatheque.controller;

import fr.blr.mediatheque.dao.MaterielDAO;
import fr.blr.mediatheque.model.Materiel;

import java.sql.SQLException;
import java.util.Arrays;
import java.util.List;

/** Contrôleur des matériels. */
public class MaterielController {

    public static final String[] ETATS = {"neuf", "bon", "use", "hors_service"};

    private final MaterielDAO dao = new MaterielDAO();

    public List<Materiel> lister() throws SQLException {
        return dao.findAll();
    }

    public List<Materiel> listerDisponibles() throws SQLException {
        return dao.findDisponibles();
    }

    public int compter() throws SQLException {
        return dao.count();
    }

    public void enregistrer(Materiel m) throws SQLException {
        valider(m);
        if (m.getId() > 0) {
            dao.update(m);
        } else {
            dao.insert(m);
        }
    }

    public void supprimer(int id) throws SQLException {
        dao.delete(id);
    }

    private void valider(Materiel m) {
        if (m.getNom() == null || m.getNom().trim().isEmpty()) {
            throw new IllegalArgumentException("Le nom du matériel est obligatoire.");
        }
        if (m.getEtat() == null || !Arrays.asList(ETATS).contains(m.getEtat())) {
            m.setEtat("bon");
        }
    }
}
