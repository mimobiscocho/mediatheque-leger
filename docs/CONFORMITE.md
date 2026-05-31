# Traçabilité — Conformité au cahier des charges

Mise en correspondance de chaque **exigence** de la fiche descriptive E6 avec sa
**réalisation** dans le projet (client lourd Java).

## Objectifs fonctionnels

| # | Exigence | Réalisation (classes) | Statut |
|---|----------|-----------------------|--------|
| 1 | Gestion des adhérents | `controller/AdherentController`, `dao/AdherentDAO`, `view/AdherentPanel` | ✅ |
| 2 | Gestion des produits (livres + matériels) | `Livre*` / `Materiel*` (model, dao, controller, view) | ✅ |
| 3 | Système de prêts | `controller/PretController`, `dao/PretDAO`, `view/PretPanel` | ✅ |
| 4 | Réservation d'espaces (coworking) | `controller/ReservationController`, `dao/ReservationDAO`, `view/ReservationPanel` | ✅ |
| 5 | Vues sur l'ensemble des données | `view/DashboardPanel` | ✅ |

## Exigences techniques

| Exigence | Réalisation | Statut |
|----------|-------------|--------|
| Application de bureau **Java** | Java 17+ / Swing | ✅ |
| Interfaces **Java Swing** | `view/` (MainFrame + panneaux) | ✅ |
| Persistance **JDBC** | `config/Database.java` + couche `dao/` | ✅ |
| Architecture **MVC** | packages `model` / `dao` / `controller` / `view` | ✅ |
| Méthode **CRUD** | Create/Read/Update/Delete sur adhérents, livres, matériels, salles | ✅ |
| Base de données **avec triggers** | 5 triggers dans `sql/schema.sql` | ✅ |
| **Versionning** Git/GitHub | Dépôt `mediathequeleger` | ✅ |
| Sécurité (injections SQL) | `PreparedStatement` dans tous les DAO | ✅ |

## Résultats attendus

| Attendu | Livré |
|---------|-------|
| Une base de données | `sql/schema.sql` (7 tables, 5 triggers, jeu de données) |
| Une application fonctionnelle | Application Swing complète (`src/`), compilée sans erreur |
| Diagrammes DCU | À joindre au dossier (Drive) |
| Documentation technique & utilisateur | `README.md` + ce document |

## Évolutions envisagées (mentionnées au cahier des charges)

- Filtrage multicritères des collections (amorcé : recherche instantanée par module).
- Module d'archivage automatique des prêts (statut `terminee` à exploiter).
