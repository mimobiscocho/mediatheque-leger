package fr.blr.mediatheque.controller;

import fr.blr.mediatheque.dao.AdherentDAO;
import fr.blr.mediatheque.dao.ReservationDAO;
import fr.blr.mediatheque.dao.SalleDAO;
import fr.blr.mediatheque.model.Adherent;
import fr.blr.mediatheque.model.Reservation;
import fr.blr.mediatheque.model.Salle;

import java.sql.SQLException;
import java.util.List;

/** Contrôleur de la réservation d'espaces (salles de coworking). */
public class ReservationController {

    private final ReservationDAO dao = new ReservationDAO();
    private final AdherentDAO adherentDao = new AdherentDAO();
    private final SalleDAO salleDao = new SalleDAO();

    public List<Reservation> lister() throws SQLException {
        return dao.findAll();
    }

    public int compterActives() throws SQLException {
        return dao.countActives();
    }

    public List<Adherent> listerAdherents() throws SQLException {
        return adherentDao.findAll();
    }

    public List<Salle> listerSalles() throws SQLException {
        return salleDao.findAll();
    }

    /**
     * Enregistre une réservation. Les conflits de créneau sont contrôlés par
     * le trigger en base (SQLException remontée en cas de chevauchement).
     */
    public void enregistrer(Reservation r) throws SQLException {
        if (r.getAdherentId() <= 0) {
            throw new IllegalArgumentException("Veuillez sélectionner un adhérent.");
        }
        if (r.getSalleId() <= 0) {
            throw new IllegalArgumentException("Veuillez sélectionner une salle.");
        }
        if (r.getHeureDebut() == null || r.getHeureFin() == null
                || r.getHeureFin().compareTo(r.getHeureDebut()) <= 0) {
            throw new IllegalArgumentException("L'heure de fin doit être postérieure à l'heure de début.");
        }
        dao.insert(r);
    }

    public void annuler(int id) throws SQLException {
        dao.annuler(id);
    }

    public void supprimer(int id) throws SQLException {
        dao.delete(id);
    }
}
