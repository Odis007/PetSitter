<?php require ROOT . '/app/views/layout/header.php'; ?>

<h2>Noter <?= e($cible->nom) ?></h2>
<p>Pour la garde « <?= e($annonce->titre) ?> »</p>

<?php require ROOT . '/app/views/layout/erreurs.php'; ?>

<form method="post" action="index.php?action=noter&id=<?= $annonce->id ?>" class="formulaire">
  <?= Securite::champ() ?>

  <label>Note</label>
  <select name="note" required>
    <option value="">-- Choisir --</option>
    <?php for ($i = 5; $i >= 1; $i--): ?>
      <option value="<?= $i ?>" <?= $note === (string) $i ? 'selected' : '' ?>>
        <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?> (<?= $i ?>/5)
      </option>
    <?php endfor; ?>
  </select>

  <label>Commentaire (facultatif, 500 caractères maximum)</label>
  <textarea name="commentaire" rows="4" maxlength="500"><?= e($commentaire) ?></textarea>

  <button type="submit">Envoyer mon avis</button>
</form>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
