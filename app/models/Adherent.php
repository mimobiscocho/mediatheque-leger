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

    /* =========================================================
     *  COMPTES CLIENTS — espace personnel adhérent
     * =========================================================*/

    /** Recherche un adhérent par son email (identifiant de connexion). */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM adherent WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Crée un compte client : insère l'adhérent et stocke directement
     * son mot de passe haché en bcrypt (jamais en clair en base).
     */
    public function createAccount(array $d): int
    {
        $sql = "INSERT INTO adherent
                    (nom, prenom, email, telephone, adresse, mot_de_passe,
                     type_abonnement, date_inscription, actif)
                VALUES
                    (:nom, :prenom, :email, :telephone, :adresse, :mot_de_passe,
                     'STANDARD', CURRENT_DATE, 1)";
        $params = [
            'nom'          => $d['nom'],
            'prenom'       => $d['prenom'],
            'email'        => $d['email'],
            'telephone'    => $d['telephone']    ?? null,
            'adresse'      => $d['adresse']      ?? null,
            'mot_de_passe' => password_hash($d['mot_de_passe'], PASSWORD_DEFAULT),
        ];
        $this->db->prepare($sql)->execute($params);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Active un compte client sur un adhérent existant (créé par un agent)
     * qui n'avait pas encore de mot de passe.
     */
    public function setPassword(int $id, string $motDePasse): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE adherent SET mot_de_passe = :mdp WHERE id = :id"
        );
        return $stmt->execute([
            'mdp' => password_hash($motDePasse, PASSWORD_DEFAULT),
            'id'  => $id,
        ]);
    }
}
