<?php

class Avis {
  // Une propriété par colonne de la table avis
  public int $id;
  public int $note;
  public ?string $commentaire = null;
  public string $date;
  public int $id_annonce;
  public int $id_auteur;
  public int $id_cible;

  // Remplis seulement par les requêtes avec jointure
  public ?string $nom_auteur = null;
  public ?string $nom_cible = null;
  public ?string $titre_annonce = null;

  // Créer un avis
  public static function creer(
    int $idAnnonce, int $idAuteur, int $idCible,
    int $note, string $commentaire
  ): void {
    Database::get()->prepare(
      "INSERT INTO avis (note, commentaire, id_annonce, id_auteur, id_cible)
       VALUES (?, ?, ?, ?, ?)"
    )->execute([$note, $commentaire, $idAnnonce, $idAuteur, $idCible]);
  }

  // Règle métier : chaque utilisateur ne note qu'une seule fois par garde
  public static function dejaNote(int $idAnnonce, int $idAuteur): bool {
    $stmt = Database::get()->prepare(
      "SELECT COUNT(*) FROM avis WHERE id_annonce = ? AND id_auteur = ?"
    );
    $stmt->execute([$idAnnonce, $idAuteur]);
    return $stmt->fetchColumn() > 0;
  }

  // Note moyenne reçue par un utilisateur (null s'il n'a jamais été noté)
  public static function noteMoyenne(int $idUtilisateur): ?float {
    $stmt = Database::get()->prepare(
      "SELECT AVG(note) FROM avis WHERE id_cible = ?"
    );
    $stmt->execute([$idUtilisateur]);
    $moyenne = $stmt->fetchColumn();
    // AVG renvoie NULL quand il n'y a aucun avis
    return $moyenne === null ? null : round((float) $moyenne, 1);
  }

  // Les avis reçus par un utilisateur (pour son profil)
  public static function recusPar(int $idUtilisateur): array {
    $stmt = Database::get()->prepare(
      "SELECT av.*, u.nom AS nom_auteur, a.titre AS titre_annonce
       FROM avis av
       JOIN utilisateurs u ON u.id = av.id_auteur
       JOIN annonces a ON a.id = av.id_annonce
       WHERE av.id_cible = ?
       ORDER BY av.date DESC"
    );
    $stmt->execute([$idUtilisateur]);
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Tous les avis (pour la modération admin)
  public static function tous(): array {
    $stmt = Database::get()->query(
      "SELECT av.*, auteur.nom AS nom_auteur, cible.nom AS nom_cible
       FROM avis av
       JOIN utilisateurs auteur ON auteur.id = av.id_auteur
       JOIN utilisateurs cible ON cible.id = av.id_cible
       ORDER BY av.date DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Supprimer un avis abusif (admin)
  public static function supprimer(int $id): void {
    Database::get()->prepare("DELETE FROM avis WHERE id = ?")->execute([$id]);
  }

  // Le membre avec la meilleure note moyenne (page d'accueil), null s'il n'y a aucun avis
  public static function meilleurMembre(): ?object {
    $stmt = Database::get()->query(
      "SELECT u.id, u.nom, AVG(av.note) AS moyenne
       FROM avis av
       JOIN utilisateurs u ON u.id = av.id_cible
       GROUP BY u.id, u.nom
       ORDER BY moyenne DESC, COUNT(*) DESC
       LIMIT 1"
    );
    return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
  }

  // Note moyenne de toute la plateforme (null s'il n'y a aucun avis)
  public static function moyenneGlobale(): ?float {
    $moyenne = Database::get()->query("SELECT AVG(note) FROM avis")->fetchColumn();
    return $moyenne === null ? null : round((float) $moyenne, 1);
  }
}
