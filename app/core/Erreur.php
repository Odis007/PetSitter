<?php
// app/core/Erreur.php : page d'erreur 403 / 404 / 405 / 400
// Même rôle que http_response_code(...); exit('...'); du cours,
// mais la page garde l'en-tête et le pied de page du site.

class Erreur
{
    public static function afficher(int $code, string $message): void
    {
        http_response_code($code);
        $titre = 'Erreur ' . $code;
        require ROOT . '/app/views/layout/erreur.php';
        exit;
    }
}
