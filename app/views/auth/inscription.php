<h2>Inscription</h2>

<?php require __DIR__ . '/../layout/erreurs.php'; ?>

<form method="post" action="index.php?action=inscription" class="formulaire">
  <label>Nom</label>
  <input type="text" name="nom" required
         value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

  <label>Email</label>
  <input type="email" name="email" required
         value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

  <label>Mot de passe (8 caractères minimum)</label>
  <input type="password" name="password" required minlength="8">

  <label>Confirmer le mot de passe</label>
  <input type="password" name="confirmation" required minlength="8">

  <button type="submit">Créer mon compte</button>
</form>

<p>Déjà inscrit ? <a href="index.php?action=connexion">Connectez-vous</a></p>
