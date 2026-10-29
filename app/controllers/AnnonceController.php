<?php
// app/controllers/AnnonceController.php

class AnnonceController
{
    // GET : page d'accueil
    public function accueil(): void
    {
        $annonces       = Annonce::recentes(3);
        $meilleurMembre = Avis::meilleurMembre();
        $avis           = Avis::tous();
        $nbAvis         = count($avis);
        $derniersAvis   = array_slice($avis, 0, 3);

        $titre = 'Accueil';
        $pleineLargeur = true;   // la page occupe toute la largeur de l'écran
        require ROOT . '/app/views/accueil.php';
    }

    // GET : liste publique avec filtres
    public function liste(): void
    {
        $filtreAnimal = trim($_GET['animal'] ?? '');
        $filtreVille  = trim($_GET['ville'] ?? '');
        $filtreDate   = trim($_GET['date'] ?? '');
        $filtreUrgent = !empty($_GET['urgent']);
        $filtreTri    = ($_GET['tri'] ?? '') === 'recentes' ? 'recentes' : 'proches';

        // Une date mal formée est simplement ignorée
        if ($filtreDate !== '' && self::lireDate($filtreDate) === null) {
            $filtreDate = '';
        }

        $annonces = Annonce::rechercher($filtreAnimal, $filtreVille, $filtreDate, $filtreUrgent, $filtreTri);

        $titre = 'Annonces ouvertes';
        $pleineLargeur = true;
        require ROOT . '/app/views/annonces/liste.php';
    }

    // GET : fiche d'une annonce
    public function detail(): void
    {
        $annonce = $this->chargerAnnonce();                  // 404 si introuvable
        $idUtilisateur = AuthController::utilisateurId();    // 0 si visiteur
        $estProprio = $idUtilisateur !== 0 && $annonce->estProprietaire($idUtilisateur);

        // Ce que l'utilisateur peut faire sur cette annonce (décidé ICI, pas dans la vue)
        $aPostule = $idUtilisateur !== 0 && Candidature::existe($annonce->id, $idUtilisateur);
        $peutPostuler = $idUtilisateur !== 0 && !$estProprio && $annonce->estOuverte() && !$aPostule;
        $nbCandidatures = $estProprio ? count(Candidature::parAnnonce($annonce->id)) : 0;

        $peutNoter = false;
        if ($idUtilisateur !== 0 && $annonce->statut === 'terminee') {
            $peutNoter = Avis::cibleDe($annonce, $idUtilisateur) !== null
                && !Avis::existe($annonce->id, $idUtilisateur);
        }

        $noteProprietaire = Avis::noteMoyenne($annonce->id_proprietaire);

        $titre = $annonce->titre;
        require ROOT . '/app/views/annonces/detail.php';
    }

    // GET : affiche le formulaire / POST : traite le formulaire
    public function creer(): void
    {
        AuthController::exigerConnexion();

        $erreurs = [];
        $data = self::donneesVides();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Securite::verifier();
            $data = self::lireFormulaire();
            $erreurs = self::valider($data);

            if (!$erreurs) {
                $id = Annonce::creer($data, AuthController::utilisateurId());
                $_SESSION['flash'] = 'Annonce publiée.';
                redirect('detail', ['id' => $id]);
            }
        }

        $titre = 'Publier une annonce';
        $actionUrl = 'index.php?action=creer';
        $bouton = 'Publier';
        require ROOT . '/app/views/annonces/formulaire.php';
    }

    public function modifier(): void
    {
        AuthController::exigerConnexion();
        $annonce = $this->chargerAnnoncePourProprietaire();

        $erreurs = [];
        $data = [
            'titre'       => $annonce->titre,
            'animal'      => $annonce->animal,
            'description' => $annonce->description,
            'ville'       => $annonce->ville,
            'date_debut'  => $annonce->date_debut,
            'date_fin'    => $annonce->date_fin,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Securite::verifier();
            $data = self::lireFormulaire();
            $erreurs = self::valider($data);

            if (!$erreurs) {
                Annonce::modifier($annonce->id, $data);
                $_SESSION['flash'] = 'Annonce modifiée.';
                redirect('detail', ['id' => $annonce->id]);
            }
        }

        $titre = 'Modifier l\'annonce';
        $actionUrl = 'index.php?action=modifier&id=' . $annonce->id;
        $bouton = 'Enregistrer';
        require ROOT . '/app/views/annonces/formulaire.php';
    }

    // POST uniquement : une suppression ne se fait jamais par un simple lien (GET)
    public function supprimer(): void
    {
        AuthController::exigerConnexion();
        Securite::exigerPost();

        $annonce = $this->chargerAnnoncePourProprietaire();
        Annonce::supprimer($annonce->id);

        $_SESSION['flash'] = 'Annonce supprimée.';
        redirect('mes_annonces');
    }

    // POST : le propriétaire déclare la garde terminée (annonce pourvue -> terminée)
    public function terminer(): void
    {
        AuthController::exigerConnexion();
        Securite::exigerPost();

        $annonce = $this->chargerAnnoncePourProprietaire(false);
        if ($annonce->statut !== 'pourvue') {
            $_SESSION['flash_erreur'] = 'Seule une annonce pourvue peut être terminée.';
            redirect('detail', ['id' => $annonce->id]);
        }

        Annonce::terminer($annonce->id);
        $_SESSION['flash'] = 'Garde terminée : vous pouvez maintenant laisser un avis.';
        redirect('detail', ['id' => $annonce->id]);
    }

    public function mesAnnonces(): void
    {
        AuthController::exigerConnexion();
        $idUtilisateur = AuthController::utilisateurId();

        $annonces = Annonce::parProprietaire($idUtilisateur);
        $stats = Annonce::statsProprietaire($idUtilisateur);

        // Pour chaque garde terminée : puis-je encore noter mon gardien ?
        $peutNoter = [];
        foreach ($annonces as $a) {
            $peutNoter[$a->id] = $a->statut === 'terminee'
                && Avis::cibleDe($a, $idUtilisateur) !== null
                && !Avis::existe($a->id, $idUtilisateur);
        }

        $titre = 'Mes annonces';
        require ROOT . '/app/views/annonces/mes_annonces.php';
    }

    // ---------- Méthodes privées ----------

    private function chargerAnnonce(): Annonce
    {
        $id = (int) ($_GET['id'] ?? 0);
        $annonce = Annonce::trouver($id);

        if ($annonce === null) {
            http_response_code(404);
            exit('Annonce introuvable.');
        }
        return $annonce;
    }

    // Charge l'annonce ET vérifie que l'utilisateur connecté en est le propriétaire.
    // $exigerModifiable = false pour "terminer" (l'annonce est alors pourvue, pas ouverte).
    private function chargerAnnoncePourProprietaire(bool $exigerModifiable = true): Annonce
    {
        $annonce = $this->chargerAnnonce();

        if (!$annonce->estProprietaire(AuthController::utilisateurId())) {
            http_response_code(403);
            exit('Accès interdit : ce n\'est pas votre annonce.');
        }
        if ($exigerModifiable && !$annonce->estModifiable()) {
            $_SESSION['flash_erreur'] = 'Une annonce déjà pourvue ou terminée ne peut plus être modifiée.';
            redirect('detail', ['id' => $annonce->id]);
        }
        return $annonce;
    }

    private static function donneesVides(): array
    {
        return [
            'titre' => '', 'animal' => '', 'description' => '',
            'ville' => '', 'date_debut' => '', 'date_fin' => '',
        ];
    }

    // Ne retient QUE les champs attendus (jamais $_POST en bloc)
    private static function lireFormulaire(): array
    {
        $data = [];
        foreach (array_keys(self::donneesVides()) as $champ) {
            $data[$champ] = trim($_POST[$champ] ?? '');
        }
        return $data;
    }

    private static function valider(array $d): array
    {
        $erreurs = [];

        if ($d['titre'] === '' || mb_strlen($d['titre']) > 120) {
            $erreurs[] = 'Le titre est obligatoire (120 caractères maximum).';
        }
        if (!in_array($d['animal'], Annonce::ANIMAUX, true)) {
            $erreurs[] = 'Choisissez un type d\'animal dans la liste.';
        }
        if (mb_strlen($d['description']) < 10) {
            $erreurs[] = 'La description doit faire au moins 10 caractères.';
        }
        if ($d['ville'] === '') {
            $erreurs[] = 'La ville est obligatoire.';
        }

        $debut = self::lireDate($d['date_debut']);
        $fin   = self::lireDate($d['date_fin']);

        if ($debut === null || $fin === null) {
            $erreurs[] = 'Les dates sont obligatoires et doivent être valides.';
        } else {
            if ($debut < new DateTimeImmutable('today')) {
                $erreurs[] = 'La date de début ne peut pas être dans le passé.';
            }
            if ($fin < $debut) {
                $erreurs[] = 'La date de fin doit être après la date de début.';
            }
        }
        return $erreurs;
    }

    // Retourne un objet date, ou null si la chaîne n'est pas une vraie date AAAA-MM-JJ
    private static function lireDate(string $valeur): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $valeur);
        return ($date && $date->format('Y-m-d') === $valeur) ? $date : null;
    }
}
