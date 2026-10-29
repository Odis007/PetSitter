<?php require ROOT . '/app/views/layout/header.php'; ?>

<!-- Page d'erreur : 400, 403, 404 ou 405 (voir app/core/Erreur.php) -->
<div class="page-erreur">
  <p class="page-erreur-code"><?= (int) $code ?></p>
  <h2><?= e($message) ?></h2>
  <p><a href="index.php" class="btn btn--corail">Retour à l'accueil</a></p>
</div>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
