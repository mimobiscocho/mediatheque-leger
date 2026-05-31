<?php
/** Modèle des types d'abonnement. */
class Abonnement extends Model
{
    protected string $table = 'abonnement';

    public function allSorted(): array
    {
        return $this->db->query("SELECT * FROM abonnement ORDER BY tarif")->fetchAll();
    }
}
