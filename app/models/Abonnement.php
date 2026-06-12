<?php
/**
 * Modèle des types d'abonnement (Découverte, Étudiant, Standard...).
 * Hérite de Model pour find(), delete(), count().
 */
class Abonnement extends Model
{
    protected string $table = 'abonnement';

    /** Tous les abonnements, du moins cher au plus cher. */
    public function allSorted(): array
    {
        return $this->db->query("SELECT * FROM abonnement ORDER BY tarif")->fetchAll();
    }

    /** Crée un abonnement (requête préparée : anti-injection SQL). */
    public function create(array $d): bool
    {
        $sql = "INSERT INTO abonnement (libelle, tarif, duree_mois, quota_emprunts)
                VALUES (:libelle, :tarif, :duree_mois, :quota_emprunts)";
        return $this->db->prepare($sql)->execute($d);
    }

    /** Met à jour un abonnement existant. */
    public function update(int $id, array $d): bool
    {
        $d['id'] = $id;
        $sql = "UPDATE abonnement SET
                    libelle = :libelle, tarif = :tarif,
                    duree_mois = :duree_mois, quota_emprunts = :quota_emprunts
                WHERE id = :id";
        return $this->db->prepare($sql)->execute($d);
    }
}
