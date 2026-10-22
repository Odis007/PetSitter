<?php

class CandidatureController {

  // Postuler à une annonce (formulaire POST de la fiche annonce)
  public function postuler(): void {
    $idGardien = requireLogin();
    $idAnnonce = (int) ($_POST['id_annonce'] ?? 0);
    $annonce = Annonce::trouver($idAnnonce);

    if ($annonce === null || $annonce->statut !== 'ouverte') {
      flash("Cette annonce n'accepte plus de candidatures.", 'erreur');
      rediriger('index.php');
    }
    if ($annonce->id_proprietaire === $idGardien) {
      flash('Vous ne pouvez pas postuler à votre propre annonce.', 'erreur');
      rediriger("index.php?action=fiche&id=$idAnnonce");
    }
    if (Candidature::dejaCandidat($idAnnonce, $idGardien)) {
      flash('Vous avez déjà postulé à cette annonce.', 'erreur');
      rediriger("index.php?action=fiche&id=$idAnnonce");
    }

    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
      flash('Écrivez un message de motivation.', 'erreur');
      rediriger("index.php?action=fiche&id=$idAnnonce");
    }

    Candidature::creer($idAnnonce, $idGardien, $message);
    flash('Candidature envoyée !');
    rediriger('index.php?action=mesCandidatures');
  }

  // Retirer une de MES candidatures (formulaire POST)
  public function retirer(): void {
    $idGardien = requireLogin();
    $candidature = Candidature::trouver((int) ($_POST['id_candidature'] ?? 0));

    // Contrôle d'accès : seulement ses propres candidatures
    if ($candidature === null || $candidature->id_gardien !== $idGardien) {
      flash("Vous n'avez pas accès à cette candidature.", 'erreur');
      rediriger('index.php?action=mesCandidatures');
    }
    if ($candidature->statut !== 'en_attente') {
      flash('Seule une candidature en attente peut être retirée.', 'erreur');
      rediriger('index.php?action=mesCandidatures');
    }

    $candidature->supprimer();
    flash('Candidature retirée.');
    rediriger('index.php?action=mesCandidatures');
  }

  // Suivi de mes candidatures
  public function mesCandidatures(): void {
    $idGardien = requireLogin();
    $candidatures = Candidature::parGardien($idGardien);

    // Je peux noter le propriétaire si j'ai été accepté et que la garde est terminée
    $peutNoter = [];
    foreach ($candidatures as $c) {
      $peutNoter[$c->id] = $c->statut === 'acceptee'
        && $c->statut_annonce === 'terminee'
        && !Avis::dejaNote($c->id_annonce, $idGardien);
    }

    vue('candidatures/mes_candidatures', [
      'candidatures' => $candidatures,
      'peutNoter'    => $peutNoter,
    ]);
  }

  // Profil public d'un membre : nom, note moyenne, gardes réalisées, avis reçus
  public function profil(): void {
    $id = (int) ($_GET['id'] ?? 0);
    $utilisateur = Utilisateur::trouver($id);
    if ($utilisateur === null) {
      flash("Ce profil n'existe pas.", 'erreur');
      rediriger('index.php');
    }

    vue('utilisateurs/profil', [
      'utilisateur' => $utilisateur,
      'note'        => Avis::noteMoyenne($id),
      'nbGardes'    => Candidature::nombreGardesRealisees($id),
      'avis'        => Avis::recusPar($id),
    ]);
  }
}
