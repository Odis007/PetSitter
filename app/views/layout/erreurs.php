<?php // Affiche la liste $erreurs d'un formulaire (si elle n'est pas vide) ?>
<?php if (!empty($erreurs)): ?>
  <ul class="erreurs">
    <?php foreach ($erreurs as $erreur): ?>
      <li><?= htmlspecialchars($erreur) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
