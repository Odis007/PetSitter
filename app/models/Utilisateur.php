<?php

class Utilisateur {
  // Une propriété par colonne de la table utilisateurs
  public int $id;
  public string $nom;
  public string $email;
  public string $password;
  public string $role;

  // Trouver un utilisateur par son id (null s'il n'existe pas)
  public static function trouver(int $id): ?Utilisateur {
    $stmt = Database::get()->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    // fetchObject crée un objet Utilisateur rempli avec la ligne trouvée
    return $stmt->fetchObject(self::class) ?: null;
  }

  // Trouver un utilisateur par son email (pour la connexion)
  public static function trouverParEmail(string $email): ?Utilisateur {
    $stmt = Database::get()->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchObject(self::class) ?: null;
  }

  // Créer un compte, renvoie son id.
  // $hash est le mot de passe DÉJÀ passé dans password_hash()
  public static function creer(string $nom, string $email, string $hash): int {
    $pdo = Database::get();
    $pdo->prepare(
      "INSERT INTO utilisateurs (nom, email, password) VALUES (?, ?, ?)"
    )->execute([$nom, $email, $hash]);
    return (int) $pdo->lastInsertId();
  }

  // Tous les comptes (pour l'admin)
  public static function tous(): array {
    $stmt = Database::get()->query("SELECT * FROM utilisateurs ORDER BY nom");
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Supprimer un compte (ses annonces, candidatures et avis partent avec : ON DELETE CASCADE)
  public static function supprimer(int $id): void {
    Database::get()->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$id]);
  }
}
