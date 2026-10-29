<?php
// app/controllers/CandidatureController.php

class CandidatureController
{
    // POST : un gardien postule à l'annonce ?id=...
    public function postuler(): void
    {
        AuthController::exigerConnexion();
        Securite::exigerPost();

        $annonce = Annonce::trouver((int) ($_GET['id'] ?? 0));
        if ($annonce === null) {
            http_response_code(404);
            exit('Annonce introuvable.');
        }

        $idGardien = AuthController::utilisateurId();

        // Règles métier
        if (!$annonce->estOuverte()) {
            $_SESSION['flash_erreur'] = 'Cette annonce n\'accepte plus de candidatures.';
            redirect('detail', ['id' => $annonce->id]);
        }
        if ($annonce->estProprietaire($idGardien)) {
            $_SESSION['flash_erreur'] = 'Vous ne pouvez pas postuler à votre propre annonce.';
            redirect('detail', ['id' => $annonce->id]);
        }
        if (Candidature::existe($annonce->id, $idGardien)) {
            $_SESSION['flash_erreur'] = 'Vous avez déjà postulé à cette annonce.';
            redirect('detail', ['id' => $annonce->id]);
        }

        $message = trim($_POST['message'] ?? '');
        $longueur = mb_strlen($message);
        if ($longueur < 10 || $longueur > 500) {
            $_SESSION['flash_erreur'] = 'Le message doit faire entre 10 et 500 caractères.';
            redirect('detail', ['id' => $annonce->id]);
        }

        Candidature::creer($annonce->id, $idGardien, $message);
        $_SESSION['flash'] = 'Candidature envoyée !';
        redirect('detail', ['id' => $annonce->id]);
    }

    // POST : le gardien retire sa candidature tant qu'elle est en attente
    public function retirer(): void
    {
        AuthController::exigerConnexion();
        Securite::exigerPost();

        $candidature = Candidature::trouver((int) ($_GET['id'] ?? 0));
        if ($candidature === null) {
            http_response_code(404);
            exit('Candidature introuvable.');
        }
        if ($candidature->id_gardien !== AuthController::utilisateurId()) {
            http_response_code(403);
            exit('Accès interdit : ce n\'est pas votre candidature.');
        }
        if ($candidature->statut !== 'en_attente') {
            $_SESSION['flash_erreur'] = 'Seule une candidature en attente peut être retirée.';
            redirect('mes_candidatures');
        }

        Candidature::supprimer($candidature->id);
        $_SESSION['flash'] = 'Candidature retirée.';
        redirect('mes_candidatures');
    }

    // GET : mes candidatures (côté gardien)
    public function mesCandidatures(): void
    {
        AuthController::exigerConnexion();
        $idGardien = AuthController::utilisateurId();

        $candidatures = Candidature::parGardien($idGardien);

        // Lien « Laisser un avis » : candidature acceptée, garde terminée, pas encore noté
        $peutNoter = [];
        foreach ($candidatures as $c) {
            $peutNoter[$c->id] = $c->statut === 'acceptee'
                && $c->annonce_statut === 'terminee'
                && !Avis::existe($c->id_annonce, $idGardien);
        }

        $titre = 'Mes candidatures';
        require ROOT . '/app/views/candidatures/mes_candidatures.php';
    }

    // GET : candidatures reçues sur UNE de mes annonces (côté propriétaire)
    public function recues(): void
    {
        AuthController::exigerConnexion();

        $annonce = Annonce::trouver((int) ($_GET['id'] ?? 0));
        if ($annonce === null) {
            http_response_code(404);
            exit('Annonce introuvable.');
        }
        if (!$annonce->estProprietaire(AuthController::utilisateurId())) {
            http_response_code(403);
            exit('Accès interdit : ce n\'est pas votre annonce.');
        }

        $candidatures = Candidature::parAnnonce($annonce->id);
        foreach ($candidatures as $c) {
            $c->note_gardien = Avis::noteMoyenne($c->id_gardien);
        }

        $titre = 'Candidatures reçues';
        require ROOT . '/app/views/candidatures/recues.php';
    }

    // POST : le propriétaire accepte ou refuse une candidature
    public function repondre(): void
    {
        AuthController::exigerConnexion();
        Securite::exigerPost();

        $candidature = Candidature::trouver((int) ($_GET['id'] ?? 0));
        if ($candidature === null) {
            http_response_code(404);
            exit('Candidature introuvable.');
        }

        $annonce = Annonce::trouver($candidature->id_annonce);
        if (!$annonce->estProprietaire(AuthController::utilisateurId())) {
            http_response_code(403);
            exit('Accès interdit : ce n\'est pas votre annonce.');
        }

        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['acceptee', 'refusee'], true)) {
            http_response_code(400);
            exit('Décision invalide.');
        }

        if ($candidature->statut !== 'en_attente' || !$annonce->estOuverte()) {
            $_SESSION['flash_erreur'] = 'Cette candidature ne peut plus être traitée.';
            redirect('recues', ['id' => $annonce->id]);
        }

        if ($decision === 'acceptee') {
            Annonce::cloturer($annonce->id, $candidature->id);   // transaction : 3 mises à jour
            $_SESSION['flash'] = 'Candidature acceptée : l\'annonce est pourvue.';
        } else {
            Candidature::changerStatut($candidature->id, 'refusee');
            $_SESSION['flash'] = 'Candidature refusée.';
        }
        redirect('recues', ['id' => $annonce->id]);
    }
}
