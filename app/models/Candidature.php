<?php

class Candidature {
  // Une propriété par colonne de la table candidatures
  public int $id;
  public ?string $message = null;
  public string $statut;
  public string $date_candidature;
  public int $id_annonce;
  public int $id_gardien;

  // Remplis seulement par les requêtes avec jointure
  public ?string $nom_gardien = null;
  public ?string $titre_annonce = null;
  public ?string $statut_annonce = null;
  // Rempli par le contrôleur (note moyenne du gardien)
  public ?float $note_gardien = null;

  // Une candidature par son id (null si elle n'existe pas)
  public static function trouver(int $id): ?Candidature {
    $stmt = Database::get()->prepare("SELECT * FROM candidatures WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchObject(self::class) ?: null;
  }

  // Règle métier : un gardien ne peut pas postuler deux fois à la même annonce
  public static function dejaCandidat(int $idAnnonce, int $idGardien): bool {
    $stmt = Database::get()->prepare(
      "SELECT COUNT(*) FROM candidatures
       WHERE id_annonce = ? AND id_gardien = ?"
    );
    $stmt->execute([$idAnnonce, $idGardien]);
    return $stmt->fetchColumn() > 0;
  }

  // Créer une candidature (statut "en_attente" par défaut), renvoie son id
  public static function creer(int $idAnnonce, int $idGardien, string $message): int {
    $pdo = Database::get();
    $pdo->prepare(
      "INSERT INTO candidatures (message, id_annonce, id_gardien)
       VALUES (?, ?, ?)"
    )->execute([$message, $idAnnonce, $idGardien]);
    return (int) $pdo->lastInsertId();
  }

  // Retirer (supprimer) cette candidature
  public function supprimer(): void {
    Database::get()->prepare("DELETE FROM candidatures WHERE id = ?")->execute([$this->id]);
  }

  // Les candidatures reçues pour une annonce, avec le nom du gardien
  public static function parAnnonce(int $idAnnonce): array {
    $stmt = Database::get()->prepare(
      "SELECT c.*, u.nom AS nom_gardien
       FROM candidatures c
       JOIN utilisateurs u ON u.id = c.id_gardien
       WHERE c.id_annonce = ?
       ORDER BY c.date_candidature"
    );
    $stmt->execute([$idAnnonce]);
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Les candidatures envoyées par un gardien, avec le titre et le statut de l'annonce
  public static function parGardien(int $idGardien): array {
    $stmt = Database::get()->prepare(
      "SELECT c.*, a.titre AS titre_annonce, a.statut AS statut_annonce
       FROM candidatures c
       JOIN annonces a ON a.id = c.id_annonce
       WHERE c.id_gardien = ?
       ORDER BY c.date_candidature DESC"
    );
    $stmt->execute([$idGardien]);
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Nombre de gardes réalisées : candidature acceptée ET garde terminée
  public static function nombreGardesRealisees(int $idGardien): int {
    $stmt = Database::get()->prepare(
      "SELECT COUNT(*)
       FROM candidatures c
       JOIN annonces a ON a.id = c.id_annonce
       WHERE c.id_gardien = ? AND c.statut = 'acceptee' AND a.statut = 'terminee'"
    );
    $stmt->execute([$idGardien]);
    return (int) $stmt->fetchColumn();
  }
}
