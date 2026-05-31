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
