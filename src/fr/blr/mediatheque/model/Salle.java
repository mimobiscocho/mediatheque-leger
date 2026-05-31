package fr.blr.mediatheque.model;

/** Entité métier : salle de coworking réservable. */
public class Salle {
    private int id;
    private String nom;
    private int capacite = 1;
    private String equipements;
    private boolean disponible = true;

    public Salle() { }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public int getCapacite() { return capacite; }
    public void setCapacite(int capacite) { this.capacite = capacite; }

    public String getEquipements() { return equipements; }
    public void setEquipements(String equipements) { this.equipements = equipements; }

    public boolean isDisponible() { return disponible; }
    public void setDisponible(boolean disponible) { this.disponible = disponible; }

    @Override
    public String toString() { return nom + " (" + capacite + " pers.)"; }
}
