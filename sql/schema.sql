-- =====================================================================
--  Médiathèque de Bourg-la-Reine — Base de données UNIFIÉE
--  SGBD : MySQL / MariaDB
--  Auteur : SEBAH Nassim — BTS SIO SLAM — Session 2026
--
--  Cette base est partagée par les DEUX applications :
--    - le client léger (PHP / web)     — gestion quotidienne, multiposte
--    - le client lourd (Java / Swing)  — gestion approfondie sur poste dédié
--
--  Import : mysql -u root -p < sql/schema.sql
--           (ou via phpMyAdmin > Importer)
-- =====================================================================

DROP DATABASE IF EXISTS mediatheque;
CREATE DATABASE mediatheque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mediatheque;

-- =====================================================================
--  AUTHENTIFICATION
--  Chaque application a son propre système de comptes :
--    - agent  : utilisé par le client léger (login = email,  hash bcrypt)
--    - profil : utilisé par le client lourd (login = libre,  hash PBKDF2)
-- =====================================================================

-- ---------------------------------------------------------------------
--  agent : comptes des agents pour l'application WEB (client léger)
-- ---------------------------------------------------------------------
CREATE TABLE agent (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(60)  NOT NULL,
    prenom        VARCHAR(60)  NOT NULL,
    email         VARCHAR(120) NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL,          -- empreinte bcrypt
    role          ENUM('admin','agent') NOT NULL DEFAULT 'agent',
    actif         TINYINT(1)   NOT NULL DEFAULT 1,
    date_creation DATE         NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  profil : comptes des agents pour l'application DESKTOP (client lourd)
-- ---------------------------------------------------------------------
CREATE TABLE profil (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    login         VARCHAR(50)  NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL,          -- empreinte PBKDF2-HMAC-SHA256 salée
    nom           VARCHAR(80)  NOT NULL,
    prenom        VARCHAR(80)  NOT NULL,
    role          ENUM('ADMIN','AGENT') NOT NULL DEFAULT 'AGENT',
    date_creation DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
--  ABONNEMENTS (types d'abonnement détaillés — utilisés par le léger)
-- =====================================================================
CREATE TABLE abonnement (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    libelle        VARCHAR(60)  NOT NULL,
    tarif          DECIMAL(6,2) NOT NULL DEFAULT 0,
    duree_mois     INT          NOT NULL DEFAULT 12,
    quota_emprunts INT          NOT NULL DEFAULT 5
) ENGINE=InnoDB;

-- =====================================================================
--  ADHÉRENT : table commune aux deux applications
--    - le client léger gère les emprunts, les abonnements détaillés
--    - le client lourd gère les réservations, animations, factures
-- =====================================================================
CREATE TABLE adherent (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(80)  NOT NULL,
    prenom              VARCHAR(80)  NOT NULL,
    email               VARCHAR(120) UNIQUE,
    telephone           VARCHAR(20),
    adresse             VARCHAR(200),
    -- Abonnement détaillé (FK) : exploité par le client léger
    abonnement_id       INT,
    -- Type d'abonnement simplifié : exploité par le client lourd
    type_abonnement     ENUM('STANDARD','PREMIUM','ETUDIANT') NOT NULL DEFAULT 'STANDARD',
    date_inscription    DATE         NOT NULL DEFAULT (CURRENT_DATE),
    date_fin_abonnement DATE,
    actif               TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_adherent_abonnement
        FOREIGN KEY (abonnement_id) REFERENCES abonnement(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
--  COLLECTION : livres et matériels (gérés par le client léger)
-- =====================================================================
CREATE TABLE livre (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    titre               VARCHAR(150) NOT NULL,
    auteur              VARCHAR(120) NOT NULL,
    isbn                VARCHAR(20),
    editeur             VARCHAR(120),
    annee_publication   INT,
    genre               VARCHAR(60),
    quantite_totale     INT NOT NULL DEFAULT 1,
    quantite_disponible INT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE materiel (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(120) NOT NULL,
    categorie   VARCHAR(60),
    description VARCHAR(255),
    etat        ENUM('neuf','bon','use','hors_service') NOT NULL DEFAULT 'bon',
    disponible  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- =====================================================================
--  SALLE : table partagée (coworking + animations)
-- =====================================================================
CREATE TABLE salle (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(80) NOT NULL UNIQUE,
    capacite    INT NOT NULL DEFAULT 1 CHECK (capacite > 0),
    equipement  VARCHAR(255),
    disponible  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- =====================================================================
--  TECHNICIENS et ANIMATIONS (gérés par le client lourd)
-- =====================================================================
CREATE TABLE technicien (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(80)  NOT NULL,
    prenom      VARCHAR(80)  NOT NULL,
    email       VARCHAR(120) UNIQUE,
    telephone   VARCHAR(20),
    specialite  VARCHAR(120)
) ENGINE=InnoDB;

CREATE TABLE animation (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    titre          VARCHAR(150) NOT NULL,
    description    TEXT,
    date_animation DATE NOT NULL,
    heure_debut    TIME NOT NULL,
    heure_fin      TIME NOT NULL,
    nb_places      INT NOT NULL DEFAULT 10 CHECK (nb_places >= 0),
    salle_id       INT NOT NULL,
    technicien_id  INT NOT NULL,
    CONSTRAINT fk_animation_salle      FOREIGN KEY (salle_id)      REFERENCES salle(id)      ON DELETE RESTRICT,
    CONSTRAINT fk_animation_technicien FOREIGN KEY (technicien_id) REFERENCES technicien(id) ON DELETE RESTRICT,
    CONSTRAINT chk_horaire_anim CHECK (heure_fin > heure_debut)
) ENGINE=InnoDB;

-- =====================================================================
--  PRÊTS (gérés par le client léger)
-- =====================================================================
CREATE TABLE pret (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    adherent_id           INT NOT NULL,
    livre_id              INT,
    materiel_id           INT,
    date_pret             DATE NOT NULL,
    date_retour_prevue    DATE NOT NULL,
    date_retour_effective DATE,
    statut                ENUM('en_cours','rendu','en_retard') NOT NULL DEFAULT 'en_cours',
    CONSTRAINT fk_pret_adherent FOREIGN KEY (adherent_id) REFERENCES adherent(id) ON DELETE CASCADE,
    CONSTRAINT fk_pret_livre    FOREIGN KEY (livre_id)    REFERENCES livre(id)    ON DELETE CASCADE,
    CONSTRAINT fk_pret_materiel FOREIGN KEY (materiel_id) REFERENCES materiel(id) ON DELETE CASCADE,
    -- Un prêt porte sur exactement un produit (un livre OU un matériel)
    CONSTRAINT chk_pret_produit CHECK (
        (livre_id IS NOT NULL AND materiel_id IS NULL) OR
        (livre_id IS NULL AND materiel_id IS NOT NULL)
    )
) ENGINE=InnoDB;

-- =====================================================================
--  RÉSERVATION : table partagée par les deux applications
--  Statut en MAJUSCULES pour l'uniformiser (TERMINEE = clôturée).
-- =====================================================================
CREATE TABLE reservation (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    adherent_id      INT NOT NULL,
    salle_id         INT NOT NULL,
    date_reservation DATE NOT NULL,
    heure_debut      TIME NOT NULL,
    heure_fin        TIME NOT NULL,
    statut           ENUM('CONFIRMEE','ANNULEE','TERMINEE') NOT NULL DEFAULT 'CONFIRMEE',
    CONSTRAINT fk_resa_adherent FOREIGN KEY (adherent_id) REFERENCES adherent(id) ON DELETE CASCADE,
    CONSTRAINT fk_resa_salle    FOREIGN KEY (salle_id)    REFERENCES salle(id)    ON DELETE RESTRICT,
    CONSTRAINT chk_resa_heures  CHECK (heure_fin > heure_debut)
) ENGINE=InnoDB;

-- =====================================================================
--  FACTURE (gérée par le client lourd)
-- =====================================================================
CREATE TABLE facture (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    adherent_id   INT NOT NULL,
    libelle       VARCHAR(200) NOT NULL,
    montant       DECIMAL(10,2) NOT NULL CHECK (montant >= 0),
    date_emission DATE NOT NULL DEFAULT (CURRENT_DATE),
    statut        ENUM('PAYEE','IMPAYEE') NOT NULL DEFAULT 'IMPAYEE',
    CONSTRAINT fk_facture_adherent FOREIGN KEY (adherent_id) REFERENCES adherent(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
--  TRIGGERS — automatisation des règles métier en base
--  (exigence du cahier des charges)
-- =====================================================================
DELIMITER //

-- ---------------------------------------------------------------------
--  PRÊTS : gestion automatique de la disponibilité
-- ---------------------------------------------------------------------

-- 1) Avant un prêt : refuse l'emprunt si le produit est indisponible
CREATE TRIGGER trg_pret_before_insert
BEFORE INSERT ON pret
FOR EACH ROW
BEGIN
    DECLARE dispo INT;
    IF NEW.livre_id IS NOT NULL THEN
        SELECT quantite_disponible INTO dispo FROM livre WHERE id = NEW.livre_id;
        IF dispo IS NULL OR dispo < 1 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Livre indisponible : aucun exemplaire en stock.';
        END IF;
    ELSEIF NEW.materiel_id IS NOT NULL THEN
        SELECT disponible INTO dispo FROM materiel WHERE id = NEW.materiel_id;
        IF dispo IS NULL OR dispo < 1 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Matériel indisponible (déjà emprunté ou hors service).';
        END IF;
    END IF;
END//

-- 2) Après un prêt : décrémente le stock du produit emprunté
CREATE TRIGGER trg_pret_after_insert
AFTER INSERT ON pret
FOR EACH ROW
BEGIN
    IF NEW.livre_id IS NOT NULL THEN
        UPDATE livre SET quantite_disponible = quantite_disponible - 1 WHERE id = NEW.livre_id;
    ELSEIF NEW.materiel_id IS NOT NULL THEN
        UPDATE materiel SET disponible = 0 WHERE id = NEW.materiel_id;
    END IF;
END//

-- 3) Au retour (date_retour_effective renseignée) : ré-incrémente le stock
CREATE TRIGGER trg_pret_after_update
AFTER UPDATE ON pret
FOR EACH ROW
BEGIN
    IF NEW.date_retour_effective IS NOT NULL AND OLD.date_retour_effective IS NULL THEN
        IF NEW.livre_id IS NOT NULL THEN
            UPDATE livre SET quantite_disponible = quantite_disponible + 1 WHERE id = NEW.livre_id;
        ELSEIF NEW.materiel_id IS NOT NULL THEN
            UPDATE materiel SET disponible = 1 WHERE id = NEW.materiel_id;
        END IF;
    END IF;
END//

-- 3bis) Suppression d'un prêt non rendu : restaure le stock du produit.
CREATE TRIGGER trg_pret_after_delete
AFTER DELETE ON pret
FOR EACH ROW
BEGIN
    IF OLD.date_retour_effective IS NULL THEN
        IF OLD.livre_id IS NOT NULL THEN
            UPDATE livre SET quantite_disponible = quantite_disponible + 1 WHERE id = OLD.livre_id;
        ELSEIF OLD.materiel_id IS NOT NULL THEN
            UPDATE materiel SET disponible = 1 WHERE id = OLD.materiel_id;
        END IF;
    END IF;
END//

-- ---------------------------------------------------------------------
--  RÉSERVATIONS : vérification de disponibilité et de chevauchement
-- ---------------------------------------------------------------------

CREATE TRIGGER trg_reservation_before_insert
BEFORE INSERT ON reservation
FOR EACH ROW
BEGIN
    DECLARE v_dispo TINYINT;
    DECLARE nb INT;

    SELECT disponible INTO v_dispo FROM salle WHERE id = NEW.salle_id;
    IF v_dispo IS NULL OR v_dispo = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Salle indisponible : reservation impossible.';
    END IF;

    SELECT COUNT(*) INTO nb
    FROM reservation
    WHERE salle_id = NEW.salle_id
      AND date_reservation = NEW.date_reservation
      AND statut = 'CONFIRMEE'
      AND NEW.heure_debut < heure_fin
      AND NEW.heure_fin   > heure_debut;
    IF nb > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Creneau deja reserve pour cette salle.';
    END IF;
END//

CREATE TRIGGER trg_reservation_before_update
BEFORE UPDATE ON reservation
FOR EACH ROW
BEGIN
    DECLARE nb INT;
    IF NEW.statut = 'CONFIRMEE' THEN
        SELECT COUNT(*) INTO nb
        FROM reservation
        WHERE salle_id = NEW.salle_id
          AND id <> NEW.id
          AND date_reservation = NEW.date_reservation
          AND statut = 'CONFIRMEE'
          AND NEW.heure_debut < heure_fin
          AND NEW.heure_fin   > heure_debut;
        IF nb > 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Creneau deja reserve pour cette salle.';
        END IF;
    END IF;
END//

-- ---------------------------------------------------------------------
--  ANIMATIONS : aucune double-occupation de salle sur un même créneau
-- ---------------------------------------------------------------------

CREATE TRIGGER trg_animation_before_insert
BEFORE INSERT ON animation
FOR EACH ROW
BEGIN
    DECLARE nb INT;
    SELECT COUNT(*) INTO nb
    FROM animation
    WHERE salle_id = NEW.salle_id
      AND date_animation = NEW.date_animation
      AND NEW.heure_debut < heure_fin
      AND NEW.heure_fin   > heure_debut;
    IF nb > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La salle est deja occupee par une animation sur ce creneau.';
    END IF;
END//

CREATE TRIGGER trg_animation_before_update
BEFORE UPDATE ON animation
FOR EACH ROW
BEGIN
    DECLARE nb INT;
    SELECT COUNT(*) INTO nb
    FROM animation
    WHERE salle_id = NEW.salle_id
      AND id <> NEW.id
      AND date_animation = NEW.date_animation
      AND NEW.heure_debut < heure_fin
      AND NEW.heure_fin   > heure_debut;
    IF nb > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La salle est deja occupee par une animation sur ce creneau.';
    END IF;
END//

DELIMITER ;

-- =====================================================================
--  JEU DE DONNÉES DE DÉMONSTRATION
-- =====================================================================

-- ---------------------------------------------------------------------
--  Comptes des deux applications (mots de passe : admin123 / agent123)
--   - léger : bcrypt   ($2y$12$...)
--   - lourd : PBKDF2-HMAC-SHA256 salé (pbkdf2_sha256$600000$...)
-- ---------------------------------------------------------------------
INSERT INTO agent (nom, prenom, email, mot_de_passe, role, actif, date_creation) VALUES
('Admin', 'Médiathèque', 'admin@mediatheque.fr', '$2y$12$8T9Jnas.F8CWgnJxZ2lZceWE9oIQnj0FPS0tQ2PxwFT0mL7BetlMi', 'admin', 1, '2026-01-05'),
('Petit', 'Julie',       'agent@mediatheque.fr', '$2y$12$ywpyIeBqfufB0t4z1tdzU.uF.wCjAoU2g.SyITK2HZ5VTWzeUU8XS', 'agent', 1, '2026-01-08');

INSERT INTO profil (login, mot_de_passe, nom, prenom, role) VALUES
('admin', 'pbkdf2_sha256$600000$kYdMj/fJowhQRkVtIRVfcw==$HP9KtqpivNhzRsWVbrZMztl2ZM50eM7iHOa549xn0cQ=', 'SEBAH',  'Nassim', 'ADMIN'),
('agent', 'pbkdf2_sha256$600000$3L3LJ+tGGTiQBhkedUdS1A==$WJXNC6YzeMb2scj0x62TyCGbrZDXdv8sM1TorHr/AFA=', 'MARTIN', 'Claire', 'AGENT');

-- ---------------------------------------------------------------------
--  Abonnements
-- ---------------------------------------------------------------------
INSERT INTO abonnement (libelle, tarif, duree_mois, quota_emprunts) VALUES
('Standard',   15.00, 12, 5),
('Étudiant',    8.00, 12, 5),
('Premium',    30.00, 12, 10),
('Découverte',  0.00,  3, 2);

-- ---------------------------------------------------------------------
--  Adhérents : table commune. abonnement_id et type_abonnement
--  sont renseignés en parallèle pour faciliter l'usage des deux apps.
-- ---------------------------------------------------------------------
INSERT INTO adherent (nom, prenom, email, telephone, adresse, abonnement_id, type_abonnement, date_inscription, date_fin_abonnement, actif) VALUES
('Martin',  'Sophie',  'sophie.martin@email.fr',  '0612345678', '12 rue des Lilas, Bourg-la-Reine',         1, 'STANDARD', '2026-01-10', '2027-01-10', 1),
('Dubois',  'Karim',   'karim.dubois@email.fr',   '0623456789', '5 av. du Général Leclerc, Bourg-la-Reine', 2, 'ETUDIANT', '2026-01-15', '2027-01-15', 1),
('Nguyen',  'Camille', 'camille.nguyen@email.fr', '0634567890', '28 rue de la Bièvre, Bourg-la-Reine',      3, 'PREMIUM',  '2026-02-01', '2027-02-01', 1),
('Lefevre', 'Hugo',    'hugo.lefevre@email.fr',   '0645678901', '3 place de la Mairie, Bourg-la-Reine',     4, 'STANDARD', '2026-02-20', '2026-05-20', 1),
('Dupont',  'Jean',    'jean.dupont@mail.fr',     '0601020304', '12 rue de la Paix, Bourg-la-Reine',        1, 'STANDARD', '2025-01-15', '2026-01-15', 1),
('Bernard', 'Sophie',  'sophie.bernard@mail.fr',  '0611223344', '5 av. du Parc, Bourg-la-Reine',            3, 'PREMIUM',  '2024-09-03', '2025-09-03', 1),
('Moreau',  'Emma',    'emma.moreau@mail.fr',     '0699887766', '3 place de la Mairie, Bourg-la-Reine',     1, 'STANDARD', '2025-03-12', '2026-03-12', 1);

-- ---------------------------------------------------------------------
--  Collection (livres + matériels)
-- ---------------------------------------------------------------------
INSERT INTO livre (titre, auteur, isbn, editeur, annee_publication, genre, quantite_totale, quantite_disponible) VALUES
('L''Étranger',     'Albert Camus',             '9782070360024', 'Gallimard',         1942, 'Roman',           4, 4),
('Le Petit Prince', 'Antoine de Saint-Exupéry', '9782070612758', 'Gallimard',         1943, 'Conte',           6, 6),
('1984',            'George Orwell',            '9782070368228', 'Gallimard',         1949, 'Science-fiction', 3, 3),
('Sapiens',         'Yuval Noah Harari',        '9782226257017', 'Albin Michel',      2015, 'Essai',           2, 2),
('Les Misérables',  'Victor Hugo',              '9782253096337', 'Le Livre de Poche', 1862, 'Roman',           3, 3),
('Clean Code',      'Robert C. Martin',         '9780132350884', 'Prentice Hall',     2008, 'Informatique',    2, 2);

INSERT INTO materiel (nom, categorie, description, etat, disponible) VALUES
('Ordinateur portable Dell', 'Informatique', 'PC portable 15" pour travail sur place',   'bon',  1),
('Liseuse Kindle',           'Multimédia',   'Liseuse électronique avec 100 titres',     'neuf', 1),
('Vidéoprojecteur Epson',    'Audiovisuel',  'Projecteur Full HD pour salle de réunion', 'bon',  1),
('Casque audio Bose',        'Audiovisuel',  'Casque à réduction de bruit',              'use',  1);

-- ---------------------------------------------------------------------
--  Salles (utilisées par les deux apps pour réservations et animations)
-- ---------------------------------------------------------------------
INSERT INTO salle (nom, capacite, equipement, disponible) VALUES
('Salle Bièvre',      4,  'Écran, tableau blanc, Wi-Fi',          1),
('Salle Coworking A', 8,  'Wi-Fi, prises individuelles, café',    1),
('Salle Conférence',  20, 'Vidéoprojecteur, micro, estrade',      1),
('Box silencieux',    2,  'Isolation phonique, Wi-Fi',            1),
('Salle Voltaire',    8,  'Vidéoprojecteur, tableau blanc, Wi-Fi', 1),
('Salle Curie',       4,  'Wi-Fi, prises USB',                    1),
('Salle Hugo',        12, 'Écran 4K, visioconférence, Wi-Fi',     1),
('Salle Atelier',     6,  'Imprimante 3D, ordinateurs',           0);

-- ---------------------------------------------------------------------
--  Techniciens (animateurs / intervenants)
-- ---------------------------------------------------------------------
INSERT INTO technicien (nom, prenom, email, telephone, specialite) VALUES
('Lefevre',  'Marc',   'marc.lefevre@medbar.fr',   '0708091011', 'Informatique / Robotique'),
('Garnier',  'Julie',  'julie.garnier@medbar.fr',  '0712131415', 'Atelier lecture'),
('Rousseau', 'Thomas', 'thomas.rousseau@medbar.fr','0716171819', 'Multimédia / Vidéo');

-- ---------------------------------------------------------------------
--  Animations (futures)
-- ---------------------------------------------------------------------
INSERT INTO animation (titre, description, date_animation, heure_debut, heure_fin, nb_places, salle_id, technicien_id) VALUES
('Initiation Python', 'Atelier de programmation pour débutants',  '2026-06-10', '14:00:00', '16:00:00', 8,  5, 1),
('Club de lecture',   'Échange autour des nouveautés littéraires','2026-06-12', '18:00:00', '19:30:00', 12, 7, 2),
('Montage vidéo',     'Découverte du montage vidéo',              '2026-06-15', '10:00:00', '12:00:00', 6,  5, 3);

-- ---------------------------------------------------------------------
--  Quelques prêts (les triggers décrémentent automatiquement les stocks)
-- ---------------------------------------------------------------------
INSERT INTO pret (adherent_id, livre_id, materiel_id, date_pret, date_retour_prevue, statut) VALUES
(1, 1, NULL, '2026-05-10', '2026-05-24', 'en_cours'),
(2, NULL, 2, '2026-05-15', '2026-05-29', 'en_cours');

-- ---------------------------------------------------------------------
--  Quelques réservations de salles
-- ---------------------------------------------------------------------
INSERT INTO reservation (adherent_id, salle_id, date_reservation, heure_debut, heure_fin, statut) VALUES
(3, 2, '2026-06-02', '09:00:00', '12:00:00', 'CONFIRMEE'),
(1, 1, '2026-06-03', '14:00:00', '16:00:00', 'CONFIRMEE'),
(5, 6, '2026-06-08', '09:00:00', '11:00:00', 'CONFIRMEE'),
(6, 7, '2026-06-09', '14:00:00', '17:00:00', 'CONFIRMEE');

-- ---------------------------------------------------------------------
--  Factures
-- ---------------------------------------------------------------------
INSERT INTO facture (adherent_id, libelle, montant, date_emission, statut) VALUES
(5, 'Abonnement annuel STANDARD',  25.00, '2025-01-15', 'PAYEE'),
(6, 'Abonnement annuel PREMIUM',   45.00, '2024-09-03', 'PAYEE'),
(2, 'Abonnement annuel ETUDIANT',  12.00, '2025-10-21', 'IMPAYEE'),
(5, 'Location salle Curie (2h)',   10.00, '2026-06-08', 'IMPAYEE');
