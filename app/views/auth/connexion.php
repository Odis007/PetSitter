<?php require ROOT . '/app/views/layout/header.php'; ?>

<h2>Connexion</h2>

<?php if ($erreur): ?>
  <ul class="erreurs"><li><?= e($erreur) ?></li></ul>
<?php endif; ?>

<form method="post" action="index.php?action=connexion" class="formulaire">
  <?= Securite::champ() ?>

  <label>Email</label>
  <input type="email" name="email" value="<?= e($email) ?>" required>

  <label>Mot de passe</label>
  <input type="password" name="mot_de_passe" required>

  <button type="submit">Se connecter</button>
</form>

<p>Pas encore de compte ? <a href="index.php?action=inscription">Inscrivez-vous</a></p>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
