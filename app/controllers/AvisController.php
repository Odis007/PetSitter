<?php

class AvisController {

  // Laisser un avis sur une garde terminée
  public function noter(): void {
    $idAuteur = requireLogin();
    $annonce = Annonce::trouver((int) ($_GET['id_annonce'] ?? 0));

    // Règle métier : on ne note qu'une garde terminée
    if ($annonce === null || $annonce->statut !== 'terminee') {
      flash("On ne peut noter qu'une garde terminée.", 'erreur');
      rediriger('index.php');
    }

    // Qui note qui ? Le propriétaire note le gardien, et inversement.
    $idGardien = $annonce->idGardienRetenu();
    if ($idAuteur === $annonce->id_proprietaire) {
      $idCible = $idGardien;
    } elseif ($idAuteur === $idGardien) {
      $idCible = $annonce->id_proprietaire;
    } else {
      flash("Vous n'avez pas participé à cette garde.", 'erreur');
      rediriger('index.php');
    }

    // Règle métier : une seule note par personne et par garde
    if (Avis::dejaNote($annonce->id, $idAuteur)) {
      flash('Vous avez déjà noté cette garde.', 'erreur');
      rediriger('index.php');
    }

    $cible = Utilisateur::trouver($idCible);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      vue('avis/formulaire', ['annonce' => $annonce, 'cible' => $cible, 'erreurs' => []]);
      return;
    }

    $erreurs = validerAvis($_POST);
    if (!empty($erreurs)) {
      vue('avis/formulaire', ['annonce' => $annonce, 'cible' => $cible, 'erreurs' => $erreurs]);
      return;
    }

    Avis::creer(
      $annonce->id, $idAuteur, $idCible,
      (int) $_POST['note'], trim($_POST['commentaire'] ?? '')
    );
    flash('Merci pour votre avis !');
    rediriger("index.php?action=profil&id=$idCible");
  }

  // Espace admin : statistiques, modération des avis, gestion des comptes
  public function admin(): void {
    requireAdmin();
    $utilisateurs = Utilisateur::tous();

    vue('admin/dashboard', [
      'nbGardes'       => Annonce::nombreTerminees(),
      'moyenne'        => Avis::moyenneGlobale(),
      'nbUtilisateurs' => count($utilisateurs),
      'avis'           => Avis::tous(),
      'utilisateurs'   => $utilisateurs,
    ]);
  }

  // Admin : supprimer un avis abusif
  public function supprimerAvis(): void {
    requireAdmin();
    Avis::supprimer((int) ($_POST['id'] ?? 0));
    flash('Avis supprimé.');
    rediriger('index.php?action=admin');
  }

  // Admin : supprimer un compte
  public function supprimerUtilisateur(): void {
    $idAdmin = requireAdmin();
    $id = (int) ($_POST['id'] ?? 0);

    if ($id === $idAdmin) {
      flash('Vous ne pouvez pas supprimer votre propre compte.', 'erreur');
      rediriger('index.php?action=admin');
    }

    Utilisateur::supprimer($id);
    flash('Compte supprimé.');
    rediriger('index.php?action=admin');
  }
}
