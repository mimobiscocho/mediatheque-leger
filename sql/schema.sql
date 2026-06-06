-- =====================================================================
--  Médiathèque de Bourg-la-Reine
--  Schéma de base de données — SGBD : MySQL / MariaDB
--  Auteur : SEBAH Nassim — BTS SIO SLAM — Session 2026
--
--  Import : mysql -u root -p < sql/schema.sql
--           (ou via phpMyAdmin > Importer)
-- =====================================================================

DROP DATABASE IF EXISTS mediatheque;
CREATE DATABASE mediatheque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mediatheque;

-- ---------------------------------------------------------------------
--  abonnement : types d'abonnement proposés aux adhérents
-- ---------------------------------------------------------------------
CREATE TABLE abonnement (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    libelle        VARCHAR(60)  NOT NULL,
    tarif          DECIMAL(6,2) NOT NULL DEFAULT 0,
    duree_mois     INT          NOT NULL DEFAULT 12,
    quota_emprunts INT          NOT NULL DEFAULT 5
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  agent : utilisateurs de l'application (personnel de la médiathèque)
--          -> sert à l'authentification (connexion sécurisée)
-- ---------------------------------------------------------------------
CREATE TABLE agent (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(60)  NOT NULL,
    prenom        VARCHAR(60)  NOT NULL,
    email         VARCHAR(120) NOT NULL UNIQUE,   -- sert d'identifiant de connexion
    mot_de_passe  VARCHAR(255) NOT NULL,          -- haché (password_hash / crypt) — jamais en clair
    role          ENUM('admin','agent') NOT NULL DEFAULT 'agent',
    actif         TINYINT(1)   NOT NULL DEFAULT 1,
    date_creation DATE         NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  adherent : les membres de la médiathèque
-- ---------------------------------------------------------------------
CREATE TABLE adherent (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(60)  NOT NULL,
    prenom              VARCHAR(60)  NOT NULL,
    email               VARCHAR(120) NOT NULL UNIQUE,
    telephone           VARCHAR(20),
    adresse             VARCHAR(180),
    abonnement_id       INT,
    date_inscription    DATE         NOT NULL,
    date_fin_abonnement DATE,
    actif               TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_adherent_abonnement
        FOREIGN KEY (abonnement_id) REFERENCES abonnement(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  livre : produit "collection" (plusieurs exemplaires possibles)
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
--  materiel : produit "matériel" empruntable (exemplaire unique)
-- ---------------------------------------------------------------------
CREATE TABLE materiel (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(120) NOT NULL,
    categorie   VARCHAR(60),
    description VARCHAR(255),
    etat        ENUM('neuf','bon','use','hors_service') NOT NULL DEFAULT 'bon',
    disponible  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  salle : salles de coworking réservables
-- ---------------------------------------------------------------------
CREATE TABLE salle (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(80) NOT NULL,
    capacite    INT NOT NULL DEFAULT 1,
    equipements VARCHAR(255),
    disponible  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  pret : emprunt d'UN livre OU d'UN matériel par un adhérent
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
--  reservation : réservation d'une salle de coworking sur un créneau
-- ---------------------------------------------------------------------
CREATE TABLE reservation (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    adherent_id      INT NOT NULL,
    salle_id         INT NOT NULL,
    date_reservation DATE NOT NULL,
    heure_debut      TIME NOT NULL,
    heure_fin        TIME NOT NULL,
    statut           ENUM('confirmee','annulee','terminee') NOT NULL DEFAULT 'confirmee',
    CONSTRAINT fk_resa_adherent FOREIGN KEY (adherent_id) REFERENCES adherent(id) ON DELETE CASCADE,
    CONSTRAINT fk_resa_salle    FOREIGN KEY (salle_id)    REFERENCES salle(id)    ON DELETE CASCADE,
    CONSTRAINT chk_resa_heures  CHECK (heure_fin > heure_debut)
) ENGINE=InnoDB;

-- =====================================================================
--  TRIGGERS — automatisation de la vérification des disponibilités
--  (exigence du cahier des charges)
-- =====================================================================
DELIMITER //

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
--       (Les suppressions en cascade depuis livre/materiel n'activent pas
--        ce trigger : MySQL ne déclenche pas les triggers sur action FK.)
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

-- 4) Avant une réservation :
--    - refuse une salle marquée indisponible
--    - interdit le chevauchement de créneaux sur une salle
CREATE TRIGGER trg_reservation_before_insert
BEFORE INSERT ON reservation
FOR EACH ROW
BEGIN
    DECLARE v_dispo TINYINT;
    DECLARE nb INT;

    SELECT disponible INTO v_dispo FROM salle WHERE id = NEW.salle_id;
    IF v_dispo IS NULL OR v_dispo = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Salle indisponible : réservation impossible.';
    END IF;

    SELECT COUNT(*) INTO nb
    FROM reservation
    WHERE salle_id = NEW.salle_id
      AND date_reservation = NEW.date_reservation
      AND statut = 'confirmee'
      AND NEW.heure_debut < heure_fin
      AND NEW.heure_fin   > heure_debut;
    IF nb > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflit : la salle est déjà réservée sur ce créneau horaire.';
    END IF;
END//

DELIMITER ;

-- =====================================================================
--  JEU DE DONNÉES DE DÉMONSTRATION
-- =====================================================================

-- Comptes agents (mots de passe hachés ; identifiants ci-dessous pour la démo) :
--   admin@mediatheque.fr  /  admin123   (rôle admin)
--   agent@mediatheque.fr  /  agent123   (rôle agent)
-- Hachage bcrypt (password_hash / PASSWORD_DEFAULT), coût 12.
INSERT INTO agent (nom, prenom, email, mot_de_passe, role, actif, date_creation) VALUES
('Admin',  'Médiathèque', 'admin@mediatheque.fr', '$2y$12$8T9Jnas.F8CWgnJxZ2lZceWE9oIQnj0FPS0tQ2PxwFT0mL7BetlMi', 'admin', 1, '2026-01-05'),
('Petit',  'Julie',       'agent@mediatheque.fr', '$2y$12$ywpyIeBqfufB0t4z1tdzU.uF.wCjAoU2g.SyITK2HZ5VTWzeUU8XS', 'agent', 1, '2026-01-08');

INSERT INTO abonnement (libelle, tarif, duree_mois, quota_emprunts) VALUES
('Standard',   15.00, 12, 5),
('Étudiant',    8.00, 12, 5),
('Premium',    30.00, 12, 10),
('Découverte',  0.00,  3, 2);

INSERT INTO adherent (nom, prenom, email, telephone, adresse, abonnement_id, date_inscription, date_fin_abonnement, actif) VALUES
('Martin',  'Sophie',  'sophie.martin@email.fr',  '0612345678', '12 rue des Lilas, Bourg-la-Reine',     1, '2026-01-10', '2027-01-10', 1),
('Dubois',  'Karim',   'karim.dubois@email.fr',   '0623456789', '5 av. du Général Leclerc, Bourg-la-Reine', 2, '2026-01-15', '2027-01-15', 1),
('Nguyen',  'Camille', 'camille.nguyen@email.fr', '0634567890', '28 rue de la Bièvre, Bourg-la-Reine',  3, '2026-02-01', '2027-02-01', 1),
('Lefevre', 'Hugo',    'hugo.lefevre@email.fr',   '0645678901', '3 place de la Mairie, Bourg-la-Reine',  4, '2026-02-20', '2026-05-20', 1);

INSERT INTO livre (titre, auteur, isbn, editeur, annee_publication, genre, quantite_totale, quantite_disponible) VALUES
('L''Étranger',              'Albert Camus',       '9782070360024', 'Gallimard',  1942, 'Roman',          4, 4),
('Le Petit Prince',         'Antoine de Saint-Exupéry', '9782070612758', 'Gallimard', 1943, 'Conte',     6, 6),
('1984',                    'George Orwell',      '9782070368228', 'Gallimard',  1949, 'Science-fiction', 3, 3),
('Sapiens',                 'Yuval Noah Harari',  '9782226257017', 'Albin Michel', 2015, 'Essai',         2, 2),
('Les Misérables',          'Victor Hugo',        '9782253096337', 'Le Livre de Poche', 1862, 'Roman',    3, 3),
('Clean Code',              'Robert C. Martin',   '9780132350884', 'Prentice Hall', 2008, 'Informatique', 2, 2);

INSERT INTO materiel (nom, categorie, description, etat, disponible) VALUES
('Ordinateur portable Dell', 'Informatique', 'PC portable 15" pour travail sur place', 'bon',  1),
('Liseuse Kindle',           'Multimédia',   'Liseuse électronique avec 100 titres',   'neuf', 1),
('Vidéoprojecteur Epson',    'Audiovisuel',  'Projecteur Full HD pour salle de réunion', 'bon', 1),
('Casque audio Bose',        'Audiovisuel',  'Casque à réduction de bruit',            'use',  1);

INSERT INTO salle (nom, capacite, equipements, disponible) VALUES
('Salle Bièvre',     4,  'Écran, tableau blanc, Wi-Fi',          1),
('Salle Coworking A', 8, 'Wi-Fi, prises individuelles, café',    1),
('Salle Conférence', 20, 'Vidéoprojecteur, micro, estrade',      1),
('Box silencieux',    2,  'Isolation phonique, Wi-Fi',           1);

-- Quelques prêts (les triggers décrémentent automatiquement les stocks)
INSERT INTO pret (adherent_id, livre_id, materiel_id, date_pret, date_retour_prevue, statut) VALUES
(1, 1, NULL, '2026-05-10', '2026-05-24', 'en_cours'),
(2, NULL, 2, '2026-05-15', '2026-05-29', 'en_cours');

-- Quelques réservations de salles
INSERT INTO reservation (adherent_id, salle_id, date_reservation, heure_debut, heure_fin, statut) VALUES
(3, 2, '2026-06-02', '09:00:00', '12:00:00', 'confirmee'),
(1, 1, '2026-06-03', '14:00:00', '16:00:00', 'confirmee');
