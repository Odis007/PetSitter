<h2>Noter <?= htmlspecialchars($cible->nom) ?></h2>
<p>Pour la garde « <?= htmlspecialchars($annonce->titre) ?> »</p>

<?php require __DIR__ . '/../layout/erreurs.php'; ?>

<form method="post" action="index.php?action=noter&id_annonce=<?= $annonce->id ?>" class="formulaire">
  <label>Note</label>
  <div class="etoiles">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <label>
        <input type="radio" name="note" value="<?= $i ?>"
          <?= (int) ($_POST['note'] ?? 0) === $i ? 'checked' : '' ?>>
        <?= $i ?> ★
      </label>
    <?php endfor; ?>
  </div>

  <label>Commentaire (facultatif)</label>
  <textarea name="commentaire" rows="4" maxlength="500"><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>

  <button type="submit">Envoyer mon avis</button>
</form>
