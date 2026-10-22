<?php
// ROUTEUR UNIQUE : toutes les pages du site passent par ce fichier.
// L'URL index.php?action=xxx dit quelle méthode de quel contrôleur appeler.

session_start();

// 1. On charge tous les fichiers dont on a besoin
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../app/helpers/session.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/vue.php';

require_once __DIR__ . '/../app/models/Utilisateur.php';
require_once __DIR__ . '/../app/models/Annonce.php';
require_once __DIR__ . '/../app/models/Candidature.php';
require_once __DIR__ . '/../app/models/Avis.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AnnonceController.php';
require_once __DIR__ . '/../app/controllers/CandidatureController.php';
require_once __DIR__ . '/../app/controllers/AvisController.php';

// 2. Clôture automatique des gardes dont la date de fin est passée
Annonce::terminerExpirees();

// 3. On lit l'action demandée (par défaut : la page d'accueil)
$action = $_GET['action'] ?? 'accueil';

// 4. On appelle le bon contrôleur
match ($action) {
  // Authentification
  'inscription'          => (new AuthController())->inscription(),
  'connexion'            => (new AuthController())->connexion(),
  'deconnexion'          => (new AuthController())->deconnexion(),

  // Partie A : propriétaire
  'accueil'              => (new AnnonceController())->accueil(),
  'liste'              => (new AnnonceController())->liste(),
  'rechercher'           => (new AnnonceController())->rechercher(),
  'fiche'                => (new AnnonceController())->fiche(),
  'creer'                => (new AnnonceController())->creer(),
  'modifier'             => (new AnnonceController())->modifier(),
  'supprimer'            => (new AnnonceController())->supprimer(),
  'mesAnnonces'          => (new AnnonceController())->mesAnnonces(),
  'candidatures'         => (new AnnonceController())->candidatures(),
  'valider'              => (new AnnonceController())->valider(),
  'terminer'             => (new AnnonceController())->terminer(),

  // Partie B : gardien
  'postuler'             => (new CandidatureController())->postuler(),
  'retirer'              => (new CandidatureController())->retirer(),
  'mesCandidatures'      => (new CandidatureController())->mesCandidatures(),
  'profil'               => (new CandidatureController())->profil(),

  // Partie C : avis et admin
  'noter'                => (new AvisController())->noter(),
  'admin'                => (new AvisController())->admin(),
  'supprimerAvis'        => (new AvisController())->supprimerAvis(),
  'supprimerUtilisateur' => (new AvisController())->supprimerUtilisateur(),

  default                => (new AnnonceController())->accueil(),
};
