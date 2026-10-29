<?php require ROOT . '/app/views/layout/header.php'; ?>

<h2>Inscription</h2>

<?php require ROOT . '/app/views/layout/erreurs.php'; ?>

<form method="post" action="index.php?action=inscription" class="formulaire">
  <?= Securite::champ() ?>

  <label>Nom</label>
  <input type="text" name="nom" value="<?= e($nom) ?>" required>

  <label>Email</label>
  <input type="email" name="email" value="<?= e($email) ?>" required>

  <label>Mot de passe (8 caractères minimum)</label>
  <input type="password" name="mot_de_passe" required minlength="8">

  <button type="submit">Créer mon compte</button>
</form>

<p>Déjà inscrit ? <a href="index.php?action=connexion">Connectez-vous</a></p>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
