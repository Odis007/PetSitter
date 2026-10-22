<h2>Connexion</h2>

<?php require __DIR__ . '/../layout/erreurs.php'; ?>

<form method="post" action="index.php?action=connexion" class="formulaire">
  <label>Email</label>
  <input type="email" name="email" required
         value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

  <label>Mot de passe</label>
  <input type="password" name="password" required>

  <button type="submit">Se connecter</button>
</form>

<p>Pas encore de compte ? <a href="index.php?action=inscription">Inscrivez-vous</a></p>
