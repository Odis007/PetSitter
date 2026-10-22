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
  <title>PetSitter Local</title>
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
         class="<?= in_array($page, ['liste', 'rechercher', 'fiche']) ? 'actif' : '' ?>">Les annonces</a>

      <?php if ($connecte): ?>
        <a href="index.php?action=mesAnnonces"
           class="<?= in_array($page, ['mesAnnonces', 'candidatures']) ? 'actif' : '' ?>">Mes annonces</a>
        <a href="index.php?action=mesCandidatures"
           class="<?= $page === 'mesCandidatures' ? 'actif' : '' ?>">Mes candidatures</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="index.php?action=admin" class="<?= $page === 'admin' ? 'actif' : '' ?>">Admin</a>
        <?php endif; ?>
      <?php else: ?>
        <a href="index.php#comment">Comment ça marche ?</a>
      <?php endif; ?>
    </nav>

    <div class="compte">
      <?php if ($connecte): ?>
        <a href="index.php?action=profil&id=<?= $_SESSION['user_id'] ?>" class="compte-nom">
          <span class="avatar"><?= initiale($_SESSION['nom']) ?></span>
          <?= htmlspecialchars($_SESSION['nom']) ?>
        </a>
        <a href="index.php?action=deconnexion" class="lien-discret">Déconnexion</a>
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
  <div class="conteneur"><?php afficherFlash(); ?></div>
