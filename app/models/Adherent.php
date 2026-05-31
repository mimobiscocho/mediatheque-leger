<?php
/** Modèle des adhérents (membres de la médiathèque). */
class Adherent extends Model
{
    protected string $table = 'adherent';

    /** Liste des adhérents avec le libellé de leur abonnement. */
    public function allWithAbonnement(): array
    {
        $sql = "SELECT a.*, ab.libelle AS abonnement_libelle
                FROM adherent a
                LEFT JOIN abonnement ab ON a.abonnement_id = ab.id
                ORDER BY a.nom, a.prenom";
        return $this->db->query($sql)->fetchAll();
    }

    public function create(array $d): bool
    {
        $sql = "INSERT INTO adherent
                    (nom, prenom, email, telephone, adresse, abonnement_id,
                     date_inscription, date_fin_abonnement, actif)
                VALUES
                    (:nom, :prenom, :email, :telephone, :adresse, :abonnement_id,
                     :date_inscription, :date_fin_abonnement, :actif)";
        return $this->db->prepare($sql)->execute($d);
    }

    public function update(int $id, array $d): bool
    {
        $d['id'] = $id;
        $sql = "UPDATE adherent SET
                    nom = :nom, prenom = :prenom, email = :email,
                    telephone = :telephone, adresse = :adresse,
                    abonnement_id = :abonnement_id,
                    date_inscription = :date_inscription,
                    date_fin_abonnement = :date_fin_abonnement,
                    actif = :actif
                WHERE id = :id";
        return $this->db->prepare($sql)->execute($d);
    }
}
