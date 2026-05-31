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

    /**
     * Recherche multicritères (requête paramétrée, anti-injection).
     * Critères acceptés : q (nom/description), categorie, etat, dispo ('1'|'0').
     * Un tableau vide retourne tous les matériels.
     */
    public function filter(array $c): array
    {
        $sql    = "SELECT * FROM materiel WHERE 1 = 1";
        $params = [];

        if (!empty($c['q'])) {
            $sql .= " AND (nom LIKE :q OR description LIKE :q)";
            $params['q'] = '%' . $c['q'] . '%';
        }
        if (!empty($c['categorie'])) {
            $sql .= " AND categorie = :categorie";
            $params['categorie'] = $c['categorie'];
        }
        if (!empty($c['etat'])) {
            $sql .= " AND etat = :etat";
            $params['etat'] = $c['etat'];
        }
        if (($c['dispo'] ?? '') === '1') {
            $sql .= " AND disponible = 1";
        } elseif (($c['dispo'] ?? '') === '0') {
            $sql .= " AND disponible = 0";
        }

        $sql .= " ORDER BY nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Liste des catégories distinctes présentes en base (pour le filtre). */
    public function distinctCategories(): array
    {
        return $this->db->query(
            "SELECT DISTINCT categorie FROM materiel
             WHERE categorie IS NOT NULL AND categorie <> '' ORDER BY categorie"
        )->fetchAll(PDO::FETCH_COLUMN);
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
