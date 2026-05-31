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
