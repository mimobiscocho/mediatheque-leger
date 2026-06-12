<?php
/** Modèle des réservations de salles de coworking. */
class Reservation extends Model
{
    protected string $table = 'reservation';

    /** Toutes les réservations, enrichies de l'adhérent et de la salle. */
    public function allDetailed(): array
    {
        $sql = "SELECT r.*,
                       CONCAT(a.prenom, ' ', a.nom) AS adherent_nom,
                       s.nom AS salle_nom
                FROM reservation r
                JOIN adherent a ON r.adherent_id = a.id
                JOIN salle s    ON r.salle_id = s.id
                ORDER BY r.date_reservation DESC, r.heure_debut";
        return $this->db->query($sql)->fetchAll();
    }

    /** Nombre de réservations confirmées à venir. */
    public function countActives(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM reservation
             WHERE statut = 'CONFIRMEE' AND date_reservation >= CURDATE()"
        )->fetchColumn();
    }

    /**
     * Enregistre une réservation.
     * Le chevauchement de créneau est contrôlé par le trigger
     * trg_reservation_before_insert (PDOException si conflit).
     */
    public function create(array $d): bool
    {
        $sql = "INSERT INTO reservation
                    (adherent_id, salle_id, date_reservation, heure_debut, heure_fin, statut)
                VALUES
                    (:adherent_id, :salle_id, :date_reservation, :heure_debut, :heure_fin, 'CONFIRMEE')";
        return $this->db->prepare($sql)->execute($d);
    }

    /** Annule une réservation. */
    public function annuler(int $id): bool
    {
        $sql = "UPDATE reservation SET statut = 'ANNULEE' WHERE id = :id";
        return $this->db->prepare($sql)->execute(['id' => $id]);
    }

    /** Toutes les réservations d'un adhérent (espace client), avec la salle. */
    public function byAdherent(int $adherentId): array
    {
        $sql = "SELECT r.*, s.nom AS salle_nom
                FROM reservation r
                JOIN salle s ON r.salle_id = s.id
                WHERE r.adherent_id = :id
                ORDER BY r.date_reservation DESC, r.heure_debut";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $adherentId]);
        return $stmt->fetchAll();
    }

    /**
     * Annule une réservation seulement si elle appartient bien à l'adhérent.
     * Retourne true si la mise à jour a touché une ligne (autorisée).
     */
    public function annulerForAdherent(int $id, int $adherentId): bool
    {
        $sql = "UPDATE reservation
                   SET statut = 'ANNULEE'
                 WHERE id = :id AND adherent_id = :adh AND statut = 'CONFIRMEE'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'adh' => $adherentId]);
        return $stmt->rowCount() > 0;
    }
}
