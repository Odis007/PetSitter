<?php

// Fonctions qui vérifient les formulaires.
// Chacune renvoie un tableau d'erreurs : tableau vide = tout est correct.

function validerAnnonce(array $donnees): array {
  $erreurs = [];

  if (trim($donnees['titre'] ?? '') === '') {
    $erreurs[] = 'Le titre est obligatoire.';
  }
  if (!in_array($donnees['type_animal'] ?? '', ['Chat', 'Chien', 'Autre'])) {
    $erreurs[] = "Choisissez un type d'animal.";
  }
  if (trim($donnees['ville'] ?? '') === '') {
    $erreurs[] = 'La ville est obligatoire.';
  }
  if (empty($donnees['date_debut']) || empty($donnees['date_fin'])) {
    $erreurs[] = 'Les deux dates sont obligatoires.';
  } elseif ($donnees['date_fin'] < $donnees['date_debut']) {
    $erreurs[] = 'La date de fin doit être après la date de début.';
  } elseif ($donnees['date_debut'] < date('Y-m-d')) {
    $erreurs[] = 'La date de début ne peut pas être dans le passé.';
  }
  if (trim($donnees['description'] ?? '') === '') {
    $erreurs[] = 'Décrivez les besoins de votre animal.';
  }

  return $erreurs;
}

function validerInscription(array $donnees): array {
  $erreurs = [];

  if (trim($donnees['nom'] ?? '') === '') {
    $erreurs[] = 'Le nom est obligatoire.';
  }
  if (!filter_var($donnees['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "L'adresse email n'est pas valide.";
  }
  if (strlen($donnees['password'] ?? '') < 8) {
    $erreurs[] = 'Le mot de passe doit faire au moins 8 caractères.';
  }
  if (($donnees['password'] ?? '') !== ($donnees['confirmation'] ?? '')) {
    $erreurs[] = 'Les deux mots de passe ne sont pas identiques.';
  }

  return $erreurs;
}

function validerAvis(array $donnees): array {
  $erreurs = [];

  $note = (int) ($donnees['note'] ?? 0);
  if ($note < 1 || $note > 5) {
    $erreurs[] = 'Choisissez une note entre 1 et 5.';
  }
  if (strlen($donnees['commentaire'] ?? '') > 500) {
    $erreurs[] = 'Le commentaire ne doit pas dépasser 500 caractères.';
  }

  return $erreurs;
}
