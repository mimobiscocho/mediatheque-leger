-- =====================================================================
--  Migration : ajout des comptes clients (espace personnel adhérent)
--  À exécuter UNE SEULE FOIS sur une base déjà installée.
--  Si vous repartez d'un import frais de schema.sql, ce fichier est inutile.
--
--  Usage : mysql -u root -p mediatheque < sql/upgrade_comptes_clients.sql
-- =====================================================================
USE mediatheque;

-- Colonne mot de passe pour le compte client (NULL = pas de compte)
ALTER TABLE adherent
    ADD COLUMN mot_de_passe VARCHAR(255) DEFAULT NULL AFTER adresse;

-- Compte de démonstration : sophie.martin@email.fr / client123
UPDATE adherent
   SET mot_de_passe = '$2y$12$tGyLa7ybnAWra4Zga4/5Zuubm.AQV6ZeXQu6xYHw.Ig0xKWiuyy0C'
 WHERE email = 'sophie.martin@email.fr';
