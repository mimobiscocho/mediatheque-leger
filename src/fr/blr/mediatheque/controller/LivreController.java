package fr.blr.mediatheque.controller;

import fr.blr.mediatheque.dao.LivreDAO;
import fr.blr.mediatheque.model.Livre;

import java.sql.SQLException;
import java.util.List;

/** Contrôleur des livres. */
public class LivreController {

    private final LivreDAO dao = new LivreDAO();

    public List<Livre> lister() throws SQLException {
        return dao.findAll();
    }

    public List<Livre> listerDisponibles() throws SQLException {
        return dao.findDisponibles();
    }

    public int compter() throws SQLException {
        return dao.count();
    }

    public void enregistrer(Livre l) throws SQLException {
        valider(l);
        if (l.getId() > 0) {
            dao.update(l);
        } else {
            dao.insert(l);
        }
    }

    public void supprimer(int id) throws SQLException {
        dao.delete(id);
    }

    private void valider(Livre l) {
        if (l.getTitre() == null || l.getTitre().trim().isEmpty()) {
            throw new IllegalArgumentException("Le titre est obligatoire.");
        }
        if (l.getAuteur() == null || l.getAuteur().trim().isEmpty()) {
            throw new IllegalArgumentException("L'auteur est obligatoire.");
        }
        if (l.getQuantiteTotale() < 0) {
            throw new IllegalArgumentException("La quantité totale ne peut être négative.");
        }
        // La disponibilité ne peut dépasser le stock total
        if (l.getQuantiteDisponible() > l.getQuantiteTotale()) {
            l.setQuantiteDisponible(l.getQuantiteTotale());
        }
        if (l.getQuantiteDisponible() < 0) {
            l.setQuantiteDisponible(0);
        }
    }
}
