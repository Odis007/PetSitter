<?php require ROOT . '/app/views/layout/header.php'; ?>

<!-- Une seule vue pour CRÉER et MODIFIER : le contrôleur fournit
     $data (valeurs des champs), $erreurs, $actionUrl et $bouton -->
<h2><?= e($titre) ?></h2>

<?php require ROOT . '/app/views/layout/erreurs.php'; ?>

<form method="post" action="<?= e($actionUrl) ?>" class="formulaire">
  <?= Securite::champ() ?>

  <label>Titre</label>
  <input type="text" name="titre" value="<?= e($data['titre']) ?>" maxlength="120" required>

  <label>Animal</label>
  <select name="animal" required>
    <option value="">-- Choisir --</option>
    <?php foreach (Annonce::ANIMAUX as $type): ?>
      <option value="<?= e($type) ?>" <?= $data['animal'] === $type ? 'selected' : '' ?>>
        <?= e($type) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Ville</label>
  <input type="text" name="ville" value="<?= e($data['ville']) ?>" required>

  <label>Date de début</label>
  <input type="date" name="date_debut" value="<?= e($data['date_debut']) ?>" required>
  <label>Date de fin</label>
  <input type="date" name="date_fin" value="<?= e($data['date_fin']) ?>" required>

  <label>Description des besoins (10 caractères minimum)</label>
  <textarea name="description" rows="5" required><?= e($data['description']) ?></textarea>

  <div class="actions">
    <button type="submit"><?= e($bouton) ?></button>
    <a href="index.php?action=mes_annonces">Annuler</a>
  </div>
</form>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
