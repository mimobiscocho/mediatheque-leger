<?php
/** Modèle des salles de coworking. */
class Salle extends Model
{
    protected string $table = 'salle';

    public function allSorted(): array
    {
        return $this->db->query("SELECT * FROM salle ORDER BY nom")->fetchAll();
    }

    public function create(array $d): bool
    {
        $sql = "INSERT INTO salle (nom, capacite, equipement, disponible)
                VALUES (:nom, :capacite, :equipement, :disponible)";
        return $this->db->prepare($sql)->execute($d);
    }

    public function update(int $id, array $d): bool
    {
        $d['id'] = $id;
        $sql = "UPDATE salle SET
                    nom = :nom, capacite = :capacite,
                    equipement = :equipement, disponible = :disponible
                WHERE id = :id";
        return $this->db->prepare($sql)->execute($d);
    }
}
