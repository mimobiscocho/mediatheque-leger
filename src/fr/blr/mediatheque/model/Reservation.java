package fr.blr.mediatheque.model;

/** Entité métier : réservation d'une salle de coworking. */
public class Reservation {
    private int id;
    private int adherentId;
    private int salleId;
    private String dateReservation;   // yyyy-MM-dd
    private String heureDebut;        // HH:mm
    private String heureFin;          // HH:mm
    private String statut = "confirmee"; // confirmee | annulee | terminee

    // Champs d'affichage (jointures)
    private String adherentNom;
    private String salleNom;

    public Reservation() { }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getAdherentId() { return adherentId; }
    public void setAdherentId(int adherentId) { this.adherentId = adherentId; }

    public int getSalleId() { return salleId; }
    public void setSalleId(int salleId) { this.salleId = salleId; }

    public String getDateReservation() { return dateReservation; }
    public void setDateReservation(String dateReservation) { this.dateReservation = dateReservation; }

    public String getHeureDebut() { return heureDebut; }
    public void setHeureDebut(String heureDebut) { this.heureDebut = heureDebut; }

    public String getHeureFin() { return heureFin; }
    public void setHeureFin(String heureFin) { this.heureFin = heureFin; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    public String getAdherentNom() { return adherentNom; }
    public void setAdherentNom(String adherentNom) { this.adherentNom = adherentNom; }

    public String getSalleNom() { return salleNom; }
    public void setSalleNom(String salleNom) { this.salleNom = salleNom; }
}
