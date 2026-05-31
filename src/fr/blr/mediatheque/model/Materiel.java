package fr.blr.mediatheque.model;

/** Entité métier : matériel empruntable (exemplaire unique). */
public class Materiel {
    private int id;
    private String nom;
    private String categorie;
    private String description;
    private String etat = "bon";   // neuf | bon | use | hors_service
    private boolean disponible = true;

    public Materiel() { }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getCategorie() { return categorie; }
    public void setCategorie(String categorie) { this.categorie = categorie; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getEtat() { return etat; }
    public void setEtat(String etat) { this.etat = etat; }

    public boolean isDisponible() { return disponible; }
    public void setDisponible(boolean disponible) { this.disponible = disponible; }

    @Override
    public String toString() { return nom; }
}
