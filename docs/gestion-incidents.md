# Gestion des incidents — Client léger

**Projet :** Application web — Médiathèque de Bourg-la-Reine
**Suivi assuré via :** GLPI (gestionnaire d'incidents) durant les phases de test.

Ce document recense les principaux incidents rencontrés pendant le
développement et les phases de test, ainsi que leur résolution.

---

| N° | Date | Gravité | Description | Cause | Résolution | Statut |
|----|------|---------|-------------|-------|------------|--------|
| INC-01 | 2026-01-22 | Majeure | Caractères accentués mal stockés en base (« é » devient « Ã© ») | Jeu de caractères par défaut (`latin1`) | Base recréée en `utf8mb4_unicode_ci` + DSN PDO `charset=utf8mb4` | Résolu |
| INC-02 | 2026-01-28 | Critique | Possibilité d'emprunter un livre déjà épuisé | Aucun contrôle de stock côté application | Trigger `trg_pret_before_insert` levant `SIGNAL SQLSTATE '45000'` + message lisible | Résolu |
| INC-03 | 2026-02-04 | Critique | Stock de livres décrémenté manuellement et oublié au retour | Logique métier dans le contrôleur PHP | Triggers `trg_pret_after_insert` / `after_update` / `after_delete` automatisant la mise à jour du stock | Résolu |
| INC-04 | 2026-02-10 | Critique | Réservations simultanées sur le même créneau possibles | Aucun verrou ni contrôle | Trigger `trg_reservation_before_insert` vérifiant le chevauchement de plages horaires | Résolu |
| INC-05 | 2026-02-15 | Majeure | Mots de passe stockés en clair | Sécurité insuffisante | Hachage via `password_hash` (bcrypt, coût 12) + vérification via `password_verify` | Résolu |
| INC-06 | 2026-02-20 | Mineure | Affichage HTML cassé pour certains noms contenant des apostrophes ou des chevrons | Sortie non échappée | Helper `e()` centralisant `htmlspecialchars(ENT_QUOTES, UTF-8)` ; utilisation systématique dans les vues | Résolu |
| INC-07 | 2026-02-27 | Critique | Hashes des comptes de démo (`$6$...`) incompatibles avec XAMPP sous Windows | Format de hash `crypt SHA-512` non supporté par PHP sous Windows | Régénération des hashes en bcrypt (`$2y$12$...`), compatible toutes plateformes | Résolu |
| INC-08 | 2026-03-03 | Critique | Suppression d'un adhérent possible via simple lien dans une image distante (CSRF) | Action destructive via GET, sans jeton | Mise en place d'un jeton CSRF par session ; toutes les actions destructives passent en POST via le helper `postButton()` | Résolu |
| INC-09 | 2026-03-05 | Majeure | Détails de la base de données (utilisateur, schéma) affichés à l'utilisateur en cas de panne BD | `die($e->getMessage())` dans `Database.php` | Page HTTP 503 neutre + journalisation `error_log` ; même approche dans les contrôleurs (messages génériques) | Résolu |
| INC-10 | 2026-03-08 | Majeure | Réservation possible sur une salle marquée indisponible via POST direct | Le trigger ne vérifiait que le chevauchement, pas la disponibilité de la salle | Ajout du contrôle `salle.disponible` dans `trg_reservation_before_insert` (SIGNAL 45000) | Résolu |
| INC-11 | 2026-03-12 | Mineure | Le statut `en_retard` du `ENUM` n'était jamais affecté en base | Calcul fait uniquement dans la vue, pas en base | Mise à jour automatique du statut dans le contrôleur frontal (`UPDATE pret SET statut='en_retard' WHERE …`) | Résolu |
| INC-12 | 2026-03-15 | Mineure | Notice PHP `Undefined index` dans les logs sur les champs facultatifs | Accès direct à `$_POST['x']` sans test | Utilisation systématique de `$_POST['x'] ?? ''` dans tous les contrôleurs | Résolu |
| INC-13 | 2026-03-18 | Mineure | Document de conformité indiquait 7 tables au lieu de 8 | Incohérence avec le schéma réel | Mise à jour de `docs/CONFORMITE.md` + ajout d'une section sécurité | Résolu |
| INC-14 | 2026-03-22 | Mineure | Cookie de session sans flag `HttpOnly` (potentiel vol via XSS) | Configuration par défaut PHP | `session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => HTTPS])` avant `session_start()` | Résolu |
| INC-15 | 2026-03-25 | Mineure | Adresse email saisie sans validation côté serveur | Confiance dans l'attribut HTML `required` uniquement | Ajout d'un `filter_var($email, FILTER_VALIDATE_EMAIL)` dans `AdherentController::save()` | Résolu |

## Procédure de signalement

1. L'agent décrit l'anomalie (capture d'écran, étapes de reproduction,
   message éventuel) dans **GLPI**.
2. Le ticket est qualifié (gravité, module concerné, priorité).
3. Correction sur une branche Git dédiée (convention : `fix/INC-XX-description`).
4. Tests de non-régression manuels sur les fonctionnalités impactées.
5. Fusion (`merge`) après validation et clôture du ticket avec un compte
   rendu de la correction.

## Classification des gravités

| Gravité | Critère | Délai cible |
|---------|---------|-------------|
| **Critique** | Indisponibilité, perte de données, faille de sécurité exploitable | < 24 h |
| **Majeure** | Fonction métier dégradée, contournement existant | < 1 semaine |
| **Mineure** | Affichage, ergonomie, message peu clair, log parasite | Itération suivante |
