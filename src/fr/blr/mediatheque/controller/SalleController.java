package fr.blr.mediatheque.controller;

import fr.blr.mediatheque.dao.SalleDAO;
import fr.blr.mediatheque.model.Salle;

import java.sql.SQLException;
import java.util.List;

/** Contrôleur des salles de coworking. */
public class SalleController {

    private final SalleDAO dao = new SalleDAO();

    public List<Salle> lister() throws SQLException {
        return dao.findAll();
    }

    public int compter() throws SQLException {
        return dao.count();
    }

    public void enregistrer(Salle s) throws SQLException {
        valider(s);
        if (s.getId() > 0) {
            dao.update(s);
        } else {
            dao.insert(s);
        }
    }

    public void supprimer(int id) throws SQLException {
        dao.delete(id);
    }

    private void valider(Salle s) {
        if (s.getNom() == null || s.getNom().trim().isEmpty()) {
            throw new IllegalArgumentException("Le nom de la salle est obligatoire.");
        }
        if (s.getCapacite() < 1) {
            s.setCapacite(1);
        }
    }
}
