<?php

// Fonctions utilisées pour l'affichage

// Affiche une page complète : en-tête + vue demandée + pied de page.
// Exemple : vue('annonces/liste', ['annonces' => $annonces]);
function vue(string $nom, array $donnees = []): void {
  // extract transforme ['annonces' => ...] en variable $annonces
  extract($donnees);
  require __DIR__ . '/../views/layout/header.php';
  require __DIR__ . "/../views/$nom.php";
  require __DIR__ . '/../views/layout/footer.php';
}

// Renvoie le badge coloré d'un statut (annonce ou candidature)
function badgeStatut(string $statut): string {
  $libelle = match ($statut) {
    'ouverte'    => 'Ouverte',
    'pourvue'    => 'Pourvue',
    'terminee'   => 'Terminée',
    'en_attente' => 'En attente',
    'acceptee'   => 'Acceptée',
    'refusee'    => 'Refusée',
  };
  return '<span class="badge badge--' . $statut . '">' . $libelle . '</span>';
}

// Transforme une date MySQL (2026-10-22) en date française (22/10/2026)
function dateFr(string $date): string {
  return date('d/m/Y', strtotime($date));
}

// Emoji affiché à la place d'une photo, selon le type d'animal
function emojiAnimal(string $type): string {
  return match ($type) {
    'Chat'  => '🐱',
    'Chien' => '🐶',
    default => '🐰',
  };
}

// Première lettre d'un nom en majuscule (pour les ronds "avatar")
function initiale(string $nom): string {
  return mb_strtoupper(mb_substr($nom, 0, 1));
}

// Période de garde lisible : "15 - 22 avril (8 jours)"
function periode(string $debut, string $fin): string {
  $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
           'août', 'septembre', 'octobre', 'novembre', 'décembre'];
  $d = strtotime($debut);
  $f = strtotime($fin);
  $jours = (int) round(($f - $d) / 86400) + 1; // 86400 secondes = 1 jour

  if ($d === $f) {
    $texte = date('j', $d) . ' ' . $mois[date('n', $d)];
  } elseif (date('n', $d) === date('n', $f)) {
    $texte = date('j', $d) . ' - ' . date('j', $f) . ' ' . $mois[date('n', $f)];
  } else {
    $texte = date('j', $d) . ' ' . $mois[date('n', $d)] . ' - '
           . date('j', $f) . ' ' . $mois[date('n', $f)];
  }

  return $texte . ' (' . $jours . ($jours > 1 ? ' jours)' : ' jour)');
}

// Raccourcit un texte trop long : "Passer deux fois par jour pour…"
function extrait(?string $texte, int $longueur = 60): string {
  return mb_strimwidth($texte ?? '', 0, $longueur, '…');
}

// Affiche une note moyenne, ou "Pas encore noté"
function afficherNote(?float $note): string {
  if ($note === null) {
    return 'Pas encore noté';
  }
  return '★ ' . number_format($note, 1, ',', '') . ' / 5';
}
