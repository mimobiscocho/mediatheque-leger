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
        $sql = "INSERT INTO salle (nom, capacite, equipements, disponible)
                VALUES (:nom, :capacite, :equipements, :disponible)";
        return $this->db->prepare($sql)->execute($d);
    }

    public function update(int $id, array $d): bool
    {
        $d['id'] = $id;
        $sql = "UPDATE salle SET
                    nom = :nom, capacite = :capacite,
                    equipements = :equipements, disponible = :disponible
                WHERE id = :id";
        return $this->db->prepare($sql)->execute($d);
    }
}
