<?php
/**
 * Fonctions utilitaires disponibles dans toute l'application.
 */

/** Construit une URL vers un contrôleur / une action. */
function url(string $ctrl = 'home', string $action = 'index', array $params = []): string
{
    $query = array_merge(['ctrl' => $ctrl, 'action' => $action], $params);
    return BASE_URL . '?' . http_build_query($query);
}

/** Échappe une valeur pour un affichage HTML sûr (anti-XSS). */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formate une date ISO (YYYY-MM-DD) au format français. */
function dateFr(?string $date): string
{
    if (empty($date)) {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : e($date);
}

/**
 * Retourne le jeton CSRF de la session (le crée si nécessaire).
 * Inséré dans les formulaires pour prouver qu'ils proviennent bien
 * de l'application, et non d'un site tiers (CSRF — RFC OWASP).
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Champ caché à inclure dans tout formulaire POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Indique si l'agent connecté a le rôle administrateur. */
function isAdmin(): bool
{
    return ($_SESSION['agent']['role'] ?? '') === 'admin';
}

/**
 * Vérifie le jeton CSRF d'une soumission POST.
 * Termine la requête (HTTP 419) si invalide.
 */
function csrf_verify(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf_token'])
            || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(419);
        exit('Jeton de sécurité invalide ou expiré. Veuillez réessayer.');
    }
}

/**
 * Rend un bouton qui déclenche une action via un mini-formulaire POST.
 * Utilisé pour les actions destructives (suppression, retour, annulation,
 * déconnexion) afin qu'elles ne soient pas accessibles via simple lien GET
 * (protection contre les CSRF par image / lien).
 *
 * Options : class, title, confirm, form_class.
 */
function postButton(string $ctrl, string $action, ?int $id, string $innerHtml, array $opts = []): string
{
    $params = $id !== null ? ['id' => $id] : [];
    $url   = url($ctrl, $action, $params);
    $cls   = $opts['class']      ?? 'btn btn-sm btn-outline-danger';
    $title = $opts['title']      ?? '';
    $conf  = $opts['confirm']    ?? '';
    $form  = $opts['form_class'] ?? 'd-inline';
    $cAttr = $conf !== '' ? ' data-confirm="' . e($conf) . '"' : '';
    return '<form method="post" action="' . e($url) . '" class="' . e($form) . '">'
         . csrf_field()
         . '<button type="submit" class="' . e($cls) . '" title="' . e($title) . '"' . $cAttr . '>'
         . $innerHtml
         . '</button>'
         . '</form>';
}
