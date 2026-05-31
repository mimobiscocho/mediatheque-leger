package fr.blr.mediatheque.model;

/** Entité métier : prêt d'un livre OU d'un matériel à un adhérent. */
public class Pret {
    private int id;
    private int adherentId;
    private Integer livreId;             // null si prêt de matériel
    private Integer materielId;          // null si prêt de livre
    private String datePret;             // yyyy-MM-dd
    private String dateRetourPrevue;     // yyyy-MM-dd
    private String dateRetourEffective;  // null tant que non rendu
    private String statut = "en_cours";  // en_cours | rendu | en_retard

    // Champs d'affichage (jointures)
    private String adherentNom;
    private String livreTitre;
    private String materielNom;

    public Pret() { }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getAdherentId() { return adherentId; }
    public void setAdherentId(int adherentId) { this.adherentId = adherentId; }

    public Integer getLivreId() { return livreId; }
    public void setLivreId(Integer livreId) { this.livreId = livreId; }

    public Integer getMaterielId() { return materielId; }
    public void setMaterielId(Integer materielId) { this.materielId = materielId; }

    public String getDatePret() { return datePret; }
    public void setDatePret(String datePret) { this.datePret = datePret; }

    public String getDateRetourPrevue() { return dateRetourPrevue; }
    public void setDateRetourPrevue(String dateRetourPrevue) { this.dateRetourPrevue = dateRetourPrevue; }

    public String getDateRetourEffective() { return dateRetourEffective; }
    public void setDateRetourEffective(String dateRetourEffective) { this.dateRetourEffective = dateRetourEffective; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    public String getAdherentNom() { return adherentNom; }
    public void setAdherentNom(String adherentNom) { this.adherentNom = adherentNom; }

    public String getLivreTitre() { return livreTitre; }
    public void setLivreTitre(String livreTitre) { this.livreTitre = livreTitre; }

    public String getMaterielNom() { return materielNom; }
    public void setMaterielNom(String materielNom) { this.materielNom = materielNom; }

    /** Libellé du produit emprunté (livre ou matériel). */
    public String getProduit() {
        return livreId != null ? livreTitre : materielNom;
    }

    /** Type de produit pour l'affichage. */
    public String getType() {
        return livreId != null ? "Livre" : "Matériel";
    }

    public boolean isRendu() {
        return dateRetourEffective != null && !dateRetourEffective.isEmpty();
    }
}
