# Documentation utilisateur — Client léger

**Application :** Site web de gestion — Médiathèque de Bourg-la-Reine

Ce guide s'adresse aux agents de la médiathèque qui utilisent l'application
au quotidien depuis un navigateur web.

---

## 1. Connexion

Au lancement, l'écran de connexion s'affiche.

1. Saisir votre **adresse email** professionnelle.
2. Saisir votre **mot de passe**.
3. Cliquer sur **Se connecter** (ou appuyer sur la touche Entrée).

Comptes de démonstration :

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| `admin@mediatheque.fr` | `admin123` | admin |
| `agent@mediatheque.fr` | `agent123` | agent |

En cas d'identifiants incorrects, un bandeau rouge affiche le message
« Identifiants incorrects ou compte désactivé ». Vérifier la saisie ; si le
problème persiste, contacter l'administrateur.

> **Sécurité :** la session expire à la fermeture du navigateur. Pensez à
> vous déconnecter explicitement lorsque vous quittez votre poste, surtout
> sur un poste partagé.

## 2. Vue générale de l'application

Après connexion, la fenêtre est organisée comme suit :

- **Barre de navigation** (en haut) : accès aux modules métier.
- **Menu utilisateur** (en haut à droite) : affiche votre nom, votre email,
  votre rôle, et un bouton **Déconnexion**.
- **Zone principale** : contenu du module sélectionné.
- **Bandeau de notification** (apparaît temporairement) : confirme les
  actions effectuées ou signale une erreur.

## 3. Tableau de bord

C'est la page d'accueil après connexion. Elle présente :

- **4 cartes de statistiques** : nombre d'adhérents, de livres, de matériels
  et de salles enregistrés. Un clic sur une carte ouvre le module concerné.
- **2 indicateurs d'activité** : prêts en cours et réservations à venir.
- **Bloc « Prêts en retard »** : liste des emprunts dont la date de retour
  prévue est dépassée et qui n'ont pas encore été rendus. N'apparaît que
  s'il y a des retards à traiter.
- **Deux mini-tableaux** : les 5 derniers prêts et les 5 dernières
  réservations.

## 4. Principe commun à tous les modules

Tous les modules de gestion (Adhérents, Livres, Matériels, Salles, Prêts,
Réservations) fonctionnent selon le même schéma :

| Action | Comment faire |
|--------|---------------|
| **Lister** | Le tableau / la grille affiche tous les enregistrements |
| **Filtrer** (livres, matériels) | Saisir des critères dans la barre de filtres et cliquer sur **Filtrer** |
| **Rechercher** (autres modules) | Taper dans le champ « Rechercher » au-dessus du tableau — le filtre est instantané |
| **Créer** | Bouton **Nouveau…** → remplir le formulaire → **Enregistrer** |
| **Modifier** | Icône ✏ sur une ligne → ajuster les champs → **Enregistrer** |
| **Supprimer** | Icône 🗑 sur une ligne → confirmer dans la boîte de dialogue |

> Les champs marqués d'un astérisque (*) sont obligatoires.

Toute action réussie est confirmée par un bandeau vert ; toute erreur par
un bandeau rouge.

## 5. Modules

### 5.1 Adhérents

Gère les membres de la médiathèque (nom, prénom, contact, abonnement).

- Le **type d'abonnement** est sélectionné dans une liste déroulante
  prédéfinie (`Standard`, `Étudiant`, `Premium`, `Découverte`).
- **Désactiver** un adhérent (décocher « Adhérent actif ») le conserve dans
  la base mais l'empêche d'apparaître dans les listes d'emprunt et de
  réservation à venir.
- La **date d'inscription** est pré-remplie avec la date du jour.

### 5.2 Livres

Gère la collection de livres (multi-exemplaires).

- Chaque livre possède une **quantité totale** et une **quantité
  disponible**. La disponibilité est ajustée automatiquement par la base de
  données à chaque prêt / retour.
- Une barre de filtres permet de rechercher par **titre/auteur/ISBN**, de
  filtrer par **genre** et par **disponibilité**. Le compteur en haut du
  tableau indique le nombre de résultats.

### 5.3 Matériels

Gère le matériel empruntable (ordinateurs, liseuses, équipements
audiovisuels…).

- L'**état** est codifié : neuf, bon, usé, hors service. Un matériel
  hors service ne peut pas être emprunté.
- Filtres : par nom/description, catégorie, état, disponibilité.

### 5.4 Salles

Gère les salles de coworking sous forme de **vignettes**. Chaque vignette
affiche le nom, la capacité, les équipements et un badge de disponibilité.

- Bouton **Réserver** pour ouvrir directement le formulaire de réservation.
- Icône ✏ pour modifier la salle, icône 🗑 pour la supprimer.
- Une salle marquée **indisponible** ne peut pas faire l'objet d'une
  nouvelle réservation (vérification garantie par un trigger en base).

### 5.5 Prêts

Gère l'emprunt et le retour des livres et matériels.

- **Nouveau prêt** :
  1. Choisir l'adhérent.
  2. Choisir le produit à emprunter (livres et matériels sont regroupés
     dans le même menu déroulant, avec les exemplaires disponibles).
  3. Ajuster les dates si besoin (par défaut : prêt du jour, retour à +14
     jours).
  4. **Enregistrer**.
- **Retour** : bouton **Retour** sur la ligne du prêt en cours. Confirme
  l'opération et restaure automatiquement la disponibilité.
- **Statut** : badges colorés (En cours / En retard / Rendu). Le passage
  en « En retard » est automatique dès le premier accès du jour à
  l'application.
- **Contrôle automatique** : la base de données refuse tout prêt si
  l'exemplaire n'est plus disponible (message remonté à l'utilisateur).

### 5.6 Réservations

Gère la réservation des salles de coworking sur un créneau horaire.

- **Nouvelle réservation** :
  1. Choisir l'adhérent.
  2. Choisir la salle (les salles indisponibles sont signalées).
  3. Saisir la date (ne peut pas être dans le passé), l'heure de début et
     l'heure de fin.
  4. **Confirmer**.
- **Annuler** : bouton **Annuler** sur une réservation confirmée. La salle
  redevient libre sur ce créneau.
- **Contrôle automatique** : la base de données refuse tout chevauchement
  de créneau sur une même salle, et toute réservation sur une salle
  indisponible.

## 6. Déconnexion

Cliquer sur votre nom (en haut à droite), puis sur **Déconnexion** dans le
menu déroulant. Vous revenez à l'écran de connexion.

> La déconnexion est protégée : elle n'est exécutée que via le bouton du
> menu, jamais via un simple lien (mesure de sécurité contre les attaques
> CSRF).

## 7. Messages courants

| Message | Signification |
|---------|---------------|
| « Identifiants incorrects ou compte désactivé. » | Email/mot de passe erronés, ou compte désactivé par un administrateur |
| « Nom, prénom et email sont obligatoires. » | Au moins un champ requis est vide |
| « Adresse email invalide. » | Format d'email non reconnu |
| « Cet email est déjà utilisé par un autre adhérent. » | L'email saisi existe déjà dans la base |
| « Livre indisponible : aucun exemplaire en stock. » | Tous les exemplaires sont actuellement empruntés |
| « Matériel indisponible (déjà emprunté ou hors service). » | Le matériel ne peut pas être prêté |
| « Conflit : la salle est déjà réservée sur ce créneau horaire. » | Une autre réservation chevauche le créneau |
| « Salle indisponible : réservation impossible. » | La salle est marquée non disponible |
| « La date de réservation ne peut pas être dans le passé. » | Date saisie antérieure à aujourd'hui |
| « L'heure de fin doit être postérieure à l'heure de début. » | Plage horaire incohérente |
| « Jeton de sécurité invalide ou expiré. » | Session expirée ou tentative d'action depuis une page trop ancienne — réessayer depuis le menu |

## 8. Bonnes pratiques

- **Ne partagez pas votre compte** : chaque agent doit utiliser son propre
  identifiant. Les actions sont tracées en base via l'identifiant de session.
- **Déconnectez-vous** systématiquement avant de quitter un poste partagé.
- **Vérifiez la disponibilité** d'un livre avant de promettre un emprunt à
  un adhérent ; le tableau de bord en affiche la synthèse.
- **Traitez les retards** régulièrement : le bloc rouge du tableau de bord
  liste les emprunts dont la date est dépassée.
- En cas de message d'erreur incompréhensible, signaler l'incident à
  l'administrateur via le système de tickets (GLPI) en précisant l'action
  réalisée et le message reçu.
