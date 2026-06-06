# Documentation technique — Client léger

**Projet :** Application web de gestion de la médiathèque de Bourg-la-Reine
**Auteur :** SEBAH Nassim — BTS SIO SLAM — Session 2026

---

## Sommaire

1. [Présentation générale](#1-présentation-générale)
2. [Architecture MVC](#2-architecture-mvc)
3. [Routage et contrôleur frontal](#3-routage-et-contrôleur-frontal)
4. [Modèle de données](#4-modèle-de-données)
5. [Triggers — règles métier en base](#5-triggers--règles-métier-en-base)
6. [Sécurité](#6-sécurité)
7. [Organisation du code source](#7-organisation-du-code-source)
8. [Installation et exécution](#8-installation-et-exécution)
9. [Tests et validations](#9-tests-et-validations)
10. [Pistes d'évolution](#10-pistes-dévolution)

---

## 1. Présentation générale

L'application est un **site web** (client léger) permettant aux agents de la
médiathèque de gérer au quotidien les adhérents, les collections (livres et
matériels), les prêts, les réservations de salles de coworking et le tableau
de bord d'activité.

| Élément | Choix |
|---------|-------|
| Langage serveur | PHP 8 (≥ 8.0) |
| Interface | HTML 5 + CSS 3 + JavaScript |
| Framework CSS | Bootstrap 5 (via CDN) |
| Persistance | PDO (requêtes préparées) |
| SGBD | MySQL / MariaDB (charset `utf8mb4`) |
| Architecture | MVC avec contrôleur frontal |
| Sécurité mot de passe | bcrypt (`password_hash` / `password_verify`) |
| Versionnage | Git / GitHub |
| Serveur cible | Apache, Nginx, ou serveur PHP intégré |

## 2. Architecture MVC

L'application suit le patron **Modèle – Vue – Contrôleur** :

```
┌──────────────┐      ┌────────────────┐      ┌──────────────┐
│     VUE      │ ───▶ │   CONTRÔLEUR   │ ───▶ │    MODÈLE    │ ───▶ MySQL
│  (PHP/HTML)  │ ◀─── │  (validation)  │ ◀─── │  (PDO/CRUD)  │
└──────────────┘      └────────────────┘      └──────────────┘
```

- **Modèle** : classes héritant de `Model`, qui expose les opérations CRUD
  génériques (`all`, `find`, `delete`, `count`). Chaque modèle métier
  (`Adherent`, `Livre`, `Materiel`, `Salle`, `Pret`, `Reservation`,
  `Abonnement`, `Agent`) déclare sa table et ses requêtes spécifiques.
- **Vue** : fichiers PHP rendus dans le gabarit `views/layouts/` (header +
  footer). Chaque entité a son couple `index.php` (liste) et `form.php`
  (saisie / édition).
- **Contrôleur** : classe héritant de `Controller` ; orchestre la lecture des
  paramètres POST/GET, la validation, l'appel au modèle, puis le rendu de la
  vue. Un contrôleur par entité, plus `HomeController` (tableau de bord) et
  `AuthController` (authentification).

### Avantages du découpage

- **Séparation nette des responsabilités** : la couche présentation ne fait
  jamais de SQL, la couche modèle ne génère jamais de HTML.
- **Code dupliqué minimisé** : `Model` factorise les opérations CRUD,
  `Controller` centralise le rendu et les flashs.
- **Évolutivité** : ajouter une entité revient à créer un modèle + un
  contrôleur + deux vues. Aucune modification du noyau requise.

## 3. Routage et contrôleur frontal

Toutes les requêtes passent par `public/index.php`. C'est le **seul point
d'entrée** exposé au web ; `app/` et `config/` ne sont pas servis.

URL canonique :
```
public/index.php?ctrl=<entité>&action=<action>&id=<id>
```

Le contrôleur frontal :

1. Configure les paramètres du cookie de session (`HttpOnly`, `SameSite=Lax`,
   `Secure` si HTTPS), puis démarre la session.
2. Nettoie les paramètres `ctrl` et `action` (liste blanche `[a-zA-Z]`) et
   transforme `ctrl` en minuscules.
3. **Vérifie le jeton CSRF** sur toute requête POST.
4. **Contrôle l'accès** : redirige vers l'écran de connexion si aucun agent
   n'est en session et que la requête ne cible pas le contrôleur `auth`.
5. **Met à jour le statut `en_retard`** des prêts dont la date prévue est
   dépassée (mise à jour idempotente passée par un simple `UPDATE`).
6. Instancie le contrôleur cible et appelle l'action demandée.

```php
$ctrl   = strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['ctrl'] ?? 'home'));
$action = preg_replace('/[^a-zA-Z]/', '', $_GET['action'] ?? 'index');
```

L'absence de réécriture d'URL côté serveur (Apache `mod_rewrite`,
Nginx) est un choix volontaire : l'application reste **fonctionnelle dans
n'importe quel environnement**, y compris XAMPP / WAMP, sans configuration
supplémentaire.

## 4. Modèle de données — schéma unifié

L'application partage **une seule base** `mediatheque` avec le client lourd
(Java Swing). Le schéma est strictement identique dans les deux dépôts ; il
suffit d'importer le script SQL une seule fois pour initialiser les deux
applications.

Le client léger exploite directement : `agent` (auth), `abonnement`,
`adherent`, `livre`, `materiel`, `pret`. Il **partage** avec le client
lourd les tables `adherent`, `salle` et `reservation`. Les tables
`profil`, `technicien`, `animation` et `facture` sont présentes dans la
base unifiée mais utilisées par le seul client lourd.

Toutes en moteur **InnoDB**, jeu de caractères `utf8mb4_unicode_ci`.

### Schéma relationnel (simplifié)

```
agent                                      abonnement
+----------------+                         +--------------+
| id (PK)        |                         | id (PK)      |
| email (UNIQUE) |                         | libelle      |
| mot_de_passe   |                         | tarif        |
| role           |                         | duree_mois   |
+----------------+                         | quota_emprunts|
                                           +------+-------+
                                                  |
                                                  | 0..1
                                                  v
adherent ─── 1..* ─── pret ─── 0..1 ─── livre  +--------+
                       │                       | id (PK)|
                       │              0..1     | titre  |
                       └─────────────── materiel
                                               +--------+
                                               (idem)

adherent ─── 1..* ─── reservation ─── 1 ─── salle
```

### Tables principales

| Table | Rôle | Particularités |
|-------|------|----------------|
| `agent` | Utilisateurs de l'application | Mot de passe haché bcrypt, rôle `admin`/`agent` |
| `abonnement` | Types d'abonnement | Tarif, durée, quota d'emprunts |
| `adherent` | Membres de la médiathèque | FK vers `abonnement` (ON DELETE SET NULL) |
| `livre` | Collection de livres | `quantite_totale` + `quantite_disponible` |
| `materiel` | Matériel empruntable | État (`neuf`/`bon`/`use`/`hors_service`) |
| `salle` | Salles de coworking | Capacité + équipements + disponibilité |
| `pret` | Emprunts | XOR `livre_id` / `materiel_id` (CHECK) |
| `reservation` | Réservations de salles | CHECK : `heure_fin > heure_debut` |

### Intégrité référentielle

- `pret` → `adherent`, `livre`, `materiel` en **ON DELETE CASCADE**.
- `reservation` → `adherent`, `salle` en **ON DELETE CASCADE**.
- `adherent` → `abonnement` en **ON DELETE SET NULL** (un abonnement
  supprimé ne supprime pas les adhérents).
- Contrainte `CHECK` sur `pret` garantissant qu'un prêt porte sur un livre
  **ou** un matériel, jamais les deux.

## 5. Triggers — règles métier en base

Conformément au cahier des charges, des **triggers** garantissent les règles
métier directement en base, indépendamment de l'application.

| Trigger | Moment | Rôle |
|---------|--------|------|
| `trg_pret_before_insert` | `pret` BEFORE INSERT | Refuse un prêt si le livre/matériel est indisponible |
| `trg_pret_after_insert` | `pret` AFTER INSERT | Décrémente automatiquement le stock du produit emprunté |
| `trg_pret_after_update` | `pret` AFTER UPDATE | Restaure le stock quand `date_retour_effective` est renseignée |
| `trg_pret_after_delete` | `pret` AFTER DELETE | Restaure le stock si un prêt en cours est supprimé |
| `trg_reservation_before_insert` | `reservation` BEFORE INSERT | Refuse une salle indisponible **ou** un créneau qui chevauche une réservation confirmée existante |

En cas de violation, les triggers lèvent `SIGNAL SQLSTATE '45000'` avec un
message lisible, capturé par les contrôleurs et affiché sous forme de message
flash.

## 6. Sécurité

### Authentification

- Mots de passe stockés en **bcrypt** (`password_hash(..., PASSWORD_DEFAULT)`,
  coût 12). Vérification via `password_verify`.
- Identifiant de session **régénéré** à la connexion (`session_regenerate_id`)
  pour neutraliser la fixation de session.
- Aucune action métier accessible sans agent en session (contrôle dans
  `public/index.php`, redirection automatique vers l'écran de connexion).

### Cookie de session

```php
session_set_cookie_params([
    'httponly' => true,    // pas d'accès JavaScript
    'samesite' => 'Lax',   // protection CSRF de base
    'secure'   => !empty($_SERVER['HTTPS']),  // HTTPS uniquement si TLS
]);
```

### Protection CSRF

- Jeton unique par session, généré aléatoirement (32 octets) via
  `random_bytes`.
- Tous les formulaires POST incluent `<input type="hidden" name="_csrf">`
  via le helper `csrf_field()`.
- Toute requête POST est vérifiée par `csrf_verify()` dans le contrôleur
  frontal — réponse HTTP 419 si le jeton est manquant ou invalide.
- **Toutes les actions destructives** (suppression, retour de prêt,
  annulation, déconnexion) sont déclenchées via formulaire POST + jeton CSRF,
  et **non** via simple lien GET. Helper dédié : `postButton()`.

### Anti-injection SQL

- 100 % des interactions BD passent par `PDO::prepare` + `execute` avec
  paramètres nommés.
- Mode PDO : `ATTR_ERRMODE = EXCEPTION` et `ATTR_EMULATE_PREPARES = false`
  (prepared statements natifs MySQL).

### Anti-XSS

- Helper `e()` échappant toute sortie HTML utilisateur via
  `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Utilisé systématiquement dans les vues (`<?= e($variable) ?>`).

### Gestion des erreurs

- Aucune trace d'erreur PDO brute n'est exposée à l'utilisateur :
  - `config/Database.php` répond une page HTTP 503 neutre + journalisation
    via `error_log`.
  - Les contrôleurs n'affichent qu'un message générique en flash, sauf pour
    les violations métier (SQLSTATE 45000) qui sont par nature lisibles.
- Les erreurs techniques restent **journalisées côté serveur** pour le
  diagnostic.

### Validation des entrées

- Champs obligatoires vérifiés côté serveur (en plus du `required` HTML).
- Email validé via `filter_var($email, FILTER_VALIDATE_EMAIL)`.
- Dates de réservation refusées dans le passé.
- Bornes numériques explicites : `quantite_disponible ∈ [0 ; quantite_totale]`.

## 7. Organisation du code source

```
mediatheque-leger/
├── config/
│   ├── config.php          # Constantes (APP_NAME, identifiants BD, BASE_URL)
│   └── Database.php        # Connexion PDO singleton
├── sql/
│   └── schema.sql          # Création BD + tables + triggers + jeu de démo
├── app/
│   ├── core/
│   │   ├── Model.php       # Classe abstraite, CRUD générique
│   │   ├── Controller.php  # Classe abstraite : rendu, redirect, flash
│   │   └── helpers.php     # url(), e(), dateFr(), csrf_*, postButton()
│   ├── models/             # Un fichier par entité métier
│   ├── controllers/        # Un fichier par entité + Home + Auth
│   └── views/
│       ├── layouts/        # header.php + footer.php (gabarit Bootstrap)
│       ├── auth/login.php  # Écran de connexion (autonome, sans gabarit)
│       ├── home/index.php  # Tableau de bord
│       └── <entité>/       # index.php (liste) + form.php (édition)
├── public/                 # SEUL dossier exposé en production
│   ├── index.php           # Contrôleur frontal (point d'entrée)
│   ├── .htaccess           # DirectoryIndex + Options -Indexes
│   ├── css/style.css       # Charte graphique
│   └── js/app.js           # Confirmations, masquage flash, filtre instantané
└── docs/                   # Cette documentation
```

## 8. Installation et exécution

### Prérequis

- **PHP ≥ 8.0** avec l'extension PDO MySQL.
- **MySQL 8+** ou **MariaDB**.

### Étape 1 — Importer la base de données

```bash
mysql -u root -p < sql/schema.sql
```

Le script :
- supprime puis recrée la base `mediatheque` (partagée avec le client lourd) ;
- crée 11 tables + 8 triggers ;
- insère un jeu de démonstration : 2 agents + 2 profils, 4 abonnements,
  7 adhérents, 6 livres, 4 matériels, 8 salles, 3 techniciens, 3 animations,
  2 prêts, 4 réservations et 4 factures.

### Étape 2 — Configurer la connexion

Adapter `config/config.php` si besoin. Les valeurs par défaut correspondent
à un environnement XAMPP/WAMP local (`root` sans mot de passe). Les
constantes peuvent être surchargées par des variables d'environnement
(`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) — pratique en production.

### Étape 3 — Lancer l'application

**Option A — Serveur PHP intégré (recommandé pour la démonstration) :**
```bash
php -S localhost:8000 -t public
```
Puis ouvrir : <http://localhost:8000>.

**Option B — XAMPP / WAMP :**
Copier le dossier dans `htdocs/`, puis ouvrir
<http://localhost/mediatheque-leger/> ; la racine redirige automatiquement
vers `public/`.

**Option C — Apache / Nginx en production :**
Configurer la racine du site (`DocumentRoot`) sur le dossier `public/`. Les
dossiers `app/` et `config/` ne doivent **pas** être servis par le serveur web.

### Comptes de démonstration

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| `admin@mediatheque.fr` | `admin123` | admin |
| `agent@mediatheque.fr` | `agent123` | agent |

> En production, supprimer ces comptes et créer de vrais agents.

## 9. Tests et validations

| Test | Méthode | Résultat attendu |
|------|---------|------------------|
| Syntaxe de tous les fichiers PHP | `find … -exec php -l {} \;` | Aucune erreur |
| Hash bcrypt des comptes de démo | `password_verify('admin123', $hash)` | `true` |
| Page de connexion rendue | `curl …?ctrl=auth&action=login` | HTML + cookie `HttpOnly` |
| Tentative POST sans CSRF | `curl -X POST …` | HTTP 419 |
| Tentative de réservation sur salle indisponible | UI → réservation | Message du trigger SQLSTATE 45000 |
| Tentative de prêt sur livre épuisé | UI → nouveau prêt | Message du trigger `trg_pret_before_insert` |
| Mise à jour automatique du statut `en_retard` | Premier accès quotidien | Statut basculé en base |

## 10. Pistes d'évolution

- **Pagination** des listes longues (livres, prêts) avec `LIMIT/OFFSET`.
- **Module d'archivage** des prêts terminés et des réservations passées.
- **Statistiques avancées** : taux de retard, top des livres empruntés,
  occupation moyenne des salles.
- **Export PDF / CSV** des listes et des reçus de prêt.
- **Authentification renforcée** : verrouillage temporaire après N tentatives
  échouées, journalisation des connexions.
- **Internationalisation** : extraction des libellés vers des fichiers de
  traduction.
- **Tests automatisés** : couche de tests PHPUnit sur les modèles.
