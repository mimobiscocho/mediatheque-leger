package fr.blr.mediatheque.model;

/** Entité métier : type d'abonnement. */
public class Abonnement {
    private int id;
    private String libelle;
    private double tarif;
    private int dureeMois;
    private int quotaEmprunts;

    public Abonnement() { }

    public Abonnement(int id, String libelle) {
        this.id = id;
        this.libelle = libelle;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getLibelle() { return libelle; }
    public void setLibelle(String libelle) { this.libelle = libelle; }

    public double getTarif() { return tarif; }
    public void setTarif(double tarif) { this.tarif = tarif; }

    public int getDureeMois() { return dureeMois; }
    public void setDureeMois(int dureeMois) { this.dureeMois = dureeMois; }

    public int getQuotaEmprunts() { return quotaEmprunts; }
    public void setQuotaEmprunts(int quotaEmprunts) { this.quotaEmprunts = quotaEmprunts; }

    /** Affichage dans les listes déroulantes. */
    @Override
    public String toString() {
        return libelle + " (" + String.format("%.2f", tarif) + " €)";
    }
}
