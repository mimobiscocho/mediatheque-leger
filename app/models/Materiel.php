<?php
/** Modèle des matériels empruntables. */
class Materiel extends Model
{
    protected string $table = 'materiel';

    public function allSorted(): array
    {
        return $this->db->query("SELECT * FROM materiel ORDER BY nom")->fetchAll();
    }

    /** Matériels actuellement disponibles (pour les prêts). */
    public function disponibles(): array
    {
        return $this->db->query(
            "SELECT * FROM materiel WHERE disponible = 1 AND etat <> 'hors_service' ORDER BY nom"
        )->fetchAll();
    }

    public function create(array $d): bool
    {
        $sql = "INSERT INTO materiel (nom, categorie, description, etat, disponible)
                VALUES (:nom, :categorie, :description, :etat, :disponible)";
        return $this->db->prepare($sql)->execute($d);
    }

    public function update(int $id, array $d): bool
    {
        $d['id'] = $id;
        $sql = "UPDATE materiel SET
                    nom = :nom, categorie = :categorie, description = :description,
                    etat = :etat, disponible = :disponible
                WHERE id = :id";
        return $this->db->prepare($sql)->execute($d);
    }
}
