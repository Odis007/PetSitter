-- sql/petsitter.sql : base PetSitter (structure + jeu d'essai)
CREATE DATABASE IF NOT EXISTS petsitter CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE petsitter;

DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS candidatures;
DROP TABLE IF EXISTS annonces;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS utilisateur; -- ancien nom de la table

CREATE TABLE utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(80) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE annonces (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_proprietaire INT NOT NULL,
  titre VARCHAR(120) NOT NULL,
  animal VARCHAR(40) NOT NULL,
  description TEXT NOT NULL,
  ville VARCHAR(80) NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  statut ENUM('ouverte','pourvue','terminee') NOT NULL DEFAULT 'ouverte',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_proprietaire) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE candidatures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_annonce INT NOT NULL,
  id_gardien INT NOT NULL,
  message TEXT NOT NULL,
  statut ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_candidature (id_annonce, id_gardien),
  FOREIGN KEY (id_annonce) REFERENCES annonces(id) ON DELETE CASCADE,
  FOREIGN KEY (id_gardien) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_annonce INT NOT NULL,
  id_auteur INT NOT NULL,
  id_cible INT NOT NULL,
  note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
  commentaire TEXT,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_avis (id_annonce, id_auteur),
  FOREIGN KEY (id_annonce) REFERENCES annonces(id) ON DELETE CASCADE,
  FOREIGN KEY (id_auteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (id_cible) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Jeu d'essai (mot de passe de tous les comptes : password123)
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Admin', 'admin@petsitter.test',
 '$2y$10$73UC7hyPe3IHFHAFC6poUuXNL5PFKyLZ4Xq5Bd8QZe2nz465OvNKu', 'admin'),
('Alice Martin', 'alice@petsitter.test',
 '$2y$10$73UC7hyPe3IHFHAFC6poUuXNL5PFKyLZ4Xq5Bd8QZe2nz465OvNKu', 'user'),
('Bob Durand', 'bob@petsitter.test',
 '$2y$10$73UC7hyPe3IHFHAFC6poUuXNL5PFKyLZ4Xq5Bd8QZe2nz465OvNKu', 'user'),
('Chloé Petit', 'chloe@petsitter.test',
 '$2y$10$73UC7hyPe3IHFHAFC6poUuXNL5PFKyLZ4Xq5Bd8QZe2nz465OvNKu', 'user');

INSERT INTO annonces
  (id_proprietaire, titre, animal, description, ville, date_debut, date_fin, statut)
VALUES
(2, 'Garde de Mochi pendant les vacances', 'Chat',
 'Chat calme, une visite par jour suffit.',
 'Lyon', '2026-12-20', '2026-12-30', 'ouverte'),
(2, 'Promenade de Rex le week-end', 'Chien',
 'Labrador joueur, deux sorties par jour.',
 'Lyon', '2026-11-14', '2026-11-16', 'pourvue'),
(3, 'Nourrir mes poissons', 'Poisson',
 'Aquarium simple, nourrissage tous les 2 jours.',
 'Villeurbanne', '2026-11-05', '2026-11-12', 'ouverte'),
(3, 'Garde de Pixel (lapin)', 'Lapin',
 'Lapin nain, cage à nettoyer.',
 'Lyon', '2026-10-01', '2026-10-05', 'terminee'),
(4, 'Hamster en congés scolaires', 'Rongeur',
 'Très facile, besoin de présence quotidienne.',
 'Bron', '2026-12-22', '2026-12-28', 'ouverte');

INSERT INTO candidatures (id_annonce, id_gardien, message, statut) VALUES
(1, 3, 'Bonjour, j''adore les chats et je suis disponible.', 'en_attente'),
(2, 4, 'Je peux promener Rex matin et soir.', 'acceptee'),
(3, 2, 'Je passerai nourrir les poissons.', 'en_attente'),
(4, 2, 'Disponible pour Pixel.', 'acceptee');

INSERT INTO avis (id_annonce, id_auteur, id_cible, note, commentaire) VALUES
(4, 3, 2, 5, 'Gardienne très soigneuse, Pixel allait très bien.'),
(4, 2, 3, 4, 'Propriétaire sympathique et ponctuel.');
