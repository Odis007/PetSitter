<?php
// app/models/Avis.php

class Avis
{
    public function __construct(
        public int $id = 0,
        public int $id_annonce = 0,
        public int $id_auteur = 0,
        public int $id_cible = 0,
        public int $note = 0,
        public ?string $commentaire = null,
        public ?string $date = null,
        public ?string $auteur = null,          // nom de l'auteur (jointure)
        public ?string $cible = null,           // nom de la personne notée (jointure)
        public ?string $annonce_titre = null    // titre de l'annonce (jointure)
    ) {}

    public static function hydrate(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['id_annonce'],
            (int) $row['id_auteur'],
            (int) $row['id_cible'],
            (int) $row['note'],
            $row['commentaire'] ?? null,
            $row['date'] ?? null,
            $row['auteur'] ?? null,
            $row['cible'] ?? null,
            $row['annonce_titre'] ?? null
        );
    }

    public static function creer(int $idAnnonce, int $idAuteur, int $idCible, int $note, ?string $commentaire): int
    {
        // `date` n'est pas listée : la base la remplit (DEFAULT CURRENT_TIMESTAMP)
        $stmt = Database::get()->prepare(
            "INSERT INTO avis (id_annonce, id_auteur, id_cible, note, commentaire)
             VALUES (:annonce, :auteur, :cible, :note, :commentaire)"
        );
        $stmt->execute([
            'annonce'     => $idAnnonce,
            'auteur'      => $idAuteur,
            'cible'       => $idCible,
            'note'        => $note,
            'commentaire' => $commentaire,
        ]);
        return (int) Database::get()->lastInsertId();
    }

    // Cet auteur a-t-il déjà noté pour cette annonce ?
    public static function existe(int $idAnnonce, int $idAuteur): bool
    {
        $stmt = Database::get()->prepare(
            "SELECT 1 FROM avis WHERE id_annonce = :annonce AND id_auteur = :auteur LIMIT 1"
        );
        $stmt->execute(['annonce' => $idAnnonce, 'auteur' => $idAuteur]);
        return $stmt->fetchColumn() !== false;
    }

    /** @return Avis[] avis reçus par un utilisateur, du plus récent au plus ancien */
    public static function parCible(int $idUtilisateur): array
    {
        $stmt = Database::get()->prepare(
            "SELECT av.*, u.nom AS auteur, a.titre AS annonce_titre
             FROM avis av
             JOIN utilisateurs u ON u.id = av.id_auteur
             JOIN annonces a ON a.id = av.id_annonce
             WHERE av.id_cible = :id
             ORDER BY av.`date` DESC"
        );
        $stmt->execute(['id' => $idUtilisateur]);
        return array_map([self::class, 'hydrate'], $stmt->fetchAll());
    }

    // Moyenne des notes reçues, null si aucun avis (SQL : AVG)
    public static function noteMoyenne(int $idUtilisateur): ?float
    {
        $stmt = Database::get()->prepare("SELECT AVG(note) FROM avis WHERE id_cible = :id");
        $stmt->execute(['id' => $idUtilisateur]);
        $moyenne = $stmt->fetchColumn();
        return $moyenne === null ? null : round((float) $moyenne, 1);
    }

    // RÈGLE MÉTIER INTER-MODULES : qui peut noter qui, pour une annonce donnée ?
    // Retourne l'id de la personne à noter, ou null si l'utilisateur n'a pas le droit de noter.
    public static function cibleDe(Annonce $annonce, int $idUtilisateur): ?int
    {
        $gardien = Candidature::gardienAccepte($annonce->id);
        if ($gardien === null) {
            return null;
        }
        if ($idUtilisateur === $annonce->id_proprietaire) {
            return $gardien;                       // le propriétaire note le gardien
        }
        if ($idUtilisateur === $gardien) {
            return $annonce->id_proprietaire;      // le gardien note le propriétaire
        }
        return null;                               // étranger à la garde
    }

    // ---------- Accueil et espace administrateur ----------

    // Le membre le mieux noté (null s'il n'y a aucun avis)
    public static function meilleurMembre(): ?array
    {
        $row = Database::get()->query(
            "SELECT u.id, u.nom, AVG(av.note) AS moyenne
             FROM avis av
             JOIN utilisateurs u ON u.id = av.id_cible
             GROUP BY u.id, u.nom
             ORDER BY moyenne DESC, COUNT(*) DESC
             LIMIT 1"
        )->fetch();
        return $row ?: null;
    }

    /** @return Avis[] tous les avis, du plus récent au plus ancien */
    public static function tous(): array
    {
        $rows = Database::get()->query(
            "SELECT av.*, auteur.nom AS auteur, cible.nom AS cible
             FROM avis av
             JOIN utilisateurs auteur ON auteur.id = av.id_auteur
             JOIN utilisateurs cible ON cible.id = av.id_cible
             ORDER BY av.`date` DESC"
        )->fetchAll();
        return array_map([self::class, 'hydrate'], $rows);
    }

    public static function supprimer(int $id): void
    {
        $stmt = Database::get()->prepare("DELETE FROM avis WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    // Note moyenne de toute la plateforme (null s'il n'y a aucun avis)
    public static function moyenneGlobale(): ?float
    {
        $moyenne = Database::get()->query("SELECT AVG(note) FROM avis")->fetchColumn();
        return $moyenne === null ? null : round((float) $moyenne, 1);
    }
}
