<?php
// Page actuelle, pour colorer le bon lien du menu
$page = $_GET['action'] ?? 'accueil';
$connecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titre ?? 'PetSitter') ?> - PetSitter</title>
  <!-- Polices Google : Outfit pour les titres, Inter pour le texte -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="entete">
  <div class="conteneur entete-interieur">
    <a href="index.php" class="logo">
      <span class="logo-icone">🐾</span><span>PetSitter<span class="logo-local">Local</span></span>
    </a>

    <nav class="menu">
      <a href="index.php" class="<?= $page === 'accueil' ? 'actif' : '' ?>">Accueil</a>
      <a href="index.php?action=liste"
         class="<?= in_array($page, ['liste', 'detail'], true) ? 'actif' : '' ?>">Les annonces</a>

      <?php if ($connecte): ?>
        <a href="index.php?action=mes_annonces"
           class="<?= in_array($page, ['mes_annonces', 'recues'], true) ? 'actif' : '' ?>">Mes annonces</a>
        <a href="index.php?action=mes_candidatures"
           class="<?= $page === 'mes_candidatures' ? 'actif' : '' ?>">Mes candidatures</a>
        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
          <a href="index.php?action=admin" class="<?= $page === 'admin' ? 'actif' : '' ?>">Admin</a>
        <?php endif; ?>
      <?php else: ?>
        <a href="index.php#comment">Comment ça marche ?</a>
      <?php endif; ?>
    </nav>

    <div class="compte">
      <?php if ($connecte): ?>
        <a href="index.php?action=profil&id=<?= (int) $_SESSION['user_id'] ?>" class="compte-nom">
          <span class="avatar"><?= Affichage::initiale($_SESSION['user_nom']) ?></span>
          <?= e($_SESSION['user_nom']) ?>
        </a>
        <!-- Déconnexion en POST avec jeton : un simple lien serait attaquable (CSRF) -->
        <form method="post" action="index.php?action=deconnexion" class="inline">
          <?= Securite::champ() ?>
          <button type="submit" class="lien-discret">Déconnexion</button>
        </form>
        <a href="index.php?action=creer" class="btn btn--corail">Publier une annonce</a>
      <?php else: ?>
        <a href="index.php?action=connexion">Connexion</a>
        <a href="index.php?action=inscription" class="btn btn--corail">S'inscrire</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Les pages "pleine largeur" (accueil, annonces) gèrent elles-mêmes leurs marges -->
<main class="<?= empty($pleineLargeur) ? 'contenu' : '' ?>">
  <div class="conteneur">
    <?php if (!empty($_SESSION['flash_erreur'])): ?>
      <p class="flash flash--erreur"><?= e($_SESSION['flash_erreur']) ?></p>
      <?php unset($_SESSION['flash_erreur']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash'])): ?>
      <p class="flash flash--succes"><?= e($_SESSION['flash']) ?></p>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
  </div>
