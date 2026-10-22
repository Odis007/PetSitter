<?php

class AuthController {

  // Créer un compte
  public function inscription(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      vue('auth/inscription', ['erreurs' => []]);
      return;
    }

    $erreurs = validerInscription($_POST);
    if (empty($erreurs) && Utilisateur::trouverParEmail(trim($_POST['email']))) {
      $erreurs[] = 'Cet email est déjà utilisé.';
    }
    if (!empty($erreurs)) {
      vue('auth/inscription', ['erreurs' => $erreurs]);
      return;
    }

    // À l'inscription : on ne stocke JAMAIS le mot de passe en clair
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $id = Utilisateur::creer(trim($_POST['nom']), trim($_POST['email']), $hash);

    $this->ouvrirSession(Utilisateur::trouver($id));
    flash('Bienvenue sur PetSitter Local !');
    rediriger('index.php');
  }

  // Se connecter
  public function connexion(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      vue('auth/connexion', ['erreurs' => []]);
      return;
    }

    $utilisateur = Utilisateur::trouverParEmail(trim($_POST['email'] ?? ''));

    // À la connexion : on compare le mot de passe tapé avec le hash enregistré
    if ($utilisateur && password_verify($_POST['password'] ?? '', $utilisateur->password)) {
      $this->ouvrirSession($utilisateur);
      flash('Bonjour ' . $utilisateur->nom . ' !');
      rediriger('index.php');
    }

    // Même message dans les deux cas, pour ne pas révéler si l'email existe
    vue('auth/connexion', ['erreurs' => ['Email ou mot de passe incorrect.']]);
  }

  // Se déconnecter
  public function deconnexion(): void {
    $_SESSION = [];            // on vide la session
    session_regenerate_id(true);
    flash('Vous êtes déconnecté.');
    rediriger('index.php');
  }

  // Enregistre l'utilisateur dans la session
  private function ouvrirSession(Utilisateur $utilisateur): void {
    session_regenerate_id(true); // nouvel identifiant de session (sécurité)
    $_SESSION['user_id'] = $utilisateur->id;
    $_SESSION['nom'] = $utilisateur->nom;
    $_SESSION['role'] = $utilisateur->role;
  }
}
