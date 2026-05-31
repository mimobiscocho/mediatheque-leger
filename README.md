# Médiathèque de Bourg-la-Reine — Application web de gestion

Application web (client léger) de gestion d'une médiathèque, développée dans le
cadre de l'épreuve **E6 – Conception et développement d'applications** du
**BTS SIO option SLAM** (session 2026).

> **Contexte (cahier des charges).** La Médiathèque de Bourg-la-Reine souhaite
> moderniser la gestion de ses services : livres, abonnements, prêts de
> matériels et salles de coworking. Cette application centralise l'ensemble de
> ce suivi pour les agents.

**Technologies :** HTML5, CSS3, JavaScript, **PHP 8 (PDO)**, **MySQL / MariaDB**,
Bootstrap 5 — architecture **MVC**.

---

## 1. Fonctionnalités (objectifs du cahier des charges)

| Objectif (cahier des charges) | Implémentation |
|---|---|
| **Gestion des adhérents** | CRUD complet + rattachement à un type d'abonnement |
| **Gestion des produits** | CRUD des **livres** (multi-exemplaires) et des **matériels**, avec **filtrage multicritères** |
| **Système de prêts** | Emprunt / retour de livres et matériels, suivi des retards |
| **Réservation d'espaces** | Réservation des **salles de coworking** avec contrôle des créneaux |
| **Vues sur l'ensemble des données** | Tableau de bord : statistiques, prêts en cours, retards, réservations |

À cela s'ajoutent deux fonctionnalités transverses :

- **Authentification des agents** : accès à l'application protégé par une connexion
  (mots de passe hachés, gestion de session sécurisée).
- **Filtrage multicritères des collections** : recherche serveur sur les livres
  (titre/auteur/ISBN, genre, disponibilité) et les matériels (nom, catégorie, état,
  disponibilité).

---

## 2. Architecture (MVC)

L'application suit le patron **Modèle-Vue-Contrôleur**, avec un **contrôleur
frontal** unique (`public/index.php`) qui route chaque requête.

```
mediatheque-leger/
├── config/
│   ├── config.php          # Constantes + identifiants BD
│   └── Database.php        # Connexion PDO (singleton)
├── sql/
│   └── schema.sql          # Base de données : tables + triggers + jeu de données
├── app/
│   ├── core/
│   │   ├── Model.php        # Modèle de base (CRUD générique via PDO)
│   │   ├── Controller.php   # Contrôleur de base (rendu des vues, flash, redirect)
│   │   └── helpers.php      # url(), e() (anti-XSS), dateFr()
│   ├── models/              # Agent, Adherent, Abonnement, Livre, Materiel, Salle, Pret, Reservation
│   ├── controllers/         # Un contrôleur par entité + HomeController + AuthController
│   └── views/               # Gabarit + vues (liste / formulaire) par entité + auth/login
└── public/                  # SEULE partie exposée au web (racine recommandée)
    ├── index.php            # Contrôleur frontal (point d'entrée)
    ├── css/style.css        # Charte graphique
    └── js/app.js            # Confirmations, recherche instantanée, flash
```

**Routage** : `public/index.php?ctrl=<entité>&action=<action>&id=<id>`
(ex. `index.php?ctrl=adherent&action=form&id=3`).

---

## 3. Base de données

Schéma relationnel (8 tables) :
`agent`, `abonnement`, `adherent`, `livre`, `materiel`, `salle`, `pret`, `reservation`.

### Triggers (automatisation des disponibilités — exigence du cahier des charges)

| Trigger | Rôle |
|---|---|
| `trg_pret_before_insert` | **Refuse** un prêt si le livre/matériel est indisponible |
| `trg_pret_after_insert` | Décrémente le stock du produit emprunté |
| `trg_pret_after_update` | Restaure le stock au retour du produit |
| `trg_pret_after_delete` | Restaure le stock si un prêt en cours est supprimé |
| `trg_reservation_before_insert` | **Bloque** le chevauchement de créneaux sur une même salle |

Les triggers lèvent une erreur SQL (`SIGNAL`) remontée à l'utilisateur sous
forme de message d'alerte.

---

## 4. Installation

### Prérequis
- PHP ≥ 8.0 avec l'extension **PDO MySQL**
- MySQL ou MariaDB

### Étape 1 — Importer la base de données
```bash
mysql -u root -p < sql/schema.sql
```
*(ou via phpMyAdmin : onglet « Importer » → `sql/schema.sql`)*

La base `mediatheque` est créée avec un **jeu de données de démonstration**.

### Étape 2 — Configurer la connexion
Adapter si besoin `config/config.php` (hôte, utilisateur, mot de passe).
Les valeurs par défaut conviennent à XAMPP/WAMP (`root`, sans mot de passe).

### Étape 3 — Lancer l'application

**Option A — Serveur intégré PHP (recommandé pour le développement)**
```bash
php -S localhost:8000 -t public
```
Puis ouvrir : <http://localhost:8000>

**Option B — XAMPP / WAMP**
Copier le dossier dans `htdocs/`, puis ouvrir
<http://localhost/mediatheque-leger/> (redirection automatique vers `public/`).

---

## 5. Sécurité

- **Authentification obligatoire** : toute page exige un agent connecté (contrôle
  d'accès dans `public/index.php` ; redirection vers l'écran de connexion sinon).
- **Mots de passe hachés** (`password_hash` / `password_verify`) — jamais stockés
  en clair. Régénération de l'identifiant de session à la connexion (anti-fixation).
- **Requêtes préparées (PDO)** sur toutes les interactions BD → anti-injection SQL.
- **Échappement HTML** systématique des sorties via `e()` → anti-XSS.
- **Séparation des dossiers** : seul `public/` est exposé ; `app/` et `config/`
  restent hors de la racine web en production.
- **Nettoyage des paramètres de routage** (liste blanche de caractères).

### Comptes de démonstration

| Email | Mot de passe | Rôle |
|---|---|---|
| `admin@mediatheque.fr` | `admin123` | admin |
| `agent@mediatheque.fr` | `agent123` | agent |

> En production, supprimez ces comptes de démonstration et créez de vrais comptes
> agents avec des mots de passe forts.

---

## 6. Guide utilisateur (rapide)

0. **Connexion** : se connecter avec un compte agent (voir comptes de démonstration
   ci-dessus). Le menu en haut à droite permet de se déconnecter.
1. **Tableau de bord** : vue d'ensemble (compteurs, prêts en cours, retards).
2. **Adhérents / Livres / Matériels / Salles** : bouton « Nouveau… » pour créer,
   icônes ✏️ / 🗑️ pour modifier / supprimer. Les **livres** et **matériels**
   disposent en plus d'un **filtre multicritères** (recherche, genre/catégorie,
   état, disponibilité).
3. **Prêts** : « Nouveau prêt » → choisir un adhérent et un produit. Le bouton
   **Retour** réenregistre la disponibilité.
4. **Réservations** : « Nouvelle réservation » → adhérent, salle, date, créneau.
   Un créneau en conflit est refusé automatiquement.

---

## 7. Productions & documentation associées

- Diagrammes (DCU, MCD/MLD), maquettes, Gantt : voir le Drive du projet.
- Code source : <https://github.com/mimobiscocho/mediathequeleger>
- Documentation de conformité : [`docs/CONFORMITE.md`](docs/CONFORMITE.md)

---

*Réalisation : SEBAH Nassim — BTS SIO SLAM — Session 2026.*
