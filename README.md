# Médiathèque de Bourg-la-Reine — Application de gestion (client lourd)

Application de bureau (**client lourd**) de gestion d'une médiathèque, développée
dans le cadre de l'épreuve **E6 – Conception et développement d'applications** du
**BTS SIO option SLAM** (session 2026).

> **Contexte (cahier des charges).** La Médiathèque de Bourg-la-Reine souhaite
> moderniser la gestion de ses services : livres, abonnements, prêts de
> matériels et salles de coworking. Cette application de bureau centralise ce
> suivi pour les agents.

**Technologies :** **Java 17+ / Swing** (IHM), **JDBC** (persistance),
**MySQL / MariaDB** (SGBD) — architecture **MVC**.

> ℹ️ Une version **client léger** (web PHP/MySQL) du même projet est disponible
> sur la branche Git `client-leger-php`.

---

## 1. Fonctionnalités (objectifs du cahier des charges)

| Objectif | Implémentation |
|---|---|
| **Gestion des adhérents** | CRUD complet + rattachement à un abonnement |
| **Gestion des produits** | CRUD des **livres** (multi-exemplaires) et des **matériels** |
| **Système de prêts** | Emprunt / retour de livres et matériels, suivi des retards |
| **Réservation d'espaces** | Réservation des **salles de coworking** avec contrôle des créneaux |
| **Vues sur l'ensemble des données** | Tableau de bord : compteurs et prêts en retard |

---

## 2. Architecture (MVC)

```
mediatheque-leger/
├── lib/                         # Connecteur JDBC MySQL (.jar à déposer, voir lib/README.md)
├── sql/
│   └── schema.sql               # Base de données : tables + triggers + jeu de données
├── src/fr/blr/mediatheque/
│   ├── App.java                 # Point d'entrée (main)
│   ├── config/
│   │   └── Database.java         # Connexion JDBC (singleton)
│   ├── model/                    # Entités (POJO) : Adherent, Livre, Materiel, Salle, Pret...
│   ├── dao/                      # Accès aux données (JDBC + requêtes préparées)
│   ├── controller/               # Logique métier / validation
│   └── view/                     # Interfaces Swing (MainFrame + panneaux par module)
├── run.sh                        # Compilation + lancement (Linux/macOS)
└── run.bat                       # Compilation + lancement (Windows)
```

- **model** : objets métier simples (POJO).
- **dao** : une classe par entité, encapsule toutes les requêtes SQL.
- **controller** : orchestre les DAO et valide les saisies.
- **view** : `MainFrame` à onglets + un panneau par module ; les formulaires
  d'ajout/modification sont des boîtes de dialogue.

---

## 3. Base de données

Schéma relationnel (7 tables) :
`abonnement`, `adherent`, `livre`, `materiel`, `salle`, `pret`, `reservation`.

### Triggers (automatisation des disponibilités — exigence du cahier des charges)

| Trigger | Rôle |
|---|---|
| `trg_pret_before_insert` | **Refuse** un prêt si le livre/matériel est indisponible |
| `trg_pret_after_insert` | Décrémente le stock du produit emprunté |
| `trg_pret_after_update` | Restaure le stock au retour du produit |
| `trg_pret_after_delete` | Restaure le stock si un prêt en cours est supprimé |
| `trg_reservation_before_insert` | **Bloque** le chevauchement de créneaux sur une salle |

Les triggers lèvent une erreur SQL (`SIGNAL`) que l'application intercepte
(`SQLException`) et affiche dans une boîte de dialogue.

---

## 4. Installation et lancement

### Prérequis
- **JDK 17 ou supérieur** (compilé et testé avec OpenJDK 21).
- **MySQL** ou **MariaDB**.
- Le **connecteur JDBC MySQL** (`.jar`) déposé dans `lib/` — voir
  [`lib/README.md`](lib/README.md).

### Étape 1 — Importer la base de données
```bash
mysql -u root -p < sql/schema.sql
```
*(ou via phpMyAdmin / MySQL Workbench)*

### Étape 2 — Configurer la connexion
Adapter au besoin les identifiants dans
`src/fr/blr/mediatheque/config/Database.java`
(par défaut : `localhost`, utilisateur `root`, mot de passe vide).

### Étape 3 — Compiler et lancer

**Linux / macOS :**
```bash
./run.sh
```
**Windows :**
```bat
run.bat
```

**Ou manuellement :**
```bash
# Compilation
javac -encoding UTF-8 -d out $(find src -name "*.java")
# Lancement (séparateur de classpath : ':' sous Linux/macOS, ';' sous Windows)
java -cp "out:lib/*" fr.blr.mediatheque.App
```

---

## 5. Sécurité

- **Requêtes préparées (PreparedStatement)** partout → protection contre les
  injections SQL.
- **Validation** des saisies dans la couche contrôleur.
- Les règles d'intégrité critiques (disponibilités, conflits) sont **garanties
  côté base** par les triggers, indépendamment de l'IHM.

---

## 6. Guide utilisateur (rapide)

L'application s'ouvre sur le **tableau de bord**. La navigation se fait par
**onglets** :

1. **Adhérents / Livres / Matériels / Salles** : boutons *Ajouter / Modifier /
   Supprimer*, double-clic pour éditer, champ de recherche instantanée.
2. **Prêts** : *Nouveau prêt* (adhérent + produit), *Enregistrer le retour*.
3. **Réservations** : *Nouvelle réservation* (adhérent, salle, date, créneau) ;
   un conflit de créneau est refusé automatiquement.

---

## 7. Productions & documentation associées

- Diagrammes (DCU, MCD/MLD), maquettes, Gantt : voir le Drive du projet.
- Code source : <https://github.com/mimobiscocho/mediathequeleger>
- Documentation de conformité : [`docs/CONFORMITE.md`](docs/CONFORMITE.md)

---

*Réalisation : SEBAH Nassim — BTS SIO SLAM — Session 2026.*
