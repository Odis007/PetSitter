-- Crée la base seulement si elle n'existe pas déjà
CREATE DATABASE IF NOT EXISTS petsitter CHARACTER SET utf8mb4;

USE petsitter;

-- On supprime les anciennes tables pour pouvoir réimporter le fichier sans erreur.
-- Ordre inverse de la création : on supprime d'abord les tables qui dépendent des autres.
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS candidatures;
DROP TABLE IF EXISTS annonces;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS utilisateur; -- ancien nom de la table

-- 1. Les utilisateurs
-- Un même membre peut être propriétaire ET gardien selon les annonces.
-- Le rôle sert seulement à distinguer l'administrateur.
CREATE TABLE utilisateurs (
	id int AUTO_INCREMENT PRIMARY KEY,
	nom varchar(50) NOT NULL,
	email varchar(100) NOT NULL UNIQUE,
	password varchar(255) NOT NULL,
	role enum('membre', 'admin') NOT NULL DEFAULT 'membre'
);

-- 2. Les annonces publiées par un propriétaire
CREATE TABLE annonces (
	id int AUTO_INCREMENT PRIMARY KEY,
	titre varchar(100) NOT NULL,
	type_animal varchar(50) NOT NULL,
	ville varchar(50) NOT NULL,
	date_debut DATE NOT NULL,
	date_fin DATE NOT NULL,
	description TEXT,
	statut enum('ouverte', 'pourvue', 'terminee') NOT NULL DEFAULT 'ouverte',
	id_proprietaire int NOT NULL,
	FOREIGN KEY (id_proprietaire) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- 3. Les candidatures d'un gardien à une annonce
CREATE TABLE candidatures (
	id int AUTO_INCREMENT PRIMARY KEY,
	message TEXT,
	statut enum('en_attente', 'acceptee', 'refusee') NOT NULL DEFAULT 'en_attente',
	date_candidature DATETIME DEFAULT CURRENT_TIMESTAMP,
	id_annonce int NOT NULL,
	id_gardien int NOT NULL,
	FOREIGN KEY (id_annonce) REFERENCES annonces(id) ON DELETE CASCADE,
	FOREIGN KEY (id_gardien) REFERENCES utilisateurs(id) ON DELETE CASCADE,
	-- Règle métier : un gardien ne peut pas postuler deux fois à la même annonce
	UNIQUE (id_annonce, id_gardien)
);

-- 4. Les avis laissés après une garde terminée
CREATE TABLE avis (
	id int AUTO_INCREMENT PRIMARY KEY,
	note int NOT NULL CHECK (note BETWEEN 1 AND 5),
	commentaire TEXT,
	date DATETIME DEFAULT CURRENT_TIMESTAMP,
	id_annonce int NOT NULL,
	id_auteur int NOT NULL, -- celui qui note
	id_cible int NOT NULL,  -- celui qui est noté
	FOREIGN KEY (id_annonce) REFERENCES annonces(id) ON DELETE CASCADE,
	FOREIGN KEY (id_auteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
	FOREIGN KEY (id_cible) REFERENCES utilisateurs(id) ON DELETE CASCADE,
	-- Règle métier : chaque utilisateur ne note qu'une seule fois par garde
	UNIQUE (id_annonce, id_auteur)
);

-- ------------------------------------------------------------------
-- Jeu de données de test
-- Mot de passe de tous les comptes : motdepasse
-- ------------------------------------------------------------------

INSERT INTO utilisateurs (id, nom, email, password, role) VALUES
(1, 'Admin',  'admin@petsitter.fr', '$2y$10$9hZcSx9JOq.DICUjF73nVO/M6V9pAbmcJjxyhBXUC69vsAXO0NC0m', 'admin'),
(2, 'Alice',  'alice@petsitter.fr', '$2y$10$9hZcSx9JOq.DICUjF73nVO/M6V9pAbmcJjxyhBXUC69vsAXO0NC0m', 'membre'),
(3, 'Bruno',  'bruno@petsitter.fr', '$2y$10$9hZcSx9JOq.DICUjF73nVO/M6V9pAbmcJjxyhBXUC69vsAXO0NC0m', 'membre'),
(4, 'Chloé',  'chloe@petsitter.fr', '$2y$10$9hZcSx9JOq.DICUjF73nVO/M6V9pAbmcJjxyhBXUC69vsAXO0NC0m', 'membre'),
(5, 'David',  'david@petsitter.fr', '$2y$10$9hZcSx9JOq.DICUjF73nVO/M6V9pAbmcJjxyhBXUC69vsAXO0NC0m', 'membre');

-- Les dates sont calculées à partir d'aujourd'hui (CURDATE)
-- pour que le jeu de données reste cohérent quel que soit le jour de l'import.
INSERT INTO annonces (id, titre, type_animal, ville, date_debut, date_fin, description, statut, id_proprietaire) VALUES
(1, 'Garde de Minou ce week-end', 'Chat', 'Lyon',
    DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY),
    'Passer deux fois par jour pour nourrir Minou et changer sa litière.', 'ouverte', 2),
(2, 'Promenades pour Rex', 'Chien', 'Lyon',
    DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 17 DAY),
    'Rex a besoin de deux promenades par jour, il est très gentil.', 'pourvue', 2),
(3, 'Lapin pendant les vacances', 'Autre', 'Paris',
    DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY),
    'Garder Caramel le lapin : foin, eau fraîche et un peu de câlins.', 'terminee', 5),
(4, 'Chat pendant un déplacement pro', 'Chat', 'Lyon',
    DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(CURDATE(), INTERVAL 12 DAY),
    'Nourrir Félix matin et soir pendant mon déplacement.', 'terminee', 2),
(5, 'Deux chiens à Paris', 'Chien', 'Paris',
    DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY),
    'Deux labradors énergiques, jardin disponible.', 'ouverte', 5),
(6, 'Oscar le bouledogue, dès demain', 'Chien', 'Lyon',
    DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY),
    'Imprévu de dernière minute : Oscar a besoin d''un gardien dès demain.', 'ouverte', 5);

INSERT INTO candidatures (message, statut, id_annonce, id_gardien) VALUES
('J''adore les chats, j''habite à deux rues.',       'en_attente', 1, 3),
('Disponible tout le week-end, j''ai déjà un chat.', 'en_attente', 1, 4),
('Je promène déjà le chien de ma voisine.',          'acceptee',   2, 3),
('Je peux passer le soir après le travail.',         'refusee',    2, 4),
('J''ai eu des lapins pendant 10 ans.',              'acceptee',   3, 4),
('Je travaille à domicile, je serai disponible.',    'acceptee',   4, 3);

-- Bruno n'a pas encore noté Alice pour l'annonce 4 : on peut tester la notation.
INSERT INTO avis (note, commentaire, id_annonce, id_auteur, id_cible) VALUES
(5, 'Chloé a été parfaite, Caramel était ravi.', 3, 5, 4),
(4, 'Propriétaire très clair dans ses consignes.', 3, 4, 5),
(4, 'Bruno est sérieux et ponctuel.',              4, 2, 3);
