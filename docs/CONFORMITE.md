# Traçabilité — Conformité au cahier des charges

Ce document met en correspondance chaque **exigence** de la fiche descriptive
E6 avec sa **réalisation** dans le projet.

## Objectifs fonctionnels

| # | Exigence | Réalisation (fichiers) | Statut |
|---|----------|------------------------|--------|
| 1 | Gestion des adhérents | `controllers/AdherentController.php`, `models/Adherent.php`, `views/adherent/*` | ✅ |
| 2 | Gestion des produits (livres + matériels) | `Livre*` / `Materiel*` (controllers, models, views) | ✅ |
| 3 | Système de prêts | `controllers/PretController.php`, `models/Pret.php`, `views/pret/*` | ✅ |
| 4 | Réservation d'espaces (coworking) | `controllers/ReservationController.php`, `models/Reservation.php`, `views/reservation/*` | ✅ |
| 5 | Vues sur l'ensemble des données | `controllers/HomeController.php`, `views/home/index.php` | ✅ |

## Fonctionnalités transverses

| Fonctionnalité | Réalisation (fichiers) | Statut |
|----------------|------------------------|--------|
| Authentification des agents (connexion sécurisée) | `controllers/AuthController.php`, `models/Agent.php`, `views/auth/login.php`, garde dans `public/index.php`, table `agent` | ✅ |
| Filtrage multicritères des collections | `Livre::filter()` / `Materiel::filter()`, formulaires GET dans `views/livre/index.php` et `views/materiel/index.php` | ✅ |

## Exigences techniques

| Exigence | Réalisation | Statut |
|----------|-------------|--------|
| Application **web** HTML/CSS/JS + PHP-MySQL | Front PHP + Bootstrap, BD MySQL | ✅ |
| Architecture **MVC** | `app/core` (Model, Controller) + dossiers `models`/`controllers`/`views` | ✅ |
| Méthode **CRUD** | Create/Read/Update/Delete sur adhérents, livres, matériels, salles | ✅ |
| Base de données **avec triggers** | 5 triggers dans `sql/schema.sql` (disponibilités, conflits de créneaux) | ✅ |
| **Framework** Bootstrap | Bootstrap 5 (CSS + composants) | ✅ |
| **Versionning** Git/GitHub | Dépôt `mediathequeleger` | ✅ |
| Sécurité (injections SQL, XSS) | PDO préparé + échappement `e()` | ✅ |
| Authentification / contrôle d'accès | Mots de passe hachés (`password_hash`), sessions, garde d'accès | ✅ |

## Résultats attendus

| Attendu | Livré |
|---------|-------|
| Une base de données | `sql/schema.sql` — base unifiée `mediatheque` partagée avec le client lourd (11 tables, 8 triggers, jeu de données) |
| Un site internet fonctionnel | Application MVC complète (`app/`, `public/`) |
| Diagrammes DCU | À joindre au dossier (Drive) |
| Documentation technique & utilisateur | `README.md` + ce document |

## Sécurité applicative

| Mesure | Mise en œuvre |
|--------|---------------|
| Mots de passe hachés (bcrypt) | `password_hash` / `password_verify` (cf. `Agent::create`, `AuthController`) |
| Session sécurisée | `HttpOnly` + `SameSite=Lax` + régénération d'ID (`public/index.php`) |
| Protection CSRF | Jeton de session vérifié sur tout POST (`helpers.php`, formulaires) |
| Actions destructives en POST | Suppression / retour / annulation / déconnexion via `postButton()` |
| Anti-injection SQL | Requêtes préparées PDO sur 100 % des interactions BD |
| Anti-XSS | Échappement systématique via `e()` |
| Pas de fuite d'erreur PDO | Messages génériques côté UI, détails techniques journalisés (`error_log`) |

## Évolutions envisagées (mentionnées au cahier des charges)

- Filtrage multicritères des collections : **réalisé** (recherche serveur sur
  livres et matériels), complété par la recherche instantanée côté client sur les
  autres listes.
- Mise à jour automatique du statut `en_retard` : **réalisée** (passage en
  `en_retard` au premier accès journalier — cf. `public/index.php`).
