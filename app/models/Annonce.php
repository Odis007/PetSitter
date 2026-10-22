<?php

class Annonce {
  // Une propriété par colonne de la table annonces
  public int $id;
  public string $titre;
  public string $type_animal;
  public string $ville;
  public string $date_debut;
  public string $date_fin;
  public ?string $description = null;
  public string $statut;
  public int $id_proprietaire;

  // Remplis seulement par les requêtes qui font une jointure
  public ?string $nom_proprietaire = null;
  public int $nb_candidatures = 0;

  // ---------- Lire des annonces ----------

  // Toutes les annonces encore ouvertes (et pas encore passées)
  public static function toutesOuvertes(): array {
    $stmt = Database::get()->query(
      "SELECT a.*, u.nom AS nom_proprietaire
       FROM annonces a
       JOIN utilisateurs u ON u.id = a.id_proprietaire
       WHERE a.statut='ouverte' AND a.date_fin >= CURDATE()
       ORDER BY a.date_debut"
    );
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Annonces ouvertes filtrées.
  // $date   : seulement les gardes qui commencent à partir de ce jour
  // $urgent : seulement les gardes qui commencent dans les 48h
  // $tri    : 'proches' (date de garde) ou 'recentes' (dernières publiées)
  public static function rechercher(
    ?string $ville, ?string $typeAnimal,
    ?string $date = null, bool $urgent = false, string $tri = 'proches'
  ): array {
    $sql = "SELECT a.*, u.nom AS nom_proprietaire
            FROM annonces a
            JOIN utilisateurs u ON u.id = a.id_proprietaire
            WHERE a.statut='ouverte' AND a.date_fin >= CURDATE()";
    $params = [];

    if (!empty($ville)) {
      // LIKE + % : "par" trouve aussi "Paris"
      $sql .= " AND a.ville LIKE ?";
      $params[] = '%' . $ville . '%';
    }
    if (!empty($typeAnimal)) {
      $sql .= " AND a.type_animal = ?";
      $params[] = $typeAnimal;
    }
    if (!empty($date)) {
      $sql .= " AND a.date_debut >= ?";
      $params[] = $date;
    }
    if ($urgent) {
      $sql .= " AND a.date_debut <= DATE_ADD(CURDATE(), INTERVAL 2 DAY)";
    }

    // On n'insère jamais $tri directement dans le SQL : on choisit parmi des valeurs sûres
    $sql .= match ($tri) {
      'recentes' => " ORDER BY a.id DESC",
      default    => " ORDER BY a.date_debut",
    };

    $stmt = Database::get()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Les dernières annonces publiées, avec leur nombre de candidatures (page d'accueil)
  public static function recentes(int $nombre): array {
    $stmt = Database::get()->query(
      "SELECT a.*, u.nom AS nom_proprietaire,
              (SELECT COUNT(*) FROM candidatures c WHERE c.id_annonce = a.id) AS nb_candidatures
       FROM annonces a
       JOIN utilisateurs u ON u.id = a.id_proprietaire
       ORDER BY a.id DESC
       LIMIT " . $nombre // $nombre est un int : aucun risque d'injection
    );
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // Une annonce est urgente si elle est ouverte et commence dans moins de 48h
  public function estUrgente(): bool {
    return $this->statut === 'ouverte'
      && $this->date_debut <= date('Y-m-d', strtotime('+2 days'));
  }

  // Une annonce par son id, avec le nom du propriétaire (null si elle n'existe pas)
  public static function trouver(int $id): ?Annonce {
    $stmt = Database::get()->prepare(
      "SELECT a.*, u.nom AS nom_proprietaire
       FROM annonces a
       JOIN utilisateurs u ON u.id = a.id_proprietaire
       WHERE a.id = ?"
    );
    $stmt->execute([$id]);
    return $stmt->fetchObject(self::class) ?: null;
  }

  // Les annonces d'un propriétaire
  public static function parProprietaire(int $idProprietaire): array {
    $stmt = Database::get()->prepare(
      "SELECT * FROM annonces
       WHERE id_proprietaire = ?
       ORDER BY date_debut DESC"
    );
    $stmt->execute([$idProprietaire]);
    return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
  }

  // ---------- Créer une annonce ----------

  // Créer une annonce, renvoie son id
  public static function creer(
    int $idProprietaire, string $titre,
    string $typeAnimal, string $ville,
    string $dateDebut, string $dateFin,
    string $description
  ): int {
    $pdo = Database::get();
    $pdo->prepare(
      "INSERT INTO annonces
       (titre, type_animal, ville, date_debut,
        date_fin, description, statut, id_proprietaire)
       VALUES (?, ?, ?, ?, ?, ?, 'ouverte', ?)"
    )->execute([$titre, $typeAnimal, $ville,
      $dateDebut, $dateFin, $description, $idProprietaire]);
    return (int) $pdo->lastInsertId();
  }

  // ---------- Modifier / supprimer ----------

  // Modifier cette annonce (le statut et le propriétaire ne changent pas)
  public function modifier(
    string $titre, string $typeAnimal, string $ville,
    string $dateDebut, string $dateFin, string $description
  ): void {
    Database::get()->prepare(
      "UPDATE annonces
       SET titre = ?, type_animal = ?, ville = ?,
           date_debut = ?, date_fin = ?, description = ?
       WHERE id = ?"
    )->execute([$titre, $typeAnimal, $ville,
      $dateDebut, $dateFin, $description, $this->id]);
  }

  // Supprimer cette annonce (ses candidatures partent avec : ON DELETE CASCADE)
  public function supprimer(): void {
    Database::get()->prepare("DELETE FROM annonces WHERE id = ?")->execute([$this->id]);
  }

  // ---------- Cycle de vie : ouverte → pourvue → terminee ----------

  // Valider un gardien : sa candidature est acceptée,
  // toutes les autres sont refusées, et l'annonce passe à "pourvue".
  public function cloturer(int $idCandidatureRetenue): void {
    $pdo = Database::get();

    // Transaction : soit les 3 requêtes réussissent, soit aucune n'est appliquée
    $pdo->beginTransaction();
    try {
      $pdo->prepare(
        "UPDATE candidatures SET statut = 'acceptee'
         WHERE id = ? AND id_annonce = ?"
      )->execute([$idCandidatureRetenue, $this->id]);

      $pdo->prepare(
        "UPDATE candidatures SET statut = 'refusee'
         WHERE id_annonce = ? AND id != ?"
      )->execute([$this->id, $idCandidatureRetenue]);

      $pdo->prepare(
        "UPDATE annonces SET statut = 'pourvue' WHERE id = ?"
      )->execute([$this->id]);

      $pdo->commit();   // on valide tout
    } catch (Exception $e) {
      $pdo->rollBack(); // on annule tout
      throw $e;
    }

    $this->statut = 'pourvue';
  }

  // Clôture manuelle d'une garde par le propriétaire
  public function terminer(): void {
    Database::get()->prepare(
      "UPDATE annonces SET statut = 'terminee' WHERE id = ?"
    )->execute([$this->id]);
    $this->statut = 'terminee';
  }

  // Clôture automatique : les gardes pourvues dont la date de fin est passée
  // deviennent "terminee". Appelée à chaque chargement de page (dans index.php).
  public static function terminerExpirees(): void {
    Database::get()->query(
      "UPDATE annonces SET statut = 'terminee'
       WHERE statut = 'pourvue' AND date_fin < CURDATE()"
    );
  }

  // L'id du gardien retenu pour cette annonce (null si aucun)
  public function idGardienRetenu(): ?int {
    $stmt = Database::get()->prepare(
      "SELECT id_gardien FROM candidatures
       WHERE id_annonce = ? AND statut = 'acceptee'"
    );
    $stmt->execute([$this->id]);
    $id = $stmt->fetchColumn();
    return $id === false ? null : (int) $id;
  }

  // ---------- Statistiques ----------

  // Statistiques d'un propriétaire : nombre de gardes confiées et gardien le plus sollicité
  public static function statsProprietaire(int $idProprietaire): array {
    $pdo = Database::get();

    $stmt = $pdo->prepare(
      "SELECT COUNT(*) FROM annonces
       WHERE id_proprietaire = ? AND statut IN ('pourvue', 'terminee')"
    );
    $stmt->execute([$idProprietaire]);
    $nbGardes = (int) $stmt->fetchColumn();

    // Le gardien que ce propriétaire a choisi le plus souvent
    $stmt = $pdo->prepare(
      "SELECT u.id, u.nom, COUNT(*) AS nb
       FROM candidatures c
       JOIN annonces a ON a.id = c.id_annonce
       JOIN utilisateurs u ON u.id = c.id_gardien
       WHERE a.id_proprietaire = ? AND c.statut = 'acceptee'
       GROUP BY u.id, u.nom
       ORDER BY nb DESC
       LIMIT 1"
    );
    $stmt->execute([$idProprietaire]);
    $gardienPrefere = $stmt->fetch(PDO::FETCH_OBJ) ?: null;

    return ['nbGardes' => $nbGardes, 'gardienPrefere' => $gardienPrefere];
  }

  // Nombre total de gardes terminées (pour l'admin)
  public static function nombreTerminees(): int {
    return (int) Database::get()
      ->query("SELECT COUNT(*) FROM annonces WHERE statut = 'terminee'")
      ->fetchColumn();
  }
}
