<?php
// app/controllers/AuthController.php

class AuthController
{
    private const MAX_TENTATIVES = 5;    // échecs autorisés avant blocage
    private const DUREE_BLOCAGE  = 60;   // secondes

    public function inscription(): void
    {
        $erreurs = [];
        $nom = $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Securite::verifier();

            $nom   = trim($_POST['nom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $mdp   = $_POST['mot_de_passe'] ?? '';

            if ($nom === '') {
                $erreurs[] = 'Le nom est obligatoire.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreurs[] = 'Email invalide.';
            }
            if (strlen($mdp) < 8) {
                $erreurs[] = 'Mot de passe : 8 caractères minimum.';
            }
            if (!$erreurs && Utilisateur::trouverParEmail($email)) {
                $erreurs[] = 'Cet email est déjà utilisé.';
            }

            if (!$erreurs) {
                Utilisateur::creer($nom, $email, $mdp);
                $_SESSION['flash'] = 'Compte créé, vous pouvez vous connecter.';
                redirect('connexion');
            }
        }

        $titre = 'Inscription';
        require ROOT . '/app/views/auth/inscription.php';
    }

    public function connexion(): void
    {
        $erreur = null;
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Securite::verifier();

            $restant = ($_SESSION['bloque_jusque'] ?? 0) - time();
            if ($restant > 0) {
                $erreur = "Trop de tentatives. Réessayez dans {$restant} secondes.";
            } else {
                $email = trim($_POST['email'] ?? '');
                $mdp   = $_POST['mot_de_passe'] ?? '';
                $user  = Utilisateur::trouverParEmail($email);

                // Même message dans tous les cas : on ne révèle pas si l'email existe
                if ($user && $user->verifierMotDePasse($mdp)) {
                    $user->rehasherSiNecessaire($mdp);
                    unset($_SESSION['tentatives'], $_SESSION['bloque_jusque']);
                    session_regenerate_id(true);
                    unset($_SESSION['csrf']);                // nouveau jeton après connexion
                    $_SESSION['user_id']  = $user->id;
                    $_SESSION['user_nom'] = $user->nom;
                    $_SESSION['role']     = $user->role;
                    $_SESSION['flash']    = 'Bonjour ' . $user->nom . ' !';
                    redirect('liste');
                }

                $_SESSION['tentatives'] = ($_SESSION['tentatives'] ?? 0) + 1;
                if ($_SESSION['tentatives'] >= self::MAX_TENTATIVES) {
                    $_SESSION['bloque_jusque'] = time() + self::DUREE_BLOCAGE;
                    $_SESSION['tentatives'] = 0;
                }
                $erreur = 'Identifiants incorrects.';
            }
        }

        $titre = 'Connexion';
        require ROOT . '/app/views/auth/connexion.php';
    }

    // POST uniquement : un simple lien permettrait à un site piégé de déconnecter les gens
    public function deconnexion(): void
    {
        Securite::exigerPost();

        $_SESSION = [];
        session_destroy();
        redirect('connexion');
    }

    // À appeler en début de méthode pour protéger une page
    public static function exigerConnexion(): void
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('connexion');
        }
    }

    // Barrière des pages d'administration
    public static function exigerAdmin(): void
    {
        self::exigerConnexion();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Accès réservé aux administrateurs.');
        }
    }

    // Identifiant de l'utilisateur connecté, 0 pour un visiteur
    public static function utilisateurId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
