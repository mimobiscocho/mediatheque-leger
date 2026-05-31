package fr.blr.mediatheque.model;

/** Entité métier : adhérent (membre de la médiathèque). */
public class Adherent {
    private int id;
    private String nom;
    private String prenom;
    private String email;
    private String telephone;
    private String adresse;
    private Integer abonnementId;        // null si aucun abonnement
    private String dateInscription;      // format ISO : yyyy-MM-dd
    private String dateFinAbonnement;    // peut être null
    private boolean actif = true;
    private String abonnementLibelle;    // champ d'affichage (jointure)

    public Adherent() { }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getPrenom() { return prenom; }
    public void setPrenom(String prenom) { this.prenom = prenom; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getTelephone() { return telephone; }
    public void setTelephone(String telephone) { this.telephone = telephone; }

    public String getAdresse() { return adresse; }
    public void setAdresse(String adresse) { this.adresse = adresse; }

    public Integer getAbonnementId() { return abonnementId; }
    public void setAbonnementId(Integer abonnementId) { this.abonnementId = abonnementId; }

    public String getDateInscription() { return dateInscription; }
    public void setDateInscription(String dateInscription) { this.dateInscription = dateInscription; }

    public String getDateFinAbonnement() { return dateFinAbonnement; }
    public void setDateFinAbonnement(String dateFinAbonnement) { this.dateFinAbonnement = dateFinAbonnement; }

    public boolean isActif() { return actif; }
    public void setActif(boolean actif) { this.actif = actif; }

    public String getAbonnementLibelle() { return abonnementLibelle; }
    public void setAbonnementLibelle(String abonnementLibelle) { this.abonnementLibelle = abonnementLibelle; }

    public String getNomComplet() { return prenom + " " + nom; }

    @Override
    public String toString() { return getNomComplet(); }
}
