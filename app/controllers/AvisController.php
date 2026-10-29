<?php
// app/controllers/AvisController.php

class AvisController
{
    // GET : formulaire / POST : enregistrement (?action=noter&id=<id annonce>)
    public function noter(): void
    {
        AuthController::exigerConnexion();
        $idAuteur = AuthController::utilisateurId();

        // 1. L'annonce existe
        $annonce = Annonce::trouver((int) ($_GET['id'] ?? 0));
        if ($annonce === null) {
            Erreur::afficher(404, 'Annonce introuvable.');
        }

        // 2. Propriétaire ou gardien accepté : sinon 403
        $idCible = Avis::cibleDe($annonce, $idAuteur);
        if ($idCible === null) {
            Erreur::afficher(403, 'Accès interdit : vous n\'avez pas participé à cette garde.');
        }

        // 3. La garde doit être terminée
        if ($annonce->statut !== 'terminee') {
            $_SESSION['flash_erreur'] = 'Vous pourrez noter une fois la garde terminée.';
            redirect('detail', ['id' => $annonce->id]);
        }

        // 4. Un seul avis par auteur et par annonce
        if (Avis::existe($annonce->id, $idAuteur)) {
            $_SESSION['flash_erreur'] = 'Vous avez déjà noté cette garde.';
            redirect('detail', ['id' => $annonce->id]);
        }

        $cible = Utilisateur::trouver($idCible);
        $erreurs = [];
        $note = '';
        $commentaire = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Securite::verifier();

            $note = trim($_POST['note'] ?? '');
            $commentaire = trim($_POST['commentaire'] ?? '');

            // filter_var refuse "abc", "4.5", 0 et 6
            $noteValide = filter_var($note, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 5],
            ]);
            if ($noteValide === false) {
                $erreurs[] = 'La note doit être un nombre entier de 1 à 5.';
            }
            if (mb_strlen($commentaire) > 500) {
                $erreurs[] = 'Le commentaire ne doit pas dépasser 500 caractères.';
            }

            if (!$erreurs) {
                Avis::creer($annonce->id, $idAuteur, $idCible, $noteValide,
                    $commentaire === '' ? null : $commentaire);
                $_SESSION['flash'] = 'Merci pour votre avis !';
                redirect('profil', ['id' => $idCible]);
            }
        }

        $titre = 'Laisser un avis';
        require ROOT . '/app/views/avis/formulaire.php';
    }

    // GET : profil public d'un utilisateur : note moyenne + avis reçus
    public function profil(): void
    {
        $utilisateur = Utilisateur::trouver((int) ($_GET['id'] ?? 0));
        if ($utilisateur === null) {
            Erreur::afficher(404, 'Utilisateur introuvable.');
        }

        $moyenne  = Avis::noteMoyenne($utilisateur->id);
        $avis     = Avis::parCible($utilisateur->id);
        $nbGardes = Candidature::nombreGardesRealisees($utilisateur->id);

        $titre = 'Profil de ' . $utilisateur->nom;
        require ROOT . '/app/views/avis/profil.php';
    }
}
