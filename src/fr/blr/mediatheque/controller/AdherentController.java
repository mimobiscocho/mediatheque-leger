package fr.blr.mediatheque.controller;

import fr.blr.mediatheque.dao.AbonnementDAO;
import fr.blr.mediatheque.dao.AdherentDAO;
import fr.blr.mediatheque.model.Abonnement;
import fr.blr.mediatheque.model.Adherent;

import java.sql.SQLException;
import java.util.List;

/** Contrôleur des adhérents : orchestration DAO + validation métier. */
public class AdherentController {

    private final AdherentDAO dao = new AdherentDAO();
    private final AbonnementDAO abonnementDao = new AbonnementDAO();

    public List<Adherent> lister() throws SQLException {
        return dao.findAll();
    }

    public List<Abonnement> listerAbonnements() throws SQLException {
        return abonnementDao.findAll();
    }

    public int compter() throws SQLException {
        return dao.count();
    }

    /** Insère ou met à jour selon que l'adhérent possède déjà un identifiant. */
    public void enregistrer(Adherent a) throws SQLException {
        valider(a);
        if (a.getId() > 0) {
            dao.update(a);
        } else {
            dao.insert(a);
        }
    }

    public void supprimer(int id) throws SQLException {
        dao.delete(id);
    }

    private void valider(Adherent a) {
        if (estVide(a.getNom()))    throw new IllegalArgumentException("Le nom est obligatoire.");
        if (estVide(a.getPrenom())) throw new IllegalArgumentException("Le prénom est obligatoire.");
        if (estVide(a.getEmail()))  throw new IllegalArgumentException("L'email est obligatoire.");
    }

    private boolean estVide(String s) {
        return s == null || s.trim().isEmpty();
    }
}
