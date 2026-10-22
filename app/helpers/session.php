<?php

// Fonctions liées à la session : connexion, messages, redirections

// Envoie le navigateur vers une autre page et arrête le script
function rediriger(string $url): void {
  header("Location: $url");
  exit;
}

// Renvoie l'id de l'utilisateur connecté,
// ou l'envoie vers la page de connexion s'il ne l'est pas
function requireLogin(): int {
  if (!isset($_SESSION['user_id'])) {
    flash('Connectez-vous pour accéder à cette page.', 'erreur');
    rediriger('index.php?action=connexion');
  }
  return $_SESSION['user_id'];
}

// Comme requireLogin, mais réservé à l'administrateur
function requireAdmin(): int {
  $id = requireLogin();
  if ($_SESSION['role'] !== 'admin') {
    flash("Cette page est réservée à l'administrateur.", 'erreur');
    rediriger('index.php');
  }
  return $id;
}

// Range un message en session pour l'afficher sur la page suivante
// $type vaut 'succes' (vert) ou 'erreur' (rouge)
function flash(string $message, string $type = 'succes'): void {
  $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Affiche le message (une seule fois) puis l'efface
function afficherFlash(): void {
  if (empty($_SESSION['flash'])) {
    return;
  }
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
  echo '<p class="flash flash--' . $flash['type'] . '">'
     . htmlspecialchars($flash['message']) . '</p>';
}
