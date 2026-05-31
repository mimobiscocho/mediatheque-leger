package fr.blr.mediatheque.controller;

import fr.blr.mediatheque.dao.AdherentDAO;
import fr.blr.mediatheque.dao.LivreDAO;
import fr.blr.mediatheque.dao.MaterielDAO;
import fr.blr.mediatheque.dao.PretDAO;
import fr.blr.mediatheque.model.Adherent;
import fr.blr.mediatheque.model.Livre;
import fr.blr.mediatheque.model.Materiel;
import fr.blr.mediatheque.model.Pret;

import java.sql.SQLException;
import java.util.List;

/** Contrôleur du système de prêts. */
public class PretController {

    private final PretDAO dao = new PretDAO();
    private final AdherentDAO adherentDao = new AdherentDAO();
    private final LivreDAO livreDao = new LivreDAO();
    private final MaterielDAO materielDao = new MaterielDAO();

    public List<Pret> lister() throws SQLException {
        return dao.findAll();
    }

    public List<Pret> listerEnRetard() throws SQLException {
        return dao.findEnRetard();
    }

    public int compterEnCours() throws SQLException {
        return dao.countEnCours();
    }

    // Sources pour les listes déroulantes du formulaire de prêt
    public List<Adherent> listerAdherents() throws SQLException {
        return adherentDao.findAll();
    }

    public List<Livre> listerLivresDisponibles() throws SQLException {
        return livreDao.findDisponibles();
    }

    public List<Materiel> listerMaterielsDisponibles() throws SQLException {
        return materielDao.findDisponibles();
    }

    /**
     * Enregistre un prêt. La disponibilité réelle est vérifiée par le trigger
     * en base : une SQLException est levée (et remontée) si le produit n'est
     * pas disponible.
     */
    public void enregistrer(Pret p) throws SQLException {
        if (p.getAdherentId() <= 0) {
            throw new IllegalArgumentException("Veuillez sélectionner un adhérent.");
        }
        boolean unLivre    = p.getLivreId() != null;
        boolean unMateriel = p.getMaterielId() != null;
        if (unLivre == unMateriel) { // ni l'un ni l'autre, ou les deux
            throw new IllegalArgumentException("Veuillez sélectionner un produit à emprunter.");
        }
        if (p.getDatePret() == null || p.getDateRetourPrevue() == null) {
            throw new IllegalArgumentException("Les dates de prêt et de retour sont obligatoires.");
        }
        dao.insert(p);
    }

    public void enregistrerRetour(int id) throws SQLException {
        dao.retour(id);
    }

    public void supprimer(int id) throws SQLException {
        dao.delete(id);
    }
}
