<?php require ROOT . '/app/views/layout/header.php'; ?>

<h2>Profil de <?= e($utilisateur->nom) ?></h2>

<div class="stats">
  <div class="stat">
    <strong><?= e(Affichage::note($moyenne)) ?></strong>
    <span>note moyenne</span>
  </div>
  <div class="stat">
    <strong><?= $nbGardes ?></strong>
    <span>garde(s) réalisée(s)</span>
  </div>
</div>

<h3>Avis reçus</h3>

<?php if (empty($avis)): ?>
  <p class="vide">Pas encore d'avis.</p>
<?php endif; ?>

<?php foreach ($avis as $av): ?>
  <div class="avis">
    <p>
      <strong><?= str_repeat('★', $av->note) . str_repeat('☆', 5 - $av->note) ?></strong>
      par <?= e($av->auteur) ?>
      — <?= e($av->annonce_titre) ?>, le <?= Affichage::date($av->date) ?>
    </p>
    <?php if ($av->commentaire): ?>
      <p><?= nl2br(e($av->commentaire)) ?></p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php require ROOT . '/app/views/layout/footer.php'; ?>
