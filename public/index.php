<?php
// public/index.php : point d'entrée unique (routeur)

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/database.php';

// --- Affichage des erreurs : détaillé en dev, discret en prod ---
if (APP_ENV === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);                       // le détail va dans le journal
    http_response_code(500);
    echo APP_ENV === 'dev'
        ? '<pre>' . htmlspecialchars((string) $e) . '</pre>'
        : 'Une erreur est survenue. Réessayez plus tard.';
});

// --- En-têtes de sécurité ---
header('X-Content-Type-Options: nosniff');        // le navigateur ne "devine" pas les types
header('X-Frame-Options: DENY');                  // pas d'affichage dans une iframe (clickjacking)
header('Referrer-Policy: same-origin');

// --- Session durcie (avant session_start) ---
ini_set('session.use_strict_mode', '1');          // refuse les identifiants de session inventés
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,                           // cookie invisible pour JavaScript
    'samesite' => 'Lax',                          // pas envoyé lors d'une requête venant d'un autre site
    'secure'   => !empty($_SERVER['HTTPS']),      // HTTPS uniquement si le site est en HTTPS
]);
session_start();

spl_autoload_register(function (string $classe): void {
    foreach (['/app/models/', '/app/controllers/', '/app/core/'] as $dossier) {
        $fichier = ROOT . $dossier . $classe . '.php';
        if (is_file($fichier)) {
            require_once $fichier;
            return;
        }
    }
});

// Échappe une valeur avant de l'afficher (protection XSS)
function e(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirige vers une action puis arrête le script.
// Le cours écrit ": never" : ce type n'existe qu'à partir de PHP 8.1.
// XAMPP fournit PHP 8.0, on écrit donc ": void" (la fonction se termine toujours par exit).
function redirect(string $action, array $params = []): void
{
    $url = 'index.php?' . http_build_query(['action' => $action] + $params);
    header('Location: ' . $url);
    exit;
}

// Clôture automatique : une garde pourvue dont la date de fin est passée devient terminée
Annonce::terminerExpirees();

$action = $_GET['action'] ?? 'accueil';

match ($action) {
    // Authentification
    'connexion'        => (new AuthController())->connexion(),
    'inscription'      => (new AuthController())->inscription(),
    'deconnexion'      => (new AuthController())->deconnexion(),

    // Module Annonces
    'accueil'          => (new AnnonceController())->accueil(),
    'liste'            => (new AnnonceController())->liste(),
    'detail'           => (new AnnonceController())->detail(),
    'creer'            => (new AnnonceController())->creer(),
    'modifier'         => (new AnnonceController())->modifier(),
    'supprimer'        => (new AnnonceController())->supprimer(),
    'terminer'         => (new AnnonceController())->terminer(),
    'mes_annonces'     => (new AnnonceController())->mesAnnonces(),

    // Module Candidatures
    'postuler'         => (new CandidatureController())->postuler(),
    'retirer'          => (new CandidatureController())->retirer(),
    'mes_candidatures' => (new CandidatureController())->mesCandidatures(),
    'recues'           => (new CandidatureController())->recues(),
    'repondre'         => (new CandidatureController())->repondre(),

    // Module Avis
    'noter'            => (new AvisController())->noter(),
    'profil'           => (new AvisController())->profil(),

    // Espace administrateur
    'admin'                 => (new AdminController())->tableauDeBord(),
    'supprimer_avis'        => (new AdminController())->supprimerAvis(),
    'supprimer_utilisateur' => (new AdminController())->supprimerUtilisateur(),

    default            => (new AnnonceController())->accueil(),
};
