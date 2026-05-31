<?php
/** Modèle des livres (collection). */
class Livre extends Model
{
    protected string $table = 'livre';

    public function allSorted(): array
    {
        return $this->db->query("SELECT * FROM livre ORDER BY titre")->fetchAll();
    }

    /** Livres ayant au moins un exemplaire disponible (pour les prêts). */
    public function disponibles(): array
    {
        return $this->db->query(
            "SELECT * FROM livre WHERE quantite_disponible > 0 ORDER BY titre"
        )->fetchAll();
    }

    /**
     * Recherche multicritères (requête paramétrée, anti-injection).
     * Critères acceptés : q (titre/auteur/ISBN), genre, dispo ('1'|'0').
     * Un tableau vide retourne tous les livres.
     */
    public function filter(array $c): array
    {
        $sql    = "SELECT * FROM livre WHERE 1 = 1";
        $params = [];

        if (!empty($c['q'])) {
            $sql .= " AND (titre LIKE :q OR auteur LIKE :q OR isbn LIKE :q)";
            $params['q'] = '%' . $c['q'] . '%';
        }
        if (!empty($c['genre'])) {
            $sql .= " AND genre = :genre";
            $params['genre'] = $c['genre'];
        }
        if (($c['dispo'] ?? '') === '1') {
            $sql .= " AND quantite_disponible > 0";
        } elseif (($c['dispo'] ?? '') === '0') {
            $sql .= " AND quantite_disponible = 0";
        }

        $sql .= " ORDER BY titre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Liste des genres distincts présents en base (pour le filtre). */
    public function distinctGenres(): array
    {
        return $this->db->query(
            "SELECT DISTINCT genre FROM livre
             WHERE genre IS NOT NULL AND genre <> '' ORDER BY genre"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(array $d): bool
    {
        $sql = "INSERT INTO livre
                    (titre, auteur, isbn, editeur, annee_publication, genre,
                     quantite_totale, quantite_disponible)
                VALUES
                    (:titre, :auteur, :isbn, :editeur, :annee_publication, :genre,
                     :quantite_totale, :quantite_disponible)";
        return $this->db->prepare($sql)->execute($d);
    }

    public function update(int $id, array $d): bool
    {
        $d['id'] = $id;
        $sql = "UPDATE livre SET
                    titre = :titre, auteur = :auteur, isbn = :isbn,
                    editeur = :editeur, annee_publication = :annee_publication,
                    genre = :genre, quantite_totale = :quantite_totale,
                    quantite_disponible = :quantite_disponible
                WHERE id = :id";
        return $this->db->prepare($sql)->execute($d);
    }
}
