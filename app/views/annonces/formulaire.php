<?php
// Le même formulaire sert à CRÉER et à MODIFIER une annonce.
// $annonce vaut null pour une création, et contient l'annonce pour une modification.
// $valeurs contient les valeurs à afficher dans les champs.
$modification = $annonce !== null;
$url = $modification
  ? "index.php?action=modifier&id=$annonce->id"
  : 'index.php?action=creer';
?>

<h2><?= $modification ? 'Modifier mon annonce' : 'Publier une annonce' ?></h2>

<?php require __DIR__ . '/../layout/erreurs.php'; ?>

<form method="post" action="<?= $url ?>" class="formulaire">
  <label>Titre</label>
  <input type="text" name="titre" required
         value="<?= htmlspecialchars($valeurs['titre'] ?? '') ?>">

  <label>Type d'animal</label>
  <select name="type_animal">
    <?php foreach (['Chat', 'Chien', 'Autre'] as $t): ?>
      <option value="<?= $t ?>"
        <?= ($valeurs['type_animal'] ?? '') === $t ? 'selected' : '' ?>>
        <?= $t ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Ville</label>
  <input type="text" name="ville" required
         value="<?= htmlspecialchars($valeurs['ville'] ?? '') ?>">

  <label>Du</label>
  <input type="date" name="date_debut" required
         value="<?= htmlspecialchars($valeurs['date_debut'] ?? '') ?>">
  <label>Au</label>
  <input type="date" name="date_fin" required
         value="<?= htmlspecialchars($valeurs['date_fin'] ?? '') ?>">

  <label>Description des besoins</label>
  <textarea name="description" rows="5" required><?= htmlspecialchars($valeurs['description'] ?? '') ?></textarea>

  <div class="actions">
    <button type="submit"><?= $modification ? 'Enregistrer' : 'Publier' ?></button>
    <?php if ($modification): ?>
      <a href="index.php?action=fiche&id=<?= $annonce->id ?>">Annuler</a>
    <?php endif; ?>
  </div>
</form>
