<?php
/** Modèle des agents (utilisateurs de l'application). */
class Agent extends Model
{
    protected string $table = 'agent';

    /** Recherche un agent par son email (identifiant de connexion). */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM agent WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Crée un agent en hachant le mot de passe (bcrypt via password_hash).
     * Le mot de passe en clair n'est jamais stocké.
     */
    public function create(array $d): bool
    {
        $sql = "INSERT INTO agent (nom, prenom, email, mot_de_passe, role, actif, date_creation)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :actif, :date_creation)";
        $d['mot_de_passe'] = password_hash($d['mot_de_passe'], PASSWORD_DEFAULT);
        $d['date_creation'] = date('Y-m-d');
        return $this->db->prepare($sql)->execute($d);
    }
}
