<?php
/**
 * Fonctions de sécurité applicative : CSRF, contrôle d'accès, validation.
 */
class Security
{
    /**
     * Génère ou récupère le jeton CSRF stocké en session.
     * Un seul jeton par session (synchronizer token pattern).
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Retourne le champ hidden HTML à insérer dans chaque formulaire. */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    /**
     * Vérifie le jeton CSRF soumis avec le formulaire.
     * Doit être appelé sur chaque requête POST.
     */
    public static function verifyCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Vérifie que l'agent connecté possède le rôle requis.
     * Retourne true si le rôle est suffisant.
     */
    public static function hasRole(string $requiredRole): bool
    {
        $role = $_SESSION['agent']['role'] ?? '';
        if ($requiredRole === 'agent') {
            return in_array($role, ['agent', 'admin'], true);
        }
        return $role === $requiredRole;
    }

    /**
     * Bloque l'accès si le rôle est insuffisant.
     * Redirige vers le tableau de bord avec un message d'erreur.
     */
    public static function requireRole(string $role): void
    {
        if (!self::hasRole($role)) {
            Logger::security("Tentative d'accès non autorisé (rôle requis : $role)");
            $_SESSION['flash'] = [
                'message' => 'Accès refusé : droits insuffisants.',
                'type'    => 'danger',
            ];
            header('Location: ' . url('home'));
            exit;
        }
    }

    /** Valide un email. */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Valide un numéro de téléphone français (formats courants). */
    public static function isValidPhone(string $phone): bool
    {
        if ($phone === '') {
            return true;
        }
        return (bool) preg_match('/^(?:0|\+33\s?)[1-9](?:[\s.-]?\d{2}){4}$/', $phone);
    }

    /** Nettoie une chaîne : trim + suppression des caractères de contrôle. */
    public static function sanitize(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value));
    }

    /** Formate un message d'erreur PDO sûr (sans détails techniques). */
    public static function dbErrorMessage(PDOException $e): string
    {
        $msg = $e->getMessage();
        // Les triggers métier lèvent SQLSTATE 45000 avec un message explicite
        // Format PDO : "SQLSTATE[45000]: <<1>> <<2>>: 1644 Message lisible"
        if (strpos($msg, '45000') !== false) {
            if (preg_match('/\d+\s+(.+)$/', $msg, $m)) {
                return trim($m[1]);
            }
        }
        // Contrainte UNIQUE violée (email en double)
        if (strpos($msg, '1062') !== false || strpos($msg, 'Duplicate entry') !== false) {
            return 'Cette valeur existe déjà (doublon détecté).';
        }
        Logger::error('Erreur BDD : ' . $msg);
        return 'Une erreur technique est survenue. Veuillez réessayer.';
    }
}
