<?php
// app/core/Securite.php : protection contre les attaques CSRF et contrôle de méthode

class Securite
{
    // Jeton secret propre à la session (créé au premier appel)
    public static function jeton(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    // À afficher dans CHAQUE formulaire POST
    public static function champ(): string
    {
        return '<input type="hidden" name="csrf" value="' . e(self::jeton()) . '">';
    }

    // Vérifie le jeton reçu ; arrête le script avec une erreur 403 sinon
    public static function verifier(): void
    {
        $recu = $_POST['csrf'] ?? '';

        if (!is_string($recu) || !hash_equals(self::jeton(), $recu)) {
            http_response_code(403);
            exit('Jeton de sécurité invalide. Rechargez la page et recommencez.');
        }
    }

    // Pour les actions qui n'acceptent QUE le POST : méthode (405) puis jeton (403)
    public static function exigerPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }
        self::verifier();
    }
}
