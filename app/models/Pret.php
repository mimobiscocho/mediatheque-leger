<?php
/** Modèle des prêts (livres et matériels). */
class Pret extends Model
{
    protected string $table = 'pret';

    /** Tous les prêts, enrichis du nom de l'adhérent et du produit. */
    public function allDetailed(): array
    {
        $sql = "SELECT p.*,
                       CONCAT(a.prenom, ' ', a.nom) AS adherent_nom,
                       l.titre AS livre_titre,
                       m.nom   AS materiel_nom
                FROM pret p
                JOIN adherent a ON p.adherent_id = a.id
                LEFT JOIN livre l    ON p.livre_id = l.id
                LEFT JOIN materiel m ON p.materiel_id = m.id
                ORDER BY p.date_pret DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Nombre de prêts non encore rendus. */
    public function countEnCours(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM pret WHERE date_retour_effective IS NULL"
        )->fetchColumn();
    }

    /** Prêts en retard : non rendus et dont la date prévue est dépassée. */
    public function enRetard(): array
    {
        $sql = "SELECT p.*,
                       CONCAT(a.prenom, ' ', a.nom) AS adherent_nom,
                       l.titre AS livre_titre,
                       m.nom   AS materiel_nom
                FROM pret p
                JOIN adherent a ON p.adherent_id = a.id
                LEFT JOIN livre l    ON p.livre_id = l.id
                LEFT JOIN materiel m ON p.materiel_id = m.id
                WHERE p.date_retour_effective IS NULL
                  AND p.date_retour_prevue < CURDATE()
                ORDER BY p.date_retour_prevue";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Enregistre un nouveau prêt.
     * La disponibilité est vérifiée par le trigger trg_pret_before_insert
     * (une PDOException est levée si le produit est indisponible).
     */
    public function create(array $d): bool
    {
        $sql = "INSERT INTO pret
                    (adherent_id, livre_id, materiel_id, date_pret, date_retour_prevue, statut)
                VALUES
                    (:adherent_id, :livre_id, :materiel_id, :date_pret, :date_retour_prevue, 'en_cours')";
        return $this->db->prepare($sql)->execute($d);
    }

    /** Enregistre le retour d'un prêt (le trigger ré-incrémente le stock). */
    public function retour(int $id): bool
    {
        $sql = "UPDATE pret
                SET date_retour_effective = CURDATE(), statut = 'rendu'
                WHERE id = :id AND date_retour_effective IS NULL";
        return $this->db->prepare($sql)->execute(['id' => $id]);
    }
}
