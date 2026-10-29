<?php
// app/models/Candidature.php

class Candidature
{
    public function __construct(
        public int $id = 0,
        public int $id_annonce = 0,
        public int $id_gardien = 0,
        public string $message = '',
        public string $statut = 'en_attente',
        public ?string $created_at = null,
        public ?string $gardien = null,          // nom du gardien (jointure)
        public ?string $annonce_titre = null,    // titre de l'annonce (jointure)
        public ?string $annonce_statut = null,   // statut de l'annonce (jointure)
        public ?float $note_gardien = null       // note moyenne, ajoutée par le contrôleur
    ) {}

    public static function hydrate(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['id_annonce'],
            (int) $row['id_gardien'],
            $row['message'],
            $row['statut'],
            $row['created_at'] ?? null,
            $row['gardien'] ?? null,
            $row['annonce_titre'] ?? null,
            $row['annonce_statut'] ?? null
        );
    }

    // INSERT : retourne l'id créé
    public static function creer(int $idAnnonce, int $idGardien, string $message): int
    {
        $stmt = Database::get()->prepare(
            "INSERT INTO candidatures (id_annonce, id_gardien, message)
             VALUES (:annonce, :gardien, :message)"
        );
        $stmt->execute(['annonce' => $idAnnonce, 'gardien' => $idGardien, 'message' => $message]);
        return (int) Database::get()->lastInsertId();
    }

    // Ce gardien a-t-il déjà postulé à cette annonce ?
    public static function existe(int $idAnnonce, int $idGardien): bool
    {
        $stmt = Database::get()->prepare(
            "SELECT 1 FROM candidatures WHERE id_annonce = :annonce AND id_gardien = :gardien LIMIT 1"
        );
        $stmt->execute(['annonce' => $idAnnonce, 'gardien' => $idGardien]);
        return $stmt->fetchColumn() !== false;
    }

    /** @return Candidature[] candidatures reçues sur une annonce (avec le nom du gardien) */
    public static function parAnnonce(int $idAnnonce): array
    {
        $stmt = Database::get()->prepare(
            "SELECT c.*, u.nom AS gardien
             FROM candidatures c
             JOIN utilisateurs u ON u.id = c.id_gardien
             WHERE c.id_annonce = :id
             ORDER BY c.created_at ASC"
        );
        $stmt->execute(['id' => $idAnnonce]);
        return array_map([self::class, 'hydrate'], $stmt->fetchAll());
    }

    /** @return Candidature[] candidatures envoyées par un gardien (avec le titre de l'annonce) */
    public static function parGardien(int $idGardien): array
    {
        $stmt = Database::get()->prepare(
            "SELECT c.*, a.titre AS annonce_titre, a.statut AS annonce_statut
             FROM candidatures c
             JOIN annonces a ON a.id = c.id_annonce
             WHERE c.id_gardien = :id
             ORDER BY c.created_at DESC"
        );
        $stmt->execute(['id' => $idGardien]);
        return array_map([self::class, 'hydrate'], $stmt->fetchAll());
    }

    public static function trouver(int $id): ?self
    {
        $stmt = Database::get()->prepare("SELECT * FROM candidatures WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? self::hydrate($row) : null;
    }

    // UPDATE du statut ('acceptee' ou 'refusee')
    public static function changerStatut(int $id, string $statut): void
    {
        $stmt = Database::get()->prepare("UPDATE candidatures SET statut = :statut WHERE id = :id");
        $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    // Le gardien retire sa candidature (seulement en attente : contrôlé par le contrôleur)
    public static function supprimer(int $id): void
    {
        $stmt = Database::get()->prepare("DELETE FROM candidatures WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    // Identifiant du gardien dont la candidature a été acceptée (null si aucun)
    public static function gardienAccepte(int $idAnnonce): ?int
    {
        $stmt = Database::get()->prepare(
            "SELECT id_gardien FROM candidatures WHERE id_annonce = :id AND statut = 'acceptee' LIMIT 1"
        );
        $stmt->execute(['id' => $idAnnonce]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    // Profil : nombre de gardes réalisées (candidature acceptée ET garde terminée)
    public static function nombreGardesRealisees(int $idGardien): int
    {
        $stmt = Database::get()->prepare(
            "SELECT COUNT(*)
             FROM candidatures c
             JOIN annonces a ON a.id = c.id_annonce
             WHERE c.id_gardien = :id AND c.statut = 'acceptee' AND a.statut = 'terminee'"
        );
        $stmt->execute(['id' => $idGardien]);
        return (int) $stmt->fetchColumn();
    }
}
