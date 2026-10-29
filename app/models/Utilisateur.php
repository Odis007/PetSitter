<?php
// app/models/Utilisateur.php

class Utilisateur
{
    public function __construct(
        public int $id = 0,
        public string $nom = '',
        public string $email = '',
        public string $mot_de_passe = '',
        public string $role = 'user'
    ) {}

    public static function hydrate(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['nom'],
            $row['email'],
            $row['mot_de_passe'],
            $row['role']
        );
    }

    public static function trouver(int $id): ?self
    {
        $stmt = Database::get()->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? self::hydrate($row) : null;
    }

    public static function trouverParEmail(string $email): ?self
    {
        $stmt = Database::get()->prepare("SELECT * FROM utilisateurs WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? self::hydrate($row) : null;
    }

    // Crée le compte et retourne son id (le mot de passe est HACHÉ ici)
    public static function creer(string $nom, string $email, string $motDePasse): int
    {
        $stmt = Database::get()->prepare(
            "INSERT INTO utilisateurs (nom, email, mot_de_passe)
             VALUES (:nom, :email, :mdp)"
        );
        $stmt->execute([
            'nom'   => $nom,
            'email' => $email,
            'mdp'   => password_hash($motDePasse, PASSWORD_DEFAULT),
        ]);
        return (int) Database::get()->lastInsertId();
    }

    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->mot_de_passe);
    }

    // Si PHP change son algorithme de hachage, on re-hache à la connexion suivante
    public function rehasherSiNecessaire(string $motDePasse): void
    {
        if (password_needs_rehash($this->mot_de_passe, PASSWORD_DEFAULT)) {
            $stmt = Database::get()->prepare("UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id");
            $stmt->execute(['mdp' => password_hash($motDePasse, PASSWORD_DEFAULT), 'id' => $this->id]);
        }
    }

    // ---------- Espace administrateur ----------

    /** @return Utilisateur[] */
    public static function tous(): array
    {
        $rows = Database::get()->query("SELECT * FROM utilisateurs ORDER BY nom")->fetchAll();
        return array_map([self::class, 'hydrate'], $rows);
    }

    // Ses annonces, candidatures et avis partent avec (ON DELETE CASCADE)
    public static function supprimer(int $id): void
    {
        $stmt = Database::get()->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
