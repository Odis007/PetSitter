<?php

class AnnonceController {

  // Page d'accueil
  public function accueil(): void {
    $avis = Avis::tous();

    vue('accueil', [
      'pleineLargeur'   => true, // la page occupe toute la largeur de l'écran
      'annonces'        => Annonce::recentes(3),
      'meilleurMembre'  => Avis::meilleurMembre(),
      'nbAvis'          => count($avis),
      'derniersAvis'    => array_slice($avis, 0, 3),
    ]);
  }

  // Annonces disponibles (statut "ouverte")
  public function liste(): void {
    $annonces = Annonce::toutesOuvertes();
    vue('annonces/liste', ['annonces' => $annonces, 'pleineLargeur' => true]);
  }

  // Annonces disponibles filtrées (barre de recherche + filtres)
  public function rechercher(): void {
    $annonces = Annonce::rechercher(
      trim($_GET['ville'] ?? ''),
      $_GET['type_animal'] ?? '',
      $_GET['date'] ?? '',
      !empty($_GET['urgent']),
      $_GET['tri'] ?? 'proches'
    );
    vue('annonces/liste', ['annonces' => $annonces, 'pleineLargeur' => true]);
  }

  // Détail d'une annonce
  public function fiche(): void {
    $annonce = Annonce::trouver((int) ($_GET['id'] ?? 0));
    if ($annonce === null) {
      flash("Cette annonce n'existe pas.", 'erreur');
      rediriger('index.php');
    }

    $idConnecte = $_SESSION['user_id'] ?? null;

    vue('annonces/fiche', [
      'annonce'          => $annonce,
      'idConnecte'       => $idConnecte,
      'estProprietaire'  => $idConnecte === $annonce->id_proprietaire,
      'dejaCandidat'     => $idConnecte && Candidature::dejaCandidat($annonce->id, $idConnecte),
      'noteProprietaire' => Avis::noteMoyenne($annonce->id_proprietaire),
    ]);
  }

  // Afficher le formulaire (GET) ou créer l'annonce (POST)
  public function creer(): void {
    $idUtilisateur = requireLogin();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      vue('annonces/formulaire', ['erreurs' => [], 'valeurs' => [], 'annonce' => null]);
      return;
    }

    $erreurs = validerAnnonce($_POST);
    if (!empty($erreurs)) {
      vue('annonces/formulaire', ['erreurs' => $erreurs, 'valeurs' => $_POST, 'annonce' => null]);
      return;
    }

    $id = Annonce::creer(
      $idUtilisateur, trim($_POST['titre']), $_POST['type_animal'],
      trim($_POST['ville']), $_POST['date_debut'],
      $_POST['date_fin'], trim($_POST['description'])
    );
    flash('Annonce publiée avec succès.');
    rediriger("index.php?action=fiche&id=$id");
  }

  // Modifier une de MES annonces (formulaire pré-rempli en GET, enregistrement en POST)
  public function modifier(): void {
    $idUtilisateur = requireLogin();
    $annonce = $this->annonceDuProprietaire((int) ($_GET['id'] ?? 0), $idUtilisateur);

    if ($annonce->statut !== 'ouverte') {
      flash('Une annonce pourvue ou terminée ne peut plus être modifiée.', 'erreur');
      rediriger('index.php?action=mesAnnonces');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // (array) transforme l'objet en tableau : ['titre' => ..., 'ville' => ...]
      vue('annonces/formulaire', ['erreurs' => [], 'valeurs' => (array) $annonce, 'annonce' => $annonce]);
      return;
    }

    $erreurs = validerAnnonce($_POST);
    if (!empty($erreurs)) {
      vue('annonces/formulaire', ['erreurs' => $erreurs, 'valeurs' => $_POST, 'annonce' => $annonce]);
      return;
    }

    $annonce->modifier(
      trim($_POST['titre']), $_POST['type_animal'], trim($_POST['ville']),
      $_POST['date_debut'], $_POST['date_fin'], trim($_POST['description'])
    );
    flash('Annonce modifiée.');
    rediriger("index.php?action=fiche&id=$annonce->id");
  }

  // Supprimer une de MES annonces (formulaire POST)
  public function supprimer(): void {
    $idUtilisateur = requireLogin();
    $annonce = $this->annonceDuProprietaire((int) ($_POST['id_annonce'] ?? 0), $idUtilisateur);

    // Une garde pourvue ou terminée garde son historique (candidatures, avis)
    if ($annonce->statut !== 'ouverte') {
      flash('Une annonce pourvue ou terminée ne peut plus être supprimée.', 'erreur');
      rediriger('index.php?action=mesAnnonces');
    }

    $annonce->supprimer();
    flash('Annonce supprimée.');
    rediriger('index.php?action=mesAnnonces');
  }

  // Mes annonces + mes statistiques de propriétaire
  public function mesAnnonces(): void {
    $idUtilisateur = requireLogin();
    $annonces = Annonce::parProprietaire($idUtilisateur);

    // Pour chaque garde terminée : est-ce que je peux encore noter le gardien ?
    $peutNoter = [];
    foreach ($annonces as $a) {
      $peutNoter[$a->id] = $a->statut === 'terminee'
        && !Avis::dejaNote($a->id, $idUtilisateur);
    }

    vue('annonces/mes_annonces', [
      'annonces'  => $annonces,
      'peutNoter' => $peutNoter,
      'stats'     => Annonce::statsProprietaire($idUtilisateur),
    ]);
  }

  // Les candidatures reçues pour une de MES annonces
  public function candidatures(): void {
    $idUtilisateur = requireLogin();
    $annonce = $this->annonceDuProprietaire((int) ($_GET['id'] ?? 0), $idUtilisateur);

    $candidatures = Candidature::parAnnonce($annonce->id);
    // On ajoute la note moyenne de chaque gardien
    foreach ($candidatures as $c) {
      $c->note_gardien = Avis::noteMoyenne($c->id_gardien);
    }

    vue('annonces/candidatures', [
      'annonce'      => $annonce,
      'candidatures' => $candidatures,
    ]);
  }

  // Valider un gardien (formulaire POST depuis la liste des candidatures)
  public function valider(): void {
    $idUtilisateur = requireLogin();

    $candidature = Candidature::trouver((int) ($_POST['id_candidature'] ?? 0));
    if ($candidature === null) {
      flash("Cette candidature n'existe pas.", 'erreur');
      rediriger('index.php?action=mesAnnonces');
    }

    $annonce = $this->annonceDuProprietaire($candidature->id_annonce, $idUtilisateur);

    // Règle métier : un seul gardien par annonce
    if ($annonce->statut !== 'ouverte') {
      flash('Un gardien a déjà été choisi pour cette annonce.', 'erreur');
      rediriger("index.php?action=candidatures&id=$annonce->id");
    }

    $annonce->cloturer($candidature->id);
    flash('Gardien validé ! Les autres candidatures ont été refusées.');
    rediriger("index.php?action=candidatures&id=$annonce->id");
  }

  // Clôture manuelle d'une garde (pourvue → terminée)
  public function terminer(): void {
    $idUtilisateur = requireLogin();
    $annonce = $this->annonceDuProprietaire((int) ($_POST['id_annonce'] ?? 0), $idUtilisateur);

    if ($annonce->statut !== 'pourvue') {
      flash('Seule une garde pourvue peut être terminée.', 'erreur');
      rediriger('index.php?action=mesAnnonces');
    }

    $annonce->terminer();
    flash('Garde terminée. Vous pouvez maintenant noter votre gardien.');
    rediriger('index.php?action=mesAnnonces');
  }

  // Récupère une annonce et vérifie qu'elle appartient bien à l'utilisateur.
  // Sinon : message d'erreur et retour à "Mes annonces".
  private function annonceDuProprietaire(int $idAnnonce, int $idUtilisateur): Annonce {
    $annonce = Annonce::trouver($idAnnonce);
    if ($annonce === null || $annonce->id_proprietaire !== $idUtilisateur) {
      flash("Vous n'avez pas accès à cette annonce.", 'erreur');
      rediriger('index.php?action=mesAnnonces');
    }
    return $annonce;
  }
}
