<?php
// app/models/Annonce.php

class Annonce
{
    public const ANIMAUX = ['Chien', 'Chat', 'Lapin', 'Rongeur', 'Oiseau', 'Poisson', 'Autre'];

    public function __construct(
        public int $id = 0,
        public int $id_proprietaire = 0,
        public string $titre = '',
        public string $animal = '',
        public string $description = '',
        public string $ville = '',
        public string $date_debut = '',
        public string $date_fin = '',
        public string $statut = 'ouverte',
        public ?string $proprietaire = null,   // nom, issu d'une jointure
        public int $nb_candidatures = 0        // issu d'une sous-requête
    ) {}

    public static function hydrate(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['id_proprietaire'],
            $row['titre'],
            $row['animal'],
            $row['description'],
            $row['ville'],
            $row['date_debut'],
            $row['date_fin'],
            $row['statut'],
            $row['proprietaire'] ?? null,
            (int) ($row['nb_candidatures'] ?? 0)
        );
    }

    // ---------- LECTURE (Read) ----------

    /** @return Annonce[] */
    public static function toutesOuvertes(): array
    {
        $sql = "SELECT a.*, u.nom AS proprietaire
                FROM annonces a
                JOIN utilisateurs u ON u.id = a.id_proprietaire
                WHERE a.statut = 'ouverte'
                ORDER BY a.date_debut ASC";
        $rows = Database::get()->query($sql)->fetchAll();
        return array_map([self::class, 'hydrate'], $rows);
    }

    // Requête construite dynamiquement : un AND par filtre renseigné.
    // $date   : gardes qui commencent à partir de ce jour
    // $urgent : gardes qui commencent dans les 48 h
    // $tri    : 'proches' (date de garde) ou 'recentes' (dernières publiées)
    /** @return Annonce[] */
    public static function rechercher(
        string $animal = '', string $ville = '',
        string $date = '', bool $urgent = false, string $tri = 'proches'
    ): array {
        $sql = "SELECT a.*, u.nom AS proprietaire
                FROM annonces a
                JOIN utilisateurs u ON u.id = a.id_proprietaire
                WHERE a.statut = 'ouverte' AND a.date_fin >= CURDATE()";
        $params = [];

        if ($animal !== '') {
            $sql .= " AND a.animal = :animal";
            $params['animal'] = $animal;
        }
        if ($ville !== '') {
            $sql .= " AND a.ville LIKE :ville";
            $params['ville'] = '%' . $ville . '%';
        }
        if ($date !== '') {
            $sql .= " AND a.date_debut >= :date";
            $params['date'] = $date;
        }
        if ($urgent) {
            $sql .= " AND a.date_debut <= DATE_ADD(CURDATE(), INTERVAL 2 DAY)";
        }

        // Liste blanche : $tri n'est JAMAIS injecté dans le SQL
        // (les paramètres liés ne fonctionnent pas pour les noms de colonnes)
        $sql .= match ($tri) {
            'recentes' => " ORDER BY a.created_at DESC",
            default    => " ORDER BY a.date_debut ASC",
        };

        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);
        return array_map([self::class, 'hydrate'], $stmt->fetchAll());
    }

    // Les dernières annonces publiées, avec leur nombre de candidatures (page d'accueil)
    /** @return Annonce[] */
    public static function recentes(int $nombre): array
    {
        $stmt = Database::get()->prepare(
            "SELECT a.*, u.nom AS proprietaire,
                    (SELECT COUNT(*) FROM candidatures c WHERE c.id_annonce = a.id) AS nb_candidatures
             FROM annonces a
             JOIN utilisateurs u ON u.id = a.id_proprietaire
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT :nombre"
        );
        $stmt->bindValue('nombre', $nombre, PDO::PARAM_INT);   // LIMIT exige un vrai entier
        $stmt->execute();
        return array_map([self::class, 'hydrate'], $stmt->fetchAll());
    }

    public static function trouver(int $id): ?self
    {
        $stmt = Database::get()->prepare(
            "SELECT a.*, u.nom AS proprietaire
             FROM annonces a
             JOIN utilisateurs u ON u.id = a.id_proprietaire
             WHERE a.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? self::hydrate($row) : null;
    }

    // Le nombre de candidatures vient d'une sous-requête : une seule requête
    // pour toutes les annonces (et non une par annonce : problème "N+1")
    /** @return Annonce[] */
    public static function parProprietaire(int $idProprietaire): array
    {
        $stmt = Database::get()->prepare(
            "SELECT a.*, u.nom AS proprietaire,
                    (SELECT COUNT(*) FROM candidatures c WHERE c.id_annonce = a.id) AS nb_candidatures
             FROM annonces a
             JOIN utilisateurs u ON u.id = a.id_proprietaire
             WHERE a.id_proprietaire = :id
             ORDER BY a.created_at DESC"
        );
        $stmt->execute(['id' => $idProprietaire]);
        return array_map([self::class, 'hydrate'], $stmt->fetchAll());
    }

    // ---------- ÉCRITURE (Create / Update / Delete) ----------

    public static function creer(array $d, int $idProprietaire): int
    {
        $stmt = Database::get()->prepare(
            "INSERT INTO annonces
                (id_proprietaire, titre, animal, description, ville, date_debut, date_fin)
             VALUES
                (:proprio, :titre, :animal, :description, :ville, :debut, :fin)"
        );
        $stmt->execute([
            'proprio'     => $idProprietaire,
            'titre'       => $d['titre'],
            'animal'      => $d['animal'],
            'description' => $d['description'],
            'ville'       => $d['ville'],
            'debut'       => $d['date_debut'],
            'fin'         => $d['date_fin'],
        ]);
        return (int) Database::get()->lastInsertId();
    }

    public static function modifier(int $id, array $d): void
    {
        $stmt = Database::get()->prepare(
            "UPDATE annonces
             SET titre = :titre, animal = :animal, description = :description,
                 ville = :ville, date_debut = :debut, date_fin = :fin
             WHERE id = :id"
        );
        $stmt->execute([
            'titre'       => $d['titre'],
            'animal'      => $d['animal'],
            'description' => $d['description'],
            'ville'       => $d['ville'],
            'debut'       => $d['date_debut'],
            'fin'         => $d['date_fin'],
            'id'          => $id,
        ]);
    }

    public static function supprimer(int $id): void
    {
        $stmt = Database::get()->prepare("DELETE FROM annonces WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    // ---------- CYCLE DE VIE : ouverte -> pourvue -> terminee ----------

    // Accepter une candidature modifie TROIS choses qui doivent réussir ENSEMBLE ou pas du tout :
    // la candidature choisie, les autres candidatures en attente, et le statut de l'annonce.
    public static function cloturer(int $idAnnonce, int $idCandidature): void
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE candidatures SET statut = 'acceptee' WHERE id = :id")
                ->execute(['id' => $idCandidature]);

            $pdo->prepare(
                "UPDATE candidatures SET statut = 'refusee'
                 WHERE id_annonce = :annonce AND id <> :id AND statut = 'en_attente'"
            )->execute(['annonce' => $idAnnonce, 'id' => $idCandidature]);

            $pdo->prepare("UPDATE annonces SET statut = 'pourvue' WHERE id = :annonce")
                ->execute(['annonce' => $idAnnonce]);

            $pdo->commit();      // tout est enregistré d'un coup
        } catch (Throwable $e) {
            $pdo->rollBack();    // au moindre problème, rien n'est modifié
            throw $e;
        }
    }

    // pourvue -> terminee (la garde a eu lieu)
    public static function terminer(int $id): void
    {
        $stmt = Database::get()->prepare(
            "UPDATE annonces SET statut = 'terminee' WHERE id = :id AND statut = 'pourvue'"
        );
        $stmt->execute(['id' => $id]);
    }

    // Clôture automatique : les gardes pourvues dont la date de fin est passée
    // (appelée à chaque chargement de page, dans index.php)
    public static function terminerExpirees(): void
    {
        Database::get()->exec(
            "UPDATE annonces SET statut = 'terminee'
             WHERE statut = 'pourvue' AND date_fin < CURDATE()"
        );
    }

    // ---------- RÈGLES MÉTIER ----------

    public function estProprietaire(int $idUtilisateur): bool
    {
        return $this->id_proprietaire === $idUtilisateur;
    }

    // Une annonce n'est modifiable ou supprimable que tant qu'elle est ouverte
    public function estModifiable(): bool
    {
        return $this->statut === 'ouverte';
    }

    public function estOuverte(): bool
    {
        return $this->statut === 'ouverte';
    }

    // Ouverte et commence dans moins de 48 h (carte rouge "Urgence")
    public function estUrgente(): bool
    {
        return $this->estOuverte()
            && $this->date_debut <= date('Y-m-d', strtotime('+2 days'));
    }

    // ---------- STATISTIQUES ----------

    // Propriétaire : nombre de gardes confiées et gardien le plus sollicité
    public static function statsProprietaire(int $idProprietaire): array
    {
        $pdo = Database::get();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM annonces
             WHERE id_proprietaire = :id AND statut IN ('pourvue', 'terminee')"
        );
        $stmt->execute(['id' => $idProprietaire]);
        $nbGardes = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT u.id, u.nom, COUNT(*) AS nb
             FROM candidatures c
             JOIN annonces a ON a.id = c.id_annonce
             JOIN utilisateurs u ON u.id = c.id_gardien
             WHERE a.id_proprietaire = :id AND c.statut = 'acceptee'
             GROUP BY u.id, u.nom
             ORDER BY nb DESC
             LIMIT 1"
        );
        $stmt->execute(['id' => $idProprietaire]);
        $gardienPrefere = $stmt->fetch() ?: null;

        return ['nbGardes' => $nbGardes, 'gardienPrefere' => $gardienPrefere];
    }

    // Administrateur : nombre de gardes réalisées sur la plateforme
    public static function nombreTerminees(): int
    {
        return (int) Database::get()
            ->query("SELECT COUNT(*) FROM annonces WHERE statut = 'terminee'")
            ->fetchColumn();
    }
}
