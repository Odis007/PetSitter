<?php
// app/controllers/AdminController.php : espace administrateur

class AdminController
{
    // GET : statistiques, modération des avis, gestion des comptes
    public function tableauDeBord(): void
    {
        AuthController::exigerAdmin();

        $utilisateurs   = Utilisateur::tous();
        $avis           = Avis::tous();
        $nbGardes       = Annonce::nombreTerminees();
        $moyenne        = Avis::moyenneGlobale();
        $nbUtilisateurs = count($utilisateurs);

        $titre = 'Espace administrateur';
        require ROOT . '/app/views/admin/dashboard.php';
    }

    // POST : supprimer un avis abusif
    public function supprimerAvis(): void
    {
        AuthController::exigerAdmin();
        Securite::exigerPost();

        Avis::supprimer((int) ($_GET['id'] ?? 0));
        $_SESSION['flash'] = 'Avis supprimé.';
        redirect('admin');
    }

    // POST : supprimer un compte (jamais le sien)
    public function supprimerUtilisateur(): void
    {
        AuthController::exigerAdmin();
        Securite::exigerPost();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id === AuthController::utilisateurId()) {
            $_SESSION['flash_erreur'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            redirect('admin');
        }

        Utilisateur::supprimer($id);
        $_SESSION['flash'] = 'Compte supprimé.';
        redirect('admin');
    }
}
